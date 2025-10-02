<?php

namespace Ingenerator\KohanaView\Exception;

use UnexpectedValueException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Thrown when a TemplateSpecifyingView does not return a valid template name
 *
 * @package Ingenerator\KohanaView\Exception
 */
class UnspecifiedTemplateNameException extends UnexpectedValueException
{
    /**
     * @param string $view_class
     *
     * @return static
     */
    public static function forEmptyValue($view_class)
    {
        return new static(
            $view_class.'::getTemplateName() must return a template name, empty value returned'
        );
    }

    /**
     * @param string $view_class
     * @param string $template
     *
     * @return static
     */
    public static function forNonStringValue($view_class, $template)
    {
        return new static(
            sprintf(
                '%s::getTemplateName() must return a string template name, %s value returned',
                $view_class,
                is_object($template) ? get_class($template) : gettype($template)
            )
        );
    }
}
