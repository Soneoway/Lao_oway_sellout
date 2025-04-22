<?php
/**
 * Model này để quản lý các config
 * Lưu cache theo [key]=value;
 */
class Application_Model_Config extends Zend_Db_Table_Abstract
{
	protected $_name = 'config';

	function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            
            if ($data){
                foreach ($data as $item){
                    $result[$item->key] = $item->value;
                }
            }

            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
}
