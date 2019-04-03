<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;


/**
 * Thrown when a required view variable has not been assigned before use
 *
 * @package Ingenerator\KohanaView\Exception
 */
class UnassignedViewVarException extends \BadMethodCallException
{

    /**
     * @param string $view_class
     * @param string $var_name
     * @param string $hint
     *
     * @return static
     */
    public static function forVariable($view_class, $var_name, $hint)
    {
        return new static(
            \sprintf(
                'Call %s::display(["%s" => "%s"]) before rendering a %s view',
                $view_class,
                $var_name,
                $hint,
                $view_class
            )
        );
    }


}
