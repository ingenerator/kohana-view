<?php

namespace test\unit\Renderer;

use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\PageLayoutRenderer;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
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
    /**
     * @var SimpleRendererStub
     */
    protected $renderer;

    /**
     * @var Request
     */
    protected $request;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            PageLayoutRenderer::class,
            $this->newSubject()
        );
    }

    #[TestWith([['is_ajax' => true]])]
    #[TestWith([['is_ajax' => false]])]
    #[TestWith([null])]
    public function test_it_renders_just_content_for_all_requests_when_use_layout_explicit_false(
        $request,
    ) {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : null;

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
        $request,
    ) {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : null;
        $subject = $this->newSubject();
        $subject->setUseLayout(true);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());

        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_no_request()
    {
        $this->request = null;
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $subject = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_request_not_ajax()
    {
        $this->request = new IsAjaxRequestStub(false);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $subject = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_just_content_when_request_is_ajax()
    {
        $this->request = new IsAjaxRequestStub(true);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView());
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame(
            '<Content#A/>',
            $this->newSubject()->render($content)
        );
    }

    public static function provider_render_chain()
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
        $use_layout,
        $expect,
    ) {
        $page = new DummyPageLayoutView();
        $sidebar_template = new DummyIntermediateLayoutView($page);
        $second_template = new DummyIntermediateLayoutView($sidebar_template);
        $content = new DummyNestedChildView($second_template);
        $this->renderer->registerViews(
            ['A' => $page, 'B' => $sidebar_template, 'C' => $second_template, 'D' => $content]
        );
        $subject = $this->newSubject();
        $subject->setUseLayout($use_layout);

        $this->assertEquals($expect, $subject->render($content));
    }

    protected function setUp(): void
    {
        $this->renderer = new SimpleRendererStub();
    }

    protected function newSubject()
    {
        return new PageLayoutRenderer(
            $this->renderer,
            $this->request
        );
    }

    protected function assertRendersContentOnly(PageContentView $view, $actual_output)
    {
        $this->assertSame(
            '<Content#'.spl_object_hash($view).'/>',
            $actual_output
        );
    }

    protected function assertRendersContentInLayout(
        PageLayoutView $layout,
        PageContentView $content,
        $actual_output,
    ) {
        $this->assertSame(
            '<Layout#'.spl_object_hash($layout).">\n"
            .'<Content#'.spl_object_hash($content).'/>'
            ."\n</Layout#".spl_object_hash($layout).'>',
            $actual_output
        );
    }
}

class SimpleRendererStub implements Renderer
{
    protected $expected_views = [];

    public function registerViews($views)
    {
        foreach ($views as $key => $view) {
            $hash = spl_object_hash($view);
            $this->expected_views[$hash] = $key;
        }
    }

    /**
     * @return string
     */
    public function render(ViewModel $view)
    {
        $hash = spl_object_hash($view);
        Assert::assertArrayHasKey(
            $hash,
            $this->expected_views,
            'Unregistered view '.$view::class
        );
        $id_letter = $this->expected_views[$hash];
        if ($view instanceof DummyPageContentView) {
            return "<Content#$id_letter/>";
        }
        if ($view instanceof DummyPageLayoutView) {
            /* @noinspection PhpUndefinedFieldInspection */
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

class IsAjaxRequestStub extends Request
{
    public function __construct(private readonly bool $is_ajax)
    {
    }

    public function is_ajax(): bool
    {
        return $this->is_ajax;
    }
}
