<?php

/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaView\ViewModel;

interface NestedChildView extends PageContentView
{
    /**
     *
     * @return NestedParentView
     */
    public function getParentView();
}
