<?php

namespace Ingenerator\KohanaViewMigrationTool;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;

class ConvertDisplayVariablesVisitor extends NodeVisitorAbstract
{

    private readonly BuilderFactory $builder;

    private ?array $pending_properties = null;

    private ?Class_ $current_class = null;

    private ?Property $current_property = null;

    public function __construct()
    {
        $this->builder = new BuilderFactory();
    }

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof Class_) {
            echo "ENTER Class\n";
            $this->current_class = $node;

            return null;
        }

        if ($node instanceof Property) {
            echo "ENTER Property\n";
            $this->current_property = $node;

            return null;
        }
    }

    private function enterProperty(Property $node)
    {
        foreach ($node->props as $prop) {
            if ($prop->name->name === 'variables') {
                return $this->enterVariablesProperty($node);
            }
        }
    }

    private function enterVariablesProperty(Property $node)
    {
        $builder = new BuilderFactory();
        assert(count($node->props) === 1, 'Expected variables to have only one property item');
        $defaults = $node->props[0]->default;
        assert($defaults instanceof Array_, 'variables should default to an array');
        foreach ($defaults->items as $view_var) {
            assert($view_var->key instanceof String_);
            $var_name = $view_var->key->value;
            // @todo check it is initialised to null
            // @todo get the type from the `@property-read` docblock?

            $new_prop = $builder->property($var_name);
        }
        $node->props[0]->default = null;
        print_r($defaults);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->current_class = null;

            return null;
        }

        if ($node instanceof Property) {
            $this->current_property = null;
            $props = $this->pending_properties;
            $this->pending_properties = null;

            return $props;
        }

        if ( ! $node instanceof Node\PropertyItem) {
            return null;
        }

        if ($node->name->name !== 'variables') {
            return null;
        }

        assert($node->default instanceof Array_, 'variables should default to an array');
        $this->pending_properties = array_map(
            $this->createNewDisplayProperty(...),
            $node->default->items
        );

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