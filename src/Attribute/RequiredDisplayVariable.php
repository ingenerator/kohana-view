<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Attribute;

use Attribute;

/**
 * Marks that $variables passed to ->display() must include a value for this property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RequiredDisplayVariable implements DisplayVariableAttribute
{
    public function canPassToDisplay(): true
    {
        return true;
    }

    public function mustPassToDisplay(): bool
    {
        return true;
    }
}
