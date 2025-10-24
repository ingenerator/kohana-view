<?php

namespace Ingenerator\KohanaView\Exception;

use BadMethodCallException;

use function sprintf;

/**
 * Thrown when a required view variable has not been assigned before use.
 */
class UnassignedViewVarException extends BadMethodCallException
{
    /**
     * @param string $view_class
     * @param string $var_name
     * @param string $hint
     */
    public static function forVariable($view_class, $var_name, $hint): static
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
