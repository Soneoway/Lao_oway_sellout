<?php
class Application_Model_AllArea extends Zend_Db_Table_Abstract
{
    protected $_name = 'all_area';

    /**
     * Get all staff id with perrmisson view reports of all areas
     * @return array - staff id list
     */
    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[] = $item->staff_id;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }

        return $result;
    }
}