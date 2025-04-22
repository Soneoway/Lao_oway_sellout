<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function __construct($application)
    {
        parent::__construct($application);

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('My_');

        $frontendOptions = array
        (
            'lifetime' 					=> null,
            'automatic_serialization' 	=> true
        );

        $strRootDir 	= APPLICATION_PATH.'/../data/cache' ;
        $backendOptions = array
        (
            'cache_dir'	=> $strRootDir
        );
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        Zend_Registry::set('cache', $cache);
    }

    // protected function _initCacheBrowser()
    // {
    //     // ob_start();
    //     $expires = 60*60*40; // 60 * 60 * 24 ... defined elsewhere
    //     header("Content-type: x-javascript");
    //     header('Content-Length: ' . ob_get_length());
    //     header('Cache-Control: max-age='.$expires.', must-revalidate');
    //     header('Pragma: public');
    //     header('Expires: '. gmdate('D, d M Y H:i:s', time()+$expires).'GMT');
    //     // ob_end_flush();
    // }

    protected function _initDB() {

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $db = Zend_Db::factory($config->resources->db);
        $db->query("SET NAMES utf8;");

        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    protected function _initPlugins()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new My_Controller_Plugin_ACL(), 1);

        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('My_', 'My/Controller/Plugin');
        $loader->addPrefixPath('', 'My/Funcs');
        $log = $loader->load('Log');

        $router = new Zend_Controller_Router_Rewrite();
        $request = new Zend_Controller_Request_Http();
        $router->route($request);
        $request->getActionName();
        
        // AJAX thì không cần check notifications
        if ( (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
            && !($request->getControllerName() == 'user' && in_array($request->getActionName(), array('noauth', 'login', 'logout', 'auth')))
        )
            My_Notification::run();

        return $frontController;
    }
}

