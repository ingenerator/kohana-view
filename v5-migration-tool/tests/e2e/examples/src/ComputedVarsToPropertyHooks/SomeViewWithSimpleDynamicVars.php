<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use DateTimeImmutable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @property-read DateTimeImmutable $with_property_read_tag
 * @property array $with_description The description of stuff
 */
class SomeViewWithSimpleDynamicVars extends AbstractViewModel
{

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
