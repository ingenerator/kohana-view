<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;


/**
 * Thrown when attempting to assign variables to a view model directly
 *
 * @package Ingenerator\KohanaView\Exception
 */
class InvalidViewVarAssignmentException extends \BadMethodCallException
{

    /**
     * @param string $view_class
     * @param string $var_name
     *
     * @return static
     */
    public static function forReadOnlyVar($view_class, $var_name)
    {
        return new static(
            $view_class.' variables are read-only, cannot assign '.$var_name
        );
    }
}
