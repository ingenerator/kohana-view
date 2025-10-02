<?php

namespace test\mock\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractIntermediateLayoutView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;

class DummyIntermediateLayoutView extends AbstractIntermediateLayoutView
{
    /**
     * @return PageLayoutView
     */
    public function getUltimatePageView()
    {
        return parent::getUltimatePageView();
    }
}
