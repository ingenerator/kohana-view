<?php

namespace My\Application\ClassWithCustomValidationMethods;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

class ClassWithCustomValidationMethod extends AbstractViewModel
{
    protected function validateDisplayVariables(array $variables): array
    {
        // My custom comment
        $errs = parent::validateDisplayVariables($variables);
        $errs[] = 'Some custom problem';
        return $errs;
    }

}
