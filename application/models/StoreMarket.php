<?php
class Application_Model_StoreMarket extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_market';

    function getMarketInfo($store_id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                ->from(array('sm' => 'store_market'), array(
                	'market_name_id' 	=> 'mn.id', 
                	'market_name' 		=> 'mn.name', 
                	'market_type_id'	=> 'mt.id',
                	'market_type_name'	=> 'mt.name',
                ))
                ->joinLeft(array('mn'  => 'market_name')	, 'sm.market_name_id = mn.id'	, array())
                ->joinLeft(array('mt'  => 'market_type')	, 'mn.market_type_id = mt.id'	, array())
                ->where('sm.store_id = ?', $store_id);

        //echo $select;
        $result = $db->fetchRow($select);
        return $result;
    }

    function get_cache(){
        //$cache = Zend_Registry::get('cache');
        //$result = $cache->load($this->_name.'_cache');

        $db = Zend_Registry::get('db');

        //if (!$result) {

            $select = $db->select()
                ->from(array('sm' => 'store_market'), array(
                    'store_id'          => 'sm.store_id',
                    'market_name_id'    => 'mn.id', 
                    'market_name'       => 'mn.name', 
                    'market_type_id'    => 'mt.id',
                    'market_type_name'  => 'mt.name',
                    'pcm_code'          => 's.code',
                    'pcm_name'          => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
                    'pcm_group'         => 'g.name',

                ))
                ->joinLeft(array('mn'  => 'market_name')    , 'sm.market_name_id = mn.id'       , array())
                ->joinLeft(array('mt'  => 'market_type')    , 'mn.market_type_id = mt.id'       , array())
                ->joinLeft(array('mnp' => 'market_name_pcm'), 'sm.market_name_id = mnp.mn_id'   , array())
                ->joinLeft(array('s'   => 'staff')          , 'mnp.staff_id = s.id'             , array())
                ->joinLeft(array('g'   => 'group')          , 's.group_id = g.id'               , array());

            $data = $db->fetchAll($select);

            $result = array();
            if ($data){
                for ($i=0;$i<count($data);$i++) {
                    $result[ $data[$i]['store_id'] ]['market_type']     = $data[$i]['market_type_name'];
                    $result[ $data[$i]['store_id'] ]['market_name']     = $data[$i]['market_name'];
                    $result[ $data[$i]['store_id'] ]['market_name_id']  = $data[$i]['market_name_id'];

                    $result[ $data[$i]['store_id'] ]['pcm_code']        = $data[$i]['pcm_code'];
                    $result[ $data[$i]['store_id'] ]['pcm_name']        = $data[$i]['pcm_name'];
                    $result[ $data[$i]['store_id'] ]['pcm_group']       = $data[$i]['pcm_group'];
                }
            }
            //$cache->save($result, $this->_name.'_cache', array(), null);
        //}
        return $result;
    }

}