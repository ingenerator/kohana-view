<?php

declare(strict_types=1);

namespace test\unit\ViewModel;

use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\Exception\InvalidViewVarAssignmentException;
use Ingenerator\KohanaView\Exception\UndefinedViewVarException;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PHPUnit\Framework\TestCase;

class AbstractViewModelTest extends TestCase
{
    public function test_it_is_initialisable(): void
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(AbstractViewModel::class, $subject);
        $this->assertInstanceOf(ViewModel::class, $subject);
    }

    public function test_it_provides_magic_read_access_to_defined_variables(): void
    {
        $this->assertEquals('expected value', $this->newSubject()->some_defined_var);
    }

    public function test_it_provides_magic_read_access_to_defined_default_variables(): void
    {
        $this->assertEquals('default value', $this->newSubject()->some_defaulted_var);
    }

    public function test_it_provides_magic_read_access_to_protected_var_methods(): void
    {
        $this->assertEquals('expected dynamic', $this->newSubject()->some_dynamic_var);
    }

    public function test_it_can_cache_dynamic_property_reads(): void
    {
        $subject = new class extends AbstractViewModel {
            private int $execution_count = 0;
            public ?string $calculated_var {
                get => $this->getCached(__PROPERTY__, $this->calculateVar(...));
            }

            private function calculateVar(): string
            {
                return 'execution-'.++$this->execution_count;
            }
        };

        $this->assertSame('execution-1', $subject->calculated_var, 'Should calculate the first time');
        $this->assertSame('execution-1', $subject->calculated_var, 'Reuses cached variable');
        $subject->display([]);
        $this->assertSame('execution-2', $subject->calculated_var, 'Cache resets after call to display()');
    }

    public function test_it_throws_if_attempting_to_read_undefined_property(): void
    {
        $this->expectException(UndefinedViewVarException::class);
        $this->expectExceptionMessage("TestViewModel does not define a 'some_undefined_var' field");
        /* @noinspection PhpUndefinedFieldInspection */
        $this->newSubject()->some_undefined_var;
    }

    public function test_it_throws_if_attempting_to_set_any_undefined_externally(): void
    {
        $this->expectException(InvalidViewVarAssignmentException::class);
        $this->expectExceptionMessage('TestViewModel variables are read-only, cannot assign some_defined_var');
        $this->newSubject()->some_defined_var = 'anything';
    }

    public function test_its_display_method_defines_variables(): void
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var' => 'whatever',
            ]
        );
        $this->assertSame('whatever', $subject->some_defined_var);
    }

    public function test_its_display_method_can_define_null_variables(): void
    {
        $subject = $this->newSubject();
        $subject->display(['some_defined_var' => null]);
        $this->assertNull($subject->some_defined_var);
    }

    public function test_its_display_method_throws_on_unexpected_values(): void
    {
        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage("'random_var' is not expected");
        $this->newSubject()->display(['some_defined_var' => 'new', 'random_var' => 'anything']);
    }

    public function test_its_display_method_throws_on_missing_values(): void
    {
        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage("'some_defined_var' is missing");
        $this->newSubject()->display([]);
    }

    public function test_its_display_method_can_override_default_values(): void
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var' => 'required',
                'some_defaulted_var' => 'custom',
            ]
        );
        $this->assertSame('custom', $subject->some_defaulted_var);
    }

    public function test_its_display_method_reinitialises_default_values_if_not_present(): void
    {
        $subject = $this->newSubject();
        $subject->display(
            [
                'some_defined_var' => 'required',
                'some_defaulted_var' => 'custom',
            ]
        );

        $subject->display(['some_defined_var' => 'custom2']);
        $this->assertSame('custom2', $subject->some_defined_var);
        $this->assertSame('default value', $subject->some_defaulted_var);
    }

    public function test_its_display_method_throws_if_variables_conflict_with_variable_methods(): void
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

    protected function newSubject(): TestViewModel
    {
        return new TestViewModel();
    }
}

/**
 * @property string $some_defined_var // also really @property-read, but suppress the IDE warning
 */
class TestViewModel extends AbstractViewModel
{
    protected array $default_variables = [
        'some_defaulted_var' => 'default value',
    ];
    public string $some_dynamic_var {
        get => $this->var_some_dynamic_var();
    }

    private int $number_times_calculated = 0;

    protected array $variables = [
        'some_defined_var' => 'expected value',
    ];

    protected function var_some_dynamic_var(): string
    {
        return 'expected dynamic';
    }
}
