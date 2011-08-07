<?php
/**
 * Tests View_Model functionality
 *
 * @group view_model
 */
class View_Model_Test extends Kohana_Unittest_TestCase
{
    protected static $old_modules = array();

    /**
     * Setups the filesystem for test view files
     */
    public static function setupBeforeClass()
    {
        self::$old_modules = Kohana::modules();        
        $new_modules = self::$old_modules+array(
                'test_views' => realpath(dirname(__FILE__).'/../test_data/')
        );                
        Kohana::modules($new_modules);
    }

    /**
     * Restores the module list
     */
    public static function teardownAfterClass()
    {
        Kohana::modules(self::$old_modules);
    }

    public function test_escape()
    {
        $view = new View_Test_Escape();
	$expected = file_get_contents(Kohana::find_file('test_output', 'escape', 'txt'));
	$this->assertSame($expected, $view->render());
    }

    public function test_methods_on_render()
    {
        $view = new View_Test_RuntimeMethods();
        $view->render();
        $this->assertTrue($view->called);
        $this->assertFalse($view->not_called);
    }
}

class View_Test_Escape extends View_Model
{
    public function var_foo()
    {
        return '<h2>foobar</h2>';
    }
}

class View_Test_RuntimeMethods extends View_Model
{
    public $called = false;
    public $not_called = false;

    public function var_called()
    {
        $called = true;
        return true;
    }

    public function var_not_called()
    {
        $not_called = true;
        return true;
    }
}