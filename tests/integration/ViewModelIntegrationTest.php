<?php

/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\integration;

use Ingenerator\KohanaView\Renderer\HTMLRenderer;
use Ingenerator\KohanaView\TemplateManager\CFSTemplateManager;
use Ingenerator\KohanaView\ViewModel;

/**
 * @package             test\integration
 * @slow
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ViewModelIntegrationTest extends \PHPUnit\Framework\TestCase
{
    const STALE_COMPILED_STRING = 'Stale content from previous compile';

    protected $tmp_dir;

    public function test_dependency_container_provides_shared_html_renderer()
    {
        $dependencies = $this->givenDependenciesBootstrapped();
        $renderer     = $dependencies->get('kohanaview.renderer.html');
        $this->assertInstanceOf('Ingenerator\KohanaView\Renderer\HTMLRenderer', $renderer);
        $this->assertSame($renderer, $dependencies->get('kohanaview.renderer.html'));
    }

    public function test_dependency_container_provides_shared_page_layout_renderer()
    {
        $this->markTestIncomplete('Cannot put the page layout renderer in the container without defining request');
    }

    public function test_template_manager_cache_dir_defaults_to_inside_kohana_cache_dir()
    {
        $this->givenFileWithContent('module/views/integration/test_view.php', 'Project source template');
        $dependencies  = $this->givenDependenciesBootstrapped();
        $manager       = $this->getTemplateManager($dependencies);
        $compiled_path = $manager->getPath('integration/test_view');
        $this->assertStringStartsWith(\Kohana::$cache_dir.'/compiled_templates/', $compiled_path);
    }

    /**
     * @testWith ["DEVELOPMENT"]
     *           ["TESTING"]
     *           ["STAGING"]
     *           ["PRODUCTION"]
     */
    public function test_template_compiler_always_compiles_when_no_compiled_template($environment)
    {
        $this->givenFileWithContent('module/views/any_view.php', 'Project source template');

        \Kohana::$environment = \constant('\Kohana::'.$environment);
        $dependencies         = $this->givenDependenciesBootstrapped();

        $cache_file = $this->getTemplateManager($dependencies)->getPath('any_view');
        $this->assertSame('Project source template', \file_get_contents($cache_file));
    }

    /**
     * @testWith ["DEVELOPMENT", true]
     *           ["TESTING", false]
     *           ["STAGING", false]
     *           ["PRODUCTION", false]
     */
    public function test_template_compiler_recompiles_always_only_in_development($environment, $expect_recompile)
    {
        $this->givenFileWithContent('cache/compiled_templates/any_view.php', self::STALE_COMPILED_STRING);
        $this->givenFileWithContent('module/views/any_view.php', 'Project source template');

        \Kohana::$environment = \constant('\Kohana::'.$environment);
        $dependencies         = $this->givenDependenciesBootstrapped();

        $cache_file = $this->getTemplateManager($dependencies)->getPath('any_view');

        $actual_content = \file_get_contents($cache_file);
        if ($expect_recompile) {
            $this->assertNotSame($actual_content, self::STALE_COMPILED_STRING);
        } else {
            $this->assertSame($actual_content, self::STALE_COMPILED_STRING);
        }
    }

    public function test_it_renders_expected_view_for_view_model()
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
        $view = new \View\Test\SomeModel;
        /** @var $view ViewModel */

        $result = $this->getHTMLRenderer($dependencies)->render($view);

        $this->assertSame(
            'This is raw view stuff',
            $result
        );
    }

    public function test_it_automatically_escapes_view_variables()
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
        $view = new \View\Test\CustomView;
        /** @var $view ViewModel */

        $this->assertSame(
            'View with &lt;p&gt;Stuff&amp;Things&lt;/p&gt;, <p>Stuff&Things</p>',
            $this->getHTMLRenderer($dependencies)->render($view)
        );
    }

    public function setUp(): void
    {
        \PHPUnit\Framework\Assert::assertTrue($this->isInIsolation(), 'Integration tests must runInSeparateProcess');
        \PHPUnit\Framework\Assert::assertTrue($this->preserveGlobalState, 'Integration tests must run without globals');
        $this->expectOutputRegex('/^$/');

        $this->tmp_dir      = \sys_get_temp_dir().'/kohana-view-integration/'.\uniqid('test');
        \Kohana::$cache_dir = $this->tmp_dir.'/cache';
        \mkdir($this->tmp_dir.'/module', 0700, TRUE);

        parent::setUp();
    }

    public function tearDown(): void
    {
        `rm -rf $this->tmp_dir`;
        $this->assertFileNotExists($this->tmp_dir, 'Temp directory should have been cleared up');
        parent::tearDown();
    }

    protected function givenDependenciesBootstrapped()
    {
        $modules                     = \Kohana::modules();
        $modules['dependencies']     = TEST_ROOT_PATH.'/../vendor/zeelot/kohana-dependencies';
        $modules['integration_test'] = $this->tmp_dir.'/module';
        \Kohana::modules($modules);

        $definitions = \Dependency_Definition_List::factory()
            ->from_array(
                \Kohana::$config->load('dependencies')->as_array()
            );

        return new \Dependency_Container($definitions);
    }

    /**
     * @return CFSTemplateManager
     */
    protected function getTemplateManager(\Dependency_Container $dependencies)
    {
        return $dependencies->get('kohanaview.template.manager');
    }

    /**
     * @param string $relative_path
     * @param string $content
     *
     * @return string
     */
    protected function givenFileWithContent($relative_path, $content)
    {
        $full_path = $this->tmp_dir.'/'.$relative_path;

        $path = \dirname($full_path);
        if ( ! \is_dir($path)) {
            \mkdir($path, 0777, TRUE);
        }

        \file_put_contents($full_path, $content);

        return $full_path;
    }

    /**
     * @return HTMLRenderer
     */
    protected function getHTMLRenderer(\Dependency_Container $dependencies)
    {
        return $dependencies->get('kohanaview.renderer.html');
    }

}
