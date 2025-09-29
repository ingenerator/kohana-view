<?php

namespace Ingenerator\KohanaView\ViewModel;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ViewModelProperty
{

    public function __construct(
        public bool $is_displayable
    )
    {

    }

}
