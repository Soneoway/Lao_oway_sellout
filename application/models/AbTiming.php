<?php
class Application_Model_AbTiming extends Zend_Db_Table_Abstract
{
    protected $_name = 'ab_timing';
    
    function fetchPagination($page, $limit, &$total, $params) {

    	$d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

    	$db = Zend_Registry::get('db');

    	if ( (isset($params['export']) and $params['export']) || 
    		(isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
            
            $get_01 = array(
                'at_id'     => 'at.id',
            );

        } else {
            $get_01['at_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS at.id');
        }

    	$get_02 = array(
    		'timing_date' 	=> 'at.created_at',
    		'sellout_b' 	=> new Zend_Db_Expr("COALESCE(SUM(CASE WHEN ats.brand_id = 1 THEN ats.sellout END),0)"),
    		'sellout_s' 	=> new Zend_Db_Expr("COALESCE(SUM(CASE WHEN ats.brand_id = 2 THEN ats.sellout END),0)"),
    		'sellout_hw' 	=> new Zend_Db_Expr("COALESCE(SUM(CASE WHEN ats.brand_id = 3 THEN ats.sellout END),0)"),
    		'sellout_others'=> new Zend_Db_Expr("COALESCE(SUM(CASE WHEN ats.brand_id = 4 THEN ats.sellout END),0)"),
    		'staff_code' 	=> 's.code',
    		'staff_name' 	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
    		'staff_group' 	=> 'g.name',
    		'area_name' 	=> 'a.name',
    		'st_id' 		=> 'st.id',
    		'st_name' 		=> 'st.name',
    	);

    	$get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('at' => $this->_name), $get)
            ->join(array('ats'=> 'ab_timing_sale')	, 'at.id = ats.ab_timing_id'	, array())
        	->join(array('s'  => 'staff')			, 'at.staff_id = s.id'			, array())
        	->join(array('g'  => 'group')			, 's.group_id = g.id'			, array())
        	->join(array('st' => 'store')			, 'at.store = st.id'			, array())
        	->join(array('rm' => 'regional_market')	, 'st.regional_market = rm.id'	, array())
        	->join(array('a'  => 'area')			, 'rm.area_id = a.id'			, array())
        	->group(array('st.id','s.id'))
        	->order('at.created_at DESC');

/*
        if ( isset($params['export']) and $params['export'] ) {
        	$select->joinLeft(array('rm2' => 'regional_market'), 's.regional_market = rm2.id', array());
        	$select->joinLeft(array('a2' => 'area'), 'rm2.area_id = a2.id', array('staff_area' => 'a2.name'));
        	$select->joinLeft(array('goo' => WAREHOUSE_DB.'.good'), 'i.good_id = goo.id', array('model' => 'goo.name'));
        	$select->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array('color' => 'gc.name'));
            $select->joinLeft(array('ts' => 'timing_sale'), 'i.imei_sn = ts.imei', array('timing_date' => 'ts.time_add'));
        }	
*/
        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id LIKE ?', '%'.$params['store_id'].'%');
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
        } 

        if ( isset($params['store_code']) && $params['store_code'] ) {
            $select->where('s.code LIKE ?', '%'.$params['store_code'].'%');
        } 

        if ( isset($params['staff_name']) && $params['staff_name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
        } 

        if ( isset($params['from']) && $params['from'] ) {
            $select->where('at.created_at >= ?', $from." 00:00:00");
        } 

        if ( isset($params['to']) && $params['to'] ) {
            $select->where('at.created_at <= ?', $to." 23:59:59");
        } 

        // Add Brand List
        if (isset($params['brand_id']) && $params['brand_id']) {
            if (is_array($params['brand_id']) && count($params['brand_id']))
                $select->where('ats.brand_id IN (?)', $params['brand_id']);
            elseif (is_numeric($params['brand_id']))
                $select->where('ats.brand_id = ?', intval($params['brand_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('st.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('st.regional_market = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter District
        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $select->where('st.district IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $select->where('st.district = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Permission for Sale
        if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
        	$select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());

			$log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
				" AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1);
			$select->where($log_where);
        }

        // Permission for Sale Leader
        if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
            $select->joinRight(array('ssl' => 'store_leader'), 'ssl.store_id = st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']);
            $select->where($log_where);
        }

        // Permission for PC 
        if (isset($params['pc_id']) && intval($params['pc_id']) > 0) {
        	$select->where('s.id = ?', $params['pc_id']);
        }

/*
        if (isset($params['get_total_count']) && $params['get_total_count'] == 0) { 
        	$select_p = $db->select()
                ->from(array('pa' => $select), array(
                	'cnt_finish' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 1 THEN pa.ti_id END )"),
                	'cnt_reject' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 2 THEN pa.ti_id END )"),
                	'cnt_wait' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 0 THEN pa.ti_id END )"),
                	'cnt_ip' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 3 THEN pa.ti_id END )"),
                ));
            //$select_p->group('pa.status_id');
            //echo $select_p; die;
            return $db->fetchRow($select_p);
        }
*/
        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }


}