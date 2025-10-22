<?php

// Bootstrap for running unit tests
define('TEST_ROOT_PATH', realpath(__DIR__).'/');
require_once __DIR__.'/../koharness_bootstrap.php';
require_once KOHARNESS_SRC.'helper_classes/Session/Fake.php';
