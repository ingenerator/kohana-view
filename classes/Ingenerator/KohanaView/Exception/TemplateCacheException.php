<?php

namespace Ingenerator\KohanaView\Exception;

use RuntimeException;

/**
 * Thrown when there are problems caching a template.
 */
class TemplateCacheException extends RuntimeException
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
