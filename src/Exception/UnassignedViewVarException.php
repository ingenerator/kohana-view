<?php

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

use function sprintf;

/**
 * Thrown when a required view variable has not been assigned before use.
 */
class UnassignedViewVarException extends BadMethodCallException
{
    public static function forVariable(string $view_class, string $var_name, string $hint): static
    {
        return new static(
            sprintf(
                'Call %s::display(["%s" => "%s"]) before rendering a %s view',
                $view_class,
                $var_name,
                $hint,
                $view_class
            )
        );
    }
}
