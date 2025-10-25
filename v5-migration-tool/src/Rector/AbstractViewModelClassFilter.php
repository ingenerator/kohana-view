<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Reflection\ReflectionResolver;

final readonly class AbstractViewModelClassFilter
{
    public function __construct(private ReflectionResolver $reflectionResolver)
    {
    }

    public function isAbstractViewModelClass(Class_ $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);

        if ( ! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $classReflection->is(AbstractViewModel::class);
    }
}
