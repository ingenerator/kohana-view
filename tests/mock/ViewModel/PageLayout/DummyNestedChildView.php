<?php

namespace test\mock\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;

class DummyNestedChildView extends AbstractNestedChildView
{
    /**
     * @return PageLayoutView
     */
    public function getUltimatePageView()
    {
        return parent::getUltimatePageView();
    }
}
