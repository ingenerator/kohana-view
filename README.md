`kohana-view` provides separation of view logic and templating for PHP applications. It's designed to be used with the
Kohana framework - but you should be able to use it with most PHP projects with a little work.

[![License](https://poser.pugx.org/ingenerator/kohana-view/license.svg)](https://packagist.org/packages/ingenerator/kohana-view)
[![Build Status](https://travis-ci.org/ingenerator/kohana-view.svg?branch=3.0.x)](https://travis-ci.org/ingenerator/kohana-view)
[![Latest Stable Version](https://poser.pugx.org/ingenerator/kohana-view/v/stable.svg)](https://packagist.org/packages/ingenerator/kohana-view)
[![Latest Unstable Version](https://poser.pugx.org/ingenerator/kohana-view/v/unstable.svg)](https://packagist.org/packages/ingenerator/kohana-view)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ingenerator/kohana-view/badges/quality-score.png?b=3.0.x)](https://scrutinizer-ci.com/g/ingenerator/kohana-view/?branch=3.0.x)
[![Total Downloads](https://poser.pugx.org/ingenerator/kohana-view/downloads.svg)](https://packagist.org/packages/ingenerator/kohana-view)

For legacy projects, it can coexist with the standard Kohana `View` class, but it is not in any way compatible - each
view needs to use either the stock `View` or be updated to work with `kohana-view`. In particular, there are significant
differences in how we approach page layout views compared to the stock Kohana `Controller_Template`.


Why you should use it
---------------------

* Class-based views keep logic out of your controllers, and out of your templates
* Easier to find and update all the display logic in your application
* Easier to customise display logic for modules, configurable sections of applications, etc
* Make the dependencies of each View more obvious and easier to maintain
* Automatically escape all view variables when output as HTML (by default, can be disabled)
* Clean, well-structured code with no global state is easier to test and less at risk of error
* Fully unit tested

Installation
------------

Install with composer: `$> composer require ingenerator/kohana-view`

Add to your `application/bootstrap.php`:

```php
Kohana::modules([
  'existing'    => 'existing/modules/call/here',
  'kohana-view' => __DIR__.'/../vendor/ingenerator/kohana-view'
]);
```

We also recommend using a dependency injection container / service container to manage all the dependencies in your
project. Kohana-view doesn't require one in particular, but comes with configuration for
[zeelot/kohana-dependencies](https://github.com/zeelot/kohana-dependencies). Examples in this readme assume you're
using that container, so if you're using something else (really, don't try and do it all inline in PHP) then fetch
dependencies from your container however required.

Creating your first view
------------------------

Each view starts with a class implementing the `Ingenerator\KohanaView\ViewModel` interface. You can roll your own,
or extend from `Ingenerator\KohanaView\ViewModel\AbstractViewModel` for a base class with some useful common
functionality. View classes can be named anything you like, with or without namespaces, whatever.

```php
<?php
//application/classes/View/Hello/WorldView.php
namespace View\Hello;

/**
 * @property-read string  $name       automatically returned from the $variables array
 * @property-read boolean $is_morning automatically returned from the var_is_morning method
 */
class WorldView extends \Ingenerator\KohanaView\ViewModel\AbstractViewModel
{
    protected $variables = [
        'name' => NULL,
    ];

    protected function var_is_morning()
    {
        $date = new \DateTime;
        return ($date->format('H') < 12);
    }

}
```

Each view class has a corresponding template - by default the template name is mapped from the class name but you can
customise this.

```php
<?php
//application/views/hello/hello_world.php
/**
 * @var \View\Hello\WorldView                         $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
?>
<html>
 <head><title>Hello <?=$view->name;?></title></head>
 <body>
   <h1>
     <?php if ($view->is_morning): ?>
       <img src="sunrise.png" alt="sunrise">Good Morning,
     <?php else: ?>
       <img src="cup_of_tea.png" alt="teacup">Good Afternoon,
     <?php endif; ?>
     <?=$view->name;?> - notice how I HTML escaped your name?
   </h1>
   <div class="alert alert-danger">
     Never render user-provided content unescaped like this: <?= ! $view->name;?>. Don't do it with pure PHP echo
     either like this <?php echo $view->name; ?>. Just do that if you're including other views or known-safe HTML
     content.
   </div>
 </body>
</html>
```

To render this view in a controller response you'd do something like this:

```php
<?php
//application/classes/Controller/Welcome.php
class Controller_Welcome extends Controller
{
  public function action_index()
  {
    $view = new View\Hello\WorldView;
    $view->display(['name' => $this->request->query('name')]);
    $renderer = $this->dependencies->get('kohanaview.renderer.html');
    /** @var \Ingenerator\KohanaView\Renderer\HTMLRenderer */
    $this->response->body($renderer->render($view));
  }
}
```

Template mapping
----------------

All templates are loaded from the /views path in the Cascading File System, using the same rules as the rest of Kohana
to select the appropriate version when a template is present in one or more modules/application directories.

By default, the template is selected based on the name of the view class. Namespace separators and underscores become
directory separators, CamelCased words become under_scored, and View/ViewModel is stripped from beginning and end. For
example:

| View Class          | Template File (within /views/ in CFS)  |
|---------------------|----------------------------------------|
| Helloworld          | helloworld.php                         |
| Hello_World         | hello/world.php                        |
| HelloWorld          | hello_world.php                        |
| View_Hello_World    | hello/world.php                        |
| View\HelloWorldView | hello_world.php                        |

If you want to customise this globally you can provide an alternate implementation of the `ViewTemplateSelector` class
used by the `TemplateManager`.

Sometimes, however, you just want to customise it for a single view - either because the default mapping isn't ideal for
some reason or because the template depends on the result of some view logic. In that case you can implement the
`TemplateSpecifyingViewModel` interface on your `ViewModel` and explicitly tell the rendering engine which template
to use.

Page Layout / Page Content
--------------------------

Kohana-view provides out-of-the-box support for the common case where you have a number of page content views that you
want to render as the body content of one (or a small number of) shared page layout view(s). This is the usecase covered
by Kohana's stock Controller_Template. With KohanaView you can implement the same behaviour but with a controller that
extends any base class, using the `PageLayoutRenderer`. This renderer has all the same functionality as the old
Controller_Template, including automatically rendering only the content for an AJAX request and the ability to explicitly
set whether or not the layout should be rendered.

To use `PageLayoutRenderer` you need two views - one implementing `PageLayoutView` and one implementing
`PageContentView`:

```php
<?php
// application/classes/View/Layout.php
/**
 * @property-read string $body_html
 * @propery-read  string $title
 */
class View_Layout extends Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageLayoutView
{
}
```

```php
<?php
// application/classes/View/HelloWorld.php
/**
 * @property-read View_Layout $page
 * @property-read string      $name
 */
class View_HelloWorld extends Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageContentView
{
  protected $variables = [
    'name' => NULL
  ];
}
```

```php
<?php
//application/views/layout.php
/**
 * @var \View_Layout                                  $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
?>
<html>
  <head><title><?=$view->title;?></title></head>
  <body><?= ! $view->body_html; // Good usecase for rendering unescaped content?></body>
</html>
```

```php
<?php
//application/views/hello_world.php
/**
 * @var \View_HelloWorld                              $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
// You can do this here if you want to keep templatey-type stuff together
// Or in your view model at display time if it's a bit more involved
$view->page->setTitle('Hello World');
?>
<h1>Hi <?=$view->name;?></h1>
```

```php
<?php
class Controller_Welcome extends Controller // Look, extend any controller! No more Controller_Template!
{
  public function action_index()
  {
    // You probably want to put your views into the dependency container too
    $layout  = new View_Layout;
    $content = new View_HelloWorld($layout);
    $content->display(['name' => $this->request->query('name')]);
    $renderer = new \Ingenerator\KohanaView\Renderer\PageLayoutRenderer(
      $this->dependencies->get('kohanaview.renderer.html'),
      $this->request
    );

    $this->response->body($renderer->render($content));
  }
}
```

Advanced examples
-----------------

### Caching variables

Views that extend `AbstractViewModel` expose all the variables in their `$variables` array and also any dynamic
variables provided by `var_variable_name` methods. The `$variables` array takes precedence over dynamic methods which
means you can also use it as a cache for calculated variables that only need to be calculated once for each view
rendering:

```php
<?php
class View_That_Does_Work {
  protected $variables = [
    'user_email' => ''
  ];

  protected function var_user_activity()
  {
    $activity = [];
    foreach ($this->database->loadActivityForUser($this->user_email) as $activity) {
      $activity[] = (string) $activity;
    }
    $this->variables['user_activity'] = $activity;
    // Future usage of $view->user_activity will now get the value cached in the variables array without calling
    // this method again.
    return $activity;
  }
}
```

The variables array is cleared with every call to `display`, so values cached in this way will be cleared every time you
provide new view data (eg if rendering a view in a loop).

### Rendering nested views (partials)

The containing view model should expose a reference to the view model for the partial, which might be passed in as a
constructor dependency, created by a dynamic variable method, or injected in some other way.

View models don't have any reference to the renderer, so they cannot render the partial directly - instead this should
happen in the template using the current renderer that is provided as a variable inside the template scope.

For example:

```php
<?php
class View_Container {
  protected $variables = [
    'users' => [],
  ];

  public function __construct(View_User_FaceWidget $face_widget)
  {
    $this->face_widget = $face_widget;
  }

  protected function var_face_widget()
  {
    return $this->face_widget;
  }
}
```

```php
<?php
//application/views/container.php
/**
 * @var \View_Container                               $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
?>
<?php foreach($view->users as $user):?>
  <?php $view->face_widget->display(['user' => $user]);?>
  <?= ! $renderer->render($view); // Note ! prefix to render unescaped HTML ?>
<?php endforeach; ?>
```

### Configuring whether or not templates are compiled

The template engine automatically compiles your source templates to add the auto-escaping functionality. Compiled
templates are cached on disk for future executions. By default, they are cached within the `Kohana::$cache` directory
- alongside your autoloader cache etc - which we recommend should be flushed on every deployment.

The template manager will always compile templates if they don't exist on disk. However, you can also configure it to
compile on every request - useful in development.

If you are using the default dependency container then these options are configured for you, including setting
`recompile_always = (Kohana::$environemnt === Kohana::DEVELOPMENT)`. You can adjust these settings by adding custom
configuration in `application/config/kohanaview.php` - see [config/kohanaview.php](config/kohanaview.php) for the
defaults.

If you are using your own service container you should configure the `$options` argument to your `CFSTemplateManager`
accordingly.

Credits
-------

This package is heavily inspired by [dyron/kohana-view](https://github.com/dyron/kohana-view) which itself is a fork of
[zombor/View-Model](https://github.com/dyron/kohana-view) but as of version 2.x has been fully rewritten for a cleaner
and more separated structure using a test-first approach. Thanks and credit to @zombor, @dyron, @nanodocumet and
@slacker for their various contributions to the original packages.

The 2.x version of this package has been sponsored by [inGenerator Ltd](http://www.ingenerator.com)

Contributing
------------

Contributions are very welcome. Please ensure that you follow our coding style, add tests for every change, and
avoid introducing global state or excessive dependencies. For major or API breaking changes please discuss your idea
with us in an issue first so we can work with you to understand the issue and find a way to resolve it that suits
current and future users.

Bug fixes should branch off from the earliest (>=2.0) version where they are relevant and we will merge them up as
required. New features should branch off from the development branch of the current version.

Licence
-------

Licensed under the [BSD-3-Clause Licence](LICENCE.md)
