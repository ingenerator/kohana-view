<?php

namespace My\Application\OptionalDisplayVariables;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read string $some
 */
class ViewWithDefaultVariables extends AbstractViewModel
{
    protected array $default_variables = [
        'some' => 'thing',
        'other' => false,
    ];

}
