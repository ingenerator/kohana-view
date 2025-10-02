<?php

namespace Ingenerator\KohanaView\TemplateManager;

use Arr;
use Ingenerator\KohanaView\Exception\TemplateCacheException;
use Ingenerator\KohanaView\Exception\TemplateNotFoundException;
use Ingenerator\KohanaView\TemplateCompiler;
use Ingenerator\KohanaView\TemplateManager;
use Kohana;
use Kohana_Exception;

use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_writable;
use function rtrim;

/**
 * Manages compilation of templates from view files located within the cascading file system. This allows extension
 * modules or applications to provide their own templates that are used in place of defaults provided by other modules.
 *
 * Templates will be dynamically compiled and cached to disk:
 *  * If the recompile_always option is TRUE, then once for every execution
 *  * If the recompile_always option is FALSE, then only if the compiled template does not yet exist
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
     * @var bool
     */
    protected $recompile_always;

    /**
     * @var string
     */
    protected $template_dir;

    /**
     * Valid options:
     * * cache_dir        => the path where compiled templates will be cached
     * * recompile_always => whether to recompile each template on every execution,
     * * template_dir     => directory (in the cascading filesystem) to search for templates
     */
    public function __construct(TemplateCompiler $compiler, array $options, ?CFSWrapper $cascading_files = null)
    {
        $this->cascading_files = $cascading_files ?: new CFSWrapper();
        $this->compiler = $compiler;
        $this->cache_dir = rtrim($options['cache_dir'], '/');
        $this->recompile_always = Arr::get($options, 'recompile_always', false);
        $this->template_dir = rtrim(Arr::get($options, 'template_dir', 'views'), '/');
    }

    public function getPath($template_name)
    {
        $compiled_path = $this->cache_dir.'/'.$template_name.'.php';

        if ($this->isCompileRequired($compiled_path)) {
            $source = $this->requireSourceFileContent($template_name);
            $compiled = $this->compiler->compile($source);
            $this->writeFile($compiled_path, $compiled);
            $this->compiled_paths[$compiled_path] = true;
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
        if ($this->recompile_always and ! isset($this->compiled_paths[$compiled_path])) {
            return true;
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
        if ( ! $source_file = $this->cascading_files->find_file($this->template_dir, $template_name)) {
            throw TemplateNotFoundException::forSourcePath($this->template_dir.'/'.$template_name);
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
        try {
            Kohana::ensureDirectory($path, 0o777);
        } catch (Kohana_Exception $e) {
            throw TemplateCacheException::cannotCreateDirectory($path);
        }

        if ( ! is_writable($path)) {
            throw TemplateCacheException::pathNotWriteable($path);
        }
    }
}
