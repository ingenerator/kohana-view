<?php

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

/**
 * Thrown when attempting to access a view variable that is not defined.
 */
class UndefinedViewVarException extends BadMethodCallException
{
    /**
     * @param string $view_class
     * @param string $var_name
     */
    public static function forClassAndVar($view_class, $var_name): static
    {
        return new static(
            "$view_class does not define a '$var_name' field"
        );
    }
}
