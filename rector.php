<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/classes',
        __DIR__.'/config',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withCodeQualityLevel(100)
    ->withImportNames(
        removeUnusedImports: true,
    );
