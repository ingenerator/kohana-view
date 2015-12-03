<?php
/**
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */

namespace test\unit\Ingenerator\KohanaView;

use Ingenerator\KohanaView\TemplateCompiler;

class TemplateCompilerTest extends \PHPUnit_Framework_TestCase
{

    protected $options = [];

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            'Ingenerator\KohanaView\TemplateCompiler',
            $this->newSubject()
        );
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\InvalidTemplateContentException
     */
    public function test_it_throws_if_template_empty()
    {
        $this->newSubject()->compile('');
    }

    public function test_it_returns_html_unmodified()
    {
        $html = '<html><head><title></title></head><body><h1>some code</h1></body>';
        $this->assertSame(
            $html,
            $this->newSubject()->compile($html)
        );
    }

    public function test_it_does_not_modify_php_comments()
    {
        $source = <<<PHP
            <?php
              /**
               * Some php comment
               */
            <html>
                <head><title></title></head>
                <body>
                <h1>some code</h1>
                </body>
            </html>
PHP;
        $this->assertSame(
            $source,
            $this->newSubject()->compile($source)
        );
    }

    public function test_it_does_not_modify_code_in_full_php_tags()
    {
        $source = <<<'PHP'
            <html>
                <head><title><?php echo $view->some_var;?></title></head>
                <body>
                <h1>some code</h1>
                </body>
            </html>
PHP;
        $this->assertSame(
            $source,
            $this->newSubject()->compile($source)
        );
    }

    /**
     * @testWith ["<?=$view->stuff;?>", "<?=HTML::chars($view->stuff);?>"]
     *           ["<?=$view->someMethod();?>", "<?=HTML::chars($view->someMethod());?>"]
     *           ["<?=$any_var;?>", "<?=HTML::chars($any_var);?>"]
     *           ["<?=$any_var?>", "<?=HTML::chars($any_var);?>"]
     */
    public function test_it_automatically_escapes_short_echo_tags_by_default($source, $expect)
    {
        $source = "<p>$source</p>";
        $this->assertSame(
            "<p>$expect</p>",
            $this->newSubject()->compile($source)
        );
    }

    /**
     * @testWith ["<?=$view->anything ? : '';?>", "<?=HTML::chars($view->anything ? : '');?>"]
     *           ["<?=$view->anything\n? 'stuff'\n: ''\n;?>", "<?=HTML::chars($view->anything\n? 'stuff'\n: '');?>"]
     */
    public function test_it_properly_escapes_short_echo_tags_with_ternaries($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    /**
     * @testWith ["<?=$foo; //comment?>", "<?=HTML::chars($foo); //comment?>"]
     *           ["<?=!$foo; //comment?>", "<?=$foo; //comment?>"]
     *           ["<?=//$foo?>", "<?='';//$foo;?>"]
     *           ["<?=//$foo;?>", "<?='';//$foo;?>"]
     */
    public function test_it_properly_escapes_short_echo_tags_with_comments($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    /**
     * @testWith ["<?=!$foo;?>", "<?=$foo;?>"]
     *           ["<?=!$foo?>", "<?=$foo;?>"]
     *           ["<?= ! $foo;?>", "<?=$foo;?>"]
     *           ["<?=! $foo;?>", "<?=$foo;?>"]
     *           ["<?= ! HTML::chars($foo);?>", "<?=HTML::chars($foo);?>"]
     */
    public function test_it_does_not_escape_short_echo_tags_when_marked_as_raw($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    /**
     * @expectedException \Ingenerator\KohanaView\Exception\InvalidTemplateContentException
     */
    public function test_it_throws_if_template_already_escapes_value_in_short_tags()
    {
        $this->newSubject()->compile('<?=HTML::chars($double_escape_whoops);?>');
    }

    /**
     * @testWith ["~"]
     *           ["raw"]
     */
    public function test_its_raw_output_character_is_configurable($prefix)
    {
        $this->options['raw_output_prefix'] = $prefix;
        $this->assertSame(
            '<?=HTML::chars($foo);?><?=HTML::chars(!$baz);?><?=$bar;?>',
            $this->newSubject()->compile(
                '<?=$foo;?><?=!$baz;?><?='.$prefix.'$bar;?>'
            )
        );
    }

    public function test_its_escape_method_is_configurable()
    {
        $this->options['escape_method'] = 'MyEscape::thing';
        $this->assertSame(
            '<?=MyEscape::thing($foo);?><?=$bar;?>',
            $this->newSubject()->compile(
                '<?=$foo;?><?=!$bar;?>'
            )
        );
    }

    public function test_it_compiles_complex_template()
    {
        $source   = <<<'PHP'
<?php
/**
 * Some view file or other
 * @var ViewModelThing $view
 */
<div class="stuff"><h1><?=$view->title;?> <small><?=$caption;?></small></h1>
 <h2><?=Date::format($anything);?></h2>
 <?php if ($foo):?>
    <?php echo $foo;?>
 <?php endif;?>
 <?=!$view->render($child_view);?>
</div>
PHP;
        $expected = <<<'PHP'
<?php
/**
 * Some view file or other
 * @var ViewModelThing $view
 */
<div class="stuff"><h1><?=HTML::chars($view->title);?> <small><?=HTML::chars($caption);?></small></h1>
 <h2><?=HTML::chars(Date::format($anything));?></h2>
 <?php if ($foo):?>
    <?php echo $foo;?>
 <?php endif;?>
 <?=$view->render($child_view);?>
</div>
PHP;
        $this->assertSame($expected, $this->newSubject()->compile($source));
    }

    protected function newSubject()
    {
        return new TemplateCompiler($this->options);
    }

}
