<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;

/**
 * Thrown when attempting to access a view variable that is not defined
 *
 * @package Ingenerator\KohanaView\Exception
 */
class UndefinedViewVarException extends \BadMethodCallException
{

    /**
     * @param string $view_class
     * @param string $var_name
     *
     * @return static
     */
    public static function forClassAndVar($view_class, $var_name)
    {
        return new static(
            "$view_class does not define a '$var_name' field"
        );
    }
}
