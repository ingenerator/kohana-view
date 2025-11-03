<?php
use function Ingenerator\KohanaView\OutputValue\raw;

/**
 * @var My_View_Class $view
 */
?>
<h1><?=raw($view->label_html);?></h1>
<p><?=raw('other things');?></p>
