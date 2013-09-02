<!--Raw Output-->
<h1>Test</h1>
<!--Unescaped class var-->
<?=!$foo?>

<!--Escaped class var-->
<?=$foo?>

<!--Unescaped class var with ;-->
<?=!$foo;?>

<!--Escaped class var with ;-->
<?=$foo;?>

<!--Loop with local var -->
<?php foreach ($array as $tmp_array_var):?>
<?=$foo . $tmp_array_var;?>

<?php endforeach; ?>

<!--Conditional with shorttags -->
<?if ($foo != 'ok'):?>
<?php echo $foo;?>

<?endif;?>

<!--Access to property within PHP code block - property mapped but *not* escaped -->
<?php
   echo $foo;
?>
