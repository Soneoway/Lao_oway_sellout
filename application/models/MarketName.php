<?php
class Application_Model_MarketName extends Zend_Db_Table_Abstract
{
    protected $_name = 'market_name';

    function fetchPagination($page, $limit, &$total, $params) {

    	$db = Zend_Registry::get('db');

    	if ( (isset($params['export']) and $params['export']) || (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
            
            $get_01 = array('mn_id' => 'mn.id');

        } else {
            $get_01['mn_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS mn.id');
        }

    	$get_02 = array(
    		'mn_name' 	=> 'mn.name',
    		'mt_id' 	=> 'mt.id',
    		'mt_name' 	=> 'mt.name',
    		'area_name' => 'a.name',
    		'cnt_store'	=> new Zend_Db_Expr("COUNT(st.id)"),
    	);

    	$get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('mn' => $this->_name), $get)
        	->join(array('mt' => 'market_type'), 'mn.market_type_id = mt.id', array())
        	->join(array('a'  => 'area'), 'mn.area_id = a.id', array())
        	->joinLeft(array('sm' => 'store_market'), 'mn.id = sm.market_name_id', array())
        	->joinLeft(array('st' => 'store'), 'sm.store_id = st.id', array())
        	->group('mn.id','a.id','mt.id')
        	->order(array('a.name ASC','mt.name ASC','mn.id ASC'));


        if ( isset($params['market_name_id']) && $params['market_name_id'] ) {
            $select->where('mn.id LIKE ?', '%'.$params['market_name_id'].'%');
        } 

        if ( isset($params['market_name']) && $params['market_name'] ) {
            $select->where('mn.name LIKE ?', '%'.$params['market_name'].'%');
        } 

        if (isset($params['market_type']) and $params['market_type']) {
            if (is_array($params['market_type']) && count($params['market_type'])) { 

                $select->where('mn.market_type_id IN (?)', $params['market_type']);

            } elseif (is_numeric($params['market_type'])) {
                
                $select->where('mn.market_type_id = ?', intval($params['market_type']));

            } else {
                $select->where('1=0', 1);
            }
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('mn.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('mn.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $area_permission = $list_regions['area'];

            if (count($area_permission) > 0)
                $select->where( 'mn.area_id IN (?)', $area_permission);
            else
                $select->where('1=0', 1);
        }

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }


    function getMarketNameByStaffCode($staff_code) {

        $db = Zend_Registry::get('db');

        $get = array(
            'mn_id'       => 'mn.id',
            'mn_name'     => 'mn.name', 
            'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group' => 'g.name',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
            ->join(array('ss' => 'store_staff')     , 's.id = ss.staff_id'          , array())
            ->join(array('st' => 'store')           , 'ss.store_id = st.id'         , array())
            ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
            ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
            ->where('s.code = ?', $staff_code)
            ->group('mn.id')
            ->order('mn.name ASC');

        $result = $db->fetchAll($select);
        return $result;
    }

    function getStoreByMarketNameStaff($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'   => 'st.id',
            'st_name' => 'st.name', 
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('ss' => 'store_staff')     , 's.id = ss.staff_id'          , array())
            ->join(array('st' => 'store')           , 'ss.store_id = st.id'         , array())
            ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
            ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
            ->where('s.code = ?', $params['staff_code'])
            ->where('mn.id = ?', $params['market_name_id'])
            ->order('st.id ASC');

        $result = $db->fetchAll($select);
        return $result;
    }

}