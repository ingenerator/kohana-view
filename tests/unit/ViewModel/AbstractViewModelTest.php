<?php

declare(strict_types=1);

namespace test\unit\ViewModel;

use Attribute;
use DateTimeImmutable;
use Ingenerator\KohanaView\Attribute\DisplayVariableAttribute;
use Ingenerator\KohanaView\Attribute\InternalDisplayVariable;
use Ingenerator\KohanaView\Attribute\OptionalDisplayVariable;
use Ingenerator\KohanaView\Attribute\RequiredDisplayVariable;
use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\Exception\InvalidViewDefinitionException;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractViewModelTest extends TestCase
{
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

    public static function provider_invalid_prop_tagging(): array
    {
        return [
            'multiple tags' => [
                new class extends AbstractViewModel {
                    #[RequiredDisplayVariable]
                    #[OptionalDisplayVariable]
                    public string $foo;
                },
                'Expected only one Ingenerator\KohanaView\Attribute\DisplayVariableAttribute per property but got 2',
            ],
            'optional with no default' => [
                new class extends AbstractViewModel {
                    #[OptionalDisplayVariable]
                    public string $foo;
                },
                'was tagged as Ingenerator\KohanaView\Attribute\OptionalDisplayVariable, but has no default value',
            ],
            'custom attribute with conflicting can & must states' => [
                new class extends AbstractViewModel {
                    #[InvalidCustomDisplayVariableAttribute]
                    public string $foo;
                },
                'Attribute test\unit\ViewModel\InvalidCustomDisplayVariableAttribute marked that property must be provided but can not be provided',
            ],
        ];
    }

    #[DataProvider('provider_invalid_prop_tagging')]
    public function test_its_display_throws_on_invalid_property_tagging(AbstractViewModel $subject, string $expect_msg): void
    {
        $this->expectException(InvalidViewDefinitionException::class);
        $this->expectExceptionMessage($expect_msg);
        $subject->display([]);
    }

    public static function provider_populate_known_props(): iterable
    {
        return [
            'can set normal value - default uses class default' => [
                ['some_defined_var' => 'whatever'],
                ['some_defined_var' => 'whatever', 'some_defaulted_var' => 'default value'],
            ],
            'can set null value - default uses class default' => [
                // To prove that we don't treat the null as "missing"
                ['some_defined_var' => null],
                ['some_defined_var' => null, 'some_defaulted_var' => 'default value'],
            ],
            'can also override default' => [
                ['some_defined_var' => 'me', 'some_defaulted_var' => 95],
                ['some_defined_var' => 'me', 'some_defaulted_var' => 95],
            ],
        ];
    }

    #[DataProvider('provider_populate_known_props')]
    public function test_its_display_method_populates_known_properties(array $display_what, array $expect): void
    {
        $subject = new class extends AbstractViewModel {
            public protected(set) ?string $some_defined_var = null;
            #[OptionalDisplayVariable]
            public protected(set) mixed $some_defaulted_var = 'default value';
        };

        $subject->display($display_what);
        $this->assertSame(
            $expect,
            [
                'some_defined_var' => $subject->some_defined_var,
                'some_defaulted_var' => $subject->some_defaulted_var,
            ],
        );
    }

    public function test_optional_properties_are_reset_to_default_on_each_display(): void
    {
        $subject = new class extends AbstractViewModel {
            public protected(set) ?string $a = null;
            #[RequiredDisplayVariable]
            public protected(set) ?string $b = null;
            #[OptionalDisplayVariable]
            public protected(set) bool $c = false;
        };

        // Explicitly set the value first time
        $subject->display(['a' => '1', 'b' => '2', 'c' => true]);
        $this->assertSame(
            ['a' => '1', 'b' => '2', 'c' => true],
            ['a' => $subject->a, 'b' => $subject->b, 'c' => $subject->c],
        );

        // And the next display() call sets it back to default
        $subject->display(['a' => '3', 'b' => '4']);
        $this->assertSame(
            ['a' => '3', 'b' => '4', 'c' => false],
            ['a' => $subject->a, 'b' => $subject->b, 'c' => $subject->c],
        );
    }

    public static function provider_unexpected_display_values(): iterable
    {
        $valid_display = [
            'some_defined_var' => 'whatever',
            'computed_real' => 'i am lowercase',
            'tagged_private' => 'mine',
        ];

        return [
            'cannot display unknown variable' => [
                [...$valid_display, 'random_unknown_var' => 'is invalid'],
                'Unexpected vars: ["random_unknown_var"]',
            ],
            'cannot display public readonly prop' => [
                [...$valid_display, 'some_promoted_var' => 'is invalid'],
                'Unexpected vars: ["some_promoted_var"]',
            ],
            'cannot display non-display property' => [
                [...$valid_display, 'internal_display_prop' => 'cannot be displayed'],
                'Unexpected vars: ["internal_display_prop"]',
            ],
            'cannot display computed property' => [
                [...$valid_display, 'computed_virtual' => 'cannot set directly'],
                'Unexpected vars: ["computed_virtual"]',
            ],
            'cannot display private property' => [
                [...$valid_display, 'some_private' => 'cannot set'],
                'Unexpected vars: ["some_private"]',
            ],
            'must provide variables for public props' => [
                ['computed_real' => 'lower'],
                'Missing vars: ["some_defined_var","tagged_private"]',
            ],
            'must provide variables for real props even with getters' => [
                ['some_defined_var' => 'any'],
                'Missing vars: ["tagged_private","computed_real"]',
            ],
            'must provide correct type' => [
                [...$valid_display, 'some_defined_var' => new DateTimeImmutable()],
                'Cannot assign DateTimeImmutable to property',
            ],
        ];
    }

    #[DataProvider('provider_unexpected_display_values')]
    public function test_its_display_method_throws_on_unexpected_or_missing_values(array $display, string $expect_msg): void
    {
        $subject = new class('foo') extends AbstractViewModel {
            // By default a public-readable property is expected / allowed in display
            public protected(set) ?string $some_defined_var = null;

            // Can override default behaviour with the ViewModelProperty attribute
            #[InternalDisplayVariable]
            public protected(set) string $internal_display_prop;

            #[RequiredDisplayVariable]
            private readonly string $tagged_private;

            // Cannot ->display() a non-public prop unless it is tagged
            private readonly string $some_private;

            // Cannot ->display() a computed prop without a backing value
            public string $computed_virtual {
                get => 'anything';
            }

            // Can ->display() a computed prop if it has a backing value
            public string $computed_real {
                get => ucfirst($this->computed_real);
            }

            public function __construct(
                // Cannot ->display() constructor props
                public readonly string $some_promoted_var,
            ) {
            }
        };

        $this->expectException(InvalidDisplayVariablesException::class);
        $this->expectExceptionMessage($expect_msg);
        $subject->display($display);
    }

    protected function newSubject(): TestViewModel
    {
        return new TestViewModel();
    }
}

class TestViewModel extends AbstractViewModel
{
    public string $some_dynamic_var {
        get => $this->var_some_dynamic_var();
    }

    private int $number_times_calculated = 0;
    #[OptionalDisplayVariable]
    public protected(set) mixed $some_defaulted_var = 'default value';

    /**
     * // also really @property-read, but suppress the IDE warning.
     */
    public protected(set) string $some_defined_var;

    protected function var_some_dynamic_var(): string
    {
        return 'expected dynamic';
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class InvalidCustomDisplayVariableAttribute implements DisplayVariableAttribute
{
    public function canPassToDisplay(): bool
    {
        return false;
    }

    public function mustPassToDisplay(): bool
    {
        return true;
    }
}
