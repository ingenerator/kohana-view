<?php

/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace test\unit\Ingenerator\KohanaView;

use Ingenerator\KohanaView\ViewModel;
use Ingenerator\KohanaView\ViewTemplateSelector;
use test\mock\ViewModel\FixedTemplateViewModelStub;
use test\mock\ViewModel\ViewModelDummy;

class ViewTemplateSelectorTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf('Ingenerator\KohanaView\ViewTemplateSelector', $this->newSubject());
    }

    /**
     * @testWith ["View_Model_With_Underscored_Class_Name", "with/underscored/class/name"]
     *           ["View\\Model\\With\\Namespaced\\Classname", "with/namespaced/classname"]
     *           ["View\\With\\Namespaced\\Classname", "with/namespaced/classname"]
     *           ["ViewModel\\With\\Namespaced\\Classname", "with/namespaced/classname"]
     *           ["Model\\Without\\View_In_Name", "model/without/view/in/name"]
     *           ["Model\\WithMixed\\CaseName", "model/with_mixed/case_name"]
     *           ["Model\\WithMixed\\UpperNAME", "model/with_mixed/upper_name"]
     *           ["Some\\Namespaced\\DefaultView", "some/namespaced/default"]
     *           ["Some\\Namespaced\\View", "some/namespaced/view"]
     *           ["Some_Underscore_DefaultView", "some/underscore/default"]
     *           ["Some_Underscore_DefaultViewModel", "some/underscore/default"]
     */
    public function test_by_default_it_selects_template_from_view_class_name($class_name, $expect_template)
    {
        $this->assertSame(
            $expect_template,
            $this->newSubject()->getTemplateName(ViewModelDummy::make($class_name))
        );
    }

    /**
     * @testWith ["some_template"]
     *           ["some/nested/template"]
     */
    public function test_template_specifying_view_class_can_indicate_which_template_to_use($template)
    {
        $view = new FixedTemplateViewModelStub($template);
        $this->assertSame($template, $this->newSubject()->getTemplateName($view));
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\UnspecifiedTemplateNameException
     */
    public function test_it_throws_if_template_specifying_view_does_not_specify_a_template()
    {
        $this->newSubject()->getTemplateName(new FixedTemplateViewModelStub(NULL));
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\UnspecifiedTemplateNameException
     */
    public function test_it_throws_if_template_specifying_view_returns_non_string_template_name()
    {
        $this->newSubject()->getTemplateName(new FixedTemplateViewModelStub(new \DateTime));
    }


    /**
     * @return ViewTemplateSelector
     */
    protected function newSubject()
    {
        return new ViewTemplateSelector;
    }
}


