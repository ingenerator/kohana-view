<?php

namespace My\Application\OptionalDisplayVariables;

use Ingenerator\KohanaView\Attribute\OptionalDisplayVariable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ViewWithDefaultVariables extends AbstractViewModel
{
    #[OptionalDisplayVariable]
    public protected(set) string $some = 'thing';
    #[OptionalDisplayVariable]
    public protected(set) mixed $other = false;
}
