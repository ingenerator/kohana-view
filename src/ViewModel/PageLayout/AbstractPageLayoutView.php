<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedParentView;

/**
 * Provides a base class for all views that are intended to provide a complete HTML template that will
 * contain some body html content - eg for the global site layout etc.
 *
 * It is commonly used together with a AbstractPageContentView or NestedChildView but note you can always
 * still create an instance of this page layout and display any html string directly for simpler cases.
 */
abstract class AbstractPageLayoutView extends AbstractViewModel implements NestedParentView
{
    /**
     * The content to display in the body HTML area.
     */
    public protected(set) string $body_html;
    /**
     * The page title.
     */
    public protected(set) string $title;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setBodyHTML(string $html): void
    {
        $this->body_html = $html;
    }

    public function setChildHtml(string $html): void
    {
        $this->body_html = $html;
    }
}
