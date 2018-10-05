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
     * @return \Ingenerator\KohanaView\ViewModel\NestedParentView
     */
    public function getParentView();
}
