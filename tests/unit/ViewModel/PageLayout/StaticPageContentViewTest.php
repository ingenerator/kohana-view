<?php

namespace test\unit\ViewModel\PageLayout;

use Ingenerator\KohanaView\Exception\UnassignedViewVarException;
use Ingenerator\KohanaView\TemplateSpecifyingViewModel;
use Ingenerator\KohanaView\ViewModel\NestedChildView;
use Ingenerator\KohanaView\ViewModel\PageLayout\StaticPageContentView;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class StaticPageContentViewTest extends TestCase
{
    public function test_it_is_initialisable(): void
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(StaticPageContentView::class, $subject);
        $this->assertInstanceOf(NestedChildView::class, $subject);
    }

    public function test_it_is_a_template_specifying_view(): void
    {
        $this->assertInstanceOf(
            TemplateSpecifyingViewModel::class,
            $this->newSubject()
        );
    }

    public function test_it_specifies_template_based_on_page_path(): void
    {
        $subject = $this->newSubject();
        $subject->display(['page_path' => 'some/content/page']);
        $this->assertSame(
            'some/content/page',
            $subject->getTemplateName()
        );
    }

    public function test_it_throws_if_page_path_not_set_before_get_template_name(): void
    {
        $this->expectException(UnassignedViewVarException::class);
        $this->newSubject()->getTemplateName();
    }

    protected function newSubject(): StaticPageContentView
    {
        return new StaticPageContentView(
            new DummyPageLayoutView()
        );
    }
}
