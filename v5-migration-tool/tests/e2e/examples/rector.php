<?php

declare(strict_types=1);

use Ingenerator\KohanaViewV5MigrationTool\KohanaViewV5Migration;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/views',
    ])
    ->withRootFiles()
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withSets([KohanaViewV5Migration::SET_PATH]);
