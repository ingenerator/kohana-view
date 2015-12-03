Upgrading from version 1.x to 2.x
=================================

2.x is a major breaking rewrite of the library and introduces a *lot* of breaking changes. You
should read the [CHANGELOG](CHANGELOG.md) and the [README](README.md) to get a sense of how the
new version fits together.

If you decide to upgrade from 1.x to 2.x in an existing project, the key steps are:

### Update your ViewModel classes

* They must now implement the `ViewModel` interface. Classes that used to extend `View_Model` should
  extend `AbstractViewModel`, classes that used to extend `View_Layout` should extend
  `AbstractPageContentView`, and classes that used to extend `View_Template_Global` should extend
  `AbstractPageLayoutView`.
* Check your constructor signatures - `AbstractPageContentView` classes must be created with a
  reference to the layout view. Runtime view variables should not usually be passed in as
  constructor dependencies but instead assigned in a call to the `display` method.
* Migrate any view variable declarations from `protected $var_whatever` to entries in the `$variables`
  array. Note that if a key is present in `$variables` its value will be returned instead of calling
  any dynamic `var_xxx()` method. Also note that by default any variables that are defined before the
  call to the parent constructor must be passed in on every call to `display()`;

### Update your templates

* You may need to move some - in particular if the class name contains CamelCase (becomes under_score) or
  begins or ends with View/ViewModel (which is now stripped).
* All references to view variables - eg `<?=$foo;?>`, `<?php foreach ($foo as $bar): ?>` must be updated
  to explicitly reference the view - eg `<?=$view->foo;?>`, `<?php foreach ($view->foo as $bar): ?>`
* Any rendering of sub-views must be updated from eg `<?=$child_view->render();?>` or `<?=$child_view;?>`
  to `<?=$renderer->render($child_view);?>`

### Update your view factory

* Only services (repositories, reverse routing engines, child views, etc) should be injected at the
  view construction phase and these would usually be handled in a view factory or dependency injection
  container.
* In particular, note that `AbstractPageContentView` classes need a `PageLayoutView` as a constructor
  dependency.

### Update your controllers

* If you've been passing view data to your view models through individual setters or in a call to your
  view factory, you'll need to assemble these as an array of variables and pass them to a single
  `$view->display($variables);` call. Alternatively if there are some properties that need to be individually
  assignable on a given view you should add appropriate setters to your view model. In that case it's your
  responsibility to ensure any cached dynamic variables and other state is properly reset.
* Your response rendering code will need to be updated. You must now explicitly render views so code like
  `$this->response->body($view);` will need to become `$this->response->body($renderer->render($view));`
  where `$renderer` is an HTMLRenderer provided by your dependency container or similar.
* For page content views, you'll need to use a `PageLayoutRenderer`. This takes a reference to the current
  request, so currently we recommend creating it in the controller. You may want to add a helper method to
  your controller base class along the lines:

```php
    protected function respondPageContent(PageContentView $content_view)
    {
        $renderer = new PageLayoutRenderer(
            $this->dependencies->get('kohanaview.renderer.html'),
            $this->request
        );

        $this->response->body(
            $renderer->render($content_view)
        );
    }
```

### Test everything!

This is of course where having a suite of automated acceptance tests comes in handy, though you should
also review the appearance of your pages manually to be sure they're all working as expected.
