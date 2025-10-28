<?php

declare(strict_types=1);

namespace test\unit;

use Ingenerator\KohanaView\Exception\UnspecifiedTemplateNameException;
use Ingenerator\KohanaView\ViewTemplateSelector;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use test\mock\ViewModel\FixedTemplateViewModelStub;
use test\mock\ViewModel\ViewModelDummy;

class ViewTemplateSelectorTest extends TestCase
{
    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(ViewTemplateSelector::class, $this->newSubject());
    }

    #[TestWith(['View_Model_With_Underscored_Class_Name', 'with/underscored/class/name'])]
    #[TestWith(['View\Model\With\Namespaced\Classname', 'with/namespaced/classname'])]
    #[TestWith(['View\With\Namespaced\Classname', 'with/namespaced/classname'])]
    #[TestWith(['ViewModel\With\Namespaced\Classname', 'with/namespaced/classname'])]
    #[TestWith(['Model\Without\View_In_Name', 'model/without/view/in/name'])]
    #[TestWith(['Model\WithMixed\CaseName', 'model/with_mixed/case_name'])]
    #[TestWith(['Model\WithMixed\UpperNAME', 'model/with_mixed/upper_name'])]
    #[TestWith(['Some\Namespaced\DefaultView', 'some/namespaced/default'])]
    #[TestWith(['Some\Namespaced\View', 'some/namespaced/view'])]
    #[TestWith(['Some_Underscore_DefaultView', 'some/underscore/default'])]
    #[TestWith(['Some_Underscore_DefaultViewModel', 'some/underscore/default'])]
    public function test_by_default_it_selects_template_from_view_class_name(string $class_name, $expect_template): void
    {
        $this->assertSame(
            $expect_template,
            $this->newSubject()->getTemplateName(ViewModelDummy::make($class_name))
        );
    }

    #[TestWith(['some_template'])]
    #[TestWith(['some/nested/template'])]
    public function test_template_specifying_view_class_can_indicate_which_template_to_use($template): void
    {
        $view = new FixedTemplateViewModelStub($template);
        $this->assertSame($template, $this->newSubject()->getTemplateName($view));
    }

    public function test_it_throws_if_template_specifying_view_does_not_specify_a_template(): void
    {
        $this->expectException(UnspecifiedTemplateNameException::class);
        $this->newSubject()->getTemplateName(new FixedTemplateViewModelStub(''));
    }

    protected function newSubject(): ViewTemplateSelector
    {
        return new ViewTemplateSelector();
    }
}
