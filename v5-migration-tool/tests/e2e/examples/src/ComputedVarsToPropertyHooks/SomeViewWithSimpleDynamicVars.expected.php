<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use DateTimeImmutable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class SomeViewWithSimpleDynamicVars extends AbstractViewModel
{

    public mixed $untyped {
        get => 'untyped';
    }
    public array $with_typed_getter {
        get => ['one' => 'a', 'two' => 'b'];
    }
    public DateTimeImmutable $with_property_read_tag {
        get => new DateTimeImmutable();
    }
    /**
     * The description of stuff
     */
    public array $with_description {
        get => [1, 2];
    }
    public mixed $with_multiple_statements {
        get => $this->var_with_multiple_statements();
    }
    public array $mapped_rows {
        get => array_map(
            $this->formatRow(...),
            $this->repo->listAllUsers(),
        );
    }
    protected function var_with_multiple_statements() {
        $rows = [];
        foreach ($this->repo->listAllUsers() as $user) {
            $rows[] = $this->formatRow($user);
        }
        return $rows;
    }

    private function formatRow(User $user): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];
    }

}
