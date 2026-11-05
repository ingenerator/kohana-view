<?php

declare(strict_types=1);

// Set up temporary directories for the app
// These need to exist and be writable, though we do very little with them
(static function (): void {
    $mkdir = static function (string $dir): string {
        if ( ! mkdir($dir, recursive: true)) {
            throw new RuntimeException('Could not create temporary directory in '.$dir);
        }

        return $dir;
    };

    $basedir = uniqid(sys_get_temp_dir().'/kohana-tests/');
    define('APPPATH', $mkdir($basedir.'/application').'/');
    $mkdir(APPPATH.'cache');
    define('MODPATH', $mkdir($basedir.'/modules').'/');
    define('DOCROOT', $mkdir($basedir.'/htdocs').'/');
})();

define('SYSPATH', __DIR__.'/../vendor/ingenerator/kohana-core/');
define('EXT', '.php');

error_reporting(\E_ALL);

define('KOHANA_START_TIME', microtime(true));
define('KOHANA_START_MEMORY', memory_get_usage());

// Load the core Kohana class and extension
require SYSPATH.'classes/Kohana/Core'.EXT;
require SYSPATH.'classes/Kohana'.EXT;

// Set default runtime environment (based on default Kohana install)
date_default_timezone_set('America/Chicago');
setlocale(\LC_ALL, 'en_US.utf-8');
mb_substitute_character('none');

// Enable the Kohana auto-loader and enable it for unserialization
spl_autoload_register(['Kohana', 'auto_load']);
ini_set('unserialize_callback_func', 'spl_autoload_call');

// Configure and initialize Kohana
I18n::lang('en-us');
Kohana::init();
Kohana::$config->attach(new Config_File());

Kohana::modules([
    'kohana-view' => __DIR__.'/../',
]);

define('TEST_ROOT_PATH', realpath(__DIR__).'/');
