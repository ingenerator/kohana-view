<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ViewModelProperty
{
    public function __construct(
        public bool $is_displayable,
    ) {
    }
}
