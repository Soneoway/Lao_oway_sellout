<?php
class Application_Model_BsArea extends Zend_Db_Table_Abstract
{
    protected $_name = 'bs_area';
    
    function bs_area_list($params) {
    	$db = Zend_Registry::get('db');

        $get = array(
        	'ba_id'			=> 'ba.id',
        	'ba_name' 		=> 'ba.name',
        	'staff_id' 		=> 's.id',
        	'staff_code'	=> 's.code',
        	'staff_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        	'staff_group'	=> 'g.name', 
        );

        $select = $db->select()->from(array('ba' => 'bs_area'), $get)
            ->joinLeft(array('s'  => 'staff')			, 'ba.staff_id = s.id'			, array())
            ->joinLeft(array('g'  => 'group')			, 's.group_id = g.id'			, array())
            ->joinLeft(array('bam'=> 'bs_area_map')		, 'ba.id = bam.bs_area_id'		, array())
            ->joinLeft(array('a'  => 'area')			, 'bam.area_id = a.id'			, array())
            ->joinLeft(array('rm' => 'regional_market')	, 'a.id = rm.area_id'			, array())
            ->joinLeft(array('st' => 'store')			, 'rm.id = st.regional_market'	, array())
            ->group('ba.id')
            ->order('ba.name ASC');


       	// Add Filter Store ID, Store Name, Staff Code, Staff Name
		if ( isset($params['store_id']) && $params['store_id'] ) {
			$select->where('st.id = ?', $params['store_id']);
		}

		if ( isset($params['store_name']) && $params['store_name'] ) {
			$select->where('st.name LIKE ?', "%".$params['store_name']."%");
		}

		if ( isset($params['staff_code']) && $params['staff_code'] ) {
			$select->where('s.code LIKE ?', "%".$params['staff_code']."%");
		}

		if ( isset($params['staff_name']) && $params['staff_name'] ) {
			$select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%".$params['staff_name']."%");
		}
		
    	// Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

       	//echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function cnt_store_by_area($bs_area_id) {
    	$db = Zend_Registry::get('db');

        $get = array(
        	'area_name' => 'a.name',
        	'cnt_store'	=> new Zend_Db_Expr("COUNT(DISTINCT st.id)"),
            'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $select = $db->select()->from(array('bam' => 'bs_area_map'), $get)
            ->join(array('a'  => 'area')			, 'bam.area_id = a.id'			, array())
            ->join(array('rm' => 'regional_market')	, 'a.id = rm.area_id'			, array())
            ->join(array('st' => 'store')			, 'rm.id = st.regional_market'	, array())
            ->join(array('o'  => 'org')				, 'st.org_dealer = o.org_id'	, array())
            ->joinLeft(array('t'  => 'timing')      , 
                "   st.id = t.store 
                    AND t.created_at >= '".date('Y-m-01 00:00:00')."' 
                    AND t.created_at <= '".date('Y-m-d 23:59:59')."' 
                ", array())
            ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
            ->where('bam.bs_area_id = ?', $bs_area_id)
            ->where('(o.store_type_id = ?', 3) 	// All BS Type
            ->orWhere('o.org_id = ?)', 18)		// BS Dealer
            // ->where('t.created_at >= ?', date('Y-m-01 00:00:00'))
            // ->where('t.created_at <= ?', date('Y-m-d 23:59:59'))
            ->group('a.id')
            ->order('a.name ASC');

       	// echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function bs_area_binding($bs_area_id) {
        $db = Zend_Registry::get('db');

        $get = array(
            'ba_id'             => 'ba.id',
            'ba_name'           => 'ba.name',
            'area_name'         => 'a.name',
            'staff_id'          => 's.id',
            'staff_name'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_email'       => 's.email',
            'staff_province'    => 's_rm.name',
        );

        $select = $db->select()->from(array('ba' => 'bs_area'), $get)
            ->joinLeft(array('bam'  => 'bs_area_map'), 'ba.id = bam.bs_area_id', array())
            ->joinLeft(array('a'    => 'area'), 'bam.area_id = a.id', array())
            ->joinLeft(array('s'    => 'staff'), 'ba.staff_id = s.id', array())
            ->joinLeft(array('s_rm' => 'regional_market'), 's.regional_market = s_rm.id', array())
            ->where('ba.id = ?', $bs_area_id)
            ->order('a.name ASC');

        // echo $select; die;
        $data = $db->fetchAll($select);

        $result = array();
        if (!empty($data)) {
            $result['area_name'] = "";

            for($i=0;$i<count($data);$i++) {
                $result['ba_id']            = $data[$i]['ba_id'];
                $result['ba_name']          = $data[$i]['ba_name'];

                if ($result['area_name'] == "") { $result['area_name'] = $data[$i]['area_name']; } 
                else { $result['area_name'] = $result['area_name']." / ".$data[$i]['area_name']; }
                
                $result['staff_id']         = $data[$i]['staff_id'];
                $result['staff_name']       = $data[$i]['staff_name'];
                $result['staff_email']      = $data[$i]['staff_email'];
                $result['staff_province']   = $data[$i]['staff_province'];

            }

        }

        return $result;

    }

    function store_list($bs_area_id) {
        $db = Zend_Registry::get('db');

        $sub_select =  $db->select()->from(array('bam' => 'bs_area_map'), array('rm_id' => 'rm.id'))
            ->join(array('rm' => 'regional_market'), 'bam.area_id = rm.area_id', array())
            ->where('bam.bs_area_id = ?', $bs_area_id);

        $get = array(
            'st_area'    => 'a.name',
            'st_id'      => 'st.id',
            'st_name'    => 'st.name',
            'st_type'    => 'o.org_name', 
            'st_status'  => 'st.del',
            'sale_id'    => 's.id',
            'sale_code'  => 's.code',
            'sale_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sale_group' => 'g.name',
            'st_sellout' => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $select = $db->select()->from(array('st' => 'store'), $get)
            ->join(array('o' => 'org'), 'st.org_dealer = o.org_id', array())
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
            ->joinLeft(array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s' => 'staff'), 'ss.staff_id = s.id', array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id', array())
            ->joinLeft(array('t' => 'timing'), 
                "   st.id = t.store 
                    AND t.created_at >= '".date('Y-m-01 00:00:00')."' 
                    AND t.created_at <= '".date('Y-m-d 23:59:59')."' 
                ", array())
            ->joinLeft(array('ts'=> 'timing_sale'), 't.id = ts.timing_id', array())
            ->where('st.regional_market IN (?)', $sub_select)
            ->where('(o.store_type_id = ?', 3)  // All BS Type
            ->orWhere('o.org_id = ?)', 18)      // BS Dealer
            // ->where('t.created_at >= ?', date('Y-m-01 00:00:00'))
            // ->where('t.created_at <= ?', date('Y-m-d 23:59:59'))
            ->group('st.id')
            ->order(array('st_status ASC', 'a.name ASC','st.id ASC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}