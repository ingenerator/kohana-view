<?php

declare(strict_types=1);

namespace test\unit\Renderer;

use ErrorException;
use HTML;
use Ingenerator\KohanaView\Exception\TemplateNotFoundException;
use Ingenerator\KohanaView\OutputValue\UnescapedHtmlSafeString;
use Ingenerator\KohanaView\OutputValue\UnescapedSafeHtmlContent;
use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\HTMLRenderer;
use Ingenerator\KohanaView\TemplateManager;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewTemplateSelector;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\ViewModelDummy;
use UnexpectedValueException;

use function error_reporting;
use function Ingenerator\KohanaView\OutputValue\raw;
use function ob_get_level;
use function spl_object_hash;
use function uniqid;

class HTMLRendererTest extends TestCase
{
    protected TemplateManager $template_manager;

    protected ViewTemplateSelectorSpy $template_selector;

    protected vfsStreamDirectory $vfs_root;

    protected int $old_error_reporting;

    public function test_it_is_initialisable(): void
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(HTMLRenderer::class, $subject);
        $this->assertInstanceOf(Renderer::class, $subject);
    }

    public function test_it_selects_template_for_view(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass($view, 'Anything');
        $this->newSubject()->render($view);
        $this->template_selector->assertCalledOnceWith($view);
    }

    public function test_it_returns_template_output_string(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass($view, 'Any <?="string";?>');
        $this->assertSame(
            'Any string',
            $this->newSubject()->render($view),
        );
    }

    public function test_it_provides_view_as_variable_in_template_scope(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass($view, 'View:<?=spl_object_hash($view);?>');
        $this->assertSame(
            'View:'.spl_object_hash($view),
            $this->newSubject()->render($view),
        );
    }

    public function test_it_provides_renderer_as_variable_in_template_scope(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass($view, 'Renderer:<?=spl_object_hash($renderer);?>');
        $subject = $this->newSubject();
        $this->assertSame(
            'Renderer:'.spl_object_hash($subject),
            $subject->render($view),
        );
    }

    public function test_it_does_not_provide_access_to_this_in_template_scope(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass(
            $view,
            '<?=isset($this) ? \'Unexpected $this: \'.get_class($this).\':\'.spl_object_hash($this) : \'OK, no $this\';?>',
        );
        $this->assertSame(
            'OK, no $this',
            $this->newSubject()->render($view),
        );
    }

    public function test_it_does_not_provide_access_to_any_unexpected_variables_in_template_scope(): void
    {
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass(
            $view,
            '<?=implode("\n", array_keys(get_defined_vars()));?>',
        );
        $this->assertSame(
            "view\nrenderer\ntemplate",
            $this->newSubject()->render($view),
        );
    }

    public function test_it_does_not_allow_access_to_superglobals_in_template_scope(): never
    {
        // @todo: Find a way to prevent templates accessing superglobals - possibly needs to happen at compile stage
        $this->markTestIncomplete('Appears to be impossible to remove superglobals from template scope');
    }

    public function test_it_suppresses_template_output_and_clears_buffer_on_exception_during_render(): void
    {
        $ob_level_before = ob_get_level();
        $this->expectOutputRegex('/^$/');
        $view = new ViewModelDummy();
        $this->givenTemplateForViewClass($view, 'Stuff <?="that works";?> then <?php throw new \InvalidArgumentException("dammit");?>');
        try {
            $this->newSubject()->render($view);
            $this->fail('Expected exception to bubble from the template rendering phase');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('dammit', $e->getMessage(), 'Ensure it is the expected exception');
        }
        $this->assertSame($ob_level_before, ob_get_level(), 'Expect any internal output buffers to be cleared');
    }

    public function test_it_can_render_same_template_multiple_times_with_same_or_different_views(): void
    {
        $view_1 = new NumberViewModel();
        $this->givenTemplateForViewClass($view_1, 'Number<?=$view->number;?>');
        $view_2 = new NumberViewModel();
        $subject = $this->newSubject();
        $output = [];

        $view_1->display(['number' => 1]);
        $output[] = $subject->render($view_1);
        $view_1->display(['number' => 2]);
        $output[] = $subject->render($view_1);
        $view_2->display(['number' => 3]);
        $output[] = $subject->render($view_2);
        $this->assertSame(['Number1', 'Number2', 'Number3'], $output);
    }

    public function test_it_generates_error_if_template_is_not_found(): void
    {
        $view = new ViewModelDummy();
        $this->template_selector->registerTemplate($view::class, '/path/to/undefined/file');
        $subject = $this->newSubject();

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('path/to/undefined/file');
        $subject->render($view);
    }

    public function test_it_throws_if_inclusion_fails_even_with_error_reporting_off(): void
    {
        $view = new ViewModelDummy();
        $this->template_selector->registerTemplate($view::class, '/path/to/undefined/file');
        $subject = $this->newSubject();

        error_reporting(0);

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage('path/to/undefined/file');
        $subject->render($view);
    }

    public static function provider_escape(): iterable
    {
        return [
            'plain string with no escapable stuff' => [
                'foobar',
                'foobar',
            ],
            'plain string with HTML characters' => [
                'I am <injected>things</injected>',
                'I am &lt;injected&gt;things&lt;/injected&gt;',
            ],
            'numbers' => [
                15,
                '15',
            ],
            'Explicitly marked as safe with a class' => [
                new UnescapedHtmlSafeString('I am <p>rendered from known safe strings</p>'),
                'I am <p>rendered from known safe strings</p>',
            ],
            'Marked as safe via the raw function' => [
                raw('I am <p>rendered from known safe strings</p>'),
                'I am <p>rendered from known safe strings</p>',
            ],
            'Custom class that provides html content' => [
                // For example a DTO for a simple view component
                new readonly class('Things & Stuff') implements UnescapedSafeHtmlContent {
                    public function __construct(public string $caption)
                    {
                    }

                    public function renderSafeHtml(): string
                    {
                        return '<div class="panel"><h4>'.HTML::chars($this->caption).'</h4></div>';
                    }
                },
                '<div class="panel"><h4>Things &amp; Stuff</h4></div>',
            ],
        ];
    }

    #[DataProvider('provider_escape')]
    public function testItEscapesValuesUnlessTheyAreMarkedAsSafe(mixed $value, string $expect): void
    {
        $this->assertSame($expect, $this->newSubject()->escape($value));
    }

    protected function setUp(): void
    {
        $this->old_error_reporting = error_reporting();
        $this->template_selector = new ViewTemplateSelectorSpy();
        $this->vfs_root = vfsStream::setup('templates');
        $this->template_manager = new readonly class($this->vfs_root->url()) implements TemplateManager {
            public function __construct(
                private string $base_path)
            {
            }

            public function getPath(string $template_name): string
            {
                return $this->base_path.'/'.$template_name;
            }
        };
    }

    protected function tearDown(): void
    {
        error_reporting($this->old_error_reporting);
    }

    protected function newSubject(): HTMLRenderer
    {
        return new HTMLRenderer(
            $this->template_selector,
            $this->template_manager,
        );
    }

    private function givenTemplateForViewClass(ViewModel $view, string $content): void
    {
        $filename = uniqid('test-template').'.php';
        $this->template_selector->registerTemplate($view::class, $filename);
        $file = new vfsStreamFile($filename);
        $file->setContent($content);
        $this->vfs_root->addChild($file);
    }
}

class ViewTemplateSelectorSpy extends ViewTemplateSelector
{
    protected $calls = [];

    private array $template_map = [];

    public function registerTemplate(string $class, string $filename): void
    {
        $this->template_map[$class] = $filename;
    }

    #[Override]
    public function getTemplateName(ViewModel $view): string
    {
        $this->calls[] = $view;
        $template = $this->template_map[$view::class] ?? '';
        if ($template === '') {
            throw new UnexpectedValueException('No template mocked for '.$view::class);
        }

        return $template;
    }

    public function assertCalledOnceWith(ViewModel $view): void
    {
        Assert::assertSame([$view], $this->calls);
    }
}

class NumberViewModel extends AbstractViewModel
{
    public protected(set) mixed $number;
}
