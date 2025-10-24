<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Exception;

use InvalidArgumentException;

/**
 * Thrown when a template cannot be found.
 */
class TemplateNotFoundException extends InvalidArgumentException
{
    public static function forFullPath(string $path): static
    {
        return new static(
            "Failed to include template '$path'"
        );
    }

    public static function forSourcePath(string $rel_path): static
    {
        return new static(
            "Cannot find template source file '$rel_path'"
        );
    }
}
