<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Basic global template view, rendered by the View_Template_Global class.
 *
 * @see View_Template_Global
 * @var string $title Page title
 * @var string $body  Page body HTML
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title><?=$title;?></title>
    </head>
    <body>
        <?=$body;?>
    </body>
</html>
