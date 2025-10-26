<?php

namespace My\Application\OptionalDisplayVariables;

use Ingenerator\KohanaView\ViewModelProperty;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ViewWithDefaultVariables extends AbstractViewModel
{
    #[ViewModelProperty(is_displayable: true, is_optional: true)]
    public protected(set) string $some = 'thing';
    #[ViewModelProperty(is_displayable: true, is_optional: true)]
    public protected(set) mixed $other = false;
}
