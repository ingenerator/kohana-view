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
