<?php
class Application_Model_StaffPriviledge extends Zend_Db_Table_Abstract
{
	protected $_name = 'staff_priviledge';

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->staff_id] = $item;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
}                                                      
