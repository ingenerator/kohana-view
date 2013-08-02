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
                'test_views' => realpath(dirname(__FILE__).'/test_data/')
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

	public function test_maps_variables_to_class_fields_and_methods()
	{
		$view = new View_Test_VarMapping();

		$expected = file_get_contents(Kohana::find_file('test_output', 'varmapping', 'txt'));
		$this->assertSame($expected, $view->render());
	}

}

class View_Test_VarMapping extends View_Model
{
	protected $var_protected = 'protected';

	public $var_public = 'public';

	public $class_only = 'class_only';

	public function var_function()
	{
		return 'function';
	}

	public function class_only_func()
	{
		return 'class-only-func';
	}
}

class View_Test_Escape extends View_Model
{
    public function var_foo()
    {
        return '<h2>foobar</h2>';
    }

    public function var_array()
    {
        return array('1','2','3');
    }
}

class View_Test_RuntimeMethods extends View_Model
{
    public $called = false;
    public $not_called = false;

    public function var_called()
    {
        $this->called = true;
        return "called";
    }

    public function var_not_called()
    {
        $this->not_called = true;
        return "not_called";
    }
}
