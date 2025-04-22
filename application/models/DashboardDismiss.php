<?php
class Application_Model_DashboardDismiss extends Zend_Db_Table_Abstract
{
	protected $_name = 'dashboard_dismiss';

	// function get_cache(){
 //        $cache      = Zend_Registry::get('cache');
 //        $result     = $cache->load($this->_name.'_cache');

 //        if ($result === false) {
 //        	$userStorage = Zend_Auth::getInstance()->getStorage()->read();

 //            $data = $this->fetchAll();

 //            $result = array();
 //            if ($data){
 //                foreach ($data as $item){
 //                    $result[$userStorage->id] = $item->value;
 //                }
 //            }
 //            $cache->save($result, $this->_name.'_cache', array(), null);
 //        }
 //        return $result;
 //    }
}