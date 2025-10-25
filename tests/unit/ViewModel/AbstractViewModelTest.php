<?php

declare(strict_types=1);

namespace test\unit\ViewModel;

use DateTimeImmutable;
use Ingenerator\KohanaView\Exception\InvalidDisplayVariablesException;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModelProperty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
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

    #[TestWith(['whatever'])]
    #[TestWith([null])]
    public function test_its_display_method_populates_known_properties(?string $value): void
    {
        $subject = new class extends AbstractViewModel {
            public protected(set) ?string $some_defined_var = null;
        };

        $subject->display([
            'some_defined_var' => $value,
        ]);
        $this->assertSame($value, $subject->some_defined_var);
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
                [...$valid_display, 'non_displayable_prop' => 'cannot be displayed'],
                'Unexpected vars: ["non_displayable_prop"]',
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
            #[ViewModelProperty(is_displayable: false)]
            public protected(set) string $non_displayable_prop;

            #[ViewModelProperty(is_displayable: true)]
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
    protected array $default_variables = [
        'some_defaulted_var' => 'default value',
    ];
    public string $some_dynamic_var {
        get => $this->var_some_dynamic_var();
    }

    private int $number_times_calculated = 0;

    /**
     * // also really @property-read, but suppress the IDE warning.
     */
    public protected(set) string $some_defined_var;

    protected function var_some_dynamic_var(): string
    {
        return 'expected dynamic';
    }
}
