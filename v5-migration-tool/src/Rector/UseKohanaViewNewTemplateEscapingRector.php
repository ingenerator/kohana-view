<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;
use function count;

class UseKohanaViewNewTemplateEscapingRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Upgrades to our new escaping and raw() strategy for templates',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        <h1><?=raw($view->status_label);?></h1>
                        <div><?=raw($renderer->render($view->child_view));?></div>
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        <?php
                        use function Ingenerator\KohanaView\OutputValue\raw;
                        ?>
                        <h1><?=raw($view->status_label);?></h1>
                        <div><?=$view->child_view;?></div>
                        CODE_SAMPLE,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        assert($node instanceof FuncCall);
        if ( ! $node->name instanceof Name) {
            return null;
        }

        if ($node->name->toString() !== 'raw') {
            return null;
        }

        if ($nestedChildToRender = $this->findNestedChildRenderArg($node)) {
            return $nestedChildToRender;
        }

        $node->name = new FullyQualified('Ingenerator\KohanaView\OutputValue\raw');

        return $node;
    }

    private function findNestedChildRenderArg(FuncCall $func_call): false|Expr
    {
        if (count($func_call->args) !== 1) {
            return false;
        }

        $arg = array_first($func_call->args);
        if (
            $arg->value instanceof MethodCall
            && $arg->value->var instanceof Variable
            && $arg->value->var->name === 'renderer'
            && $arg->value->name instanceof Identifier
            && $arg->value->name->name === 'render'
            && count($arg->value->args) === 1
        ) {
            return array_first($arg->value->args)->value;
        }

        return false;
    }
}
