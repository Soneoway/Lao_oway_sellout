<?php
class Application_Model_GoodCategory extends Zend_Db_Table_Abstract
{
	protected $_name = 'good_category';
    protected $_schema = WAREHOUSE_DB;
 function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if (!$result) {
            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }
    
}
