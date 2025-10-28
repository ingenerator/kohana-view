<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/v5-migration-tool',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/v5-migration-tool/tests/e2e',
        __DIR__.'/v5-migration-tool/vendor'
    ])
    ->withRootFiles()
    ->withPreparedSets(
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
    )
    ->withPhpSets()
    ->withAttributesSets(all: true)
    ->withSkip([
        ClosureToArrowFunctionRector::class => [
            // Needs to be a traditional function in order to restrict the scope
            __DIR__.'/src/Renderer/HTMLRenderer.php',
        ],
        AddFunctionVoidReturnTypeWhereNoReturnRector::class => [
            // This stub function shouldn't return `void` as it will actually "return" a string
            __DIR__.'/src/raw_fn_stub.php',
        ],
        ReturnNeverTypeRector::class => [
            // This stub function shouldn't return `never` as it will actually "return" a string
            __DIR__.'/src/raw_fn_stub.php',
        ],
    ])
    ->withImportNames(
        removeUnusedImports: true,
    );
