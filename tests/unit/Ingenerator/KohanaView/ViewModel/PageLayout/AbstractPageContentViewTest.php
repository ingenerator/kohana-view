<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\unit\Ingenerator\KohanaView\ViewModel\PageLayout;

use Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView;
use test\mock\ViewModel\PageLayout\DummyPageLayoutView;

class AbstractPageContentViewTest extends \PHPUnit_Framework_TestCase
{
    protected $page_layout;

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView',
            $subject
        );
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel\PageContentView',
            $subject
        );
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\ViewModel',
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
    }

    public function setUp()
    {
        $this->page_layout = new DummyPageLayoutView;
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
        'message' => NULL,
    ];

}
