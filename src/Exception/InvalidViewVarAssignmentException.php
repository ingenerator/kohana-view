<?php

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

/**
 * Thrown when attempting to assign variables to a view model directly.
 */
class InvalidViewVarAssignmentException extends BadMethodCallException
{
    /**
     * @return static
     */
    public static function forReadOnlyVar(string $view_class, string $var_name)
    {
        return new static(
            $view_class.' variables are read-only, cannot assign '.$var_name
        );
    }
}
