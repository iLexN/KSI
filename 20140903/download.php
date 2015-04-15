<?php

/* 
 * For crontab use
 * becare the user/group permissions
 */
require_once 'vendor/autoload.php';

include 'config.php';
include 'model/model.php';
include 'lib/array_column.php';


/* @var $allQuote array->object */

$t = (isset($_GET['t']) && !empty($_GET['t']))  ? $_GET['t'] : false;

if ( $t == 'm') {
    $allQuote = Ksi\Quote::downloadQuote('m');
} else { 
    $allQuote = Ksi\Quote::downloadQuote();
}

echo( date("Y-m-d H:i:s") . " : " . implode(',', $allQuote) ) . "\n";



