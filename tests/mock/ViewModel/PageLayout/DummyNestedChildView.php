<?php

namespace test\mock\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView;
use Ingenerator\KohanaView\ViewModel\PageLayoutView;
use Override;

class DummyNestedChildView extends AbstractNestedChildView
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
