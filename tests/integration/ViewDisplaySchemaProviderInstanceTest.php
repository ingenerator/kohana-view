<?php

declare(strict_types=1);

namespace test\integration;

use Ingenerator\KohanaView\Attribute\InternalDisplayVariable;
use Ingenerator\KohanaView\Attribute\OptionalDisplayVariable;
use Ingenerator\KohanaView\Attribute\RequiredDisplayVariable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\DisplaySchema\CoreDisplaySchemaProvider;
use Ingenerator\KohanaView\ViewModel\DisplaySchema\ViewDisplaySchemaProviderInstance;
use Ingenerator\KohanaView\ViewModel\DisplaySchema\ViewModelDisplaySchema;
use Ingenerator\KohanaView\ViewModelDisplaySchemaProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function strlen;

class ViewDisplaySchemaProviderInstanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ViewDisplaySchemaProviderInstance::resetToDefault();
    }

    public function test_it_can_compile_schema_without_being_explicitly_initialised(): void
    {
        $view = new class extends AbstractViewModel {
            public protected(set) string $foo;
            #[InternalDisplayVariable]
            public readonly string $bar;
            #[OptionalDisplayVariable]
            public protected(set) int $number = 1;
            #[RequiredDisplayVariable]
            protected string $info;
        };

        $this->assertEquals(
            new ViewModelDisplaySchema(
                expected_vars: ['foo', 'number', 'info'], defaults: ['number' => 1],
            ),
            ViewDisplaySchemaProviderInstance::getDisplaySchema($view::class),
        );
    }

    public function test_it_can_register_and_compile_with_a_custom_provider(): void
    {
        $view = new class extends AbstractViewModel {
        };

        ViewDisplaySchemaProviderInstance::init(
            provider: new class implements ViewModelDisplaySchemaProvider {
                public function getSchema(string $model_class): ViewModelDisplaySchema
                {
                    return new ViewModelDisplaySchema(
                        expected_vars: [$model_class],
                        defaults: ['two' => 15],
                    );
                }
            },
        );

        $this->assertEquals(
            new ViewModelDisplaySchema(
                expected_vars: [$view::class],
                defaults: ['two' => 15],
            ),
            ViewDisplaySchemaProviderInstance::getDisplaySchema($view::class),
        );
    }

    public function test_it_can_cache_schemas_if_a_cache_is_provided(): void
    {
        $view_1 = new class extends AbstractViewModel {
            public protected(set) string $foo;
        };

        $view_2 = new class extends AbstractViewModel {
            #[OptionalDisplayVariable]
            public protected(set) string $bar = 'whatever';
        };

        $provider = new class implements ViewModelDisplaySchemaProvider {
            public private(set) array $calls = [];

            public function getSchema(string $model_class): ViewModelDisplaySchema
            {
                $this->calls[] = $model_class;

                return new CoreDisplaySchemaProvider()->getSchema($model_class);
            }
        };

        $cache = new ArrayAdapter();
        ViewDisplaySchemaProviderInstance::init(provider: $provider, cache: $cache);

        $this->assertEquals(
            [
                new ViewModelDisplaySchema(expected_vars: ['foo'], defaults: []),
                new ViewModelDisplaySchema(expected_vars: ['bar'], defaults: ['bar' => 'whatever']),
                [$view_1::class, $view_2::class],
            ],
            [
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_1::class),
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_2::class),
                $provider->calls,
            ],
            'Behaves as expected on empty cache',
        );

        $this->assertEquals(
            [
                new ViewModelDisplaySchema(expected_vars: ['foo'], defaults: []),
                new ViewModelDisplaySchema(expected_vars: ['bar'], defaults: ['bar' => 'whatever']),
                [$view_1::class, $view_2::class],
            ],
            [
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_1::class),
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_2::class),
                $provider->calls,
            ],
            'Does not re-compile on cache hit',
        );

        $cache->clear();

        $this->assertEquals(
            [
                new ViewModelDisplaySchema(expected_vars: ['foo'], defaults: []),
                new ViewModelDisplaySchema(expected_vars: ['bar'], defaults: ['bar' => 'whatever']),
                [$view_1::class, $view_2::class, $view_1::class, $view_2::class],
            ],
            [
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_1::class),
                ViewDisplaySchemaProviderInstance::getDisplaySchema($view_2::class),
                $provider->calls,
            ],
            'Re-compiles on subsequent cache miss',
        );
    }

    public function test_its_cached_values_use_psr_compliant_key(): void
    {
        $cache = new ArrayAdapter();
        $view = new class extends AbstractViewModel {
        };
        ViewDisplaySchemaProviderInstance::init(cache: $cache);
        ViewDisplaySchemaProviderInstance::getDisplaySchema($view::class);

        $items = $cache->getValues();
        $this->assertCount(1, $items, 'Cache should have exactly one item');
        $key = array_key_first($items);
        $this->assertMatchesRegularExpression(
            '/^[A-Za-z0-9_.]{0,64}$/',
            $key,
            'Key should match expected psr/cache pattern',
        );
        $this->assertGreaterThanOrEqual(
            40,
            strlen((string) $key),
            'Key should be at least 40 chars',
        );
    }
}
