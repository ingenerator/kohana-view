<?php

namespace Ingenerator\KohanaView\Renderer;

use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Request;

/**
 * Renders a NestedChildView and - when appropriate - renders the generated output inside a tree of NestedParentView.
 * By default it will render the parents on normal requests (or when no request is present) but not on AJAX requests.
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
 */
class PageLayoutRenderer
{
    protected ?bool $use_layout = null;

    public function __construct(
        protected Renderer $view_renderer,
        protected ?Request $current_request = null,
    ) {
    }

    public function render(NestedChildView $content_view): string
    {
        $content = $this->view_renderer->render($content_view);
        if ( ! $this->shouldUseLayout()) {
            return $content;
        }

        return $this->renderParent($content_view->getParentView(), $content);
    }

    protected function renderParent(NestedParentView $parent, string $content_html): string
    {
        $parent->setBodyHTML($content_html);

        if ($parent instanceof NestedChildView) {
            return $this->render($parent);
        }

        return $this->view_renderer->render($parent);
    }

    protected function shouldUseLayout(): bool
    {
        return $this->use_layout ?? ! ($this->current_request && $this->current_request->is_ajax());
    }

    /**
     * Configure whether to always wrap the content in the layout (TRUE), never (FALSE) or automatically for
     * non-AJAX requests (NULL).
     */
    public function setUseLayout(?bool $use_layout): void
    {
        $this->use_layout = $use_layout;
    }
}
