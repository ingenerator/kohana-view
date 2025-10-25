<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

use function in_array;

final readonly class ViewModelClassUpdater
{
    public function __construct(
        private PhpDocDynamicPropertyManager $dynamicPropertyManager)
    {
    }

    public function updateClass(
        Class_ $class,
        ?PhpDocInfo $classPhpDoc,
        array $insertProperties = [],
        array $removePhpDoc = [],
        array $removeStatements = [],
    ): bool {
        $hasChanged = false;
        if ($insertProperties !== []) {
            $this->insertNewProperties($class, ...$insertProperties);
            $hasChanged = true;
        }

        $removePhpDoc = array_filter($removePhpDoc);
        if ($removePhpDoc !== []) {
            $this->dynamicPropertyManager->removePropertyTags($classPhpDoc, ...$removePhpDoc);
            $hasChanged = true;
        }

        if ($removeStatements !== []) {
            $this->removeStatements($class, ...$removeStatements);
            $hasChanged = true;
        }

        return $hasChanged;
    }

    private function insertNewProperties(Class_ $class, Property ...$newProperties): void
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

    private function removeStatements(Class_ $node, Node ...$statementsToRemove): void
    {
        foreach ($node->stmts as $index => $stmt) {
            if (in_array($stmt, $statementsToRemove, true)) {
                unset($node->stmts[$index]);
            }
        }
    }
}
