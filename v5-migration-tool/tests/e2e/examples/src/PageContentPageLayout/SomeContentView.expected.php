<?php

namespace My\Application\View;

use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView;
use My\Application\SomeCustomClass;

/**
 * @property-read NestedParentView $page
 */
class SomeContentView extends AbstractPageContentView
{
    public function __construct(
        NestedParentView $page,
        private readonly SomeCustomClass $my_dependency,
    ) {
        parent::__construct($page);
    }

    public function getLayoutView(): NestedParentView
    {
        return $this->page;
    }
}
