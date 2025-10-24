<?php

namespace test\integration;

use Dependency_Container;
use Dependency_Definition_List;
use Ingenerator\KohanaView\Renderer\HTMLRenderer;
use Ingenerator\KohanaView\TemplateManager\CFSTemplateManager;
use Ingenerator\KohanaView\ViewModel;
use Kohana;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use View\Test\CustomView;
use View\Test\SomeModel;

use function constant;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

/**
 * @slow
 */
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class ViewModelIntegrationTest extends TestCase
{
    public const STALE_COMPILED_STRING = 'Stale content from previous compile';

    protected $tmp_dir;

    public function test_dependency_container_provides_shared_html_renderer(): void
    {
        $dependencies = $this->givenDependenciesBootstrapped();
        $renderer = $dependencies->get('kohanaview.renderer.html');
        $this->assertInstanceOf(HTMLRenderer::class, $renderer);
        $this->assertSame($renderer, $dependencies->get('kohanaview.renderer.html'));
    }

    public function test_dependency_container_provides_shared_page_layout_renderer(): never
    {
        $this->markTestIncomplete('Cannot put the page layout renderer in the container without defining request');
    }

    public function test_template_manager_cache_dir_defaults_to_inside_kohana_cache_dir(): void
    {
        $this->givenFileWithContent('module/views/integration/test_view.php', 'Project source template');
        $dependencies = $this->givenDependenciesBootstrapped();
        $manager = $this->getTemplateManager($dependencies);
        $compiled_path = $manager->getPath('integration/test_view');
        $this->assertStringStartsWith(Kohana::$cache_dir.'/compiled_templates/', $compiled_path);
    }

    #[TestWith(['DEVELOPMENT'])]
    #[TestWith(['TESTING'])]
    #[TestWith(['STAGING'])]
    #[TestWith(['PRODUCTION'])]
    public function test_template_compiler_always_compiles_when_no_compiled_template(string $environment): void
    {
        $this->givenFileWithContent('module/views/any_view.php', 'Project source template');

        Kohana::$environment = constant('\Kohana::'.$environment);
        $dependencies = $this->givenDependenciesBootstrapped();

        $cache_file = $this->getTemplateManager($dependencies)->getPath('any_view');
        $this->assertSame('Project source template', file_get_contents($cache_file));
    }

    #[TestWith(['DEVELOPMENT', true])]
    #[TestWith(['TESTING', false])]
    #[TestWith(['STAGING', false])]
    #[TestWith(['PRODUCTION', false])]
    public function test_template_compiler_recompiles_always_only_in_development(string $environment, $expect_recompile): void
    {
        $this->givenFileWithContent('cache/compiled_templates/any_view.php', self::STALE_COMPILED_STRING);
        $this->givenFileWithContent('module/views/any_view.php', 'Project source template');

        Kohana::$environment = constant('\Kohana::'.$environment);
        $dependencies = $this->givenDependenciesBootstrapped();

        $cache_file = $this->getTemplateManager($dependencies)->getPath('any_view');

        $actual_content = file_get_contents($cache_file);
        if ($expect_recompile) {
            $this->assertNotSame($actual_content, self::STALE_COMPILED_STRING);
        } else {
            $this->assertSame($actual_content, self::STALE_COMPILED_STRING);
        }
    }

    public function test_it_renders_expected_view_for_view_model(): void
    {
        $this->givenFileWithContent(
            'module/classes/View/Test/SomeModel.php',
            <<<'PHP'
                <?php
                namespace View\Test;

                class SomeModel extends \Ingenerator\KohanaView\ViewModel\AbstractViewModel {}
                PHP
        );

        $this->givenFileWithContent('module/views/test/some_model.php', 'This is raw view stuff');

        $dependencies = $this->givenDependenciesBootstrapped();

        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /** @noinspection PhpUndefinedClassInspection */
        $view = new SomeModel();
        /** @var ViewModel $view */
        $result = $this->getHTMLRenderer($dependencies)->render($view);

        $this->assertSame(
            'This is raw view stuff',
            $result
        );
    }

    public function test_it_automatically_escapes_view_variables(): void
    {
        $this->givenFileWithContent(
            'module/classes/View/Test/CustomView.php',
            <<<'PHP'
                <?php
                namespace View\Test;

                class CustomView extends \Ingenerator\KohanaView\ViewModel\AbstractViewModel
                {
                    protected $variables = [
                        'html_string' => '<p>Stuff&Things</p>'
                    ];
                }
                PHP
        );

        $this->givenFileWithContent(
            'module/views/test/custom.php',
            'View with <?=$view->html_string;?>, <?=raw($view->html_string);?>'
        );

        $dependencies = $this->givenDependenciesBootstrapped();

        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /** @noinspection PhpUndefinedClassInspection */
        $view = new CustomView();
        /** @var ViewModel $view */
        $this->assertSame(
            'View with &lt;p&gt;Stuff&amp;Things&lt;/p&gt;, <p>Stuff&Things</p>',
            $this->getHTMLRenderer($dependencies)->render($view)
        );
    }

    protected function setUp(): void
    {
        $this->expectOutputRegex('/^$/');

        $this->tmp_dir = sys_get_temp_dir().'/kohana-view-integration/'.uniqid('test');
        Kohana::$cache_dir = $this->tmp_dir.'/cache';
        mkdir($this->tmp_dir.'/module', 0o700, true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        shell_exec("rm -rf $this->tmp_dir");
        $this->assertFileDoesNotExist($this->tmp_dir, 'Temp directory should have been cleared up');
        parent::tearDown();
    }

    protected function givenDependenciesBootstrapped()
    {
        $modules = Kohana::modules();
        $modules['dependencies'] = TEST_ROOT_PATH.'/../vendor/ingenerator/kohana-dependencies';
        $modules['integration_test'] = $this->tmp_dir.'/module';
        Kohana::modules($modules);

        $definitions = Dependency_Definition_List::factory()
            ->from_array(
                Kohana::$config->load('dependencies')->as_array()
            );

        return new Dependency_Container($definitions);
    }

    /**
     * @return CFSTemplateManager
     */
    protected function getTemplateManager(Dependency_Container $dependencies)
    {
        return $dependencies->get('kohanaview.template.manager');
    }

    /**
     * @param string $content
     */
    protected function givenFileWithContent(string $relative_path, $content): string
    {
        $full_path = $this->tmp_dir.'/'.$relative_path;

        $path = dirname($full_path);
        if ( ! is_dir($path)) {
            mkdir($path, 0o777, true);
        }

        file_put_contents($full_path, $content);

        return $full_path;
    }

    /**
     * @return HTMLRenderer
     */
    protected function getHTMLRenderer(Dependency_Container $dependencies)
    {
        return $dependencies->get('kohanaview.renderer.html');
    }
}
