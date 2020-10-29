<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\unit\Ingenerator\KohanaView\Renderer;


use Ingenerator\KohanaView\Exception\TemplateNotFoundException;
use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\HTMLRenderer;
use Ingenerator\KohanaView\TemplateManager;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewTemplateSelector;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\ViewModelDummy;

class HTMLRendererTest extends TestCase
{
    /**
     * @var TemplateManagerSpy
     */
    protected $template_manager;

    /**
     * @var ViewTemplateSelectorSpy
     */
    protected $template_selector;

    /**
     * @var vfsStreamDirectory
     */
    protected $vfs_root;

    /**
     * @var int
     */
    protected $old_error_reporting;

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(HTMLRenderer::class, $subject);
        $this->assertInstanceOf(Renderer::class, $subject);
    }

    public function test_it_selects_template_for_view()
    {
        $view = new ViewModelDummy;
        $this->newSubject()->render($view);
        $this->template_selector->assertCalledOnceWith($view);
    }

    public function test_it_locates_required_template()
    {
        $this->newSubject()->render(new ViewModelDummy);
        $this->template_manager->assertCalledOnceWith(ViewTemplateSelectorSpy::FIXED_TEMPLATE_NAME);
    }

    public function test_it_returns_template_output_string()
    {
        $this->givenTemplate('Any <?="string";?>');
        $this->assertSame(
            'Any string',
            $this->newSubject()->render(new ViewModelDummy)
        );
    }

    public function test_it_provides_view_as_variable_in_template_scope()
    {
        $this->givenTemplate('View:<?=spl_object_hash($view);?>');
        $view = new ViewModelDummy;
        $this->assertSame(
            'View:'.\spl_object_hash($view),
            $this->newSubject()->render($view)
        );
    }

    public function test_it_provides_renderer_as_variable_in_template_scope()
    {
        $this->givenTemplate('Renderer:<?=spl_object_hash($renderer);?>');
        $subject = $this->newSubject();
        $this->assertSame(
            'Renderer:'.\spl_object_hash($subject),
            $subject->render(new ViewModelDummy)
        );
    }

    public function test_it_does_not_provide_access_to_this_in_template_scope()
    {
        $this->givenTemplate(
            '<?=isset($this) ? \'Unexpected $this: \'.get_class($this).\':\'.spl_object_hash($this) : \'OK, no $this\';?>'
        );
        $this->assertSame(
            'OK, no $this',
            $this->newSubject()->render(new ViewModelDummy)
        );
    }

    public function test_it_does_not_provide_access_to_any_unexpected_variables_in_template_scope()
    {
        $this->givenTemplate(
            '<?=implode("\n", array_keys(get_defined_vars()));?>'
        );
        $this->assertSame(
            "view\nrenderer\ntemplate",
            $this->newSubject()->render(new ViewModelDummy)
        );
    }

    public function test_it_does_not_allow_access_to_superglobals_in_template_scope()
    {
        //@todo: Find a way to prevent templates accessing superglobals - possibly needs to happen at compile stage
        $this->markTestIncomplete('Appears to be impossible to remove superglobals from template scope');
    }

    public function test_it_suppresses_template_output_and_clears_buffer_on_exception_during_render()
    {
        $ob_level_before = \ob_get_level();
        $this->expectOutputRegex('/^$/');
        $this->givenTemplate('Stuff <?="that works";?> then <?php throw new \InvalidArgumentException("dammit");?>');
        try {
            $this->newSubject()->render(new ViewModelDummy);
            $this->fail('Expected exception to bubble from the template rendering phase');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('dammit', $e->getMessage(), 'Ensure it is the expected exception');
        }
        $this->assertSame($ob_level_before, \ob_get_level(), 'Expect any internal output buffers to be cleared');
    }

    public function test_it_can_render_same_template_multiple_times_with_same_or_different_views()
    {
        $this->givenTemplate('Number<?=$view->number;?>');
        $view_1  = new NumberViewModel;
        $view_2  = new NumberViewModel;
        $subject = $this->newSubject();
        $output  = [];

        $view_1->display(['number' => 1]);
        $output[] = $subject->render($view_1);
        $view_1->display(['number' => 2]);
        $output[] = $subject->render($view_1);
        $view_2->display(['number' => 3]);
        $output[] = $subject->render($view_2);
        $this->assertSame(['Number1', 'Number2', 'Number3'], $output);
    }

    public function test_it_generates_error_if_template_is_not_found()
    {
        $this->template_manager->setTemplatePath(vfsStream::url('/path/to/undefined/file'));

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage("path/to/undefined/file");

        $this->newSubject()->render(new ViewModelDummy);
    }

    public function test_it_throws_if_inclusion_fails_even_with_error_reporting_off()
    {
        \error_reporting(0);
        $this->template_manager->setTemplatePath(vfsStream::url('/path/to/undefined/file'));

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage("path/to/undefined/file");
        $this->newSubject()->render(new ViewModelDummy);
    }

    public function setUp(): void
    {
        $this->old_error_reporting = \error_reporting();
        $this->template_selector   = new ViewTemplateSelectorSpy;
        $this->template_manager    = new TemplateManagerSpy;
        $this->vfs_root            = vfsStream::setup('templates');
        $this->givenTemplate('Default');

        parent::__construct();
    }

    public function tearDown(): void
    {
        \error_reporting($this->old_error_reporting);
    }

    protected function newSubject()
    {
        return new HTMLRenderer(
            $this->template_selector,
            $this->template_manager
        );
    }

    protected function givenTemplate($content)
    {
        $filename = \uniqid('test-template').'.php';
        $file     = new vfsStreamFile($filename);
        $file->setContent($content);
        $this->vfs_root->addChild($file);
        $this->template_manager->setTemplatePath($file->url());
    }

}

class ViewTemplateSelectorSpy extends ViewTemplateSelector
{
    const FIXED_TEMPLATE_NAME = 'selected_template';

    protected $calls = [];

    public function getTemplateName(ViewModel $view)
    {
        $this->calls[] = $view;

        return static::FIXED_TEMPLATE_NAME;
    }

    public function assertCalledOnceWith(ViewModel $view)
    {
        \PHPUnit\Framework\Assert::assertSame([$view], $this->calls);
    }

}

class TemplateManagerSpy implements TemplateManager
{

    protected $calls = [];
    protected $template_path;

    public function setTemplatePath($path)
    {
        $this->template_path = $path;
    }

    public function getPath($template_name)
    {
        $this->calls[] = $template_name;

        return $this->template_path;
    }

    public function assertCalledOnceWith($template_name)
    {
        \PHPUnit\Framework\Assert::assertSame([$template_name], $this->calls);
    }

}

class NumberViewModel extends ViewModel\AbstractViewModel
{
    protected $variables = [
        'number' => 0,
    ];
}
