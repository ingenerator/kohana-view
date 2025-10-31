<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Marks that this property is calculated within the class, and cannot be provided through the ->display() method.
 */
final readonly class InternalDisplayVariable implements DisplayVariableAttribute
{
    public function canPassToDisplay(): false
    {
        return false;
    }

    public function mustPassToDisplay(): false
    {
        return false;
    }
}
