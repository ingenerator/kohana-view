<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use Ingenerator\KohanaView\Exception\InvalidTemplateContentException;
use InvalidArgumentException;

use function array_key_exists;
use function array_merge;
use function preg_match;
use function preg_replace_callback;
use function sprintf;
use function trim;

/**
 * The TemplateCompiler takes a plain PHP template string and processes it to add automatic variable escaping within
 * PHP short echo tags, before returning the compiled template.
 *
 * For example, the template:
 *
 *    <h1><?=$view->title;?></h1>
 *
 * Will compile to:
 *
 *    <h1><?=$renderer->escape($view->title);?></h1>
 */
class TemplateCompiler
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        if (array_key_exists('escape_method', $options)) {
            throw new InvalidArgumentException('The escape_method option has been removed, escaping is now a renderer concern');
        }
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Compile a string containing a PHP template, automatically escaping variables that are echoed in PHP short tags,
     * and return the compiled PHP string.
     *
     * @throws InvalidArgumentException if the template is empty or invalid
     */
    public function compile(string $source): string
    {
        if ($source === '') {
            throw InvalidTemplateContentException::forEmptyTemplate();
        }

        if (preg_match('/<?php echo/', $source)) {
            throw InvalidTemplateContentException::hasLegacyPhpEcho();
        }

        return preg_replace_callback('/<\?=(.+?)(;|\?>)/s', [$this, 'compilePhpShortTag'], $source);
    }

    /**
     * @param string[] $matches
     */
    protected function compilePhpShortTag(array $matches): string
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
