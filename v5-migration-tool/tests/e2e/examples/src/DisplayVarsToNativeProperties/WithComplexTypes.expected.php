<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\ViewModelProperty;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @phpstan-type TSomeType array{info: string}
 */
class WithComplexTypes extends AbstractViewModel
{

    /**
     * @var list<array{value: string, caption: string, selected: string}>
     */
    public protected(set) mixed $copy_options;

    /**
     * @var TSomeType
     */
    #[ViewModelProperty(is_displayable: true, is_optional: true)]
    public protected(set) mixed $info_list = [];

}
