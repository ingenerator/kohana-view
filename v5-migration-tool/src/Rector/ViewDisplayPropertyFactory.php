<?php

declare(strict_types=1);

namespace Ingenerator\KohanaViewV5MigrationTool\Rector;

use Ingenerator\KohanaView\ViewModelProperty;
use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\PropertyHook;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\CodeQuality\NodeFactory\TypedPropertyFactory as RectorTypedPropertyFactory;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;

use function count;

class ViewDisplayPropertyFactory
{
    public function __construct(
        private readonly RectorTypedPropertyFactory $rectorFactory,
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly BuilderFactory $builderFactory,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocInfoPrinter $phpDocPrinter,
        private readonly VarTagRemover $varTagRemover,
        private readonly SimplifyUselessVariableRector $simplifyUselessVariables,
    ) {
    }

    public function createDisplayProperty(
        string $propertyName,
        Class_ $class,
        ?PropertyTagValueNode $docBlockPropertyTag,
        bool $hasDefaultValue,
        ?Node $defaultValue = null,
    ): Property {
        $builder = $this
            ->buildInitialProperty($propertyName, $docBlockPropertyTag, $class)
            ->makeProtectedSet();

        if ($hasDefaultValue) {
            $builder->setDefault($defaultValue);
            $builder->addAttribute(
                $this->builderFactory->attribute(
                    new FullyQualified(ViewModelProperty::class),
                    ['is_displayable' => true, 'is_optional' => true],
                ),
            );
        }

        return $this->addPhpDocToPropertyIfRequired($builder->getNode(), $docBlockPropertyTag);
    }

    public function createComputedProperty(string $propertyName, Class_ $class, ?PropertyTagValueNode $docBlockPropertyTag, ClassMethod $getterMethod): Property
    {
        $builder = $this->buildInitialProperty($propertyName, $docBlockPropertyTag, $class);

        // Find and refactor any caching previously implemented in the method (by saving the result into $this->variables)
        // and replace that with our caching wrapper
        $isCached = $this->refactorCachedMethodImplementation($getterMethod, $propertyName);
        $builder->addHook(
            new PropertyHook(
                'get',
                $isCached
                    ? $this->createCachedGetHookBody($getterMethod)
                    : $this->createNonCachedGetHookBody($getterMethod),
            ),
        );

        if ($getterMethod->returnType instanceof Node) {
            // Force the prop to have the same type as the getter, regardless of the phpdoc
            $builder->setType($getterMethod->returnType);
        }

        return $this->addPhpDocToPropertyIfRequired($builder->getNode(), $docBlockPropertyTag);
    }

    private function refactorCachedMethodImplementation(ClassMethod $varMethod, string $propertyName): bool
    {
        $isCached = false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $varMethod->stmts,
            function (Node $subNode) use ($propertyName, &$isCached): ?Variable {
                if (
                    $subNode instanceof ArrayDimFetch
                    && $subNode->var instanceof PropertyFetch
                    && $subNode->var->var instanceof Variable
                    && $subNode->var->name instanceof Identifier
                    && $subNode->dim instanceof String_
                    && $subNode->var->var->name === 'this'
                    && $subNode->var->name->name === 'variables'
                    && $subNode->dim->value === $propertyName
                ) {
                    // It's tricky to tell here whether the method actually needs a variable - the simplest thing is to
                    // start with one and then run another Rector to refactor it out to a direct return if possible.
                    $isCached = true;

                    return new Variable('__cached_result__');
                }

                // It's some other array access, so ignore it
                return null;
            },
        );

        if ($isCached) {
            // Remove any unnecessary variables in the getter
            $this->simplifyUselessVariables->refactor($varMethod);
        }

        return $isCached;
    }

    private function createCachedGetHookBody(ClassMethod $varMethod): MethodCall
    {
        $simpleReturnExpr = $this->findSingleReturnExpression($varMethod);
        if ($simpleReturnExpr instanceof Expr) {
            // The var_method has a single statement, we can just inline that into the getter
            // get => $this->getCached(__PROPERTY__, fn () => new Thing)
            $actualGetter = new ArrowFunction(['expr' => $simpleReturnExpr]);
        } else {
            // We need to keep the var_ method and use it as a callable.
            // BuilderFactory currently doesn't support taking a VariadicPlaceholder as an arg
            // So we have to create the object manually
            $actualGetter = new MethodCall(
                $this->builderFactory->var('this'),
                $varMethod->name,
                [new VariadicPlaceholder()],
            );
        }

        // We need to build syntax like
        // get => $this->getCached('my_property', $this->var_my_property(...));
        return $this->builderFactory->methodCall(
            $this->builderFactory->var('this'),
            'getCached',
            [
                $this->builderFactory->constFetch('__PROPERTY__'),
                $actualGetter,
            ],
        );
    }

    private function findSingleReturnExpression(ClassMethod $method): false|Expr
    {
        if (count($method->stmts) !== 1) {
            return false;
        }

        $stmt = array_first($method->stmts);
        if ($stmt instanceof Return_) {
            return $stmt->expr;
        }

        return false;
    }

    private function createNonCachedGetHookBody(ClassMethod $varMethod): Expr
    {
        $simpleReturnExpr = $this->findSingleReturnExpression($varMethod);
        if ($simpleReturnExpr instanceof Expr) {
            // The var_method has a single statement, we can just inline that into the getter
            // get => new Whatever('foo');
            return $simpleReturnExpr;
        }

        // There are multiple statements, leave it as a getter call for now
        // get => $this->var_my_property(...);
        return $this->builderFactory->methodCall(
            $this->builderFactory->var('this'),
            $varMethod->name,
        );
    }

    private function buildInitialProperty(string $propertyName, ?PropertyTagValueNode $docBlockPropertyTag, Class_ $class): PropertyBuilder
    {
        if ($docBlockPropertyTag instanceof PropertyTagValueNode) {
            // It might be nullable if the phpdoc says so, but their `isNullable` param here really means
            // "force it to be nullable"
            $type = $this->rectorFactory->createPropertyTypeNode($docBlockPropertyTag, $class, isNullable: false);
        }

        // Coalesce anything unknown (which will include complex phpdoc types e.g. array-shape, phpstan templates, etc)
        // to `mixed` so that we have something.
        $type ??= 'mixed';

        $builder = $this
            ->builderFactory
            ->property($propertyName)
            ->setType($type)
            ->makePublic();

        return $builder;
    }

    private function addPhpDocToPropertyIfRequired(Property $node, ?PropertyTagValueNode $docBlockPropertyTag): Property
    {
        if ( ! $docBlockPropertyTag instanceof PropertyTagValueNode) {
            return $node;
        }

        $phpdoc = $this->phpDocInfoFactory->createEmpty($node);
        // First, add the description and type from the existing @property tag
        if ($docBlockPropertyTag->description !== '') {
            // Add the description as a separate line - often the type information in the @var tag will be redundant
            // so we don't want the presence of a description to force that to be kept as an @var.
            $phpdoc->addPhpDocTagNode(new PhpDocTextNode($docBlockPropertyTag->description));
            $phpdoc->addPhpDocTagNode(new PhpDocTextNode(''));
        }

        if ($docBlockPropertyTag?->type) {
            $phpdoc->addTagValueNode(new VarTagValueNode($docBlockPropertyTag?->type, '', ''));
        }

        // Then remove @var tags with redundant type information
        $this->varTagRemover->removeVarTagIfUseless($phpdoc, $node);

        // Then remove any pointless trailing newline
        $phpdocNode = $phpdoc->getPhpDocNode();
        $lastNode = array_last($phpdocNode->children);
        if ($lastNode instanceof PhpDocTextNode && $lastNode->text === '') {
            array_pop($phpdocNode->children);
        }

        if ($phpdocNode->children !== []) {
            $node->setDocComment(new Doc($this->phpDocPrinter->printNew($phpdoc)));
        }

        return $node;
    }
}
