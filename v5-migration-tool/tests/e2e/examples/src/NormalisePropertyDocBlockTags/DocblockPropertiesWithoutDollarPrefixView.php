<?php

namespace My\Application\NormalisePropertyDocBlockTags;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @phpstan-type  TSomeType array{info: string}
 * @property-read \DateTimeImmutable date      With information
 * @property      string             some_name
 */
class DocblockPropertiesWithoutDollarPrefixView extends AbstractViewModel
{

    protected $variables = [
        'date' => null,
        'some_name' => null
    ];

}
