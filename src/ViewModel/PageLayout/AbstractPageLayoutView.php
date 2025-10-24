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
     */
    public function setTitle($title): void
    {
        $this->variables['title'] = $title;
    }

    /**
     * @param string $html
     */
    public function setBodyHTML($html): void
    {
        $this->variables['body_html'] = $html;
    }

    public function setChildHtml($html): void
    {
        $this->variables['body_html'] = $html;
    }
}
