<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;


/**
 * Thrown when there are problems caching a template
 *
 * @package Ingenerator\KohanaView\Exception
 */
class TemplateCacheException extends \RuntimeException
{
    /**
     * @param string $path
     *
     * @return static
     */
    public static function cannotCreateDirectory($path)
    {
        return new static(
            "Cannot create template cache directory in '$path'"
        );
    }

    /**
     * @param string $path
     *
     * @return static
     */
    public static function pathNotWriteable($path)
    {
        return new static("Cannot write to compiled template path '$path'");
    }

}
