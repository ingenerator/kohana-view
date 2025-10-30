<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function assert;

final class AddKohanaViewReturnTypesRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter                                   $classFilter,
        private readonly AddReturnTypeDeclarationBasedOnParentClassMethodRector $chainedRector
    )
    {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds return types to classes that implement ViewModel or TemplateSpecifyingViewModel',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel implements TemplateSpecifyingViewModel
                        {
                            public function display($variables)
                            {
                                $variables['foo'] = 'bar';
                                parent::display($variables);
                            }
                        
                            public function getTemplateName()
                            {
                                return 'foobar';
                            }

                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel implements TemplateSpecifyingViewModel
                        {
                            public function display($variables): void
                            {
                                $variables['foo'] = 'bar';
                                parent::display($variables);
                            }
                        
                            public function getTemplateName(): string
                            {
                                return 'foobar';
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
        if (!$this->classFilter->implementsAnyKohanaViewInterface($node)) {
            // Not an AbstractViewModel
            return null;
        }

        return $this->chainedRector->refactor($node);
    }
}
