<?php

namespace Ingenerator\KohanaViewMigrationTool;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;

class ConvertComputedPropertiesVisitor extends NodeVisitorAbstract
{

    private readonly BuilderFactory $builder;

    private array $pending_props = [];

    public function __construct()
    {
        $this->builder = new BuilderFactory();
    }

    public function leaveNode(Node $node)
    {

        if ($node instanceof Class_ && $this->pending_props) {
            $node->stmts = [...$this->pending_props, ...$node->stmts];
            $this->pending_props = [];

            return null;
        }

        if ( ! $node instanceof Node\Stmt\ClassMethod) {
            return null;
        }


        if ( ! str_starts_with($node->name->name, 'var_')) {
            return null;
        }

        $var_name = preg_replace('/^var_/', '', $node->name->name);

        // @todo phpdoc type
        // @todo inline var_... method into getter if simple
        // @todo replace with direct public readonly if getter just exposes a constructor prop
        // @todo detect cached computed
        $this->pending_props[] = $this
            ->builder
            ->property($var_name)
            ->makePublic()
            ->setType($node->getReturnType() ?? 'mixed')
            ->addHook(
                new Node\PropertyHook(
                    'get',
                    //[
//                        $this->builder->methodCall(
//                            $this->builder->var('this'),
//                            $node->name->name,
//                            [new Node\VariadicPlaceholder()]
//                        )
                        new Node\Expr\MethodCall(
                            $this->builder->var('this'),
                            $node->name->name,
//                            [new Node\VariadicPlaceholder()]
                            []
                        )
                    //]
                )
            )
            ->getNode();


        return null;
    }

    private function createNewDisplayProperty(Node\ArrayItem $var_definition): Property
    {
        assert($var_definition->key instanceof String_);
        $var_name = $var_definition->key->value;
        // @todo check it is initialised to null
        // @todo get the type from the `@property-read` docblock?

        return $this
            ->builder
            ->property($var_name)
            ->makePublic()
            ->makeProtectedSet()
            ->setType('mixed')
            ->getNode();
    }


}