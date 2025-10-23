<?php

namespace Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;

abstract class AbstractNestedChildView extends AbstractViewModel implements NestedChildView
{
    public function __construct(
        protected NestedParentView $parent_view,
    ) {
        parent::__construct();
    }

    public function getParentView(): NestedParentView
    {
        return $this->parent_view;
    }

    public function getUltimatePageView(): NestedParentView
    {
        $parent = $this->getParentView();
        while ($parent instanceof NestedChildView) {
            $parent = $parent->getParentView();
        }

        return $parent;
    }
}
