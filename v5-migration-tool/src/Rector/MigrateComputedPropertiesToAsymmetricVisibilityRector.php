<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;
use function count;

final class MigrateComputedPropertiesToAsymmetricVisibilityRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter $classFilter,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocDynamicPropertyManager $dynamicPropertyManager,
        private readonly ViewModelClassUpdater $viewClassUpdater,
        private readonly PropertyDeclarationResolver $propertyResolver,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts view properties from simple var_xxx getters to asymmetric visibility',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            protected string $value;
                            
                            protected function var_value() 
                            {
                                return $this->value;
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            protected(set) string $value;
                            
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

        $candidateMethods = $this->findCandidateMethods($node);
        if ($candidateMethods === []) {
            // No suitable methods in this view
            return null;
        }

        $classPhpDoc = $this->phpDocInfoFactory->createFromNode($node);
        $phpDocPropertyDeclarations = $this->dynamicPropertyManager->findDynamicPropertiesFromPhpdoc($classPhpDoc);

        $phpDocToRemove = [];
        $methodsToRemove = [];
        foreach ($candidateMethods as $propertyName => $varMethod) {
            if ( ! $this->isSimplePropertyReturnMethod($varMethod, $propertyName)) {
                continue;
            }

            $propertyNode = $this->propertyResolver->getPropertyDeclaration($node, $propertyName);
            assert($propertyNode !== null);

            $phpDocToRemove[] = $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;

            $this->makePropertyPublicProtectedSet($propertyNode);

            if ( ! $propertyNode->getDocComment() instanceof Doc && $propertyTag?->description !== '') {
                $propertyNode->setDocComment(new Doc("/**\n * ".$propertyTag->description."\n */"));
            }

            $methodsToRemove[] = $varMethod;
        }

        $this->viewClassUpdater->updateClass(
            $node,
            $classPhpDoc,
            removePhpDoc: $phpDocToRemove,
            removeStatements: $methodsToRemove,
        );

        return $node;
    }

    private function findCandidateMethods(Class_ $class): array
    {
        $varMethods = array_filter(
            $class->getMethods(),
            fn (ClassMethod $m): bool => str_starts_with($m->name->toString(), 'var_'),
        );
        $candidateMethods = [];
        foreach ($varMethods as $varMethod) {
            $propertyName = preg_replace('/^var_/', '', $varMethod->name->toString());
            $existingProp = $this->propertyResolver->getPropertyDeclaration($class, $propertyName);
            if ($existingProp === null) {
                // Don't have a property yet with this name, so this is more than asymmetric visibility
                continue;
            }

            if ($existingProp->hooks !== []) {
                // Property has hooks, so it's not something we can deal with
                continue;
            }
            if ($existingProp->isProtectedSet()) {
                // It's already been looked at
                continue;
            }
            if ($existingProp->isPrivateSet()) {
                // It's already been looked at
                continue;
            }

            $candidateMethods[$propertyName] = $varMethod;
        }

        return $candidateMethods;
    }

    private function isSimplePropertyReturnMethod(ClassMethod $varMethod, string $propertyName): bool
    {
        if (count($varMethod->stmts) !== 1) {
            // It's a more complex method
            return false;
        }

        $statement = $varMethod->stmts[0];
        if ( ! $statement instanceof Return_) {
            // It's doing something other than returning a value
            return false;
        }

        return $statement->expr instanceof PropertyFetch
            && $statement->expr->var->name === 'this'
            && $statement->expr->name->name === $propertyName;
    }

    private function makePropertyPublicProtectedSet(Param|Property $propertyNode): void
    {
        // Turn off existing visibility modifiers
        $flags = $propertyNode->flags
            & ~Modifiers::PROTECTED
            & ~Modifiers::PRIVATE
            & ~Modifiers::PRIVATE_SET;

        if ($propertyNode instanceof Param && $propertyNode->isPromoted()) {
            // Promoted properties become public readonly
            $propertyNode->flags = $flags | Modifiers::PUBLIC | Modifiers::READONLY;
        } else {
            // Direct properties become public protected set (needs to be protected so the base view class can access it for display)
            $propertyNode->flags = $flags | Modifiers::PROTECTED_SET | Modifiers::PUBLIC;
        }
    }
}
