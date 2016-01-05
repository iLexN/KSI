<?php

/* 
 * config
 * - database
 */

//db config

$sourceDbHost = '';
$sourceDbName = '';
$sourceDbUser = '';
$sourceDbPass = '';

$ksiDbHost = '';
$ksiDbName = '';
$ksiDbUser = '';
$ksiDbPass = '';

$localDbHost = '';
$localDbName = '';
$localDbUser = '';
$localDbPass = '';

//source connection
ORM::configure('mysql:host='.$sourceDbHost.';dbname='.$sourceDbName, null, 'source');
ORM::configure('username', $sourceDbUser, 'source');
ORM::configure('password', $sourceDbPass, 'source');
ORM::configure('driver_options', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], 'source');
ORM::configure('return_result_sets', true, 'source');
ORM::configure('caching', true, 'source');
ORM::configure('caching_auto_clear', true, 'source'); // automatically clear it on save
ORM::configure('logging', false, 'source');
ORM::configure('logger', function ($log_string, $query_time) {
    echo $log_string.' in '.$query_time.'<br/>';
}, 'source');

//yellowsheet connection
ORM::configure('mysql:host='.$ksiDbHost.';dbname='.$ksiDbName, null, 'ksi');
ORM::configure('username', $ksiDbUser, 'ksi');
ORM::configure('password', $ksiDbPass, 'ksi');
ORM::configure('return_result_sets', true, 'ksi');
ORM::configure('caching', true, 'ksi');
ORM::configure('caching_auto_clear', true, 'ksi'); // automatically clear it on save
ORM::configure('logging', false, 'ksi');
ORM::configure('logger', function ($log_string, $query_time) {
   echo $log_string.' in '.$query_time.'<br/>';
}, 'ksi');

//localhost connection
ORM::configure('mysql:host='.$localDbHost.';dbname='.$localDbName, null, 'local');
ORM::configure('username', $localDbUser, 'local');
ORM::configure('password', $localDbPass, 'local');
ORM::configure('driver_options', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], 'local');
ORM::configure('return_result_sets', true, 'local');
ORM::configure('caching', true, 'local');
ORM::configure('caching_auto_clear', true, 'local'); // automatically clear it on save
ORM::configure('logging', false, 'local');
ORM::configure('logger', function ($log_string, $query_time) {
    echo $log_string.' in '.$query_time.'<br/>';
}, 'local');
