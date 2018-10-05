## Unreleased

## 4.2.0 (2018-10-05)

* Add the `kohanaview.renderer.page_layout` dependency definition to the dependencies configuration. 
  There is a very small chance this will be breaking, if you are using our DI container but not our 
  standard kohana core dependency config. If so, you may need to add an explicit definition for 
  kohana.request (pointing to Request::initial() )to your project's definitions. It seemed such an edge
  we thought the risk was small enough to justify including in a non-breaking release.
* Adds support for recursively chained view-models rather than the previous fixed limit of content and
  page template. 

## 4.1.0 (2018-09-04)

* Adds support for PHP ^7.2

## 4.0.0 (2018-02-09)

* [BREAKING] Rewrite handling of 'raw' content to bypass escaping : now use `<?=raw('<em>some html</em>)`
  instead of either the `<?=!` or `<?php echo` approaches used in the past. Makes the unescaped content
  more visible in templates and easier to follow. Templates will now throw an exception if they include 
  either of the old-style variations. 

## 3.0.0
* AbstractViewModel now supports default variables, which can be declared in the view model class,
  overridden as optional arguments to the display method, and are reinitialised to default for each
  display call. May be breaking if your child classes define their own `default_variables` property.

## 2.1.0
* Made the template directory (within the CFS) configurable - eg to allow templates
  in /templates etc rather than /views.

## 2.0.1

* Corrected the phpunit dependency specification in composer.json - it is only required for development.

## 2.0.0

Major rewrite to support namespaces, dependency injection and better separation of concerns. This is a
*very* breaking change, but it'll be worth it. Promise. See also the
[UPGRADING-1.x-2.x.md](UPGRADING-1.x-2.x.md) file for steps to update your existing application.

### Removed
* Support for PHP versions below 5.5
* Transparent extension of package classes - configure your service container to use extension classes
  or alternate implementations of the core interfaces and inject them into the library as required.
* `ViewModel::render()` and `ViewModel::toString()` - views cannot now render themselves but must be
  passed to a `ViewRenderer` instance which is responsible for converting them to string output in some
  way. The default `HTMLRenderer` injects itself into template scope for easy rendering of subviews -
  for example in a template the old call `<?=$child_view->render();?>` or `<?=$child_view;?>` should be
  replaced with `<?=$renderer->render($child_view);?>`.
* The runtime `View_Stream_Wrapper` has been removed. Templates are now compiled and cached to disk for
  better opcode cache and debugging support.
* `View_Layout` has been removed in favour of a `PageLayoutView` and `PageContentView` implementation
  using a separate `PageLayoutRenderer` - for better dependency management and separation of
  responsibilities.
* ViewFactory has been removed - creating views should be the responsibility of your service container
  or similar.

### Changed
* All the class names (well, actually all the classes - everything has been rewritten). You will need
  to change the inheritance of basically everything and in some cases you may need to move extension
  code into more than one new class because of the division of responsibilities.
* Mapping of view class to template is slightly changed as part of supporting namespaces. Underscores
  are still converted to directory separators, but we also now convert namespace separators, add
  underscores in CamelCased classes, and strip any leading or trailing View(Model) from the class name.
* View variables are no longer presented in template scope as though they were globals - instead an
  instance of the view model is provided as `$view` and you can then access variables/methods of the
  view as required. For example, a view with a `caption` property would previously have been rendered
  as `<?=$caption;?>` and would now be `<?=$view->caption;?>`. This reduces the need for duplicate
  variable typehints all over the place and is more robust.
* Storage and assignment of view variables has changed significantly. By default all are now read-only
  and stored in a `$variables` array instead of the legacy `$var_whatever` properties. Variables must
  be provided all at once in a call to the `$view->display($variables)` method, which will throw if
  any variable is missing or unexpected. You may of course add setters or alter the variable validation
  in your own extension view model classes if required.
* The precedence of `->var_variable()` and `->variables['variable']` has swapped. Now a dynamic variable
  method will only be called when there is no variable defined in the `$variables` array. This makes
  caching calculated values simpler - on the first call to your dynamic method assign the result to the
  `$variables` array and subsequent references to the variable will use the value from the array. The
  `display()` method resets the content of the array, so your dynamic method will be called once for
  each rendering of a view if you use the same view in a loop.

### Added
* The `StaticPageContentView` provides a simple viewmodel that you can use where you have a number of
  pages with static content - about/privacy policy/etc - that need a template but don't justify their
  own dedicated view model.

## 1.0.0
This was the first released version of the forked package with a few tweaks
