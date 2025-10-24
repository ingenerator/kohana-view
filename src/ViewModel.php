<?php

namespace Ingenerator\KohanaView;

/**
 * This is the basic interface for view models, which are responsible for holding and presenting data to
 * the template.
 */
interface ViewModel
{
    public function display(array $variables): void;
}
