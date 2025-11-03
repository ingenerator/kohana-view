<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Ingenerator\KohanaView\Renderer;
use Ingenerator\KohanaView\Renderer\PageLayoutRenderer;
use Ingenerator\KohanaView\TemplateManager;
use Ingenerator\KohanaView\TemplateSpecifyingViewModel;
use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Reflection\ReflectionResolver;

final readonly class ViewModelClassFilter
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

    public function implementsAnyKohanaViewInterface(Class_ $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);

        if ( ! $classReflection instanceof ClassReflection) {
            return false;
        }
        if ($classReflection->is(ViewModel::class)) {
            return true;
        }
        if ($classReflection->is(TemplateSpecifyingViewModel::class)) {
            return true;
        }
        if ($classReflection->is(TemplateSpecifyingViewModel::class)) {
            return true;
        }
        if ($classReflection->is(Renderer::class)) {
            return true;
        }
        if ($classReflection->is(PageLayoutRenderer::class)) {
            return true;
        }

        return $classReflection->is(TemplateManager::class);
    }
}
