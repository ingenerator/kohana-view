<?php

namespace test\mock\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractIntermediateLayoutView;

class DummyIntermediateLayoutView extends AbstractIntermediateLayoutView
{
    /**
     * @return \Ingenerator\KohanaView\ViewModel\PageLayoutView
     */
    public function getUltimatePageView()
    {
        return parent::getUltimatePageView();
    }
}
