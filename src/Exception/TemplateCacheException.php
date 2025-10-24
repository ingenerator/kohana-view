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
     */
    public static function cannotCreateDirectory($path): static
    {
        return new static(
            "Cannot create template cache directory in '$path'"
        );
    }

    /**
     * @param string $path
     */
    public static function pathNotWriteable($path): static
    {
        return new static("Cannot write to compiled template path '$path'");
    }
}
