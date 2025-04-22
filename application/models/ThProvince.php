<?php
class Application_Model_ThProvince extends Zend_Db_Table_Abstract
{
    protected $_name = 'th_province';

    function get_cache(){
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name_th');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name_th;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

}