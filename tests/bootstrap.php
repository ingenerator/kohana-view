<?php
# Bootstrap for running unit tests
\error_reporting(E_ALL | E_STRICT);
\define('TEST_ROOT_PATH', \realpath(__DIR__).'/');
require_once(__DIR__.'/../koharness_bootstrap.php');
require_once(KOHARNESS_SRC.'helper_classes/Session/Fake.php');

// Autoload mocks and test-support helpers that should not autoload in the main app
$mock_loader = new \Composer\Autoload\ClassLoader;
$mock_loader->addPsr4('test\\mock\\', [__DIR__.'/mock/']);
$mock_loader->addPsr4('test\\unit\\', [__DIR__.'/unit/']);
$mock_loader->register();
