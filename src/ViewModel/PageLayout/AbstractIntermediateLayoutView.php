<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\NestedParentView;

abstract class AbstractIntermediateLayoutView extends AbstractNestedChildView implements NestedParentView
{
    /**
     * @var string set at rendering time by the PageLayoutRenderer
     */
    public protected(set) string $child_html;

    public function setBodyHtml(string $html): void
    {
        $this->child_html = $html;
    }
}
