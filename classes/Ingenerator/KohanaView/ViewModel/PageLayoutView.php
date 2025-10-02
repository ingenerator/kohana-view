<?php

namespace Ingenerator\KohanaView\ViewModel;

use Ingenerator\KohanaView\ViewModel;

/**
 * Interface for a view that is intended to be the outer containing HTML template for a full page layout.
 *
 * @see     AbstractPageLayoutView
 * @see     PageContentView
 *
 * @package Ingenerator\KohanaView\ViewModel
 */
interface PageLayoutView extends ViewModel
{
    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHTML($html);
}
