<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

final class NewViewPropertyInserter
{
    public function insertNewProperties(Class_ $class, array $newProperties): void
    {
        $firstMethodIndex = array_find_key($class->stmts, fn (Node $n): bool => $n instanceof ClassMethod);

        if ($firstMethodIndex === null) {
            // No methods in this view class - just insert the new properties at the end of the class
            array_push($class->stmts, ...$newProperties);

            return;
        }

        if ($firstMethodIndex === 0) {
            // There are no existing properties, just insert at the start
            array_unshift($class->stmts, ...$newProperties);

            return;
        }

        // Otherwise insert before the first method
        array_splice(
            $class->stmts,
            offset: $firstMethodIndex - 1,
            length: 0,
            replacement: $newProperties,
        );
    }
}
