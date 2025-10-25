<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
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

        $variablesProp = $node->getProperty('variables');
        $propNames = $this->findDisplayPropertiesFromVariablesDefinition($variablesProp, $node);

        if ($propNames === []) {
            // Nothing to migrate
            return null;
        }

        $classPhpDoc = $this->phpDocInfoFactory->createFromNode($node);
        $phpDocPropertyDeclarations = $this->dynamicPropertyManager->findDynamicPropertiesFromPhpdoc($classPhpDoc);

        $phpDocToRemove = [];
        $newProperties = [];
        foreach ($propNames as $propertyName) {
            $phpDocToRemove[] = $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;
            $newProperties[] = $this->propertyFactory->createDisplayProperty(
                $propertyName,
                $node,
                $propertyTag,
            );
        }

        $this->viewModelUpdater->updateClass(
            $node,
            $classPhpDoc,
            insertProperties: $newProperties,
            removePhpDoc: $phpDocToRemove,
            removeStatements: [$variablesProp],
        );

        return $node;
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
}
