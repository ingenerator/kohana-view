<?php

namespace e2e\examples\src\DropAbstractViewModelConstructor;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ViewWithRedundantConstructorCall extends AbstractViewModel
{
    private string $foo;

    public function __construct(public readonly MyService $something)
    {
        $this->foo = 'bar';
        parent::__construct();
    }
}
