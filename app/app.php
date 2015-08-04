<?php

// Error reporting
//error_reporting(E_ALL ^ E_NOTICE);

// SlimPHP portable route fix
$_SERVER['SCRIPT_NAME'] = preg_replace('/public\\/index\\.php$/', 'index.php', $_SERVER['SCRIPT_NAME'], 1);

// RedBeanPHP alias fix
use RedBeanPHP\Facade as R;


// Load Config
require 'app/config.php';

// Autoload
require 'vendor/autoload.php';

// RedBeanPHP setup
R::setup('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD);
R::freeze(DB_FREEZE);

// Slim app instance
$app = new \Slim\Slim();

// Slim Config
$app->config([
	'templates.path' => 'app/views',
	'debug' => APP_DEBUG
]);

// Set webroot for portable
$app->hook('slim.before', function () use ($app) {
	$app->wroot = $app->request->getUrl().$app->request->getRootUri();
	$app->view()->appendData(array('wroot' => $app->wroot));
});

// HybridAuth instance
$app->container->singleton('hybridInstance', function($app) {
	$config = [
		"base_url"  => $app->wroot."/cb",
		"providers" => [
			"Facebook" => [
				"enabled"        => true,
				"keys"           => [ "id" => FB_ID, "secret" => FB_SECRET ],
				"scope"   => "email, user_about_me, user_birthday, user_location", // optional
				"trustForwarded" => false
			],
			"Twitter"  => [
				"enabled" => true,
				"keys"    => [ "key" => TW_KEY, "secret" => TW_SECRET ]
			]
		],
		"debug_mode" => HYBRIDAUTH_DEBUG_MODE, 
		"debug_file" => HYBRIDAUTH_DEBUG_FILE
	];
	$instance = new Hybrid_Auth($config);
	return $instance;
} );


// Auth Check
$authenticate = function ($app){
	return function () use ($app){
		$app->hybridInstance;
		$u_ses = Hybrid_Auth::storage()->get('user');
		if (is_null($u_ses) && $app->request()->getPathInfo() != '/login/') {
			$app->redirect($app->wroot.'/login/');
		}
	};
};

// Root, check auth
$app->get('/', $authenticate($app) );


// HybridAuth callback
$app->get('/cb/', function () use ($app){
	Hybrid_Endpoint::process();
});

// Show login
$app->get('/login/', $authenticate( $app ), function () use ($app){
	$app->render('login.php' );
});

// Do login
$app->get('/login/:idp', function ($idp) use ($app){
	try {
		$adapter      = $app->hybridInstance->authenticate(ucwords($idp));
		$user_profile = $adapter->getUserProfile();

		// Show error
		if (empty($user_profile)){
			$app->redirect($app->wroot.'/login/?err=1');
		}

		$snid = ['facebook'=>1,'twitter'=>2];

		// Get id
		$identifier = $user_profile->identifier;

		// Check if user exist
		if (
				R::findOne('users', ' snid = ? AND identifier = ?', array(
					$snid[$idp],
					$user_profile->identifier
				))
		){

			\Hybrid_Auth::storage()->set('user', ['identifier'=>$user_profile->identifier, 'snid'=>$snid[$idp]]);
			$app->redirect($app->wroot.'/welcome/');

		}else{

			$user = R::dispense('users');
			$user->snid	= $snid[$idp];
			$user->identifier	= $user_profile->identifier;
			$user->email		= $user_profile->email;
			$user->first_name	= $user_profile->firstName;
			$user->last_name	= $user_profile->lastName;
			$user->avatar_url	= $user_profile->photoURL;
			$user->reg			= R::isoDateTime();

			if(R::store($user)){
				\Hybrid_Auth::storage()->set('user', ['identifier'=>$user_profile->identifier, 'snid'=>$snid[$idp]]);
				$app->redirect($app->wroot.'/welcome/');
			}

		}

	// Get error
	} catch ( Exception $e ) {
		echo $e->getMessage();
	}
});

// Logout
$app->get('/logout/', function () use ($app){
	$app->hybridInstance;
	\Hybrid_Auth::storage()->set('user', null);
	Hybrid_Auth::logoutAllProviders();
	$app->redirect( $app->wroot.'/login/');
});

// Wellcome
$app->get('/welcome/', $authenticate($app), function () use ($app){
	$u_ses = Hybrid_Auth::storage()->get('user');
	$user = R::findOne('users', ' snid = ? AND identifier = ?', array(
		$u_ses['snid'],
		$u_ses['identifier']
	));
	$app->render('welcome.php',[
		'user' => $user->export()
	]);
});



$app->run();

