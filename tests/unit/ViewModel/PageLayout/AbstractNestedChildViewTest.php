<?php

namespace test\unit\ViewModel\PageLayout;

use BadMethodCallException;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\PageLayout\DummyIntermediateLayoutView;
use test\mock\ViewModel\PageLayout\DummyNestedChildView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class AbstractNestedChildViewTest extends TestCase
{
    /**
     * @var DummyPageLayoutView
     */
    protected $parent_view;

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(NestedChildView::class, $this->newSubject());
    }

    public function test_it_exposes_parent_view_on_new_interface(): void
    {
        $this->assertSame($this->parent_view, $this->newSubject()->getParentView());
    }

    public function test_it_throws_on_attempt_to_access_page_on_old_interface(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->newSubject()->page;
    }

    public static function provider_parent_page()
    {
        return [
            [
                $page = new DummyPageLayoutView(),
                $page,
            ],
            [
                new DummyIntermediateLayoutView(
                    new DummyIntermediateLayoutView(
                        $page = new DummyPageLayoutView()
                    )
                ),
                $page,
            ],
        ];
    }

    #[DataProvider('provider_parent_page')]
    public function test_it_can_provide_ultimate_parent_page_up_the_chain(DummyPageLayoutView|DummyIntermediateLayoutView $parent, DummyPageLayoutView $expect_page): void
    {
        $this->parent_view = $parent;
        $this->assertSame($expect_page, $this->newSubject()->getUltimatePageView());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->parent_view = new DummyPageLayoutView();
    }

    protected function newSubject()
    {
        return new DummyNestedChildView($this->parent_view);
    }
}
