<?php

namespace test\unit\Ingenerator\KohanaView;

use Ingenerator\KohanaView\Exception\InvalidTemplateContentException;
use Ingenerator\KohanaView\TemplateCompiler;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class TemplateCompilerTest extends TestCase
{
    protected $options = [];

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            TemplateCompiler::class,
            $this->newSubject()
        );
    }

    public function test_it_throws_if_template_empty()
    {
        $this->expectException(InvalidTemplateContentException::class);
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
                            <head><title><?php $foo = 'you shouldn\'t do this but whatever';?></title></head>
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

    #[TestWith(['<?=$view->stuff;?>', '<?=HTML::chars($view->stuff);?>'])]
    #[TestWith(['<?=$view->someMethod();?>', '<?=HTML::chars($view->someMethod());?>'])]
    #[TestWith(['<?=$any_var;?>', '<?=HTML::chars($any_var);?>'])]
    #[TestWith(['<?=$any_var?>', '<?=HTML::chars($any_var);?>'])]
    public function test_it_automatically_escapes_short_echo_tags_by_default($source, $expect)
    {
        $source = "<p>$source</p>";
        $this->assertSame(
            "<p>$expect</p>",
            $this->newSubject()->compile($source)
        );
    }

    #[TestWith(["<?=\$view->anything ? : '';?>", '<?=HTML::chars($view->anything ? : \'\');?>'])]
    #[TestWith(['<?=$view->anything
? \'stuff\'
: \'\'
;?>', '<?=HTML::chars($view->anything
? \'stuff\'
: \'\');?>'])]
    public function test_it_properly_escapes_short_echo_tags_with_ternaries($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    #[TestWith(['<?=$foo; //comment?>', '<?=HTML::chars($foo); //comment?>'])]
    #[TestWith(['<?=raw($foo); //comment?>', '<?php echo($foo); //comment?>'])]
    #[TestWith(['<?=//$foo?>', "<?='';//\$foo;?>"])]
    #[TestWith(['<?=//$foo;?>', "<?='';//\$foo;?>"])]
    public function test_it_properly_escapes_short_echo_tags_with_comments($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    #[TestWith(['<?=raw($foo);?>', '<?php echo($foo);?>'])]
    #[TestWith(['<?=raw($foo)?>', '<?php echo($foo);?>'])]
    #[TestWith(['<?= raw($foo);?>', '<?php echo($foo);?>'])]
    #[TestWith(['<?= raw(HTML::chars($foo));?>', '<?php echo(HTML::chars($foo));?>'])]
    #[TestWith(['<?=raw(do(lots(of(nested(things()))))) ;?>', '<?php echo(do(lots(of(nested(things())))));?>'])]
    public function test_it_does_not_escape_short_echo_tags_when_marked_as_raw($source, $expect)
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    public function test_it_throws_if_template_already_escapes_value_in_short_tags()
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<?=HTML::chars($double_escape_whoops);?>');
    }

    public function test_it_throws_if_template_uses_old_style_raw_exclamation_mark_prefix()
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<?=!$var;?>');
    }

    public function test_it_throws_if_template_uses_old_style_native_php_echo()
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<p><?php echo $raw_content;?></p>');
    }

    public function test_its_escape_method_is_configurable()
    {
        $this->options['escape_method'] = 'MyEscape::thing';
        $this->assertSame(
            '<?=MyEscape::thing($foo);?><?php echo($bar);?>',
            $this->newSubject()->compile(
                '<?=$foo;?><?=raw($bar);?>'
            )
        );
    }

    public function test_it_compiles_complex_template()
    {
        $source = <<<'PHP'
            <?php
            /**
             * Some view file or other
             * @var ViewModelThing $view
             */
            <div class="stuff"><h1><?=$view->title;?> <small><?=$caption;?></small></h1>
             <h2><?=Date::format($anything);?></h2>
             <?php if ($foo):?>
                <?=raw($foo);?>
             <?php endif;?>
             <?=raw($view->render($child_view));?>
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
                <?php echo($foo);?>
             <?php endif;?>
             <?php echo($view->render($child_view));?>
            </div>
            PHP;
        $this->assertEquals($expected, $this->newSubject()->compile($source));
    }

    public function test_it_compiles_complex_template_with_multiline_raw_call()
    {
        $source = <<<'PHP'
            <?php
                <td>
                    <?=raw(Button::link(
                        [
                            'href'           => $employment['employment_url'],
                            'title'          => $employment['link_title'],
                            'disallowed_msg' => 'You do not have permission to view this employment',
                            'icon'           => 'fa-file',
                            'caption'        => 'View',
                            'class'          => 'info',
                            'class_always'   => 'btn-xs btn-block'
                        ]
                    )); ?>
                    <?=raw(our(content(here('yikes'))));?>
                </td>
            PHP;

        $expect = <<<'PHP'
            <?php
                <td>
                    <?php echo(Button::link(
                        [
                            'href'           => $employment['employment_url'],
                            'title'          => $employment['link_title'],
                            'disallowed_msg' => 'You do not have permission to view this employment',
                            'icon'           => 'fa-file',
                            'caption'        => 'View',
                            'class'          => 'info',
                            'class_always'   => 'btn-xs btn-block'
                        ]
                    )); ?>
                    <?php echo(our(content(here('yikes'))));?>
                </td>
            PHP;

        $this->assertEquals($expect, $this->newSubject()->compile($source));
    }

    protected function newSubject()
    {
        return new TemplateCompiler($this->options);
    }
}
