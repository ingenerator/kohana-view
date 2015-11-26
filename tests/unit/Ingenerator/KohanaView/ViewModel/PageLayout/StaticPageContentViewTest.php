<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel\PageLayout\StaticPageContentView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class StaticPageContentViewTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf('Ingenerator\KohanaView\ViewModel\PageLayout\StaticPageContentView', $subject);
        $this->assertInstanceOf('Ingenerator\KohanaView\ViewModel\PageContentView', $subject);
    }

    public function test_it_is_a_template_specifying_view()
    {
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\TemplateSpecifyingViewModel',
            $this->newSubject()
        );
    }

    public function test_it_specifies_template_based_on_page_path()
    {
        $subject = $this->newSubject();
        $subject->display(['page_path' => 'some/content/page']);
        $this->assertSame(
            'some/content/page',
            $subject->getTemplateName()
        );
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_it_throws_if_page_path_not_set_before_get_template_name()
    {
        $this->newSubject()->getTemplateName();
    }

    protected function newSubject()
    {
        return new StaticPageContentView(
            new DummyPageLayoutView
        );
    }

}
