<?php

namespace My\Application\DynamicVarsToPropertyHooks;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use stdClass;

/**
 * @property-read object $complex_var
 * @property-read array $looping_var
 */
class ViewWithCachedVars extends AbstractViewModel
{

    protected function var_complex_var()
    {
        $this->variables['complex_var'] = new stdClass();
        return $this->variables['complex_var'];
    }

    protected function var_looping_var()
    {
        foreach ([1, 2, 3] as $i) {
            $this->variables['looping_var'][] = 'a new row -'.$i;
        }

        return $this->variables['looping_var'];
    }

    protected function var_mapping_var()
    {
        $this->variables['mapping_var'] = array_map($this->formatRow(...), $this->looping_var);
        return $this->variables['mapping_var'];
    }

    protected function var_with_inline_caching()
    {
        return $this->variables['with_inline_caching'] = array_map(
            fn(int $k) => $k ^ 10,
            [1, 2, 3, 4],
        );
    }

    private function formatRow(int $row): array
    {
        return ['index' => $row];
    }

}
