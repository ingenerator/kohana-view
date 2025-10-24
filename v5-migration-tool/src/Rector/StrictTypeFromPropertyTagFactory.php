<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;

class StrictTypeFromPropertyTagFactory
{
    public function __construct(
        private readonly StaticTypeMapper $mapper,
    ) {
    }

    public function findStrictType(PropertyTagValueNode $propertyTag, Node $class): Node|ComplexType|Identifier|Name|null
    {
        $phpStanType = $this->mapper->mapPHPStanPhpDocTypeNodeToPHPStanType($propertyTag->type, $class);

        return $this->mapper->mapPHPStanTypeToPhpParserNode($phpStanType, TypeKind::PROPERTY);
    }
}
