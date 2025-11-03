<?php
// Anomaly, but there might be a NS declaration in a view - it's not illegal.
namespace My\Application\View\Template;
use function Ingenerator\KohanaView\OutputValue\raw;

/**
 * @var My_View_Class $view
 */
?>
<h1><?=raw($view->label_html);?></h1>
