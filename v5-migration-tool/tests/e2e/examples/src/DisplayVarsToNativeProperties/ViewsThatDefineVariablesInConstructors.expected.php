<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class SomeChildView extends AbstractViewModel
{

    public protected(set) mixed $foo;
    /**
     * Some property defined in a method
     */
    public protected(set) string $bar;
    public function __construct()
    {
        // Note we'll leave the assignment in (for now at least) so users can see what it was
        $this->bar = null;
    }
}
