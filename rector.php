<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;

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
        //        \Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        // ClosureToArrowFunctionRector::class,
        // ReturnNeverTypeRector::class,
        RemoveUnusedVariableInCatchRector::class,
        //        \Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        //        \Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
    ])
    ->withImportNames(
        removeUnusedImports: true,
    );
