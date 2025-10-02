<?php

namespace Ingenerator\KohanaView;

/**
 * Allows this view to specify a custom template file name at runtime, for example when the template may depend on
 * dynamic data from within the view itself.
 */
interface TemplateSpecifyingViewModel
{
    /**
     * @return string
     */
    public function getTemplateName();
}
