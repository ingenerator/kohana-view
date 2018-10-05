<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\mock\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView;

class DummyNestedChildView extends AbstractNestedChildView
{
    /**
     * @return \Ingenerator\KohanaView\ViewModel\PageLayoutView
     */
    public function getUltimatePageView()
    {
        return parent::getUltimatePageView();
    }


}
