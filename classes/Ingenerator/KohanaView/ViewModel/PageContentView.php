<?php

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

/**
 * Interface for a view intended to be the main body view on a page.
 *
 * @see     AbstractPageContentView
 * @see     PageLayoutView
 *
 * @package Ingenerator\KohanaView\ViewModel
 */
interface PageContentView extends ViewModel
{
    /**
     * The page layout that this content view will be rendered into
     *
     * @return PageLayoutView
     */
    public function var_page();
}
