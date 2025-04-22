<?php
class Application_Model_OGuard extends Zend_Db_Table_Abstract
{
	protected $_name = 'oguard';
    protected $_schema = WAREHOUSE_DB;
/*
    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if (!$result) {

            $data = $this->fetchAll(null);

            $result = array();
            if ($data){
                foreach ($data as $item){
                    if ( is_null($item->sim_activated_at) ) { $result[$item->imei_sn] = ''; } 
                    else { $result[$item->imei_sn] = $item->sim_activated_at; }
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }
*/
}