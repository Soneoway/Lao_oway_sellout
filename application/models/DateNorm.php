<?php
class Application_Model_DateNorm extends Zend_Db_Table_Abstract
{
	protected $_name = 'date_norm';
    
     function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'date');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->date;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

}                                                      
