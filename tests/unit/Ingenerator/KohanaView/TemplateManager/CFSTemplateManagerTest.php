<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace test\unit\Ingenerator\KohanaView\TemplateManager;


use Ingenerator\KohanaView\TemplateCompiler;
use Ingenerator\KohanaView\TemplateManager\CFSTemplateManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use test\mock\CFSWrapper\SingleDirectoryCFSWrapperMock;

class CFSTemplateManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $options = [];

    /**
     * @var \test\mock\CFSWrapper\SingleDirectoryCFSWrapperMock
     */
    protected $cfs_wrapper;

    /**
     * @var SpyingTemplateCompiler
     */
    protected $compiler;

    /**
     * @var vfsStreamDirectory
     */
    protected $vfs_root;


    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\TemplateManager\CFSTemplateManager',
            $subject
        );
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\TemplateManager',
            $subject
        );
    }

    public function test_it_creates_cache_dir_on_compile_if_it_does_not_exist()
    {
        $this->options['cache_dir'] = vfsStream::url('template/path/to/some/random/directory');
        $this->givenFile('module/views/test.php', 'This is the raw view file');
        $compiled_path = $this->newSubject()->getPath('test');
        $this->assertCompiledToFile($compiled_path);
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\TemplateCacheException
     * @expectedExceptionMessage Cannot create template cache directory
     */
    public function test_it_throws_if_it_cannot_create_cache_dir()
    {
        $this->options['cache_dir'] = vfsStream::url('template/cache');
        chmod($this->options['cache_dir'], 0500);
        $this->givenFile('module/views/any/view.php', 'Raw view file');
        $this->newSubject()->getPath('any/view');
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\TemplateCacheException
     * @expectedExceptionMessage Cannot write to compiled template path
     */
    public function test_it_throws_if_it_cannot_create_compiled_file()
    {
        $this->options['cache_dir'] = vfsStream::url('template/cache');
        chmod($this->options['cache_dir'], 0500);
        $this->givenFile('module/views/anything.php', 'Raw view file');
        $this->newSubject()->getPath('anything');
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\TemplateNotFoundException
     */
    public function test_it_throws_if_no_source_template_for_uncompiled_template_name()
    {
        $this->newSubject()->getPath('some/random/template/file/we/do/not/have');
    }

    public function test_it_compiles_template_to_file_if_not_already_compiled()
    {
        $this->options['cache_dir'] = vfsStream::url('template/cache');
        $this->givenFile('module/views/any/raw_view.php', 'This is the raw view');

        $compiled_url = $this->newSubject()->getPath('any/raw_view');
        $this->compiler->assertCompiledOnce('This is the raw view');
        $this->assertCompiledToFile($compiled_url);
    }

    public function test_it_does_not_recompile_templates_when_disabled()
    {
        $this->options['cache_dir']        = vfsStream::url('template/cache');
        $this->options['recompile_always'] = FALSE;
        $this->givenFile('cache/some/compiled_view.php', 'Any compiled content');
        $this->assertSame(
            vfsStream::url('template/cache/some/compiled_view.php'),
            $this->newSubject()->getPath('some/compiled_view')
        );
        $this->compiler->assertNothingCompiled();
    }

    public function test_it_recompiles_once_per_instance_when_enabled()
    {
        $this->options['cache_dir']        = vfsStream::url('template/cache');
        $this->options['recompile_always'] = TRUE;
        $this->givenFile('cache/some/compiled_view.php', 'Any compiled content');
        $this->givenFile('module/views/some/compiled_view.php', 'Raw template content');
        $subject = $this->newSubject();

        $compiled_url = $subject->getPath('some/compiled_view');
        $this->assertCompiledToFile($compiled_url);
        $this->compiler->assertCompiledOnce('Raw template content');

        $this->assertSame($compiled_url, $subject->getPath('some/compiled_view'));
        $this->compiler->assertCompiledOnce('Raw template content');
    }

    public function setUp()
    {
        $this->compiler             = new SpyingTemplateCompiler;
        $this->vfs_root             = vfsStream::setup(
            'template',
            0700,
            [
                'cache'  => [],
                'module' => [],
            ]
        );
        $this->options['cache_dir'] = vfsStream::url('template/cache');
        $this->cfs_wrapper          = new SingleDirectoryCFSWrapperMock(vfsStream::url('template/module'));
        parent::setUp();
    }

    protected function newSubject()
    {
        return new CFSTemplateManager($this->compiler, $this->options, $this->cfs_wrapper);
    }

    /**
     * @param string $compiled_url
     */
    protected function assertCompiledToFile($compiled_url)
    {
        $this->assertTrue(file_exists($compiled_url), "Compiled file $compiled_url should exist");
        $this->assertSame(SpyingTemplateCompiler::COMPILED_OUTPUT, file_get_contents($compiled_url));
        $this->assertStringStartsWith(
            $this->options['cache_dir'],
            $compiled_url,
            'Compiled file should be in cache dir'
        );
    }

    protected function givenFile($path_to_file, $content)
    {
        $file = vfsStream::url('template/'.$path_to_file);
        $path = dirname($file);
        if ( ! is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
        file_put_contents($file, $content);
    }

}

class SpyingTemplateCompiler extends TemplateCompiler
{
    const COMPILED_OUTPUT = 'compiled template content';
    protected $compiled = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct() { }

    public function compile($source)
    {
        $this->compiled[] = $source;

        return static::COMPILED_OUTPUT;
    }

    public function assertCompiledOnce($string)
    {
        \PHPUnit_Framework_Assert::assertEquals([$string], $this->compiled);
    }

    public function assertNothingCompiled()
    {
        \PHPUnit_Framework_Assert::assertEmpty($this->compiled);
    }
}

