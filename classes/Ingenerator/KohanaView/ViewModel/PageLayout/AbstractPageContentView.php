<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;

/**
 * Provides a base class for all views that are intended to be the main view on a page, to
 * allow the use of the PageLayoutRenderer to dynamically wrap the rendered output in a separate
 * PageLayoutView when appropriate.
 *
 * This also allows the page content view to have access to the containing page - for example
 * to set the title or otherwise interact with the <head> and <meta> parts of the page.
 *
 * @property-read PageLayoutView $page
 *
 * @package Ingenerator\KohanaView\ViewModel\PageLayout
 */
abstract class AbstractPageContentView extends AbstractViewModel implements PageContentView
{

    /**
     * @var PageLayoutView
     */
    protected $page_view;

    public function __construct(PageLayoutView $page)
    {
        $this->page_view = $page;
        parent::__construct();
    }

    /**
     * @return PageLayoutView
     */
    public function var_page()
    {
        return $this->page_view;
    }

}
