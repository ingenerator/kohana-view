<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\unit\Ingenerator\KohanaView\Renderer;


use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\PageLayoutRenderer;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
use PHPUnit\Framework\Assert;
use test\mock\ViewModel\PageLayout\DummyNestedChildView;
use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;
use test\mock\ViewModel\PageLayout\DummyPageContentView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class PageLayoutRendererTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \test\unit\Ingenerator\KohanaView\Renderer\SimpleRendererStub
     */
    protected $renderer;

    /**
     * @var \Request
     */
    protected $request;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\Renderer\PageLayoutRenderer',
            $this->newSubject()
        );
    }

    /**
     * @testWith [{"is_ajax": true}]
     *           [{"is_ajax": false}]
     *           [null]
     */
    public function test_it_renders_just_content_for_all_requests_when_use_layout_explicit_false(
        $request
    ) {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : NULL;

        $subject = $this->newSubject();
        $subject->setUseLayout(FALSE);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView);

        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Content#A/>", $subject->render($content));
    }

    /**
     * @testWith [{"is_ajax": true}]
     *           [{"is_ajax": false}]
     *           [null]
     */
    public function test_it_renders_layout_containing_content_for_all_requests_when_use_layout_explicit_true(
        $request
    ) {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : NULL;
        $subject       = $this->newSubject();
        $subject->setUseLayout(TRUE);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView);

        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_no_request()
    {
        $this->request = NULL;
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $subject       = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_request_not_ajax()
    {
        $this->request = new IsAjaxRequestStub(FALSE);
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $subject       = $this->newSubject();
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame("<Layout#B>\n<Content#A/>\n</Layout#B>", $subject->render($content));

    }

    public function test_by_default_it_renders_just_content_when_request_is_ajax()
    {
        $this->request = new IsAjaxRequestStub(TRUE);
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $this->renderer->registerViews(['A' => $content, 'B' => $layout]);
        $this->assertSame(
            "<Content#A/>",
            $this->newSubject()->render($content)
        );
    }

    public function provider_render_chain()
    {
        return [
            [
                TRUE,
                "<Layout#A>\n"
                ."<Intermediate#B>\n"
                ."<Intermediate#C>\n"
                ."<Child#D/>"
                ."\n</Intermediate#C>"
                ."\n</Intermediate#B>"
                ."\n</Layout#A>"
            ],
            [
                FALSE,
                "<Child#D/>"
            ],
        ];
    }

    /**
     * @dataProvider provider_render_chain
     */
    public function test_it_renders_full_chain_if_with_layout_or_only_first_child_if_not(
        $use_layout,
        $expect
    ) {
        $page             = new DummyPageLayoutView;
        $sidebar_template = new DummyIntermediateLayoutView($page);
        $second_template  = new DummyIntermediateLayoutView($sidebar_template);
        $content          = new DummyNestedChildView($second_template);
        $this->renderer->registerViews(
            ['A' => $page, 'B' => $sidebar_template, 'C' => $second_template, 'D' => $content]
        );
        $subject = $this->newSubject();
        $subject->setUseLayout($use_layout);

        $this->assertEquals($expect, $subject->render($content));
    }

    protected function setUp()
    {
        $this->renderer = new SimpleRendererStub;
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
            '<Content#'.\spl_object_hash($view).'/>',
            $actual_output
        );
    }

    protected function assertRendersContentInLayout(
        PageLayoutView $layout,
        PageContentView $content,
        $actual_output
    ) {
        $this->assertSame(
            "<Layout#".\spl_object_hash($layout).">\n"
            ."<Content#".\spl_object_hash($content)."/>"
            ."\n</Layout#".\spl_object_hash($layout).">",
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
            $hash                        = \spl_object_hash($view);
            $this->expected_views[$hash] = $key;
        }
    }

    /**
     * @param ViewModel $view
     *
     * @return string
     */
    public function render(ViewModel $view)
    {
        $hash = \spl_object_hash($view);
        Assert::assertArrayHasKey(
            $hash,
            $this->expected_views,
            'Unregistered view '.\get_class($view)
        );
        $id_letter = $this->expected_views[$hash];
        if ($view instanceof DummyPageContentView) {
            return "<Content#$id_letter/>";
        } elseif ($view instanceof DummyPageLayoutView) {
            /** @noinspection PhpUndefinedFieldInspection */
            return "<Layout#$id_letter>\n".$view->body_html."\n</Layout#$id_letter>";
        } elseif ($view instanceof DummyNestedChildView) {
            return "<Child#$id_letter/>";
        } elseif ($view instanceof DummyIntermediateLayoutView) {
            return "<Intermediate#$id_letter>\n".$view->child_html."\n</Intermediate#$id_letter>";
        }

        throw new \UnexpectedValueException('Don\'t know how to render '.\get_class($view));
    }

}

class IsAjaxRequestStub extends \Request
{
    public function __construct($is_ajax)
    {
        $this->is_ajax = $is_ajax;
    }

    public function is_ajax()
    {
        return $this->is_ajax;
    }
}
