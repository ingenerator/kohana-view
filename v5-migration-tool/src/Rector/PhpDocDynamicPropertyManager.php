<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use UnexpectedValueException;

use function assert;
use function sprintf;

final readonly class PhpDocDynamicPropertyManager
{
    public function __construct(
        private DocBlockUpdater $docBlockUpdater,
        private PhpDocTagRemover $phpDocTagRemover,
    ) {
    }

    /**
     * @return array<string, PropertyTagValueNode>
     */
    public function findDynamicPropertiesFromPhpdoc(PhpDocInfo $classPhpdoc): array
    {
        assert($classPhpdoc->getNode() instanceof Class_);
        $className = $classPhpdoc->getNode()->name->toString();

        $props = [];

        foreach (
            [
                ...$classPhpdoc->getTagsByName('property-read'),
                ...$classPhpdoc->getTagsByName('property'),
            ] as $propertyTag
        ) {
            $tagValue = $propertyTag->value;

            if ( ! $tagValue instanceof PropertyTagValueNode) {
                throw new UnexpectedValueException(
                    sprintf(
                        'Could not parse @property tags from %s - expected a PropertyTagValueNode, got %s',
                        $className,
                        $tagValue::class,
                    ),
                );
            }

            $propertyName = preg_replace('/^\$/', '', $tagValue->propertyName);

            if (isset($props[$propertyName])) {
                throw new UnexpectedValueException(
                    sprintf(
                        'Duplicate @property-read / @property tags for %s on %s',
                        $propertyName,
                        $className,
                    ),
                );
            }

            $props[$propertyName] = $tagValue;
        }

        return $props;
    }

    public function removePropertyTags(PhpDocInfo $classPhpDoc, PropertyTagValueNode ...$tagsToRemove): void
    {
        foreach ($tagsToRemove as $tag) {
            $this->phpDocTagRemover->removeTagValueFromNode($classPhpDoc, $tag);
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classPhpDoc->getNode());
    }
}
