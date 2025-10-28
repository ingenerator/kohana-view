<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\NestedParentView;
use RuntimeException;

/**
 * Provides a base class for all views that are intended to be the main view on a page, to
 * allow the use of the PageLayoutRenderer to dynamically wrap the rendered output in a separate
 * NestedParentView when appropriate.
 *
 * This also allows the page content view to have access to the containing page - for example
 * to set the title or otherwise interact with the <head> and <meta> parts of the page.
 */
abstract class AbstractPageContentView extends AbstractNestedChildView
{
    public NestedParentView $page {
        get => $this->getUltimatePageView();
    }

    final protected function var_page(): never
    {
        throw new RuntimeException('Call to legacy '.__METHOD__);
    }
}
