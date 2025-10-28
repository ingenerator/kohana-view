<?php

declare(strict_types=1);

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
     * @param array{unexpected?: list<string>, missing?: list<string>} $error_groups
     */
    public static function passedToDisplay(string $view_class, array $error_groups): static
    {
        $errors = [];
        foreach ($error_groups as $key => $var_names) {
            $errors[] = ucfirst($key). ' vars: '.json_encode($var_names);
        }

        return new static(
            sprintf(
                "Invalid variables provided to %s::display()\n%s",
                $view_class,
                ' - '.implode("\n - ", $errors)
            )
        );
    }
}
