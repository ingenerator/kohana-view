<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace test\unit\Ingenerator\KohanaView\ViewModel;


use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\Exception\InvalidViewVarAssignmentException;
use Ingenerator\KohanaView\Exception\UndefinedViewVarException;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PHPUnit\Framework\TestCase;

class AbstractViewModelTest extends TestCase
{

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(AbstractViewModel::class, $subject);
        $this->assertInstanceOf(ViewModel::class, $subject);
    }

    public function test_it_provides_magic_read_access_to_defined_variables()
    {
        $this->assertEquals('expected value', $this->newSubject()->some_defined_var);
    }

    public function test_it_provides_magic_read_access_to_defined_default_variables()
    {
        $this->assertEquals('default value', $this->newSubject()->some_defaulted_var);
    }

    public function test_it_provides_magic_read_access_to_protected_var_methods()
    {
        $this->assertEquals('expected dynamic', $this->newSubject()->some_dynamic_var);
    }

    public function test_it_uses_defined_variables_in_preference_to_defined_methods()
    {
        $subject = $this->newSubject();
        $this->assertEquals('calculated', $subject->lazy_calculated_value);
        $this->assertEquals('cached', $subject->lazy_calculated_value);
    }

    public function test_it_throws_if_attempting_to_read_undefined_property()
    {
        $this->expectException(UndefinedViewVarException::class);
        $this->expectExceptionMessage("TestViewModel does not define a 'some_undefined_var' field");
        /** @noinspection PhpUndefinedFieldInspection */
        $this->newSubject()->some_undefined_var;
    }

    public function test_it_throws_if_attempting_to_set_any_undefined_externally()
    {
        $this->expectException(InvalidViewVarAssignmentException::class);
        $this->expectExceptionMessage("TestViewModel variables are read-only, cannot assign some_defined_var");
        $this->newSubject()->some_defined_var = 'anything';
    }

    public function test_its_display_method_defines_variables()
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var' => 'whatever',
            ]
        );
        $this->assertSame('whatever', $subject->some_defined_var);
    }

    public function test_its_display_method_can_define_null_variables()
    {
        $subject = $this->newSubject();
        $subject->display(['some_defined_var' => NULL]);
        $this->assertNull($subject->some_defined_var);
    }

    public function test_its_display_method_throws_on_unexpected_values()
    {
        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage("'random_var' is not expected");
        $this->newSubject()->display(['some_defined_var' => 'new', 'random_var' => 'anything']);
    }

    public function test_its_display_method_throws_on_missing_values()
    {
        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage("'some_defined_var' is missing");
        $this->newSubject()->display([]);
    }

    public function test_its_display_method_can_override_default_values()
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var'   => 'required',
                'some_defaulted_var' => 'custom',
            ]
        );
        $this->assertSame('custom', $subject->some_defaulted_var);
    }

    public function test_its_display_method_reinitialises_default_values_if_not_present()
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var'   => 'required',
                'some_defaulted_var' => 'custom',
            ]
        );

        $subject->display(['some_defined_var' => 'custom2']);
        $this->assertSame('custom2', $subject->some_defined_var);
        $this->assertSame('default value', $subject->some_defaulted_var);
    }

    public function test_its_display_method_throws_if_variables_conflict_with_variable_methods()
    {
        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage("'some_dynamic_var' conflicts with ::var_some_dynamic_var()");
        $this->newSubject()->display(
            [
                'some_defined_var' => 'ok',
                'some_dynamic_var' => 'problemo',
            ]
        );
    }

    public function test_its_display_method_does_not_require_dynamically_set_variables()
    {
        $subject = $this->newSubject();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $ok = $subject->lazy_calculated_value;
        $subject->display(['some_defined_var' => 'ok']);

        // We got this far successfully, provide assertion to keep PHPUnit happy.
        $this->assertTrue(TRUE);
    }

    protected function newSubject()
    {
        return new TestViewModel;
    }

}

/**
 * @property      string some_defined_var      // also really @property-read, but suppress the IDE warning
 * @property-read string some_dynamic_var
 * @property-read string lazy_calculated_value
 */
class TestViewModel extends AbstractViewModel
{

    protected $default_variables = [
        'some_defaulted_var' => 'default value',
    ];

    protected $variables = [
        'some_defined_var' => 'expected value',
    ];

    protected function var_some_dynamic_var()
    {
        return 'expected dynamic';
    }

    protected function var_lazy_calculated_value()
    {
        $this->variables['lazy_calculated_value'] = 'cached';

        return 'calculated';
    }

}
