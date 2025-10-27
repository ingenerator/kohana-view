<?php

namespace My\Application\NormalisePropertyDocBlockTags;

use DateTimeImmutable;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @phpstan-type TSomeType array{info: string}
 */
class DocblockPropertiesWithoutDollarPrefixView extends AbstractViewModel
{
    /**
     * With information
     */
    public protected(set) DateTimeImmutable $date;
    public protected(set) string $some_name;
}
