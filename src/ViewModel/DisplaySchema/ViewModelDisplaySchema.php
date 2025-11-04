<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\ViewModel\DisplaySchema;

final readonly class ViewModelDisplaySchema
{
    public function __construct(
        /**
         * @var list<string>
         */
        public array $expected_vars,
        /**
         * @var array<string, mixed>
         */
        public array $defaults,
    ) {
    }
}
