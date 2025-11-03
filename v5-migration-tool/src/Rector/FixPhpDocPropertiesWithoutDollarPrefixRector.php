<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use InvalidArgumentException;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\Node as PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Rector\AbstractRector;
use RuntimeException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;

final class FixPhpDocPropertiesWithoutDollarPrefixRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter $classFilter,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Fixes defined `@property` and `@property-read` properties to have a $ prefix if missing',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        /**
                         * @property SomeThing foo
                         * @property-read ?string anything with a description
                         */
                        class SomeView extends AbstractViewModel
                        {
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        /**
                         * @property SomeThing $foo
                         * @property-read ?string $anything with a description
                         */
                        class SomeView extends AbstractViewModel
                        {
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
        if ( ! $classPhpDoc instanceof PhpDocInfo) {
            // No phpdoc to fix
            return null;
        }

        $hasChanged = false;
        new PhpDocNodeTraverser()
            ->traverseWithCallable(
                $classPhpDoc->getPhpDocNode(),
                '',
                function (PhpDocNode $tag) use (&$hasChanged): ?PhpDocTextNode {
                    if ( ! $this->isInvalidPropertyTagNode($tag)) {
                        return null;
                    }

                    // @todo: This is ugly, it would be much better to fix this in the PHPDoc Parser itself
                    if ( ! $tag->value instanceof DoctrineAnnotationTagValueNode) {
                        // the phpdoc-parser behaviour has changed
                        throw new RuntimeException('Unexpected parsing of '.$tag->name.' as '.$tag->value::class);
                    }

                    $originalText = $tag->name.' '.$tag->value->getOriginalContent();
                    if ( ! preg_match(
                        '/^(?P<tag>@property|@property-read)\s+(?P<type>[^\s]+)\s+(?P<name>[^\s]+)(?P<description>\s*.*)$/',
                        $originalText,
                        $matches,
                    )) {
                        throw new InvalidArgumentException('Cannot parse "'.$originalText.'" as a property tag');
                    }

                    $hasChanged = true;

                    return new PhpDocTextNode(
                        $matches['tag'].' '.$matches['type'].' $'.$matches['name'].$matches['description'],
                    );
                },
            );

        if ( ! $hasChanged) {
            return null;
        }

        $node->setDocComment(new Doc($classPhpDoc->getPhpDocNode()->__toString()));

        // We need to return a clone of the node, so that the phpdoc is re-parsed for future Rectors
        // Because the parsed phpdoc is cached by spl_object_id and therefore will still hold the
        // PhpDocTextNode instances we created above.
        return clone $node;
    }

    private function isInvalidPropertyTagNode(PhpDocNode $tagNode): bool
    {
        if ( ! $tagNode instanceof PhpDocTagNode) {
            return false;
        }

        if ($tagNode->name === '@property-read' || $tagNode->name === '@property') {
            return ! $tagNode->value instanceof PropertyTagValueNode;
        }

        return false;
    }
}
