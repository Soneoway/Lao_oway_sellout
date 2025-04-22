<?php
class Application_Model_Org extends Zend_Db_Table_Abstract
{
	protected $_name = 'org';

	function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'org_name');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->org_id] = $item->org_name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
 
    function getAll($where) {

        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('o' => $this->_name), array('o.*'));

        if($where != ""){
            $select->orWhere('o.store_type_id = ?', '1');           
            $select->orWhere('o.org_id IN (?)', $where);               
        }

        $select->order('org_name ASC');

        // echo $select; exit(); 
        $result = $db->fetchAll($select);
        return $result;
    }
}
