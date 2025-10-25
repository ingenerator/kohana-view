<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $foo
 * @property-read int $other A custom value
 */
class SimpleViewWithVariables extends AbstractViewModel
{
    public protected(set) string $foo;
    /**
     * A custom value
     */
    public protected(set) int $other;
    public protected(set) mixed $unknown;
}
