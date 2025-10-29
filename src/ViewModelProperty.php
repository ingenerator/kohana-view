<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ViewModelProperty
{
    public function __construct(
        public bool $is_displayable,
        /**
         * Indicates that the property is not required when calling ->display().
         */
        public bool $is_optional = false,
    ) {
    }
}
