<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $foo
 * @property-read int $other some property we have info about
 */
class SimpleViewWithVariables extends AbstractViewModel
{
    protected $variables = [
        'foo' => null,
        'other' => null,
        'unknown' => null,
    ];

}
