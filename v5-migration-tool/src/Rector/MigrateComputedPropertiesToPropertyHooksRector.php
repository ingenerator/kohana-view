<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;

final class MigrateComputedPropertiesToPropertyHooksRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
        private readonly BuilderFactory $builderFactory,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly NewViewPropertyInserter $propertyInserter,
        private readonly PhpDocDynamicPropertyManager $dynamicPropertyManager,
        private readonly ViewDisplayPropertyFactory $viewPropertyFactory,
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
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);

        if ( ! $classReflection->is(AbstractViewModel::class)) {
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

        $newProperties = [];
        $phpDocToRemove = [];

        foreach ($candidateMethods as $propertyName => $varMethod) {
            $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;
            if ($propertyTag) {
                $phpDocToRemove[] = $propertyTag;
            }

            $newProperties[] = $this->viewPropertyFactory->createComputedProperty(
                $propertyName,
                $node,
                $propertyTag,
                $varMethod
            );
        }

        $this->propertyInserter->insertNewProperties($node, $newProperties);
        if ($phpDocToRemove !== []) {
            $this->dynamicPropertyManager->removePropertyTags($classPhpDoc, ...$phpDocToRemove);
        }

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
}
