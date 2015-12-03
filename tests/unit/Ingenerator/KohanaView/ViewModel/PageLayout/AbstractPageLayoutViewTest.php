<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;


use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageLayoutView;

class AbstractPageLayoutViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewModel
     */
    protected $body_view;

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageLayoutView',
            $subject
        );
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel\PageLayoutView',
            $subject
        );
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel',
            $subject
        );
    }

    public function test_it_has_body_html_variable_with_setter_access()
    {
        $subject = $this->newSubject();
        $subject->setBodyHTML('Any HTML');
        $this->assertSame('Any HTML', $subject->body_html);
    }

    public function test_it_has_page_title_variable_with_setter_access()
    {
        $subject = $this->newSubject();
        $subject->setTitle('Page title goes here');
        $this->assertSame('Page title goes here', $subject->title);
    }

    public function test_it_supports_assigning_all_variables_with_display()
    {
        $subject = $this->newSubject();
        $subject->display(['body_html' => 'Here is the content', 'title' => 'And the title']);
        $this->assertSame('Here is the content', $subject->body_html);
        $this->assertSame('And the title', $subject->title);
    }

    protected function newSubject()
    {
        return new TestablePageLayoutView;
    }

}

class TestablePageLayoutView extends AbstractPageLayoutView
{

}
