Upgrading to version 5.x
========================

5.x is a major upgrade of the library and introduces a *lot* of breaking changes to take advantage
of modern PHP features. These will require changes to your view models and potentially to code that
uses them.

We have provided an automated migration tool (powered by Rector) that you can run to assist with
adapting code built for earlier versions.

### Installing and running the migration tool

The tool **is not included in the composer package**. Instead, you will need to first clone our
git repository. You should install the tool **outside your own project's root directory**. You
can then install the tool's composer dependencies.

The tool will operate against the current working directory, so switch to your project's root
directory to run it.

```bash
git clone https://github.com/ingenerator/kohana-view $HOME/kohana-view
cd $HOME/kohana-view/v5-migration-tool
composer install
cd $PATH_TO_YOUR_PROJECT
$HOME/kohana-view/v5-migration-tool/migrate
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
