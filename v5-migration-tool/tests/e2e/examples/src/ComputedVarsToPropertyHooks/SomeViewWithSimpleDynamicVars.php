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
        return [1, 2];
    }

    protected function var_with_multiple_statements() {
        $rows = [];
        foreach ($this->repo->listAllUsers() as $user) {
            $rows[] = $this->formatRow($user);
        }
        return $rows;
    }

    protected function var_mapped_rows(): array
    {
        return array_map(
            $this->formatRow(...),
            $this->repo->listAllUsers(),
        );
    }

    private function formatRow(User $user): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];
    }

}
