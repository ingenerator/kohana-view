<?php

namespace Ingenerator\KohanaView\Exception;

use UnexpectedValueException;

use function sprintf;

/**
 * Thrown when a TemplateSpecifyingView does not return a valid template name.
 */
class UnspecifiedTemplateNameException extends UnexpectedValueException
{
    public static function forEmptyValue(string $view_class): static
    {
        return new static(
            $view_class.'::getTemplateName() must return a template name, empty value returned'
        );
    }

    /**
     * @param string $view_class
     * @param string $template
     */
    public static function forNonStringValue($view_class, $template): static
    {
        return new static(
            sprintf(
                '%s::getTemplateName() must return a string template name, %s value returned',
                $view_class,
                get_debug_type($template)
            )
        );
    }
}
