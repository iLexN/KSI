<?php

use Interop\Container\ContainerInterface;

$settings = [
    'settings' => [
        // View settings
        'view' => [
            'template_path' => __DIR__.'/template',
            'twig'          => [
                'cache'       => __DIR__.'/cache',
                'debug'       => true,
                'auto_reload' => true,
            ],
        ],
        // monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__.'/logs/'.date('Y-m-d').'.log',
        ],
        'displayErrorDetails' => true,
    ],
];

$app = new \Slim\App($settings);

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container['view'] = function (ContainerInterface $c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);
    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Flash messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------
// monolog
$container['logger'] = function (ContainerInterface $c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    $logger->pushHandler(new Monolog\Handler\NativeMailerHandler('alex@kwiksure.com','KSI error log','alex@kwiksure.com'));

    return $logger;
};
