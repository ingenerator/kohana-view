<?php

namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;

use Override;
use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;

class AbstractIntermediateLayoutViewTest extends AbstractNestedChildViewTest
{
    public function test_it_exposes_injected_body_html_as_child_html()
    {
        $subject = $this->newSubject();
        $subject->setBodyHtml('<p>I am the middle bit of your page</p>');
        $this->assertSame('<p>I am the middle bit of your page</p>', $subject->child_html);
    }

    #[Override]
    protected function newSubject()
    {
        return new DummyIntermediateLayoutView($this->parent_view);
    }
}
