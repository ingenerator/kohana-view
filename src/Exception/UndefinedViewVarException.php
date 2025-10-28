<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

/**
 * Thrown when attempting to access a view variable that is not defined.
 */
class UndefinedViewVarException extends BadMethodCallException
{
    public static function forClassAndVar(string $view_class, string $var_name): static
    {
        return new static(
            "$view_class does not define a '$var_name' field"
        );
    }
}
