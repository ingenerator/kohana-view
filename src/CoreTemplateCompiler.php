<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use Ingenerator\KohanaView\Exception\InvalidTemplateContentException;
use InvalidArgumentException;

use function array_key_exists;
use function sprintf;

final readonly class CoreTemplateCompiler implements TemplateCompiler
{
    public function __construct(array $options = [])
    {
        if (array_key_exists('escape_method', $options)) {
            throw new InvalidArgumentException('The escape_method option has been removed, escaping is now a renderer concern');
        }
    }

    public function compile(string $source): string
    {
        if ($source === '') {
            throw InvalidTemplateContentException::forEmptyTemplate();
        }

        if (preg_match('/<?php echo/', $source)) {
            throw InvalidTemplateContentException::hasLegacyPhpEcho();
        }

        return preg_replace_callback('/<\?=(.+?)(;|\?>)/s', $this->compilePhpShortTag(...), $source);
    }

    /**
     * @param string[] $matches
     */
    private function compilePhpShortTag(array $matches): string
    {
        $var = trim($matches[1]);
        $terminator = $matches[2];

        if (str_starts_with($var, '//')) {
            // Echo an empty string to prevent the comment causing a parse error
            $compiled = "<?='';$var;";
        } elseif (str_starts_with($var, 'HTML::chars')) {
            throw InvalidTemplateContentException::containsImplicitDoubleEscape(
                'HTML::chars',
                $matches[0]
            );
        } elseif (str_starts_with($var, '!')) {
            throw InvalidTemplateContentException::hasLegacyRawEscapePrefix($matches[0]);
        } elseif (str_starts_with($var, '$renderer->escape(')) {
            // They are already escaping, no need to escape again
            $compiled = '<?='.$var.';';
        } else {
            // Escape the value (if required) before echoing
            $compiled = sprintf('<?=$renderer->escape(%s);', $var);
        }

        if ($terminator === '?>') {
            $compiled .= '?>';
        }

        return $compiled;
    }
}
