<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use RuntimeException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;
use function count;

final class MigrateDisplayVariablesToNativePropertiesRector extends AbstractRector
{
    public function __construct(
        private readonly AbstractViewModelClassFilter $classFilter,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocDynamicPropertyManager $dynamicPropertyManager,
        private readonly ViewDisplayPropertyFactory $propertyFactory,
        private readonly ViewModelClassUpdater $viewModelUpdater,
        private readonly BuilderFactory $builderFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts the old $variables array to native property declarations',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        /**
                         * @property-read string $foo
                         */
                        class SomeView extends AbstractViewModel
                        {
                            protected $variables = [
                                'foo' => null,
                                'unknown' => null,
                            ]
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            public protected(set) string $foo;
                            public protected(set) mixed $unknown;
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

        $classPhpDoc = $this->phpDocInfoFactory->createFromNode($node);

        $variablesProp = $node->getProperty('variables');

        // 1. Generate properties for elements in the default value of the $variables property
        $definedVariables = $this->findDisplayPropertiesFromVariablesDefinition($variablesProp, $node);
        if ($definedVariables !== []) {
            $this->defineNativeProperties($node, $classPhpDoc, $definedVariables);
        }

        // 2. Convert read/write in the $variables property to direct property access
        $directlyAccessedPropNames = $this->updateVariableReadWriteToOwnProperties($node);

        // 3. Generate any missing properties
        $undefinedVariables = $this->findUndefinedPropertyNames($node, $directlyAccessedPropNames);
        if ($undefinedVariables !== []) {
            $this->defineNativeProperties($node, $classPhpDoc, $undefinedVariables);
        }

        // 4. Remove $variables if it exists
        if ($variablesProp instanceof Property) {
            $this->viewModelUpdater->updateClass(
                $node,
                $classPhpDoc,
                removeStatements: [$variablesProp],
            );
        }

        if (
            $variablesProp instanceof Property
            || $definedVariables !== []
            || $directlyAccessedPropNames !== []
            || $undefinedVariables !== []
        ) {
            return $node;
        }

        // Nothing changed
        return null;
    }

    private function defineNativeProperties(Class_ $class, PhpDocInfo $classPhpDoc, array $propNames): void
    {
        $phpDocPropertyDeclarations = $this->dynamicPropertyManager->findDynamicPropertiesFromPhpdoc($classPhpDoc);

        $phpDocToRemove = [];
        $newProperties = [];
        foreach ($propNames as $propertyName) {
            $phpDocToRemove[] = $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;
            $newProperties[] = $this->propertyFactory->createDisplayProperty(
                $propertyName,
                $class,
                $propertyTag,
            );
        }

        $this->viewModelUpdater->updateClass(
            $class,
            $classPhpDoc,
            insertProperties: $newProperties,
            removePhpDoc: $phpDocToRemove,
        );
    }

    private function findDisplayPropertiesFromVariablesDefinition(?Property $variablesProp, Class_ $node): array
    {
        if ( ! $variablesProp instanceof Property) {
            // Doesn't have any variables to migrate
            return [];
        }

        assert(count($variablesProp->props) === 1, 'Expected $variables to be defined as a single property');
        $default = $variablesProp->props[0]->default;
        if ( ! $default instanceof Array_) {
            throw new RuntimeException('Expected '.$node->name->toString().'::variables to default to an array');
        }

        $names = [];
        foreach ($default->items as $item) {
            if ( ! $item->key instanceof String_) {
                throw new RuntimeException(
                    'Expected everything in '.$node->name->toString().'::variables to have string keys',
                );
            }
            $names[] = $item->key->value;
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function updateVariableReadWriteToOwnProperties(Class_ $class): array
    {
        $foundPropertyNames = [];
        $this->traverseNodesWithCallable(
            $class->stmts,
            function (Node $subNode) use (&$foundPropertyNames): ?Node {
                if (
                    $subNode instanceof ArrayDimFetch
                    && $subNode->var instanceof PropertyFetch
                    && $subNode->var->var instanceof Variable
                    && $subNode->var->name instanceof Identifier
                    && $subNode->dim instanceof String_
                    && $subNode->var->var->name === 'this'
                    && $subNode->var->name->name === 'variables'
                ) {
                    $propertyName = $subNode->dim->value;
                    $foundPropertyNames[] = $propertyName;

                    return $this->builderFactory->propertyFetch($this->builderFactory->var('this'), $propertyName);
                }

                return null;
            },
        );

        return array_unique($foundPropertyNames);
    }

    /**
     * @param list<string> $directlyAccessedPropNames
     *
     * @return list<string>
     */
    private function findUndefinedPropertyNames(Class_ $class, array $directlyAccessedPropNames): array
    {
        $knownNames = [];
        foreach ($class->getProperties() as $property) {
            foreach ($property->props as $prop) {
                $knownNames[] = $prop->name;
            }
        }

        return array_diff($directlyAccessedPropNames, $knownNames);
    }
}
