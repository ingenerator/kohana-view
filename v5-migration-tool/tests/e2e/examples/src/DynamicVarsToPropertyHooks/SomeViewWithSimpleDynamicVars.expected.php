<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use DateTimeImmutable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class SomeViewWithSimpleDynamicVars extends AbstractViewModel
{

    public mixed $untyped {
        get => $this->var_untyped();
    }
    public array $with_typed_getter {
        get => $this->var_with_typed_getter();
    }
    public DateTimeImmutable $with_property_read_tag {
        get => $this->var_with_property_read_tag();
    }
    /**
     * The description of stuff
     */
    public array $with_description {
        get => $this->var_with_description();
    }
    protected function var_untyped()
    {
        return 'untyped';
    }

    protected function var_with_typed_getter(): array
    {
        return ['one' => 'a', 'two' => 'b'];
    }

    protected function var_with_property_read_tag()
    {
        return new DateTimeImmutable();
    }

    protected function var_with_description()
    {
        return [1,2];
    }

}
