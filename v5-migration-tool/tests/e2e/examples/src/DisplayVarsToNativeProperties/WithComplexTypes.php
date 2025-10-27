<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @phpstan-type TSomeType array{info: string}
 *
 * @property-read list<array{value:string, caption:string, selected:string}> $copy_options
 * @property-read TSomeType $info_list
 */
class WithComplexTypes extends AbstractViewModel
{

    protected $variables = [
      'copy_options' => null
    ];

    protected $default_variables = [
      'info_list' => []
    ];

}
