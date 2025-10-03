<?php

namespace test\mock\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractIntermediateLayoutView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
use Override;

class DummyIntermediateLayoutView extends AbstractIntermediateLayoutView
{
    /**
     * @return PageLayoutView
     */
    #[Override]
    public function getUltimatePageView()
    {
        return parent::getUltimatePageView();
    }
}
