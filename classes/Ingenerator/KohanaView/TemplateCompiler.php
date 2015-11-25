<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView;

/**
 * The TemplateCompiler takes a plain PHP template string and processes it to add automatic variable escaping within
 * PHP short echo tags, before returning the compiled template. You can optionally prefix your variables to mark that
 * they should not be escaped, or manually echo them from a full PHP code block.
 *
 * For example, the template:
 *
 *    <h1><?=$view->title;?></h1>
 *    <p><?=!$partial;?></p>
 *    <?php echo $stuff;?>
 *
 * Will compile to:
 *
 *    <h1><?=HTML::chars($view->title);?></h1>
 *    <p><?=$partial;?></p>
 *    <?php echo $stuff;?>
 *
 * The raw output prefix and escape method are configurable via the options array passed to the constructor.
 *
 * @package Ingenerator\KohanaView
 */
class TemplateCompiler
{

    /**
     * @var array
     */
    protected $options = [
        'raw_output_prefix' => '!',
        'escape_method'     => 'HTML::chars',
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Compile a string containing a PHP template, automatically escaping variables that are echoed in PHP short tags,
     * and return the compiled PHP string.
     *
     * @param string $source
     *
     * @return string
     * @throws \InvalidArgumentException if the template is empty or invalid
     */
    public function compile($source)
    {
        if ( ! $source) {
            throw new \InvalidArgumentException('Cannot compile empty template');
        }

        return preg_replace_callback('/<\?=(.+?)(;|\?>)/s', [$this, 'compilePhpShortTag'], $source);
    }

    /**
     * @param string[] $matches
     *
     * @return string
     */
    protected function compilePhpShortTag($matches)
    {
        $var               = trim($matches[1]);
        $terminator        = $matches[2];
        $escape_method     = $this->options['escape_method'];
        $raw_output_prefix = $this->options['raw_output_prefix'];

        if ($this->startsWith($var, $raw_output_prefix)) {
            // Remove prefix and echo unescaped
            $compiled = '<?='.trim(substr($var, strlen($raw_output_prefix))).';';
        } elseif ($this->startsWith($var, '//')) {
            // Echo an empty string to prevent the comment causing a parse error
            $compiled = "<?='';$var;";

        } elseif ($this->startsWith($var, $escape_method)) {
            // Try to help user to avoid accidental double-escaping
            throw new \InvalidArgumentException(
                "Invalid implicit double-escape in template - remove $escape_method from {$matches[0]} or mark as raw"
            );

        } else {
            // Escape the value before echoing
            $compiled = "<?={$escape_method}($var);";
        }

        if ($terminator === '?>') {
            $compiled .= '?>';
        }

        return $compiled;
    }

    /**
     * @param string $string
     * @param string $prefix
     *
     * @return bool
     */
    protected function startsWith($string, $prefix)
    {
        return (strncmp($string, $prefix, strlen($prefix)) === 0);
    }

}
