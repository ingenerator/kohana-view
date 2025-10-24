<?php

declare(strict_types=1);

use Ingenerator\RiskyRectorRules\PhpDocToStrictTypes\AddParamTypeFromPhpDocRector;
use Ingenerator\RiskyRectorRules\PhpDocToStrictTypes\AddPropertyTypeFromPhpDocRector;
use Ingenerator\RiskyRectorRules\PhpDocToStrictTypes\AddReturnTypeFromPhpDocRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
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
        // Temporarily disable while we're doing typehinting to avoid mixing these in
        ClassPropertyAssignToConstructorPromotionRector::class,
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
    )
    ->withRules([
        AddPropertyTypeFromPhpDocRector::class,
        AddParamTypeFromPhpDocRector::class,
        AddReturnTypeFromPhpDocRector::class,
    ]);
