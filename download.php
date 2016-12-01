<?php

/* 
 * For crontab use
 * becare the user/group permissions
 */
require_once 'vendor/autoload.php';

include 'config.php';
include 'model/model.php';
include 'lib/array_column.php';


$logger = new \Monolog\Logger('download');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('./logs/'.date('Y-m-d').'.log', \Monolog\Logger::DEBUG));
$logger->pushHandler(new Monolog\Handler\NativeMailerHandler('alex@kwiksure.com','KSI error log','alex@kwiksure.com'));

/* @var $allQuote array->object */

\Monolog\ErrorHandler::register($logger);


$t = (isset($_GET['t']) && !empty($_GET['t']))  ? $_GET['t'] : false;

if ( $t == 'm') {
    $allQuote = Ksi\Quote::downloadQuote('m');
} else { 
    $allQuote = Ksi\Quote::downloadQuote();
}

echo( date("Y-m-d H:i:s") . " : " . implode(',', $allQuote) ) . "\n";



