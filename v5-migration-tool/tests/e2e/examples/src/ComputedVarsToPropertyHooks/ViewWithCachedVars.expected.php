<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use stdClass;

class ViewWithCachedVars extends AbstractViewModel
{

    public object $complex_var {
        get => $this->getCached(__PROPERTY__, fn() => new stdClass());
    }
    public array $looping_var {
        get => $this->getCached(__PROPERTY__, $this->var_looping_var(...));
    }
    public mixed $mapping_var {
        get => $this->getCached(__PROPERTY__, fn() => array_map($this->formatRow(...), $this->looping_var));
    }
    public mixed $with_inline_caching {
        get => $this->getCached(__PROPERTY__, fn() => array_map(
            fn(int $k) => $k ^ 10,
            [1, 2, 3, 4],
        ));
    }
    protected function var_looping_var()
    {
        foreach ([1, 2, 3] as $i) {
            $__cached_result__[] = 'a new row -'.$i;
        }

        return $__cached_result__;
    }

    private function formatRow(int $row): array
    {
        return ['index' => $row];
    }

}
