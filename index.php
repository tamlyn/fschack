<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

defined('APP_ROOT_PATH')
    || define('APP_ROOT_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'live'));


//error reporting
if (APPLICATION_ENV != 'live') {
	error_reporting(E_ALL);
}

//autoloader to load zend components on demand
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

Zend_Session::start();

if (file_exists(APP_ROOT_PATH.'/maintenance.html')) {
	if (isset($_REQUEST['bypass']) || isset($_SESSION['bypass'])) {
		$_SESSION['bypass'] = true;
	} else {
		include APP_ROOT_PATH.'/maintenance.html';
		exit;
	}
}


require_once APPLICATION_PATH.'/controllers/BaseController.php';

$resourceLoader = new Zend_Application_Module_Autoloader(array(
	'namespace' => '',
	'basePath'  => APPLICATION_PATH
));

//load config
$config = new Zend_Config_Yaml(
    APPLICATION_PATH . '/configs/config.yaml',
    APPLICATION_ENV
);
Zend_Registry::set('config', $config);

//connect to database
$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

//set up logging
$logger = new Zend_Log();
if (APPLICATION_ENV != 'live') {
	$writer = new Zend_Log_Writer_Firebug();
} else {
	$writer = new Zend_Log_Writer_Stream('php://stderr');
}
$logger->addWriter($writer);

//set up front controller
$front = Zend_Controller_Front::getInstance();
$front->setControllerDirectory(array(
	'default' => APPLICATION_PATH.'/controllers'
));
// $front->throwExceptions(true);
// $front->setParams(array(
	// 'noErrorHandler' => true,
	// 'db' => $db,
	// 'logger' => $logger
// ));
$front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());

//add layout to view
Zend_Layout::startMvc();
Zend_Layout::getMvcInstance()->setLayout('layout');

//do MVC stuff
$front->dispatch();

//simple debug function
 function dbg($message, $level = 'info') {
	 $GLOBALS['logger']->$level($message);
 }