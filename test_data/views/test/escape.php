<!--Raw Output-->
<h1>Test</h1>
<!--Unescaped class var-->
<?=^$foo?>

<!--Escaped class var-->
<?=$foo?>

<!--Unescaped class var with ;-->
<?=^$foo;?>

<!--Escaped class var with ;-->
<?=$foo;?>

<!--Loop with local var -->
<?php foreach (array('1','2','3') as $tmp_array_var):?>
<?=$foo . $tmp_array_var;?>

<?php endforeach; ?>
