<?php

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedParentView;

/**
 * Provides a base class for all views that are intended to provide a complete HTML template that will
 * contain some body html content - eg for the global site layout etc.
 *
 * It is commonly used together with a AbstractPageContentView or NestedChildView but note you can always
 * still create an instance of this page layout and display any html string directly for simpler cases.
 *
 * @property-read string $body_html The content to display in the body HTML area
 * @property-read string $title The page title
 */
abstract class AbstractPageLayoutView extends AbstractViewModel implements NestedParentView
{
    /**
     * @var array
     */
    protected $variables = [
        'body_html' => null,
        'title' => null,
    ];

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->variables['title'] = $title;
    }

    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHTML($html)
    {
        $this->variables['body_html'] = $html;
    }

    public function setChildHtml($html)
    {
        $this->variables['body_html'] = $html;
    }
}
