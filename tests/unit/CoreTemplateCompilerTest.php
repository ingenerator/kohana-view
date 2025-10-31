<?php

declare(strict_types=1);

namespace test\unit;

use Ingenerator\KohanaView\CoreTemplateCompiler;
use Ingenerator\KohanaView\Exception\InvalidTemplateContentException;
use Ingenerator\KohanaView\TemplateCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class CoreTemplateCompilerTest extends TestCase
{
    protected array $options = [];

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(
            TemplateCompiler::class,
            $this->newSubject(),
        );
    }

    public function test_it_throws_if_template_empty(): void
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('');
    }

    public function test_it_returns_html_unmodified(): void
    {
        $html = '<html><head><title></title></head><body><h1>some code</h1></body>';
        $this->assertSame(
            $html,
            $this->newSubject()->compile($html),
        );
    }

    public function test_it_does_not_modify_php_comments(): void
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
            $this->newSubject()->compile($source),
        );
    }

    public function test_it_does_not_modify_code_in_full_php_tags(): void
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
            $this->newSubject()->compile($source),
        );
    }

    #[TestWith(['<?=$view->stuff;?>', '<?=$renderer->escape($view->stuff);?>'])]
    #[TestWith(['<?=$view->someMethod();?>', '<?=$renderer->escape($view->someMethod());?>'])]
    #[TestWith(['<?=$any_var;?>', '<?=$renderer->escape($any_var);?>'])]
    #[TestWith(['<?=$any_var?>', '<?=$renderer->escape($any_var);?>'])]
    #[TestWith(['<?=raw($foo);?>', '<?=$renderer->escape(raw($foo));?>'])]
    #[TestWith(['<?=raw($foo)?>', '<?=$renderer->escape(raw($foo));?>'])]
    public function test_it_automatically_escapes_short_echo_tags_by_default($source, $expect): void
    {
        $source = "<p>$source</p>";
        $this->assertSame(
            "<p>$expect</p>",
            $this->newSubject()->compile($source),
        );
    }

    #[TestWith(['<p><?=$renderer->escape("some var");?></p>'])]
    #[TestWith([<<<'PHP'
                    <p>Foo</p>
                    <div my-view>
                        <?=$renderer->escape($view->child);?>
                    </div>           
        PHP])]
    public function test_it_does_not_re_escape_if_already_calling_escape(string $source): void
    {
        $this->assertSame(
            $source,
            $this->newSubject()->compile($source),
        );
    }

    #[TestWith(["<?=\$view->anything ? : '';?>", '<?=$renderer->escape($view->anything ? : \'\');?>'])]
    #[TestWith([
        <<<'PHP'
            <?=$view->anything
            ? 'stuff'
            : ''
            ;?>
            PHP, <<<'PHP'
            <?=$renderer->escape($view->anything
            ? 'stuff'
            : '');?>
            PHP,
    ])]
    public function test_it_properly_escapes_short_echo_tags_with_ternaries(string $source, $expect): void
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    #[TestWith(['<?=$foo; //comment?>', '<?=$renderer->escape($foo); //comment?>'])]
    #[TestWith(['<?=raw($foo); //comment?>', '<?=$renderer->escape(raw($foo)); //comment?>'])]
    #[TestWith(['<?=//$foo?>', "<?='';//\$foo;?>"])]
    #[TestWith(['<?=//$foo;?>', "<?='';//\$foo;?>"])]
    public function test_it_properly_escapes_short_echo_tags_with_comments(string $source, $expect): void
    {
        $this->assertSame($expect, $this->newSubject()->compile($source));
    }

    public function test_it_throws_if_template_already_escapes_value_in_short_tags(): void
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<?=HTML::chars($double_escape_whoops);?>');
    }

    public function test_it_throws_if_template_uses_old_style_raw_exclamation_mark_prefix(): void
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<?=!$var;?>');
    }

    public function test_it_throws_if_template_uses_old_style_native_php_echo(): void
    {
        $this->expectException(InvalidTemplateContentException::class);
        $this->newSubject()->compile('<p><?php echo $raw_content;?></p>');
    }

    public function test_it_throws_on_attempt_to_configure_escape_method(): void
    {
        $this->options['escape_method'] = 'MyEscape::thing';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('escape_method option has been removed');
        $this->newSubject();
    }

    public function test_it_compiles_complex_template(): void
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
            <div class="stuff"><h1><?=$renderer->escape($view->title);?> <small><?=$renderer->escape($caption);?></small></h1>
             <h2><?=$renderer->escape(Date::format($anything));?></h2>
             <?php if ($foo):?>
                <?=$renderer->escape(raw($foo));?>
             <?php endif;?>
             <?=$renderer->escape(raw($view->render($child_view)));?>
            </div>
            PHP;
        $this->assertEquals($expected, $this->newSubject()->compile($source));
    }

    public function test_it_compiles_complex_template_with_multiline_raw_call(): void
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
                    <?=$renderer->escape(raw(Button::link(
                        [
                            'href'           => $employment['employment_url'],
                            'title'          => $employment['link_title'],
                            'disallowed_msg' => 'You do not have permission to view this employment',
                            'icon'           => 'fa-file',
                            'caption'        => 'View',
                            'class'          => 'info',
                            'class_always'   => 'btn-xs btn-block'
                        ]
                    ))); ?>
                    <?=$renderer->escape(raw(our(content(here('yikes')))));?>
                </td>
            PHP;

        $this->assertEquals($expect, $this->newSubject()->compile($source));
    }

    protected function newSubject(): TemplateCompiler
    {
        return new CoreTemplateCompiler($this->options);
    }
}
