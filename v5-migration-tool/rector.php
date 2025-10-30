<?php

declare(strict_types=1);

use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
use Ingenerator\KohanaViewV5MigrationTool\Rector\AddKohanaViewReturnTypesRector;
use Ingenerator\KohanaViewV5MigrationTool\Rector\DisableCustomVarValidationRector;
use Ingenerator\KohanaViewV5MigrationTool\Rector\FixPhpDocPropertiesWithoutDollarPrefixRector;
use Ingenerator\KohanaViewV5MigrationTool\Rector\MigrateComputedPropertiesToAsymmetricVisibilityRector;
use Ingenerator\KohanaViewV5MigrationTool\Rector\MigrateComputedPropertiesToPropertyHooksRector;
use Ingenerator\KohanaViewV5MigrationTool\Rector\MigrateDisplayVariablesToNativePropertiesRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

$project_base_dir = getcwd();

return RectorConfig::configure()
    ->withPaths([$project_base_dir])
    ->withSkip([
        $project_base_dir.'/vendor',
    ])
    ->withRootFiles()
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withConfiguredRule(
        RenameClassRector::class,
        [
            PageContentView::class => NestedChildView::class,
            PageLayoutView::class => NestedParentView::class,
        ],
    )
    ->withRules([
        FixPhpDocPropertiesWithoutDollarPrefixRector::class,
        MigrateComputedPropertiesToAsymmetricVisibilityRector::class,
        MigrateComputedPropertiesToPropertyHooksRector::class,
        MigrateDisplayVariablesToNativePropertiesRector::class,
        DisableCustomVarValidationRector::class,
        AddKohanaViewReturnTypesRector::class
    ]);
