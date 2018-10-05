<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaView\ViewModel;


interface NestedParentView extends PageLayoutView
{
    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHtml($html);
}
