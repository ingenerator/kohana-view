<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class SimpleViewWithVariables extends AbstractViewModel
{
    public protected(set) string $foo;
    /**
     * some property we have info about
     */
    public protected(set) int $other;
    public protected(set) mixed $unknown;
    /**
     * With typed info
     *
     * @var \My\Application[]
     */
    public protected(set) array $applications;
}
