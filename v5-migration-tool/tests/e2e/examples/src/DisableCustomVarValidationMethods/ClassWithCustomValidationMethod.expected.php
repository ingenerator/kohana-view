<?php

namespace My\Application\ClassWithCustomValidationMethods;

use Override;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ClassWithCustomValidationMethod extends AbstractViewModel
{
    #[Override]
    protected function validateDisplayVariables(array $variables): array
    {
        // @todo: This method has been removed from AbstractViewModel and will never be called
        // My custom comment
        $errs = parent::validateDisplayVariables($variables);
        $errs[] = 'Some custom problem';
        return $errs;
    }

}
