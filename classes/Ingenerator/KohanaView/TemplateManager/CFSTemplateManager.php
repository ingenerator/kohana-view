<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace Ingenerator\KohanaView\TemplateManager;

use Ingenerator\KohanaView\TemplateCompiler;
use Ingenerator\KohanaView\TemplateManager;

/**
 * Manages compilation of templates from view files located within the cascading file system. This allows extension
 * modules or applications to provide their own templates that are used in place of defaults provided by other modules.
 *
 * Templates will be dynamically compiled and cached to disk:
 *  * If the recompile_always option is TRUE, then once for every execution
 *  * If the recompile_always option is FALSE, then only if the compiled template does not yet exist
 *
 * @package Ingenerator\KohanaView\TemplateManager
 */
class CFSTemplateManager implements TemplateManager
{
    /**
     * @var string
     */
    protected $cache_dir;

    /**
     * @var CFSWrapper
     */
    protected $cascading_files;

    /**
     * @var array
     */
    protected $compiled_paths = [];

    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    /**
     * @var boolean
     */
    protected $recompile_always;

    /**
     * Valid options:
     * * cache_dir => the path where compiled templates will be cached
     * * recompile_always => whether to recompile each template on every execution,
     *
     * @param TemplateCompiler $compiler
     * @param array            $options
     * @param CFSWrapper       $cascading_files
     */
    public function __construct(TemplateCompiler $compiler, array $options, CFSWrapper $cascading_files = NULL)
    {
        $this->cascading_files  = $cascading_files ?: new CFSWrapper;
        $this->compiler         = $compiler;
        $this->cache_dir        = rtrim($options['cache_dir'], '/');
        $this->recompile_always = \Arr::get($options, 'recompile_always', FALSE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($template_name)
    {
        $compiled_path = $this->cache_dir.'/'.$template_name.'.php';

        if ($this->isCompileRequired($compiled_path)) {
            $source   = $this->requireSourceFileContent($template_name);
            $compiled = $this->compiler->compile($source);
            $this->writeFile($compiled_path, $compiled);
            $this->compiled_paths[$compiled_path] = TRUE;
        }

        return $compiled_path;
    }

    /**
     * @param string $compiled_path
     *
     * @return bool
     */
    protected function isCompileRequired($compiled_path)
    {
        if ($this->recompile_always AND ! isset($this->compiled_paths[$compiled_path])) {
            return TRUE;
        }

        return ! file_exists($compiled_path);
    }

    /**
     * @param string $template_name
     *
     * @return string
     */
    protected function requireSourceFileContent($template_name)
    {
        if ( ! $source_file = $this->cascading_files->find_file('views', $template_name)) {
            throw new \InvalidArgumentException("Cannot find template source file 'views/$template_name'");
        }

        return file_get_contents($source_file);
    }

    /**
     * @param string $compiled_path
     * @param string $compiled
     */
    protected function writeFile($compiled_path, $compiled)
    {
        $this->ensureWriteableDirectory(dirname($compiled_path));
        file_put_contents($compiled_path, $compiled);
    }

    /**
     * @param string $path
     */
    protected function ensureWriteableDirectory($path)
    {
        if (is_dir($path)) {
            if ( ! is_writeable($path)) {
                throw new \RuntimeException("Cannot write to compiled template path '$path'");
            }
        } else {
            if ( ! mkdir($path, 0777, TRUE)) {
                throw new \RuntimeException("Cannot create template cache directory in '$path'");
            }
        }
    }

}
