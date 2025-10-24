<?php

namespace Ingenerator\KohanaView\Exception;

use InvalidArgumentException;

/**
 * Thrown when a template cannot be found.
 */
class TemplateNotFoundException extends InvalidArgumentException
{
    /**
     * @param string $path
     */
    public static function forFullPath($path): static
    {
        return new static(
            "Failed to include template '$path'"
        );
    }

    /**
     * @param string $rel_path
     */
    public static function forSourcePath($rel_path): static
    {
        return new static(
            "Cannot find template source file '$rel_path'"
        );
    }
}
