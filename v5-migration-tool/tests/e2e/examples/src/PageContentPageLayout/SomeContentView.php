<?php

namespace My\Application\View;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
use My\Application\SomeCustomClass;

/**
 * @property-read PageLayoutView $page
 */
class SomeContentView extends AbstractPageContentView
{
    public function __construct(
        PageLayoutView $page,
        private readonly SomeCustomClass $my_dependency,
    ) {
        parent::__construct($page);
    }

    public function getLayoutView(): PageLayoutView
    {
        return $this->page;
    }
}
