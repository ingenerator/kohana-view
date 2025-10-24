<?php

namespace Ingenerator\KohanaView\Exception;

use InvalidArgumentException;

/**
 * Thrown when the content of a template is not valid for some reason.
 */
class InvalidTemplateContentException extends InvalidArgumentException
{
    public static function containsImplicitDoubleEscape(string $escape_method, string $source_fragment): static
    {
        return new static(
            "Invalid implicit double-escape in template - remove $escape_method from `$source_fragment` or mark as raw"
        );
    }

    public static function hasLegacyRawEscapePrefix($source_fragment): static
    {
        return new static(
            "Invalid legacy-style <?=! raw escape prefix in template, please change `$source_fragment` to use `<?=raw()`"
        );
    }

    public static function hasLegacyPhpEcho(): static
    {
        return new static(
            'Invalid legacy-style use of `<?php echo` to avoid automatic escaping : use `<?=raw()` instead'
        );
    }

    public static function forEmptyTemplate(): static
    {
        return new static('Cannot compile empty template');
    }
}
