<?php

/*
 * For crontab use
 * becare the user/group permissions
 */
$autoloader = require 'vendor/autoload.php';
$autoloader->addPsr4('Ksi\\', __DIR__.'/model');

include 'config.php';

$t = (isset($_GET['t']) && !empty($_GET['t']))  ? $_GET['t'] : false;

if ($t == 'm') {
    $allQuote = Ksi\QuoteBuilder::downloadQuote('m');
} else {
    $allQuote = Ksi\QuoteBuilder::downloadQuote();
}

echo(date('Y-m-d H:i:s').' : '.implode(',', $allQuote))."\n";
