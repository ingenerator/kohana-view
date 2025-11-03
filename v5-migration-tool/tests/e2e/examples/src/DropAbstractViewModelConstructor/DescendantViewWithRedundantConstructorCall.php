<?php

namespace e2e\examples\src\DropAbstractViewModelConstructor;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class DescendantViewWithRedundantConstructorCall extends IntermediateView
{

    public function __construct(public readonly MyService $something)
    {
        parent::__construct();
    }
}

class IntermediateView extends AbstractViewModel
{

}
