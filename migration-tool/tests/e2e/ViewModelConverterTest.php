<?php

namespace tests\e2e;

use Ingenerator\KohanaViewMigrationTool\ViewModelConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ViewModelConverterTest extends TestCase
{

    public static function providerConvertSource(): iterable
    {
        return [
            '$variables to individual properties (with no phpdoc)' => [
                <<<'PHP'
                <?php
                
                use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
                class SomeView extends AbstractViewModel
                {
                
                    protected $variables = [
                        'my_first_variable' => null,
                        'another_variable' => null,
                    ];

                }
                PHP,
                <<<'PHP'
                <?php
                
                use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
                class SomeView extends AbstractViewModel
                {
                    public protected(set) mixed $my_first_variable;
                    public protected(set) mixed $another_variable;
                }
                PHP

            ],
            'computed properties to getter properties' => [
                <<<'PHP'
                <?php
                
                use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
                class SomeView extends AbstractViewModel
                {
                    protected function var_something() 
                    {
                        return 'something';
                    }
                    
                    protected function var_other_things(): array 
                    {
                        return ['one' => 'a', 'two' => 'b'];
                    }
                
                }
                PHP,
                <<<'PHP'
                <?php
                
                use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
                class SomeView extends AbstractViewModel
                {
                    public mixed $something {
                        get => $this->var_something();
                    }
                    public array $other_things {
                        get => $this->var_other_things();
                    }
                    protected function var_something()
                    {
                        return 'something';
                    }
                    protected function var_other_things(): array
                    {
                        return ['one' => 'a', 'two' => 'b'];
                    }
                }
                PHP
            ],
        ];
    }

    #[DataProvider('providerConvertSource')]
    public function testItConvertsViewModelSource(string $input, string $expect): void
    {
        $this->assertSame($expect, new ViewModelConverter()->convert($input));
    }
}