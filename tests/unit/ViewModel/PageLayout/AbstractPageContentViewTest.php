<?php

namespace test\unit\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\PageContentView;
use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class AbstractPageContentViewTest extends TestCase
{
    protected $page_layout;

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(
            AbstractPageContentView::class,
            $subject
        );
        $this->assertInstanceOf(
            PageContentView::class,
            $subject
        );
        $this->assertInstanceOf(
            ViewModel::class,
            $subject
        );
    }

    public function test_it_exposes_page_as_view_variable()
    {
        $this->assertSame($this->page_layout, $this->newSubject()->page);
    }

    public function test_it_does_not_take_page_as_display_value()
    {
        $this->newSubject()->display(['message' => 'anything']);

        // We got this far successfully, provide assertion to keep PHPUnit happy.
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        $this->page_layout = new DummyPageLayoutView();
        parent::setUp();
    }

    protected function newSubject()
    {
        return new TestableAbstractPageContentView(
            $this->page_layout
        );
    }
}

class TestableAbstractPageContentView extends AbstractPageContentView
{
    protected $variables = [
        'message' => null,
    ];
}
