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
use PhpParser\Node\Stmt\InlineHTML;
use Rector\PhpParser\Node\FileNode;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RuntimeException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;

class UseKohanaViewNewTemplateEscapingRector extends AbstractRector
{
    private bool $fileStartsWithPhp;
    private bool $hasAlreadyAddedImport = false;

    public function __construct(
        private readonly UseNodesToAddCollector $useNodesToAddCollector,
        private readonly FullyQualifiedNodeMapper $fullyQualifiedNodeMapper,
    ) {
    }

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

    private function doesFileStartWithPhp(FileNode $node): bool
    {
        if ($node->stmts === []) {
            return false;
        }

        $firstNode = array_first($node->stmts);

        return ! $firstNode instanceof InlineHTML;
    }

    public function getNodeTypes(): array
    {
        return [
            FileNode::class,
            FuncCall::class,
        ];
    }

    /**
     * @param FileNode|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        return match (true) {
            $node instanceof FileNode => $this->refactorFileNode($node),
            $node instanceof FuncCall => $this->refactorFuncCallNode($node),
            default => throw new RuntimeException('Unexpected node type: '.$node::class),
        };
    }

    private function refactorFileNode(FileNode $node): ?Node
    {
        $this->hasAlreadyAddedImport = false;
        $this->fileStartsWithPhp = $this->doesFileStartWithPhp($node);

        return $node;
    }

    private function refactorFuncCallNode(FuncCall $node): ?Node
    {
        if ( ! $node->name instanceof Name) {
            return null;
        }

        if ($node->name->toString() !== 'raw') {
            return null;
        }

        if ($nestedChildToRender = $this->findNestedChildRenderArg($node)) {
            return $nestedChildToRender;
        }

        $newFunctionName = new FullyQualified('Ingenerator\KohanaView\OutputValue\raw');
        $newFuncImportType = $this->fullyQualifiedNodeMapper->mapToPHPStan($newFunctionName);

        if ($this->fileStartsWithPhp && $newFuncImportType instanceof FullyQualifiedObjectType) {
            // The node itself doesn't need to change, just add a use statement (if it's the first in this file)
            if ( ! $this->hasAlreadyAddedImport) {
                $this->useNodesToAddCollector->addFunctionUseImport($newFuncImportType);
                $this->hasAlreadyAddedImport = true;
            }

            return null;
        }

        // edge case, either Rector can't deduce the import statement needed, or the file doesn't have php at the
        // start (meaning Rector won't correctly add use statments) so we just make it a fully qualified call.
        $node->name = $newFunctionName;

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
