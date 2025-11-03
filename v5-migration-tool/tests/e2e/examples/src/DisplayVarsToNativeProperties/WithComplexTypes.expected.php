<?php

namespace My\Application\DisplayVarsToNativeProperties;

use Ingenerator\KohanaView\Attribute\OptionalDisplayVariable;
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
    #[OptionalDisplayVariable]
    public protected(set) mixed $info_list = [];

}
