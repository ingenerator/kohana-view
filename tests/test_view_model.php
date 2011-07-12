<?php

/**
 * Tests View_Model functionality
 *
 * @group view_model
 */
class View_Model_Test extends Kohana_Unittest_TestCase
{
    public function test_escape()
    {
        $view = new View_Test_Escape();
	$expected = file_get_contents(Kohana::find_file('tests', 'output/test/escape', 'txt'));
	$this->assertSame($expected, $view->render());
    }
}

class View_Test_Escape extends View_Model
{
	public function var_foo()
	{
		return '<h2>foobar</h2>';
	}
}