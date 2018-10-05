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
     Never render user-provided content unescaped like this: <?= raw($view->name);?>. Just do that if you're including 
     other views or known-safe HTML content.
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
want to render within a (or possibly a chain of) containing page layout view(s). This is similar to Kohana's stock 
Controller_Template, except that it supports a recursive rendering model where each view can be contained in another up 
to the final overall site template.

For example, this would allow you to have a set of content-area views, a view that renders any of these content areas 
inside a layout with a sidebar and a main area, and a further parent view that renders your overall page header/footer/
etc. As with the old Controller_Template, for AJAX requests the renderer by default only renders the content-area view
and not any of the containing template(s) - this can be customised. This also means you can have your controller extend 
any arbitrary base class.

To use `PageLayoutRenderer` you need a minimum of two views - one implementing `PageLayoutView` and one implementing
`PageContentView`. Note that these interfaces are now deprecated in favour of the more flexible `NestedChildView` and 
`NestedParentView` and will be removed in a future release.

You may want to extend from the provided `AbstractIntermediateLayoutView` and `AbstractNestedChildView` though this is 
in no way compulsory. Your top-level page view should be an instance of `PageLayoutView`.

```php
<?php
namespace View\Layout;

/**
 * @property-read string $body_html
 * @propery-read  string $title
 */
class SitePageTemplateView extends Ingenerator\KohanaView\ViewModel\PageLayout\AbstractPageLayoutView
{
}
```

```php
<?php
namespace View\Layout;

/**
 * @property-read ViewModel $sidebar  
 */
class ContentWithSidebarLayoutView extends Ingenerator\KohanaView\ViewModel\PageLayout\AbstractIntermediateLayoutView
{
    public function __construct(SitePageTemplateView $page, ViewModel $sidebar) 
    {
        parent::__construct($page);
        $this->sidebar = $sidebar;
    }
    
    protected function var_sidebar()
    {
        return $this->sidebar;
    }
}
```

```php
<?php
namespace View\Layout;

class SidebarView extends AbstractViewModel
{
    // Whatever you want it to show
}
```

```php
<?php
namespace View\Pages;

/**
 * @property-read View\Layout\SitePageTemplateView $page
 * @property-read string                           $name
 */
class HelloWorldView extends Ingenerator\KohanaView\ViewModel\PageLayout\AbstractNestedChildView
{
  protected $variables = [
    'name' => NULL
  ];
  
  protected function var_page()
  {
      // If you want to make this available to set things from the view : it's not required
      return $this->getUltimatePageView();
  }
}
```

```php
<?php
//application/views/site_page_template.php
/**
 * @var \View\Layout\SitePageTemplateView $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
?>
<html>
  <head><title><?=$view->title;?></title></head>
  <body><?=raw($view->body_html); // Good usecase for rendering unescaped content?></body>
</html>
```

```php
<?php
//application/views/content_with_sidebar_layout.php
/**
 * @var \View\Layout\ContentWithSidebarLayout $view
 * @var \Ingenerator\KohanaView\Renderer\HTMLRenderer $renderer
 */
?>
<div class="row">
  <div class="sidebar"><?=raw($renderer->render($view->sidebar));?></div>
  <div class="content"><?=raw($view->child_html);?></div>
</div>
```

```php
<?php
//application/views/pages/hello_world.php
/**
 * @var \View\Pages\HelloWorldView                    $view
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
    $content = new HelloWorldView(
        new ContentWithSidebarLayoutView(
            new SitePageTemplateView(),
            new SidebarView()
        )
    );
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

### Default variables

As standard, Views require that the array passed to `AbstractViewModel->display()` contains values for all defined variables.
This is to ensure that the view model is always in the correct state even if it is rendered multiple times (as often happens
with partials and sub-views).

You can define optional view variables by populating the `$default_variables` array in your ViewModel. Note that these 
defaults **will be reassigned** to the `$variables` array on every call to `->display()` to ensure that they are always in
expected state.

```php
class View_Something extends AbstractViewModel {
  protected $default_variables = [
    'title' => 'My page title',
  ];

  protected $variables = [
    'caption' => NULL
  ];

}

print $view->title;    // 'My page title'
print $view->caption;  // ''

$view->display(['caption' => 'Something', 'title' => 'A title']);
print $view->title;    // 'A title'
print $view->caption;  // 'Something'

$view->display(['caption' => 'Something else']);
print $view->title;    // 'My page title'
print $view->caption;  // 'Something else'

```

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
  <?=raw($renderer->render($view)); // Note rendering unescaped HTML ?>
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
