<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;


use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;

class AbstractIntermediateLayoutViewTest extends AbstractNestedChildViewTest
{

    public function test_it_exposes_injected_body_html_as_child_html()
    {
        $subject = $this->newSubject();
        $subject->setBodyHtml('<p>I am the middle bit of your page</p>');
        $this->assertSame('<p>I am the middle bit of your page</p>', $subject->child_html);
    }

    protected function newSubject()
    {
        return new DummyIntermediateLayoutView($this->parent_view);
    }


}
