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
use test\mock\ViewModel\PageLayout\DummyPageContentView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class PageLayoutRendererTest extends \PHPUnit_Framework_TestCase implements Renderer
{

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
    public function test_it_renders_just_content_for_all_requests_when_use_layout_explicit_false($request)
    {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : NULL;

        $subject = $this->newSubject();
        $subject->setUseLayout(FALSE);
        $view = new DummyPageContentView(new DummyPageLayoutView);
        $this->assertRendersContentOnly($view, $subject->render($view));
    }

    /**
     * @testWith [{"is_ajax": true}]
     *           [{"is_ajax": false}]
     *           [null]
     */
    public function test_it_renders_layout_containing_content_for_all_requests_when_use_layout_explicit_true($request)
    {
        $this->request = $request ? new IsAjaxRequestStub($request['is_ajax']) : NULL;
        $subject       = $this->newSubject();
        $subject->setUseLayout(TRUE);
        $content = new DummyPageContentView($layout = new DummyPageLayoutView);
        $this->assertRendersContentInLayout($layout, $content, $subject->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_no_request()
    {
        $this->request = NULL;
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $this->assertRendersContentInLayout($layout, $content, $this->newSubject()->render($content));
    }

    public function test_by_default_it_renders_layout_containing_content_when_request_not_ajax()
    {
        $this->request = new IsAjaxRequestStub(FALSE);
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $this->assertRendersContentInLayout($layout, $content, $this->newSubject()->render($content));
    }

    public function test_by_default_it_renders_just_content_when_request_is_ajax()
    {
        $this->request = new IsAjaxRequestStub(TRUE);
        $content       = new DummyPageContentView($layout = new DummyPageLayoutView);
        $this->assertRendersContentOnly($content, $this->newSubject()->render($content));
    }

    public function render(ViewModel $view)
    {
        $hash = spl_object_hash($view);
        if ($view instanceof DummyPageContentView) {
            return "Rendered:Content:$hash";
        } elseif ($view instanceof DummyPageLayoutView) {
            /** @noinspection PhpUndefinedFieldInspection */
            return "Rendered:Layout:$hash with ".$view->body_html;
        }

        throw new \UnexpectedValueException('I only know how to render PageContentView and PageLayoutView');
    }

    protected function newSubject()
    {
        return new PageLayoutRenderer(
            $this,
            $this->request
        );
    }

    protected function assertRendersContentOnly(PageContentView $view, $actual_output)
    {
        $this->assertSame(
            'Rendered:Content:'.spl_object_hash($view),
            $actual_output
        );
    }

    protected function assertRendersContentInLayout(
        PageLayoutView $layout,
        PageContentView $content,
        $actual_output
    ) {
        $this->assertSame(
            'Rendered:Layout:'.spl_object_hash($layout).' with Rendered:Content:'.spl_object_hash($content),
            $actual_output
        );
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
