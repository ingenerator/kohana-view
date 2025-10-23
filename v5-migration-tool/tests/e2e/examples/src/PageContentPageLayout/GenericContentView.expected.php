<?php

namespace My\Application\View;

use Ingenerator\KohanaView\ViewModel\NestedChildView;
use BadMethodCallException;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedParentView;

class GenericContentView extends AbstractViewModel implements NestedChildView
{
    public function getParentView(): NestedParentView
    {
        throw new BadMethodCallException();
    }
}
