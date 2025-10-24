<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

/**
 * Thrown when attempting to assign variables to a view model directly.
 */
class InvalidViewVarAssignmentException extends BadMethodCallException
{
    public static function forReadOnlyVar(string $view_class, string $var_name): static
    {
        return new static(
            $view_class.' variables are read-only, cannot assign '.$var_name
        );
    }
}
