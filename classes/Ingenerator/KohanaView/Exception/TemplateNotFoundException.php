<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;

/**
 * Thrown when a template cannot be found
 *
 * @package Ingenerator\KohanaView\Exception
 */
class TemplateNotFoundException extends \InvalidArgumentException
{

    /**
     * @param string $path
     *
     * @return static
     */
    public static function forFullPath($path)
    {
        return new static(
            "Failed to include template '$path'"
        );
    }

    /**
     * @param string $rel_path
     *
     * @return static
     */
    public static function forSourcePath($rel_path)
    {
        return new static(
            "Cannot find template source file '$rel_path'"
        );
    }
}
