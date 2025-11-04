<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\DisplaySchema\ViewModelDisplaySchema;

interface ViewModelDisplaySchemaProvider
{
    /**
     * @param class-string<AbstractViewModel> $model_class
     */
    public function getSchema(string $model_class): ViewModelDisplaySchema;
}
