<?php

$app = new \Slim\Slim();

//setup logger
$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => array(
        new \Monolog\Handler\StreamHandler('./logs/'.date('Y-m-d').'.log')
    ),
    'processors' => array(
        new \Monolog\Processor\WebProcessor
    )
));

// config Slim
$app->config(array(
    'debug' => true,
    'templates.path' => 'template',
    'view' => new \Slim\Views\Twig(),
    'log.writer' => $logger
));

//setup view
$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension()
);
