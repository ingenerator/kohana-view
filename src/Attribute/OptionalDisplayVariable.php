<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Attribute;

use Attribute;

/**
 * Marks that $variables passed to ->display() MAY include a value for this property. If not, it will reset to default.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class OptionalDisplayVariable implements DisplayVariableAttribute
{
    public function canPassToDisplay(): true
    {
        return true;
    }

    public function mustPassToDisplay(): false
    {
        return false;
    }
}
