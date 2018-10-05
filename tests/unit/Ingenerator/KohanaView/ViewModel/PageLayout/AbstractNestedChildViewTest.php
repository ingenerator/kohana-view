<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\NestedParentView;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;
use test\mock\ViewModel\PageLayout\DummyNestedChildView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class AbstractNestedChildViewTest extends TestCase
{

    /**
     * @var \test\mock\ViewModel\PageLayout\DummyPageLayoutView
     */
    protected $parent_view;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(NestedChildView::class, $this->newSubject());
    }

    public function test_it_exposes_parent_view_on_new_interface()
    {
        $this->assertSame($this->parent_view, $this->newSubject()->getParentView());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_it_throws_on_attempt_to_access_page_on_old_interface()
    {
        $this->newSubject()->page;
    }

    public function provider_parent_page()
    {
        return [
            [
                $page = new DummyPageLayoutView,
                $page
            ],
            [
                new DummyIntermediateLayoutView(
                    new DummyIntermediateLayoutView(
                        $page = new DummyPageLayoutView
                    )
                ),
                $page
            ]
        ];
    }

    /**
     * @dataProvider provider_parent_page
     */
    public function test_it_can_provide_ultimate_parent_page_up_the_chain($parent, $expect_page)
    {
        $this->parent_view = $parent;
        $this->assertSame($expect_page, $this->newSubject()->getUltimatePageView());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->parent_view = new DummyPageLayoutView;
    }

    protected function newSubject()
    {
        return new DummyNestedChildView($this->parent_view);
    }
}
