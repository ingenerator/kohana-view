<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\DisplaySchema;

use DateInterval;
use Ingenerator\KohanaView\ViewModelDisplaySchemaProvider;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ViewDisplaySchemaProviderInstance
{
    private static ?ViewModelDisplaySchemaProvider $provider = null;
    private static ?CacheItemPoolInterface $cache = null;
    private static ?DateInterval $cache_ttl = null;

    /**
     * Initialise the global singleton for generating expected ->display() schema for view models.
     *
     * If the singleton is not initialised, the generic CoreDisplaySchemaProvider will be used -
     * however we strongly recommend registering a cache in production environments to avoid
     * using reflection on every model at runtime.
     */
    public static function init(
        ViewModelDisplaySchemaProvider $provider = new CoreDisplaySchemaProvider(),
        ?CacheItemPoolInterface $cache = null,
        ?DateInterval $cache_ttl = null,
    ): void {
        self::$provider = $provider;
        self::$cache = $cache;
        self::$cache_ttl = $cache_ttl;
    }

    /**
     * @internal used for testing, not expected to be used in external code
     */
    public static function resetToDefault(): void
    {
        self::$provider = null;
        self::$cache = null;
        self::$cache_ttl = null;
    }

    public static function getDisplaySchema(string $model_class): ViewModelDisplaySchema
    {
        self::$provider ??= new CoreDisplaySchemaProvider();

        $cached = self::$cache?->getItem('vds.'.hash('sha1', $model_class));
        if ($cached && $cached->isHit()) {
            return $cached->get();
        }

        $result = self::$provider->getSchema($model_class);

        if ($cached instanceof CacheItemInterface) {
            $cached->set($result);
            $cached->expiresAfter(self::$cache_ttl);
            self::$cache->save($cached);
        }

        return $result;
    }
}
