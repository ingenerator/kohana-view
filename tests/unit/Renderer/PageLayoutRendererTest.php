<?php

namespace test\unit\Renderer;

use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\PageLayoutRenderer;
use Ingenerator\KohanaView\ViewModel;
use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Request;
use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;
use test\mock\ViewModel\PageLayout\DummyNestedChildView;
use test\mock\ViewModel\PageLayout\DummyPageContentView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;
use UnexpectedValueException;

use function spl_object_hash;

class PageLayoutRendererTest extends TestCase
{
    protected SimpleRendererStub $renderer;

    protected Request $request;

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(
            PageLayoutRenderer::class,
            $this->newSubject(),
        );
    }

    #[TestWith([['is_ajax' => true]])]
    #[TestWith([['is_ajax' => false]])]
    #[TestWith([null])]
    public function test_it_renders_just_content_for_all_requests_when_use_layout_explicit_false(
        ?array $request,
    ): void {
        $this->request = $this->stubRequest($request);

        $subject = $this->newSubject();
        $subject->setUseLayout(false);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());

        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame('<Content#A/>', $subject->render($content));
    }

    #[TestWith([['is_ajax' => true]])]
    #[TestWith([['is_ajax' => false]])]
    #[TestWith([null])]
    public function test_it_renders_layout_containing_content_for_all_requests_when_use_layout_explicit_true(
        ?array $request,
    ): void {
        $this->request = $this->stubRequest($request);
        $subject = $this->newSubject();
        $subject->setUseLayout(true);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());

        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_no_request(): void
    {
        $this->request = null;
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $subject = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_request_not_ajax(): void
    {
        $this->request = $this->stubRequest(['is_ajax' => false]);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $subject = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_just_content_when_request_is_ajax(): void
    {
        $this->request = $this->stubRequest(['is_ajax' => true]);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame(
            '<Content#A/>',
            $this->newSubject()->render($content),
        );
    }

    public static function provider_render_chain(): array
    {
        return [
            [
                true,
                "<Layout#A>\n"
                ."<Intermediate#B>\n"
                ."<Intermediate#C>\n"
                .'<Child#D/>'
                ."\n</Intermediate#C>"
                ."\n</Intermediate#B>"
                ."\n</Layout#A>",
            ],
            [
                false,
                '<Child#D/>',
            ],
        ];
    }

    #[DataProvider('provider_render_chain')]
    public function test_it_renders_full_chain_if_with_layout_or_only_first_child_if_not(
        bool $use_layout,
        string $expect,
    ): void {
        $page = new DummyPageLayoutView();
        $sidebar_template = new DummyIntermediateLayoutView($page);
        $second_template = new DummyIntermediateLayoutView($sidebar_template);
        $content = new DummyNestedChildView($second_template);
        $this->renderer->registerViews(
            ['A' => $page, 'B' => $sidebar_template, 'C' => $second_template, 'D' => $content],
        );
        $subject = $this->newSubject();
        $subject->setUseLayout($use_layout);

        $this->assertEquals($expect, $subject->render($content));
    }

    protected function setUp(): void
    {
        $this->renderer = new SimpleRendererStub();
    }

    protected function newSubject(): PageLayoutRenderer
    {
        return new PageLayoutRenderer(
            $this->renderer,
            $this->request,
        );
    }

    private function stubRequest(?array $request): ?Request
    {
        if ($request === null) {
            return null;
        }

        return new class(...$request) extends Request {
            public function __construct(private readonly bool $is_ajax)
            {
            }

            #[Override]
            public function is_ajax(): bool
            {
                return $this->is_ajax;
            }
        };
    }
}

class SimpleRendererStub implements Renderer
{
    protected $expected_views = [];

    public function registerViews($views): void
    {
        foreach ($views as $key => $view) {
            $hash = spl_object_hash($view);
            $this->expected_views[$hash] = $key;
        }
    }

    public function render(ViewModel $view): string
    {
        $hash = spl_object_hash($view);
        Assert::assertArrayHasKey(
            $hash,
            $this->expected_views,
            'Unregistered view '.$view::class,
        );
        $id_letter = $this->expected_views[$hash];
        if ($view instanceof DummyPageContentView) {
            return "<Content#$id_letter/>";
        }
        if ($view instanceof DummyPageLayoutView) {
            return "<Layout#$id_letter>\n".$view->body_html."\n</Layout#$id_letter>";
        }
        if ($view instanceof DummyNestedChildView) {
            return "<Child#$id_letter/>";
        }
        if ($view instanceof DummyIntermediateLayoutView) {
            return "<Intermediate#$id_letter>\n".$view->child_html."\n</Intermediate#$id_letter>";
        }

        throw new UnexpectedValueException('Don\'t know how to render '.$view::class);
    }
}
