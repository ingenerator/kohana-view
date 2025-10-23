<?php

namespace My\Application\Controller;

use Ingenerator\KohanaView\ViewModel\NestedChildView;

class BaseController
{
    protected function respondPageContent(NestedChildView $content_view)
    {
        $renderer = $this->dependencies->get('kohanaview.renderer.page_layout');
        $this->response->body($renderer->render($content_view));
    }
}
