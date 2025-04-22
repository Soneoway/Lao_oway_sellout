<?php

header('Access-Control-Allow-Origin: *');
$start_time = microtime(true);
// require_once(__DIR__ . '/../library/PhpConsole/__autoload.php');
// PhpConsole\Helper::register();

// $handler = PhpConsole\Handler::getInstance();
// $handler->start();
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

if (file_exists(__DIR__ . '/../library/PhpConsole/__autoload.php') && APPLICATION_ENV == 'development') {
    require_once(__DIR__ . '/../library/PhpConsole/__autoload.php');
    PhpConsole\Helper::register();

    $handler = PhpConsole\Handler::getInstance();
    $handler->start();
} else {
    require_once(__DIR__ . '/../library/My/Fake_PC.php');
}

/** Zend_Application */
require_once 'Zend/Application.php';
/** Custom configuration */
include_once APPLICATION_PATH . '/configs/common.conf.php';
date_default_timezone_set('Asia/Saigon');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();
$end_time = microtime(true);

PC::db($end_time - $start_time);