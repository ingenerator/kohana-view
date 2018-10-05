<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;

abstract class AbstractNestedChildView extends AbstractViewModel implements NestedChildView
{

    /**
     * @var \Ingenerator\KohanaView\ViewModel\NestedParentView
     */
    protected $parent_view;

    public function __construct(NestedParentView $parent_view)
    {
        $this->parent_view = $parent_view;
        parent::__construct();
    }

    /**
     *
     * @return \Ingenerator\KohanaView\ViewModel\NestedParentView
     */
    public function getParentView()
    {
        return $this->parent_view;
    }

    /**
     * The page layout that this content view will be rendered into
     *
     * @return PageLayoutView
     */
    public function var_page()
    {
        throw new \BadMethodCallException('Call to legacy '.__METHOD__.' interface');
    }

    /**
     * @return \Ingenerator\KohanaView\ViewModel\PageLayoutView
     */
    protected function getUltimatePageView()
    {
        $parent = $this->getParentView();
        while ($parent instanceof NestedChildView) {
            $parent = $parent->getParentView();
        }
        if ( ! $parent instanceof PageLayoutView) {
            throw new \UnexpectedValueException('No ultimate PageLayoutView for '.get_class($this));
        }
        return $parent;
    }

}
