<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/classes',
        __DIR__.'/config',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withPreparedSets(
        codeQuality: true,
        earlyReturn: true,
    )
    ->withPhpSets()
    ->withSkip([
        ClosureToArrowFunctionRector::class => [
            // Needs to be a traditional function in order to restrict the scope
            __DIR__.'/classes/Ingenerator/KohanaView/Renderer/HTMLRenderer.php',
        ],
        ReturnNeverTypeRector::class => [
            // This stub function shouldn't return `never` as it will actually "return" a string
            __DIR__.'/classes/raw_fn_stub.php',
        ],
    ])
    ->withImportNames(
        removeUnusedImports: true,
    );
