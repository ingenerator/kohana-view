Upgrading to version 5.x
========================

5.x is a major upgrade of the library and introduces a *lot* of breaking changes to take advantage
of modern PHP features. These will require changes to your view models and potentially to code that
uses them.

We have provided an automated migration tool (powered by Rector) that you can run to assist with
adapting code built for earlier versions.

### Installing and running the migration tool

If you do not already have Rector enabled in your project, you'll first need to add it with
a basic configuration file:

```bash
composer require rector/rector
vendor/bin/rector
# Follow the prompts to add your initial rector.php
```

You are then ready to install the migration tool.

The migration tool **is not included in the composer package**.

Instead, first clone our git repository to a path **outside your own project's root directory**.

You can then use our configuration script to temporarily add the tool to your project's composer
dependencies and Rector configuration.

```bash
git clone https://github.com/ingenerator/kohana-view $HOME/kohana-view

# Switch to your project's working directory
cd $PATH_TO_YOUR_PROJECT

# Run the configure script to:
# - have composer temporarily copy / symlink the tool into your project's dependencies
# - add the tool's Rector set to your rector.php config file
# - install any other required composer dependencies.
$HOME/kohana-view/v5-migration-tool/configure

# You can now run Rector as required / usual
vendor/bin/rector

# Once you are happy the migration has run OK, revert the changes to your rector 
# config and composer.json
# (the tool is not intended to be committed / pushed with your project).
git restore rector.php composer.json composer.lock
```

You should **carefully** review the diff before committing the changes. The tool is not guaranteed
to work perfectly in every case.

We assume that **you have a coding-standards tool (e.g. php-cs-fixer)** already configured in your
project. You will need to run this after the migration tool to reformat your code to your 
desired style.

### PageContentView and PageLayoutView interfaces are removed

These have been consolidated into the more flexible NestedChildView and NestedParentView interfaces.
In most cases, classes that extend from our abstract base classes will not require significant
functional changes.

However, any typehints that refer to the old interfaces will need to be changed to the new
ones:

* `\Ingenerator\KohanaView\ViewModel\PageContentView` => `\Ingenerator\KohanaView\ViewModel\NestedChildView`
* `\Ingenerator\KohanaView\ViewModel\PageLayoutView` => `\Ingenerator\KohanaView\ViewModel\NestedParentView`

The migration tool should find and rename all these class references for you.

### Strict return types

All kohana-view interfaces now declare methods with strict return types.

The migration tool will apply the correct return types to any classes you have that implement interfaces we provide.

### Use of "magic" `var_{property name}` methods to define computed properties

Previously, computed properties could be implemented like:

```php
/**
 * @property-read DateTimeImmutable $now 
 */
class MyView extends AbstractViewModel {
  protected function var_now() {
    return new DateTimeImmutable();
  }
}
```

These should now be implemented as native properties with getter hooks:

```php
class MyView extends AbstractViewModel {
  public DateTimeImmutable $now {
    get => new DateTimeImmutable();
  }
}
```

If your getter was doing work, or needed to be consistent for one rendering of the view, you might have been caching it 
into the `$variables` array so that it was only called once: 

```php
/**
 * @property-read int $lottery_number; 
 */
class MyView extends AbstractViewModel {
  protected function var_lottery_number() {
    return $this->variables['lottery_number'] = random_int(0, 49);
  }
}
```

You can use the new `getCached()` helper for this:

```php
class MyView extends AbstractViewModel {
  public int $lottery_number {
    get => $this->getCached(__PROPERTY__, fn() => random_int(0, 49));
  }
}
```

You might also have been using computed properties to "fake" asymmetric visibility on an injected dependency:

```php
/**
 * @property-read AssetManager $assets
 */
class MyView extends AbstractViewModel {
  public function __construct(
     private AssetManager $assets, 
  ) 
  {}
  
  protected function var_assets() {
    return $this->assets;
  }
}
```

This can now just use PHP's native asymmetric visibility, or readonly properties:
```php
class MyView extends AbstractViewModel {
  public function __construct(
     public readonly AssetManager $assets,
     // Or:
     public protected(set) AssetManager $assets,
  ) 
  {}  
}
```

The migration tool will attempt to convert all of these for you:

* If a var_ method only contains a single statement (after removing any that only relate to caching in the $variables 
  array), it will inline the implementation to the property hook and remove the method.
* If a var_method is more complex, it will be kept and called from the get hook (e.g. `get => $this->var_do_stuff()`).

If your view classes have complex logic (e.g. calculating and caching multiple values in a single var_ method), or use
complex property / method inheritance chains, the migration tool may produce unexpected results. You should be able
to identify these from your test failures, but you should also carefully review the diff.

The new property type will be defined from:

* The `var_method` return type (if defined); or
* The `@property-read` docblock type (if this is defined and can be converted to a PHP runtime type, see below); or
* `mixed`

### `$variables` and `$default_variables` to define view variables passed to display() and accessed via __get

This feature is now removed in favour of defining each variable as an explicit property with `public protected(set)` and
potentially a `DisplayVariableAttribute` to control how it is validated during `->display()`.

The migration tool will detect and convert both the definition and usage of these variables. For example:

```php
/**
 * @property-read string $title
 * @property-read bool $has_errors
 */
class MyView extends AbstractViewModel {
  protected $variables = [
    'title'=> null
  ];
  
  protected $default_variables = [
    'has_errors' => false,
  ];
}
```

Will become:

```php
use Ingenerator\KohanaView\Attribute\OptionalDisplayVariable;

class MyView extends AbstractViewModel {
  public protected(set) string $title;
  
  #[OptionalDisplayVariable]
  public protected(set) string $has_errors = false;
}
```

The new property type will be defined from:

* The `@property-read` docblock type (if this is defined and can be converted to a PHP runtime type, see below); or
* `mixed`

### Property types

When generating properties, the migration tool will attempt to define types based on the `@property-read` docblock 
tag if present. If the property is defined with a complex phpdoc / phpstan type, the tool will attempt to generate
the correct PHP runtime type and move the phpdoc type to the property declaration - for example:

```php
/**
 * @property-read Person[] $people
 */
class MyView extends AbstractViewModel {
  protected $variables = [
    'people'=> null
  ];
}
```

will become:

```php
class MyView extends AbstractViewModel {

  /**
   * @var People[] 
   */
  public protected(set) array $people;
}
```

This may cause errors in your app if your phpdoc is incorrect, particularly for example if you are passing custom
collection classes without explicitly defining this in your typehints.

### Custom display variable validation

Previously, you could add custom validation to display variables by extending the `validateDisplayVariables` 
method. This was commonly used to add type-checking of passed values - this is no longer required because
variables must match the native PHP property type defined in your model. Validation is now entirely private
to AbstractViewModel and we do not provide a mechanism to override it (you can of course extend `display()`
if absolutely required).

The migration tool will add an `#[Override]` attribute to any custom `validateDisplayVariables` method.
This will cause a PHP error when your file is parsed, as `validateDisplayVariables` no longer exists 
in `AbstractViewModel`. This will prompt you to manually review and/or remove your method.

### Template `raw` display syntax

Previously, in a template, `<?=raw('<p>My tag</p>);?>` was a "virtual" function syntax that was used
to tell the template compiler not to add escaping around the value.

In 5.x, `raw` becomes a real function in the `Ingenerator\KohanaView\OutputValue` namespace, which 
wraps the value in an explicit `UnescapedHtmlSafeString` class before passing it into the renderer's
escaping layer.

Additionally, to render a child view from a template, you had to explicitly render it and mark the result
as raw - e.g. `<?=raw($renderer->render($view->child_view));?>`. The escaping layer now automatically detects
a `ViewModel` instance and recursively renders it so this syntax is no longer required.

The migration tool will therefore look for uses of the global `raw` function in any file and replace it,
so:

```php
<?php
/**
 * @var MyView $view  
 */
?>
<h1><?=raw($view->label);?> Heading</h1>
<div><?=raw($renderer->render($view->content_view));?></div>
```

Will become:

```php
<?php
use function Ingenerator\KohanaView\OutputValue\raw;
/**
 * @var MyView $view  
 */
?>
<h1><?=raw($view->label);?> Heading</h1>
<div><?=$view->content_view;?></div>
```

### `AbstractViewModel` no longer has a constructor

Any extending classes will need to remove `parent::__construct()` calls (unless they extend an intermediate class
that defines a constructor).

The migration tool will attempt to detect and remove these redundant calls.
