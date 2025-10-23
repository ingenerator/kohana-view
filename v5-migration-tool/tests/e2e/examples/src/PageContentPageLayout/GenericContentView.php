<?php

namespace My\Application\View;

use BadMethodCallException;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Ingenerator\KohanaView\ViewModel\PageContentView;

class GenericContentView extends AbstractViewModel implements PageContentView
{
    public function getParentView(): NestedParentView
    {
        throw new BadMethodCallException();
    }
}
