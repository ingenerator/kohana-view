<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Renderer;


use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;


/**
 * Renders a PageContentView and - when appropriate - renders the generated output inside a PageLayoutView. By
 * default it will render the layout on normal requests (or when no request is present) but not on AJAX requests.
 * This behaviour can be customised by calling the setUseLayout method.
 *
 * For example, from a controller:
 *
 *    public function action_login()
 *    {
 *       $layout  = new DefaultPageLayout;
 *       $content = new LoginView($layout);
 *       $renderer = new PageLayoutRenderer(new HTMLRenderer, $this->request);
 *       $this->response->body($renderer->render($content));
 *    }
 *
 * Obviously in real life the creation of the views and renderers would happen in your dependency container.
 *
 * @package Ingenerator\KohanaView\Renderer
 */
class PageLayoutRenderer
{
    /**
     * @var bool Whether to force (or not force) embedding the content in the layout
     */
    protected $use_layout;

    /**
     * @var Renderer
     */
    protected $view_renderer;

    /**
     * @var \Request
     */
    protected $current_request;

    public function __construct(Renderer $view_renderer, \Request $current_request = NULL)
    {
        $this->view_renderer   = $view_renderer;
        $this->current_request = $current_request;
    }

    /**
     * @param PageContentView $content_view
     *
     * @return string
     */
    public function render(PageContentView $content_view)
    {
        $content = $this->view_renderer->render($content_view);
        if ( ! $this->shouldUseLayout()) {
            return $content;
        }

        if ($content_view instanceof NestedChildView) {
            $parent = $content_view->getParentView();
        } else {
            $parent = $content_view->var_page();
        }

        return $this->renderParent($parent, $content);
    }

    /**
     * @param PageLayoutView $parent
     * @param string         $content
     *
     * @return string
     */
    protected function renderParent(PageLayoutView $parent, $content_html)
    {
        $parent->setBodyHTML($content_html);

        if ($parent instanceof NestedChildView) {
            return $this->render($parent);
        } else {
            return $this->view_renderer->render($parent);
        }
    }

    /**
     * @return bool
     */
    protected function shouldUseLayout()
    {
        if ($this->use_layout !== NULL) {
            return $this->use_layout;
        }

        if ($this->current_request AND $this->current_request->is_ajax()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Configure whether to always wrap the content in the layout (TRUE), never (FALSE) or automatically for
     * non-AJAX requests (NULL)
     *
     * @param bool $use_layout
     *
     * @return void
     */
    public function setUseLayout($use_layout)
    {
        $this->use_layout = $use_layout;
    }

}
