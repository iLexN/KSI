<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

session_cache_limiter(false);
session_start();

error_reporting(E_ALL);

$autoloader = require 'vendor/autoload.php';

include 'config.php';
include 'setup.php';

$autoloader->addPsr4('Ksi\\', __DIR__.'/model');

//middleware start
$authenticate = function (ServerRequestInterface $request, ResponseInterface $response, $next) {
    if (!isset($_SESSION['login'])) {
        $this->flash->addMessage('loginError', 'Login required');
        return $response->withStatus(301)->withHeader('Location', $this->router->pathFor('home'));
    }
    $response = $next($request, $response);
    return $response;
};
// middleware end

$app->get('/', function (ServerRequestInterface $req, ResponseInterface $res, $args = []) {
    return $this->view->render($res, 'login.html.twig',
                ['flash' => $this->flash->getMessages()]
            );
})->setName('home');

$app->post('/', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    if (Ksi\User::validateLogin($req->getParsedBody()['login'], $req->getParsedBody()['pwd'])) {
        $_SESSION['login'] = true;
        return $res->withStatus(301)->withHeader('Location', $this->router->pathFor('list'));
    } 
    
    $this->flash->addMessage('loginError', 'Login Error, please check username and password');
    return $res->withStatus(301)->withHeader('Location', $this->router->pathFor('home'));
    
});

$app->get('/logout', function (ServerRequestInterface $req, ResponseInterface $res, $args = []) {
    unset($_SESSION['login']);
    $this->flash->addMessage('loginError', 'You are Logout');
    return $res->withStatus(301)->withHeader('Location', $this->router->pathFor('home'));
})->setName('logout');

$app->get('/list', function (ServerRequestInterface $req, ResponseInterface $res, $args = []) {

    $salesList = \Ksi\User::salesList();
    list($totalQuote, $quoteAr, $numberListedQuote) = \Ksi\Quote::outstandingQuote();

    $data = [
                'totalQuote'        => $totalQuote,
                'numberListedQuote' => $numberListedQuote,
                'quoteAr'           => $quoteAr,
                'salesList'         => $salesList,
                'flash'             => $this->flash->getMessages(),
                //'lastUpdateDateTime'=>date("Y-m-d H:i", filemtime('download_log/'. date("Ymd") . '.logs'))
    ];

    return $this->view->render($res, 'list.html.twig',
                $data
            );

})->add($authenticate)->setName('list');

$app->post('/pass', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    $allPostVars = $req->getParsedBody();

    $emailArray = [];
    if (isset($allPostVars['pass']['all'])  && $allPostVars['pass']['all'] === 'Pass All') {
        foreach ($allPostVars['quote'] as $oneQuoteAr) {
            if (!empty($oneQuoteAr['sale'])) {
                array_push($emailArray, \Ksi\Quote::pushOneQuote($oneQuoteAr));
            }
        }
    } else { // pass one
        $processID = array_keys($allPostVars['pass']);
        $oneQuote = $allPostVars['quote'][$processID[0]];
        if (!empty($oneQuote['sale'])) {
            $emailArray = [\Ksi\Quote::pushOneQuote($oneQuote)];
        }
    }

    if (count($emailArray) === 0) {
        $this->flash->addMessage('quoteError', 'Please select sale to Pass');
        return $res->withStatus(301)->withHeader('Location', $this->router->pathFor('list'));
    }

    return $this->view->render($res, 'pass.html.twig',
                ['emailArray' => $emailArray]
            );

})->add($authenticate)->setName('pass');

$app->get('/compare/{id}', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    $newQuote = ORM::for_table('motor_quote', 'local')->
                    where('id', $args['id'])->
                    find_one();

    $oldQuote = ORM::for_table('motor_quote', 'local')->
                    where('id', $newQuote->oldRefID)->
                    find_one();

    echo '<table border="1">';
    echo '<tr>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td>Old Ref Info</td>';
    echo '</tr>';

    foreach ($newQuote->as_array() as $k => $v) {
        if ($k == 'refno') {
            continue;
        }
        if ($k == 'oldRefID') {
            continue;
        }
        if ($k == 'drivingExp_key') {
            continue;
        }
        if ($k == 'carMake_key') {
            continue;
        }
        if ($k == 'carModel_key') {
            continue;
        }
        if ($k == 'occupation_key') {
            continue;
        }
        if ($k == 'drivingExp_key2') {
            continue;
        }
        if ($k == 'occupation_key2') {
            continue;
        }

        if ($newQuote->$k == $oldQuote->$k) {
            continue;
        }

        echo '<tr>';
        echo '<td>'.$k.'</td>';
        echo '<td>'.$newQuote->$k.'</td>';
        echo '<td>'.$oldQuote->$k.'</td>';
        echo '</tr>';
    }
    echo '</table>';

})->add($authenticate);

$app->get('/adlog', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    $adArray = \Ksi\AdLog::adLogList();
    echo $adArray;
});

//assets
$app->get('/js/{js}', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    $file = 'assets/js/'.$args['js'];

    $out = new Assetic\Asset\AssetCollection([
        new Assetic\Asset\FileAsset($file),
    ], [
        new Assetic\Filter\JSMinFilter(),
    ]);

    return $res->write($out->dump())
                ->withHeader('Content-type', 'application/javascript;charset=utf-8');

})->setName('js');

$app->get('/css/{css}', function (ServerRequestInterface $req, ResponseInterface  $res, $args = []) {

    $file = 'assets/css/'.$args['css'];

    $out = new Assetic\Asset\AssetCollection([
            new Assetic\Asset\FileAsset($file),
        ], [
            new Assetic\Filter\CssMinFilter(),
        ]);

        return $res->write($out->dump())
                ->withHeader('Content-type', 'text/css;charset=utf-8');

})->setName('css');

$app->run();
