<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;

use function assert;

class DropAbstractViewModelConstructorRector extends AbstractRector
{
    public function __construct(
        private readonly ViewModelClassFilter $classFilter,
        private readonly ReflectionResolver $reflectionResolver,
    ) {
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node)
    {
        assert($node instanceof Class_);
        if ( ! $this->classFilter->isAbstractViewModelClass($node)) {
            // Not an AbstractViewModel
            return null;
        }

        $constructor = $node->getMethod('__construct');
        if ( ! $constructor instanceof ClassMethod) {
            // No constructor
            return null;
        }

        if ($this->shouldSkipClass($node)) {
            return null;
        }

        foreach ($constructor->stmts as $index => $stmt) {
            if ($this->isParentConstructorCall($stmt)) {
                unset($constructor->stmts[$index]);

                return $node;
            }
        }

        // Nothing removed
        return null;
    }

    private function isParentConstructorCall(mixed $stmt): bool
    {
        if ( ! $stmt instanceof Expression) {
            return false;
        }

        if ( ! $stmt->expr instanceof StaticCall) {
            return false;
        }

        return
            $stmt->expr->class instanceof Name
            && $stmt->expr->class->toString() === 'parent'
            && $stmt->expr->name instanceof Identifier
            && $stmt->expr->name->toString() === '__construct'
        ;
    }

    private function shouldSkipClass(Class_ $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if ( ! $classReflection instanceof ClassReflection) {
            // Can't resolve reflection for this class, err on side of safety and abort
            return true;
        }

        $parentReflection = $classReflection->getParentClass();
        while ($parentReflection) {
            if ($parentReflection->hasMethod('__construct')) {
                // A parent class does have a constructor so we need to call it
                return true;
            }
            $parentReflection = $parentReflection->getParentClass();
        }

        // There are no parents with constructors so we can remove any parent call
        return false;
    }
}
