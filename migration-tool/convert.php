<?php
require_once __DIR__.'/vendor/autoload.php';
$converter = new \Ingenerator\KohanaViewMigrationTool\ViewModelConverter;
$updated=$converter->convert(file_get_contents($argv[1]));
file_put_contents($argv[1].'.new.php', $updated);