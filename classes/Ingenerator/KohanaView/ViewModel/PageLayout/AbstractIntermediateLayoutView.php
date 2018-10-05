<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\NestedParentView;

/**
 * @property-read string $child_html
 */
abstract class AbstractIntermediateLayoutView extends AbstractNestedChildView implements NestedParentView
{
    /**
     * @var string set at rendering time by the PageLayoutRenderer
     */
    protected $child_html;

    /**
     * @param string $html
     *
     * @return void
     */
    public function setBodyHtml($html)
    {
        $this->child_html = $html;
    }

    protected function var_child_html()
    {
        return $this->child_html;
    }

}
