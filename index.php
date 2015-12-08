<?php
session_cache_limiter(false);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

//require_once 'vendor/autoload.php';
$autoloader = require  'vendor/autoload.php';

include 'config.php';
include 'setup.php';

$autoloader->addPsr4('Ksi\\', __DIR__ . '/model');

//middleware start
$authenticate = function ($request, $response, $next) {
    if (!isset($_SESSION['login'])) {
        $this->flash->addMessage('loginError', 'Login required');
        return $res->withStatus(301)->withHeader("Location", $this->router->pathFor('home'));
    }
    $response = $next($request, $response);

    return $response;
};
// middleware end


$app->get('/', function ($req,   $res, $args = []) {
    return $this->view->render($res, 'login.html.twig', 
                ['flash'=>$this->flash->getMessages()]
            );
})->setName('home');


$app->post('/', function ($req,   $res, $args = []) {
    
    if (Ksi\User::validateLogin($req->getParsedBody()['login'], $req->getParsedBody()['pwd'])) {
        $_SESSION['login'] = true;
        return $res->withStatus(301)->withHeader("Location", $this->router->pathFor('list'));
    } else {
        $this->flash->addMessage('loginError', 'Login Error, please check username and password');
        return $res->withStatus(301)->withHeader("Location", $this->router->pathFor('home'));
    }
});

$app->get('/logout', function ($req,   $res, $args = []) {
    unset($_SESSION['login']);
    $this->flash->addMessage('loginError', 'You are Logout');
    return $res->withStatus(301)->withHeader("Location", $this->router->pathFor('home'));
})->setName('logout');



$app->get('/list', function ($req,   $res, $args = []) {
    
    $salesList = \Ksi\User::salesList();
    list($totalQuote, $quoteAr, $numberListedQuote) = \Ksi\Quote::outstandingQuote();

    $data = array(
        'totalQuote'=>$totalQuote,
                'numberListedQuote'=>$numberListedQuote,
                'quoteAr'=>$quoteAr,
                'salesList'=>$salesList,
                'flash'=>$this->flash->getMessages(),
                //'lastUpdateDateTime'=>date("Y-m-d H:i", filemtime('download_log/'. date("Ymd") . '.logs'))
    );
    
    //$app->render('list.html.twig');
    return $this->view->render($res, 'list.html.twig', 
                $data
            );
    
})->add($authenticate)->setName('list');

$app->post('/pass', function ($req,   $res, $args = []) {
    
    $allPostVars = $req->getParsedBody();
    
    $emailArray = array();
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
            $emailArray = array( \Ksi\Quote::pushOneQuote($oneQuote) );
        }
    }
    
    if (count($emailArray) === 0) {
        $this->flash->addMessage('quoteError', 'Please select sale to Pass');
        return $res->withStatus(301)->withHeader("Location", $this->router->pathFor('list'));
    }
    
    
    return $this->view->render($res, 'pass.html.twig', 
                array('emailArray'=>$emailArray)
            );
    
})->add($authenticate)->setName('pass');


$app->get('/compare/{id}', function ($req,   $res, $args = []) {
    
    $newQuote = ORM::for_table('motor_quote', 'local')->
                    where('id', $args['id'])->
                    find_one();
    
    $oldQuote = ORM::for_table('motor_quote', 'local')->
                    where('id', $newQuote->oldRefID)->
                    find_one();
    
    //print_r($newQuote->as_array());
    //print_r($oldQuote->as_array());

    echo('<table border="1">');
    echo('<tr>');
        echo('<td></td>');
        echo('<td></td>');
        echo('<td>Old Ref Info</td>');
    echo('</tr>');
    
    
    
    foreach ($newQuote->as_array() as $k => $v) {
        if ($k=='refno') {
            continue;
        }
        if ($k=='oldRefID') {
            continue;
        }
        if ($k=='drivingExp_key') {
            continue;
        }
        if ($k=='carMake_key') {
            continue;
        }
        if ($k=='carModel_key') {
            continue;
        }
        if ($k=='occupation_key') {
            continue;
        }
        if ($k=='drivingExp_key2') {
            continue;
        }
        if ($k=='occupation_key2') {
            continue;
        }
        
        $style = '';
        
        if ($newQuote->$k != $oldQuote->$k) {
            $style = 'style="color:red"';
        } else {
            continue;
        }
        
        echo('<tr>');
            //echo('<td '.$style.'>' .$k. '</td>');
            echo('<td>' .$k. '</td>');
        echo('<td>' .$newQuote->$k. '</td>');
        echo('<td>' .$oldQuote->$k. '</td>');
        echo('</tr>');
    }
    echo('</table>');
    
})->add($authenticate);


$app->get('/adlog', function ($req,   $res, $args = []) {
    /**
     * @todo need process
     */
    $adArray = \Ksi\AdLog::adLogList();
    
    print_r($adArray);
    
});

//assets 
$app->get('/js/{js}', function ($req,   $res, $args = []) {
    
    $file = 'assets/js/' . $args['js'];
    //@todo add http cache
    //$app->lastModified(filemtime($file));

    $out = new Assetic\Asset\AssetCollection(array(
        new Assetic\Asset\FileAsset($file)
    ), array(
        new Assetic\Filter\JSMinFilter(),
    ));
    //print $out->dump();
    
    return $res->write($out->dump())
                ->withHeader('Content-type', 'application/javascript;charset=utf-8');
    
})->setName('js');

$app->get('/css/{css}', function ($req,   $res, $args = []) {
    
    $file = 'assets/css/' . $args['css'];
    //@todo add http cache
    //$app->lastModified(filemtime($file));

    require('lib/cssmin-v3.0.1.php');

    $out = new Assetic\Asset\AssetCollection(array(
            new Assetic\Asset\FileAsset($file)
        ), array(
            new Assetic\Filter\CssMinFilter()
        ));
        //print $out->dump();
        
        return $res->write($out->dump())
                ->withHeader('Content-type', 'text/css;charset=utf-8');
        
})->setName('css');


$app->run();
