<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\Exception;


/**
 * Thrown when the content of a template is not valid for some reason
 *
 * @package Ingenerator\KohanaView\Exception
 */
class InvalidTemplateContentException extends \InvalidArgumentException
{
    /**
     * @param string $escape_method
     * @param string $source_fragment
     *
     * @return static
     */
    public static function containsImplicitDoubleEscape($escape_method, $source_fragment)
    {
        return new static(
            "Invalid implicit double-escape in template - remove $escape_method from `$source_fragment` or mark as raw"
        );
    }

    public static function hasLegacyRawEscapePrefix($source_fragment)
    {
        return new static(
            "Invalid legacy-style <?=! raw escape prefix in template, please change `$source_fragment` to use `<?=raw()`"
        );
    }

    public static function hasLegacyPhpEcho()
    {
        return new static(
            "Invalid legacy-style use of `<?php echo` to avoid automatic escaping : use `<?=raw()` instead"
        );
    }

    public static function forEmptyTemplate()
    {
        return new static('Cannot compile empty template');
    }

}
