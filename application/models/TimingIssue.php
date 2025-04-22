<?php
class Application_Model_TimingIssue extends Zend_Db_Table_Abstract
{
    protected $_name = 'timing_issue';

    function fetchPagination($page, $limit, &$total, $params) {

    	$d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

    	$db = Zend_Registry::get('db');

    	if ( (isset($params['export']) and $params['export']) || (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
            
            $get_01 = array(
                'ti_id'     => 'ti.id',
                'remark'    => 'ti.remark',
            );

        } else {
            $get_01['ti_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ti.id');
        }

    	$get_02 = array(
    		'request_timing_date' => 'ti.timing_date',
    		'staff_code' 	=> 's.code',
    		'staff_name' 	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
    		'staff_group' 	=> 'g.name',
    		'area_name' 	=> 'a.name',
    		'st_id' 		=> 'st.id',
    		'st_name' 		=> 'st.name',
    		'imei' 			=> 'ti.imei',
    		'old_data'		=> 'i.old_data',
    		'issue_type' 	=> 'ti.issue_type',
    		'status_id' 	=> 'ti.status',
    	);

    	$get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('ti' => $this->_name), $get)
        	->join(array('s'  => 'staff'), 'ti.staff_id = s.id'	, array())
        	->join(array('g'  => 'group'), 's.group_id = g.id'	, array())
        	->joinLeft(array('st' => 'store'), 'ti.store_id = st.id', array())
        	->joinLeft(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
        	->joinLeft(array('a'  => 'area') ,'rm.area_id = a.id'	, array())
        	->joinLeft(array('i'  => WAREHOUSE_DB.'.imei'), 'ti.imei = i.imei_sn', array('activated_date' => 'i.activated_date'))
        	->order('ti.timing_date DESC');

        if ( isset($params['export']) and $params['export'] ) {
        	$select->joinLeft(array('rm2' => 'regional_market'), 's.regional_market = rm2.id', array());
        	$select->joinLeft(array('a2' => 'area'), 'rm2.area_id = a2.id', array('staff_area' => 'a2.name'));
        	$select->joinLeft(array('goo' => WAREHOUSE_DB.'.good'), 'i.good_id = goo.id', array('model' => 'goo.name'));
        	$select->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array('color' => 'gc.name'));
            $select->joinLeft(array('ts' => 'timing_sale'), 'i.imei_sn = ts.imei', array('timing_date' => 'ts.time_add'));
        }	

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

        if ( isset($params['imei']) && $params['imei'] ) {
            $select->where('ti.imei IN (?)', $params['imei']);
        } 

        if ( isset($params['from']) && $params['from'] ) {
            $select->where('ti.timing_date >= ?', $from." 00:00:00");
        } 

        if ( isset($params['to']) && $params['to'] ) {
            $select->where('ti.timing_date <= ?', $to." 23:59:59");
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

        // Add Filter Issue Type
        if (isset($params['issue_type_id']) && $params['issue_type_id']) {
            if (is_array($params['issue_type_id']) && count($params['issue_type_id']))
                $select->where('ti.issue_type IN (?)', $params['issue_type_id']);
            elseif (is_numeric($params['issue_type_id']))
                $select->where('ti.issue_type = ?', intval($params['issue_type_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Status
        if (isset($params['status_id']) && $params['status_id']) {
            if (is_array($params['status_id']) && count($params['status_id']))
                $select->where('ti.status IN (?)', $params['status_id']);
            elseif (is_numeric($params['status_id']))
                $select->where('ti.status = ?', intval($params['status_id']));
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

        // Permission for PCM
        if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
        	$select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());
        	
			$log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
				" AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2);
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

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }


    function getDetails($ti_id) {
    	$db = Zend_Registry::get('db');

    	$get = array(
    		'st_area'		=> 'a.name',
    		'st_id' 		=> 'st.id',
    		'st_name' 		=> 'st.name',
    		'st_type' 		=> 'o.org_name',
            'st_d_area'     => 'a2.name',
    		'st_d_id' 		=> 'd.id',
    		'st_d_name' 	=> 'd.title',
    		'st_d_type' 	=> 'd.rank',
    		'st_d_parent'	=> new Zend_Db_Expr("CONCAT('[', d2.id, '] ', d2.title)"),

    		'staff_code'	=> 's.code',
    		'staff_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
    		'staff_group' 	=> 'g.name',
    		'timing_daet' 	=> 'ti.timing_date',

    		'imei' 			=> 'ti.imei',
    		'model' 		=> 'goo.name',
    		'color'			=> 'gc.name',
    		'activated_date'=> 'i.activated_date',
    		'old_data'		=> 'i.old_data',
            'd_area'        => 'a3.name',
    		'd_id'			=> 'd3.id',
    		'd_name'		=> 'd3.title',
    		'd_type'		=> 'd3.rank',
    		'd_parent'		=> new Zend_Db_Expr("CONCAT('[', d4.id, '] ', d4.title)"),

    		'ti_id'			=> 'ti.id',
    		'issue_status'	=> 'ti.status',
    		'issue_type'	=> 'ti.issue_type',
    		'ti_remark'		=> 'ti.remark',
    		'updated_name'	=> new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
    		'updated_date'	=> 'ti.updated_at',

            'imei_type'     => new Zend_Db_Expr(
                                "(  CASE 
                                        WHEN i.type = 1 THEN 'Normal' 
                                        WHEN i.type = 2 THEN 'DEMO' 
                                        WHEN i.type = 5 THEN 'APK' 
                                        ELSE 'Bad'
                                    END
                                )"),

    	);

    	$select = $db->select()
            ->from(array('ti' => $this->_name), $get)
        	->joinLeft(array('s'  => 'staff'), 'ti.staff_id = s.id', array())
        	->joinLeft(array('g'  => 'group'), 's.group_id = g.id', array())
        	->joinLeft(array('st' => 'store'), 'ti.store_id = st.id', array())
        	->joinLeft(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
        	->joinLeft(array('a'  => 'area') ,'rm.area_id = a.id', array())
        	->joinLeft(array('o'  => 'org'),'st.org_dealer = o.org_id', array())

            // Distributor Chain 1 
        	->joinLeft(array('d'  => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id', array())
            ->joinLeft(array('rm2' => 'regional_market'), 'd.region = rm2.id', array())
            ->joinLeft(array('a2'  => 'area') ,'rm2.area_id = a2.id', array())
        	->joinLeft(array('d2' => WAREHOUSE_DB.'.distributor'), 'd.parent = d2.id', array())

        	->joinLeft(array('i'  => WAREHOUSE_DB.'.imei'), 'ti.imei = i.imei_sn', array())
        	->joinLeft(array('goo'=> WAREHOUSE_DB.'.good'), 'i.good_id = goo.id', array())
        	->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array())

            // Distributor By Imei
        	->joinLeft(array('d3' => WAREHOUSE_DB.'.distributor'), 'i.distributor_id = d3.id', array())
            ->joinLeft(array('rm3' => 'regional_market'), 'd3.region = rm3.id', array())
            ->joinLeft(array('a3'  => 'area') ,'rm3.area_id = a3.id', array())
        	->joinLeft(array('d4' => WAREHOUSE_DB.'.distributor'), 'd3.parent = d4.id', array())

        	->joinLeft(array('s2' => 'staff'), 'ti.updated_by = s2.id', array())
        	->where('ti.id = ?', $ti_id);

        $result = $db->fetchRow($select);
        return $result;
    }

}