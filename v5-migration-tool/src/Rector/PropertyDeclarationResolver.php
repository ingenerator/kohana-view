<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

class PropertyDeclarationResolver
{
    public function getPropertyDeclaration(Class_ $class, string $propertyName): Param|Property|null
    {
        $directProp = $class->getProperty($propertyName);
        if ($directProp instanceof Property) {
            return $directProp;
        }

        $constructor = $class->getMethod('__construct');
        if ( ! $constructor instanceof ClassMethod) {
            return null;
        }
        foreach ($constructor->getParams() as $param) {
            if ( ! $param->isPromoted()) {
                continue;
            }

            if ($param->var->name === $propertyName) {
                return $param;
            }
        }

        return null;
    }

    public function hasPropertyDeclaration(Class_ $class, string $propertyName): bool
    {
        return $this->getPropertyDeclaration($class, $propertyName) !== null;
    }
}
