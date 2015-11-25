<?php

/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
namespace test\unit\Ingenerator\KohanaView;

use Ingenerator\KohanaView\TemplateSpecifyingViewModel;
use Ingenerator\KohanaView\ViewModel;

class ViewTemplateSelectorTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf('Ingenerator\KohanaView\ViewTemplateSelector', $this->newSubject());
    }

    /**
     * @testWith ["View_Model_With_Underscored_Class_Name", "model/with/underscored/class/name"]
     *           ["View\\Model\\With\\Namespaced\\Classname", "model/with/namespaced/classname"]
     *           ["Model\\Without\\View_In_Name", "model/without/view/in/name"]
     *           ["Model\\WithMixed\\CaseName", "model/with_mixed/case_name"]
     *           ["Model\\WithMixed\\UpperNAME", "model/with_mixed/upper_name"]
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
     * @expectedException \UnexpectedValueException
     */
    public function test_it_throws_if_template_specifying_view_does_not_specify_a_template()
    {
        $this->newSubject()->getTemplateName(new FixedTemplateViewModelStub(NULL));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function test_it_throws_if_template_specifying_view_returns_non_string_template_name()
    {
        $this->newSubject()->getTemplateName(new FixedTemplateViewModelStub(new \DateTime));
    }


    /**
     * @return \Ingenerator\KohanaView\ViewTemplateSelector
     */
    protected function newSubject()
    {
        return new \Ingenerator\KohanaView\ViewTemplateSelector;
    }
}


class ViewModelDummy implements ViewModel
{
    /**
     * Create an instance with any arbitrary class name
     *
     * @param string $class_name
     *
     * @return ViewModelDummy
     */
    public static function make($class_name)
    {
        if (class_exists($class_name)) {
            $instance = new $class_name;
            \PHPUnit_Framework_Assert::assertInstanceOf(__CLASS__, $instance);
            return $instance;
        }

        $simple_class = trim(strrchr($class_name, '\\') ?: $class_name, '\\');
        $namespace    = trim(substr($class_name, 0, -strlen($simple_class)), '\\');
        $definition   = sprintf(
            "%s class %s extends %s {}",
            $namespace ? "namespace $namespace;" : "",
            $simple_class,
            '\\'.__CLASS__
        );
        eval($definition);

        return new $class_name;
    }

    public function display(array $variables)
    {
    }

}

class FixedTemplateViewModelStub extends ViewModelDummy implements TemplateSpecifyingViewModel
{
    /**
     * @var
     */
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template;
    }

}
