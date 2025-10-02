<?php

declare(strict_types=1);

use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/classes',
        __DIR__.'/config',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withCodeQualityLevel(100)
//    ->withPhpSets()
    ->withRules([
        ClassConstantToSelfClassRector::class,
        StringClassNameToClassConstantRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        // ClosureToArrowFunctionRector::class,
        // ReturnNeverTypeRector::class,
        RemoveUnusedVariableInCatchRector::class,
        NullToStrictStringFuncCallArgRector::class,
                ReadOnlyPropertyRector::class,
    ])
    ->withImportNames(
        removeUnusedImports: true,
    );
