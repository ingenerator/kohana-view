<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Override;
use PhpParser\BuilderFactory;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;

final class DisableCustomVarValidationRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter $classFilter,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer,
        private readonly BuilderFactory $builderFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Marks up any custom validateDisplayVariables for review as this is now removed',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            protected function validateDisplayVariables() {
                               return [
                                 ...parent::validateDisplayVariables(),
                                 'some custom problem'
                               ]; 
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            #[Override]
                            protected function validateDisplayVariables() {
                               //@todo: This method has been removed from AbstractViewModel and will never be called
                               return [
                                 ...parent::validateDisplayVariables(),
                                 'some custom problem'
                               ]; 
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

        $method = $node->getMethod('validateDisplayVariables');
        if ( ! $method instanceof ClassMethod) {
            return null;
        }

        if ($this->phpAttributeAnalyzer->hasPhpAttribute($method, Override::class)) {
            // Already marked
            return null;
        }

        if ($method->stmts !== []) {
            // Comments in php-parser are associated with the statement,
            $method->stmts[0]->setAttribute('comments', [
                new Comment('// @todo: This method has been removed from AbstractViewModel and will never be called'),
                ...$method->stmts[0]->getComments(),
            ]);
        }

        $method->attrGroups[] = new AttributeGroup([
            $this->builderFactory->attribute(new FullyQualified(Override::class)),
        ]);

        return $node;
    }
}
