<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyHook;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function assert;

final class MigrateComputedPropertiesToPropertyHooksRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
        private readonly BuilderFactory $builderFactory,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly NewViewPropertyInserter $propertyInserter,
        private readonly PhpDocDynamicPropertyManager $dynamicPropertyManager,
        private readonly StrictTypeFromPropertyTagFactory $propertyTypeFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts dynamic view properties from var_xxx methods to properties with get hooks',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            protected function var_whatever() 
                            {
                                return 'foo';
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class SomeView extends AbstractViewModel
                        {
                            public mixed $whatever {
                              get => $this->var_whatever();
                            }

                            protected function var_whatever() 
                            {
                                return 'foo';
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
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);

        if ( ! $classReflection->is(AbstractViewModel::class)) {
            // Not an AbstractViewModel
            return null;
        }

        $candidateMethods = $this->findComputedViewVarMethods($node);
        if ($candidateMethods === []) {
            // No dynamic methods in this view
            return null;
        }

        $classPhpDoc = $this->phpDocInfoFactory->createFromNode($node);
        $phpDocPropertyDeclarations = $this->dynamicPropertyManager->findDynamicPropertiesFromPhpdoc($classPhpDoc);

        $newProperties = [];
        $phpDocToRemove = [];

        foreach ($candidateMethods as $propertyName => $varMethod) {
            $propertyTag = $phpDocPropertyDeclarations[$propertyName] ?? null;
            if ($propertyTag) {
                $phpDocToRemove[] = $propertyTag;
            }
            $newProperties[] = $this->createNativeProperty($varMethod, $propertyTag, $propertyName);
        }

        $this->propertyInserter->insertNewProperties($node, $newProperties);
        if ($phpDocToRemove !== []) {
            $this->dynamicPropertyManager->removePropertyTags($classPhpDoc, ...$phpDocToRemove);
        }

        return $node;
    }

    private function findComputedViewVarMethods(Class_ $class): array
    {
        $varMethods = array_filter(
            $class->getMethods(),
            fn (ClassMethod $m): bool => str_starts_with($m->name->toString(), 'var_'),
        );
        $candidateMethods = [];
        foreach ($varMethods as $varMethod) {
            $propertyName = preg_replace('/^var_/', '', $varMethod->name->toString());
            if ($class->getProperty($propertyName) instanceof Property) {
                // Already have a native property with this name
                continue;
            }

            $candidateMethods[$propertyName] = $varMethod;
        }

        return $candidateMethods;
    }

    private function createNativeProperty(
        ClassMethod $varMethod,
        ?PropertyTagValueNode $propertyTag,
        string $propertyName,
    ): Property|Node {
        $isCached = $this->refactorCachedMethodImplementation($varMethod, $propertyName);

        $prop = $this->builderFactory->property($propertyName)
            ->makePublic()
            ->addHook(new PropertyHook('get', $this->createGetHookBody($isCached, $propertyName, $varMethod)))
            ->setType($this->identifyPropertyType($varMethod, $propertyTag));

        if ($propertyTag?->description) {
            $prop->setDocComment("/**\n * ".$propertyTag->description."\n */");
        }

        return $prop->getNode();
    }

    private function identifyPropertyType(ClassMethod $varMethod, ?PropertyTagValueNode $propertyTag): mixed
    {
        if ($varMethod->returnType instanceof Node) {
            $type = $varMethod->returnType;
        } elseif ($propertyTag instanceof PropertyTagValueNode) {
            $type = $this->propertyTypeFactory->findStrictType($propertyTag, $varMethod);
        } else {
            $type = 'mixed';
        }

        return $type;
    }

    private function refactorCachedMethodImplementation(ClassMethod $varMethod, string $propertyName): bool
    {
        $isCached = false;
        $this->traverseNodesWithCallable(
            $varMethod->stmts,
            function (Node $subNode) use ($propertyName, &$isCached): ?Variable {
                if ($subNode instanceof ArrayDimFetch && ($subNode->var instanceof PropertyFetch && $subNode->var->var instanceof Variable && $subNode->var->name instanceof Identifier && $subNode->dim instanceof String_ && $subNode->var->var->name === 'this' && $subNode->var->name->name === 'variables' && $subNode->dim->value === $propertyName)) {
                    // We can't reliably tell whether the method actually needs a variable (or if it could e.g.
                    // be refactored to an immediate return) but it's anyway simpler for the syntax to replace the
                    // property reference with a local variable which can always be refactored out later.
                    $isCached = true;

                    return new Variable('__cached_result__');
                }

                // It's some other array access, so ignore it
                return null;
            },
        );

        return $isCached;
    }

    private function createGetHookBody(bool $isCached, string $propertyName, ClassMethod $varMethod): MethodCall
    {
        if ($isCached) {
            // We need to build syntax like
            // get => $this->getCached('my_property', $this->var_my_property(...));
            return $this->builderFactory->methodCall(
                $this->builderFactory->var('this'),
                'getCached',
                [
                    $this->builderFactory->constFetch('__PROPERTY__'),
                    // BuilderFactory currently doesn't support taking a VariadicPlaceholder as an arg
                    // So we have to create the object manually
                    new MethodCall(
                        $this->builderFactory->var('this'),
                        $varMethod->name,
                        [new VariadicPlaceholder()],
                    ),
                ],
            );
        }

        // Much simpler, it's just a proxy to call the method every time
        // get => $this->var_my_property(...);
        return $this->builderFactory->methodCall(
            $this->builderFactory->var('this'),
            $varMethod->name,
        );
    }
}
