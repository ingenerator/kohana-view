<?php

declare(strict_types=1);

namespace Ingenerator\KohanaView\Attribute;

interface DisplayVariableAttribute
{
    /**
     * Is this property *allowed* in the $variables array passed to AbstractViewModel::display()?
     */
    public function canPassToDisplay(): bool;

    /**
     * Is this property *required* in the $variables array passed to AbstractViewModel::display()?
     *
     * Note: if this returns true then canPassToDisplay() must also return true - otherwise an
     * InvalidViewDefinitionException will be thrown.
     */
    public function mustPassToDisplay(): bool;
}
