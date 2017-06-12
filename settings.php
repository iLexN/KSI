<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
return [
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
        'dataCacheConfig' => [
            'path'         => __DIR__.'/cache',
            'expiresAfter' => 60,
        ],
    ],
];
