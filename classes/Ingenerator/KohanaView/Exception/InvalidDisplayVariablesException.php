<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;

/**
 * Thrown when the application attempts to pass invalid variables to a view's display
 * method.
 *
 * @package Ingenerator\KohanaView\Exception
 */
class InvalidDisplayVariablesException extends \InvalidArgumentException
{

    /**
     * @param string   $view_class
     * @param string[] $errors
     *
     * @return static
     */
    public static function passedToDisplay($view_class, $errors)
    {
        return new static(
            \sprintf(
                "Invalid variables provided to %s::display()\n%s",
                $view_class,
                ' - '.\implode("\n - ", $errors)
            )
        );
    }
}
