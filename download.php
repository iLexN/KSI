<?php

/*
 * For crontab use
 * becare the user/group permissions
 */
$autoloader = require 'vendor/autoload.php';
$autoloader->addPsr4('Ksi\\', __DIR__.'/model');

include 'config.php';
include 'lib/helper.php';

$config = include_once 'settings.php';
$settings = $config['settings'];

$logger = new \Monolog\Logger($settings['logger']['name']);
$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
$logger->pushHandler(new Monolog\Handler\NativeMailerHandler('alex@kwiksure.com','SG-KSI download error log','alex@kwiksure.com'));
\Monolog\ErrorHandler::register($logger);

$t = (isset($_GET['t']) && !empty($_GET['t']))  ? $_GET['t'] : 'a';

if (isWorkingHour()){
    $min = '5';
} else {
    $min = '60';
}

$allQuote = Ksi\QuoteBuilder::downloadQuote($t,$min);

echo(date('Y-m-d H:i:s').' : '.implode(',', $allQuote))."\n";
