<?php

namespace My\Application\ClassWithCustomValidationMethods;

use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Override;

class ClassAlreadyMarked extends AbstractViewModel
{
    #[Override]
    protected function validateDisplayVariables(array $variables): array
    {
        // @todo already marked for review
        $errs = parent::validateDisplayVariables($variables);
        $errs[] = 'Some custom problem';
        return $errs;
    }

}
