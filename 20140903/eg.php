<?php
session_cache_limiter(false);
session_start();

require_once '../vendor/autoload.php';
error_reporting(E_ALL);

//db config
$host = "10.0.2.10";
$dbname = "ks-car2";
$dbusername = "root";
$dbpassword = "root";


ORM::configure('mysql:host=' . $host . ';dbname=' . $dbname);
ORM::configure('username', $dbusername);
ORM::configure('password', $dbpassword);
ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
ORM::configure('return_result_sets', true);
ORM::configure('caching', true);
ORM::configure('caching_auto_clear', true); // automatically clear it on save
ORM::configure('logging', false);
ORM::configure('logger', function($log_string, $query_time) {
    echo $log_string . ' in ' . $query_time . '<br/>';
});

//define('DOMPDF_ENABLE_AUTOLOAD', false);

//end config
include('orm/model.class.php');
// ORM class


// define route condition
\Slim\Route::setDefaultConditions(array(
	'lang' => '(en|zh)',
	'wh'=>'(w|h)',
	'num'=>'\d+'
));



$app = new \Slim\Slim();
/* i don't like this
$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'appsession',
    'secret' => 'diulasing',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));*/
// set logger , try the monolog	
$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => array(
        new \Monolog\Handler\StreamHandler('./logs/'.date('Y-m-d').'.log'),
//	new \Monolog\Handler\NativeMailerHandler('alex@kwiksure.com','car app log', 'carappLog@logger.com')
    ),
    'processors' => array(
//	new \Monolog\Processor\IntrospectionProcessor,
	new \Monolog\Processor\WebProcessor
    )
));
$app->config(array(
    'debug' => true,
    'templates.path' => 'template',
    'view' => new \Slim\Views\Twig(),
    'log.writer' => $logger,
//    'log.enabled' => true,
//    'log.level' => \Slim\Log::DEBUG
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension()
);

// middleware
$authenticate = function ($app) {
    return function () use ($app) {
        if (!isset($_SESSION['loginID'])) {
            $app->flash('loginError', 'Login required');
            $app->redirect( $app->urlFor('login')  );
        }
    };
};

//$app->hook('slim.before.dispatch', function() use ($app) { 
$app->hook('slim.before.router', function() use ($app) {

//	$app->log->info('slim.before.dispatch');
	
   $user = null;
   if (isset($_SESSION['loginID'])) {
      $app->userInfo = UserLogin::LoadByID($_SESSION['loginID']);
   }

   $detect = new Mobile_Detect;
   $app->deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

   $app->view->setData(
	array(
		'deviceType'=> $app->deviceType,
		'deviceObj' => $detect
	)
   );


});
/*
$app->hook('slim.before', function() use ($app) {
        $app->log->info('slim.before');
});
*/
$app->hook('slim.before.router', function() use ($app) {
  //      $app->log->info('slim.before.router');
// add support /
	$app->environment['PATH_INFO'] = rtrim($app->environment['PATH_INFO'],'/');

});
/*
$app->hook('slim.after.dispatch', function() use ($app) {
        $app->log->info('slim.after.dispatch');
});

$app->hook('slim.after.router', function() use ($app) {
        $app->log->info('slim.after.router');
});

$app->hook('slim.after', function() use ($app) {
        $app->log->info('slim.after');
});
*/


//start rounter


$app->get('/', function() use ($app) {
	//echo(' Home');
	$app->log->info('go redirect');
	$app->redirect( $app->urlFor('one', array('lang' => 'en'))  );

})->name('home');


$app->get('/:lang/one',function($lang) use ($app) {

	$rule1 = QuoteRule::LoadOne(1);
	$rule1->getOcc();

	$app->view->appendData(array(
		'lang' => $lang,
                'rule'=>$rule1,
		'aa'=>''
        ));
	 $app->render( $app->deviceType . '/one-lang2.html');

})->name('one');


$app->get('/make-pdf',function() use ($app) {

	$rule1 = QuoteRule::LoadOne(1);
        $rule1->getOcc();

        $app->view->appendData(array(
                'lang' => 'zh',
                'rule'=>$rule1,
                'aa'=>''
        ));
	
	$out =  $app->view->fetch('one-lang2.html');

require_once '../vendor/dompdf/dompdf/dompdf_config.inc.php';
$dompdf = new DOMPDF();
$dompdf->load_html($out,'UTF-8');
$dompdf->render();
$dompdf->stream("dompdf_out.pdf", array("Attachment" => false));

	//echo ($out);
	//$pdf = new mikehaertl\wkhtmlto\Pdf($out);
	//$pdf->binary = '../vendor/bin/wkhtmltopdf-i386';

	//if ( !$pdf->saveAs('pdf/' . time() . '.pdf') ) {
	//	throw new Exception('Could not create PDF: '.$pdf->getError());

	//}



});




$app->get('/:lang/two',function($lang) use ($app) {

        $rule1 = QuoteRule::LoadOne(1);
        $rule1->getOcc();

        $app->view->appendData(array(
                'lang' => $lang,
                'rule'=>$rule1,
		'aa'=>'hard code not in seesion'
        ));

         $app->render('one-lang2.html');

})->name('two');

$app->get('/info',function () {
	phpinfo();
});

$app->get('/email',function() use ($app) {
	$rule1 = QuoteRule::LoadOne(1);
        $rule1->getOcc();

	$lang = 'en';

        $app->view->appendData(array(
                'lang' => $lang,
                'rule'=>$rule1,
                'aa'=>'hard code not in seesion'
        ));

         $mailBody = $app->view->fetch('one-lang2.html');

	 $mail = new PHPMailer(true);
try {
	$mail->setFrom('alex@kwiksure.com', 'alex test');
	$mail->addAddress('alex@kwiksure.com', 'Alex Ng');
	$mail->Subject = 'edm test version ..';
	$mail->msgHTML($mailBody, dirname(__FILE__));
	$mail->send();
} catch (phpmailerException $e) {
    echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
    echo $e->getMessage(); //Boring error messages from anything else!
}



/* if not using try catch 
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
*/


});

$app->get('/login',function () use ($app) {
	$app->render('login.html');
})->name('login');


$app->post('/login',function() use ($app) {

	$user = UserLogin::LoadByLogin($app->request()->post('login') , $app->request()->post('password'));

	$app->log->info('post login');

	if ( $user_id = $user->validate() ) {
		$_SESSION['loginID'] = $user_id->id;
		$app->redirect( $app->urlFor('home')  );
	} else { 
		$app->flash('loginError','Login Error, please check username and password');
		$app->redirect( $app->urlFor('login')  );
	}

});

$app->get('/logout',function () use ($app) {
	unset($_SESSION['loginID']);
	unset($app->userInfo);
	$app->flash('loginError','You are logout.');
	$app->redirect($app->urlFor('login') );
});


$app->get("/about", $authenticate($app), function () use ($app) {
	$app->userInfo->getOcc();
	$app->view->appendData(array(
		'user'=>$app->userInfo
	));
   $app->render('about.html');
});

//try group for authenticate
$app->group('/member', $authenticate($app)  ,function () use ($app) {
	
	$app->get('/p1',function() use ($app) {
		$app->userInfo->getOcc();

		$app->view->appendData(array(
		    'user' => $app->userInfo
		    
		));
		$app->log->info('aaa :: {occupation}', $app->userInfo->userInfo->as_array() );

		$app->render('about.html');
	})->name('mp1');

	$app->get('/p2',function() use ($app) {
                $app->userInfo->getOcc();
                $app->view->appendData(array(
                        'user'=>$app->userInfo
                ));
		$app->render('about.html');
        })->name('mp2');

});


// try js 
$app->get('/js/:js',function($js) use ($app) {
	$app->response->headers->set('Content-Type', 'application/javascript;charset=utf-8 ');
	$file = $app->config('templates.path') . '/' .  'js/' . $js;
	$app->lastModified( filemtime( $file ) );


	$out = new Assetic\Asset\AssetCollection(array(
	    new Assetic\Asset\FileAsset( $file )
	), array(
	    new Assetic\Filter\JSMinFilter(),
	));

	print $out->dump();

});

$app->get('/css/:css',function($css) use ($app) {
        $app->response->headers->set('Content-Type', 'text/css;charset=utf-8 ');

        $file = $app->config('templates.path') . '/' .'css/' . $css;
        $app->lastModified( filemtime( $file )  );

	require ('lib/cssmin-v3.0.1.php');

        $out = new Assetic\Asset\AssetCollection(array(
            new Assetic\Asset\FileAsset( $file   )
        ), array(
            new Assetic\Filter\CssMinFilter()
        ));
	
        print $out->dump();

})->name('css');

$app->get('/resize/:wh/:num/:img',function($resize_wh , $num , $img ) use ($app) {

	$file = 'images/' . $img;
	if ( file_exists ( $file  ) ) {

		$app->lastModified( filemtime( $file )  );	
		$img = Intervention\Image\ImageManagerStatic::make($file);
	
		if ( $resize_wh === 'w'	) {
			$img->widen( (int) $num);
		} elseif ( $resize_wh === 'h' ) {
			$img->heighten( (int)  $num);
		}
		$app->response->headers->set('Content-Type',$img->mime());


		echo( $img->response('')  );
	} else {
		//$s = getimagesize ('images/no-img.png');
		//$app->response->headers->set('Content-Type', $s['mime']);
		$app->response->headers->set('Content-Type',$img->mime());
		echo Intervention\Image\ImageManagerStatic::make('images/no-img.png')->response();
	}
})->name('resize');


$app->get('/device',function () use ( $app) {


	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');


	echo('deviceType : '. $deviceType);
	echo('<br/>');
	
	if( $detect->isiOS() ){
		echo('ios');
	}

	if( $detect->isAndroidOS() ){
		echo('android');
	}
});


$app->notFound(function () use ($app) {
    $app->render('404.html');
});


$app->run();
