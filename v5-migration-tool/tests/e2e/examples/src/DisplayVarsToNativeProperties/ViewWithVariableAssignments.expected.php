<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ViewWithVariableAssignments extends AbstractViewModel
{
    public protected(set) string $foo;

    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }


}
