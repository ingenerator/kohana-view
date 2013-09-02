<?php
/**
 * Tests mapping of view class variables and functions
 * 
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 *
 */

// Fields prefixed var should be available under their local name
echo $protected.PHP_EOL;
echo $public.PHP_EOL;
echo $function.PHP_EOL;

if ( ! isset($protected)) {
	// Obviously you probably wouldn't throw exceptions in views other than for tests
	throw new Exception("var_protected should be mapped anywhere it appears");
}

if (isset($class_only)) {
	throw new Exception("fields not beginning \$var should not be available");
}

if (isset($class_only_func)) {
	throw new Exception("functions not beginning func should not be available");
}

?>

<?=$protected;?>
<?=$public;?>
<?=isset($class_only) ? 'hidden class_only field should not be set' : 'ok';?>
<?=isset($class_only_func) ? 'hidden class_only_func should not be set' : 'ok';?>
