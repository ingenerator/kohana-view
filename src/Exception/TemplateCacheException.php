<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Exception;

use RuntimeException;

/**
 * Thrown when there are problems caching a template.
 */
class TemplateCacheException extends RuntimeException
{
    public static function cannotCreateDirectory(string $path): static
    {
        return new static(
            "Cannot create template cache directory in '$path'"
        );
    }

    public static function pathNotWriteable(string $path): static
    {
        return new static("Cannot write to compiled template path '$path'");
    }
}
