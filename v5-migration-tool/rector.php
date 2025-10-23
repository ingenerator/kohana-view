<?php

declare(strict_types=1);

use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
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
    );
