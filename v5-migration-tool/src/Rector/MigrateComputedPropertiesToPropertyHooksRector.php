<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;
use function in_array;

final class MigrateComputedPropertiesToPropertyHooksRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter         $classFilter,
        private readonly PhpDocInfoFactory            $phpDocInfoFactory,
        private readonly PhpDocDynamicPropertyManager $dynamicPropertyManager,
        private readonly ViewDisplayPropertyFactory   $viewPropertyFactory,
        private readonly ViewModelClassUpdater        $viewModelUpdater,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts dynamic view properties from var_xxx methods to properties with get hooks',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            protected function var_whatever() 
                            {
                                return 'foo';
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            public mixed $whatever {
                              get => $this->var_whatever();
                            }

                            protected function var_whatever() 
                            {
                                return 'foo';
                            }
                        }
                        CODE_SAMPLE,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        assert($node instanceof Class_);
        if ( ! $this->classFilter->isAbstractViewModelClass($node)) {
            // Not an AbstractViewModel
            return null;
        }

        $candidateMethods = $this->findComputedViewVarMethods($node);
        if ($candidateMethods === []) {
            // No dynamic methods in this view
            return null;
        }

        $classPhpDoc = $this->phpDocInfoFactory->createFromNode($node);
        $phpDocPropertyDeclarations = $this->dynamicPropertyManager->findDynamicPropertiesFromPhpdoc($classPhpDoc);

        // First, identify the properties to add and phpdoc to remove
        $newProperties = [];
        $phpDocToRemove = [];
        foreach ($candidateMethods as $propertyName => $varMethod) {
            $phpDocToRemove[] = $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;
            $newProperties[] = $this->viewPropertyFactory->createComputedProperty(
                $propertyName,
                $node,
                $propertyTag,
                $varMethod,
            );
        }

        $this->viewModelUpdater->updateClass(
            $node,
            $classPhpDoc,
            insertProperties: $newProperties,
            removePhpDoc: $phpDocToRemove,
        );

        // Once all hooks have been optimised into one-liners where possible, we can check for any var_ methods that
        // are no longer required (not public, not called from inside this class, and begin var_)
        $methodsToRemove = $this->findRedundantVarMethods($node);
        $this->viewModelUpdater->updateClass($node, $classPhpDoc, removeStatements: $methodsToRemove);

        return $node;
    }

    private function findComputedViewVarMethods(Class_ $class): array
    {
        $varMethods = array_filter(
            $class->getMethods(),
            fn (ClassMethod $m): bool => str_starts_with($m->name->toString(), 'var_'),
        );
        $candidateMethods = [];
        foreach ($varMethods as $varMethod) {
            $propertyName = preg_replace('/^var_/', '', $varMethod->name->toString());
            if ($class->getProperty($propertyName) instanceof Property) {
                // Already have a native property with this name
                continue;
            }

            $candidateMethods[$propertyName] = $varMethod;
        }

        return $candidateMethods;
    }

    private function findVarMethodsCalledInClass(Class_ $classNode): array
    {
        $varMethodsCalled = [];
        $this->traverseNodesWithCallable(
            $classNode->stmts,
            function (Node $subnode) use (&$varMethodsCalled): void {
                if (
                    $subnode instanceof MethodCall
                    && str_starts_with($subnode->name->toString(), 'var_')
                ) {
                    $varMethodsCalled[] = $subnode->name->toString();
                }
            },
        );

        return array_unique($varMethodsCalled);
    }

    private function findRedundantVarMethods(Class_ $classNode): array
    {
        $varMethodsCalled = $this->findVarMethodsCalledInClass($classNode);

        return array_filter(
            $classNode->getMethods(),
            fn (ClassMethod $classMethod): bool => str_starts_with($classMethod->name->toString(), 'var_')
                && ! in_array($classMethod->name->toString(), $varMethodsCalled, true)
                && ! $classMethod->isPublic(),
        );
    }
}
