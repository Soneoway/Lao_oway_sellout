<?php
class Application_Model_PackedSim extends Zend_Db_Table_Abstract
{
	protected $_name = 'packed_sim';
    protected $_schema = WAREHOUSE_DB;

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

}