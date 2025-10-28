<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Exception;

use UnexpectedValueException;

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
}
