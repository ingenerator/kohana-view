<?php

namespace e2e\examples\src\DropAbstractViewModelConstructor;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class DescendantViewWithNeededConstructorCall extends IntermediateViewWithConstructor
{

    public function __construct(public readonly MyService $something)
    {
        parent::__construct();
    }
}

class IntermediateViewWithConstructor extends AbstractViewModel
{

    protected string $foo;

    public function __construct()
    {
        $this->foo = 'bar';
    }
}
