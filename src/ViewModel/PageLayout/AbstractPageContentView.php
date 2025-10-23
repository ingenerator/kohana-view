<?php

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\NestedParentView;

/**
 * Provides a base class for all views that are intended to be the main view on a page, to
 * allow the use of the PageLayoutRenderer to dynamically wrap the rendered output in a separate
 * NestedParentView when appropriate.
 *
 * This also allows the page content view to have access to the containing page - for example
 * to set the title or otherwise interact with the <head> and <meta> parts of the page.
 *
 * @property-read NestedParentView $page
 */
abstract class AbstractPageContentView extends AbstractNestedChildView
{
    public function var_page(): NestedParentView
    {
        return $this->getUltimatePageView();
    }
}
