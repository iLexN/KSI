<?php
session_cache_limiter(false);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

//require_once 'vendor/autoload.php';
$autoloader = require  'vendor/autoload.php';

include 'config.php';
include 'setup.php';
//include 'model/model.php';
$autoloader->addPsr4('Ksi\\', __DIR__ . '/model');

//middleware start
$authenticate = function ($app) {
    return function () use ($app) {
        if (!isset($_SESSION['login'])) {
            $app->flash('loginError', 'Login required');
            $app->redirect($app->urlFor('home'));
        }
    };
};
// middleware end


$app->get('/', function () use ($app) {
    $app->render('login.html.twig');
})->name('home');


$app->post('/', function () use ($app) {
    if (Ksi\User::validateLogin($app->request()->post('login'), $app->request()->post('pwd'))) {
        $_SESSION['login'] = true;
        $app->redirect($app->urlFor('list'));
    } else {
        $app->flash('loginError', 'Login Error, please check username and password');
        $app->redirect($app->urlFor('home'));
    }
});

$app->get('/logout', function () use ($app) {
    unset($_SESSION['login']);
    $app->flash('loginError', 'You are Logout');
    $app->redirect($app->urlFor('home'));
})->name('logout');



$app->get('/list', $authenticate($app), function () use ($app) {
    
    $salesList = \Ksi\User::salesList();
    list($totalQuote, $quoteAr, $numberListedQuote) = \Ksi\Quote::outstandingQuote();

    $app->view->appendData(array(
        'totalQuote'=>$totalQuote,
                'numberListedQuote'=>$numberListedQuote,
                'quoteAr'=>$quoteAr,
                'salesList'=>$salesList,
                'lastUpdateDateTime'=>date("Y-m-d H:i", filemtime('download_log/'. date("Ymd") . '.logs'))
    ));
    
    $app->render('list.html.twig');
    
})->name('list');

$app->post('/pass', $authenticate($app), function () use ($app) {
    $allPostVars = $app->request->post();
    
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
        } /*else {
            $app->flash('quoteError','Please select sale to Pass');
            $app->redirect($app->urlFor('list'));
        }*/
    }
    
    if (count($emailArray) === 0) {
        $app->flash('quoteError', 'Please select sale to Pass');
        $app->redirect($app->urlFor('list'));
    }
    
    $app->view->appendData(array('emailArray'=>$emailArray));
    $app->render('pass.html.twig');
})->name('pass');


$app->get('/compare/:id', $authenticate($app), function ($id) use ($app) {
    
    $newQuote = ORM::for_table('motor_quote', 'local')->
                    where('id', $id)->
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
    
});


$app->get('/adlog', function () use ($app) {
    /**
     * @todo need process
     */
    $adArray = \Ksi\AdLog::adLogList();
    
    print_r($adArray);
    
});

//assets 
$app->get('/js/:js', function ($js) use ($app) {
    $app->response->headers->set('Content-Type', 'application/javascript;charset=utf-8 ');
    $file = 'assets/js/' . $js;
    $app->lastModified(filemtime($file));

    $out = new Assetic\Asset\AssetCollection(array(
        new Assetic\Asset\FileAsset($file)
    ), array(
        new Assetic\Filter\JSMinFilter(),
    ));
    print $out->dump();
})->name('js');

$app->get('/css/:css', function ($css) use ($app) {
    $app->response->headers->set('Content-Type', 'text/css;charset=utf-8');
    $file = 'assets/css/' . $css;
    $app->lastModified(filemtime($file));

    require('lib/cssmin-v3.0.1.php');

    $out = new Assetic\Asset\AssetCollection(array(
            new Assetic\Asset\FileAsset($file)
        ), array(
            new Assetic\Filter\CssMinFilter()
        ));
        print $out->dump();
})->name('css');


$app->get('/tcss/css.css', function () use ($app) {
    $app->response->headers->set('Content-Type', 'text/css;charset=utf-8 ');
    $file = 'assets/css/css.css';
    $app->lastModified(filemtime($file));
    echo(csscrush_string(file_get_contents($file)));
});

$app->run();
