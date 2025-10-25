<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $foo
 */
class ViewWithVariableAssignments extends AbstractViewModel
{
    protected $variables = [
        'foo' => null,
    ];

    public function setFoo(string $foo): void
    {
        $this->variables['foo'] = $foo;
    }


}
