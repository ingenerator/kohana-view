<?php

namespace Ingenerator\KohanaView\Exception;

use InvalidArgumentException;

use function implode;
use function sprintf;

/**
 * Thrown when the application attempts to pass invalid variables to a view's display
 * method.
 */
class InvalidDisplayVariablesException extends InvalidArgumentException
{
    /**
     * @param string[] $errors
     */
    public static function passedToDisplay(string $view_class, array $errors): static
    {
        return new static(
            sprintf(
                "Invalid variables provided to %s::display()\n%s",
                $view_class,
                ' - '.implode("\n - ", $errors)
            )
        );
    }
}
