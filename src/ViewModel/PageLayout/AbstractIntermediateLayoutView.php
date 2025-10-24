<?php

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\NestedParentView;

/**
 * @property-read string $child_html
 */
abstract class AbstractIntermediateLayoutView extends AbstractNestedChildView implements NestedParentView
{
    /**
     * @var string set at rendering time by the PageLayoutRenderer
     */
    protected $child_html;

    public function setBodyHtml(string $html): void
    {
        $this->child_html = $html;
    }

    protected function var_child_html()
    {
        return $this->child_html;
    }
}
