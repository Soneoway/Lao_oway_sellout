<?php
class Application_Model_AreaControl extends Zend_Db_Table_Abstract
{
    protected $_name = 'area_control';

    function APDS_List($params){
        $db = Zend_Registry::get('db');

        $get = array(
        	'area_name' 		=> 'a.name',
        	'province_name' 	=> 'rm.name',
        	'district_name' 	=> 'rm2.name',
        	'sub_area_id'       => 'sa.id',
        	'sub_area_name'     => 'sa.name',
        	'staff_code' 		=> 's.code',
        	'staff_name'		=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
        );

        $select = $db->select()->from(array('a' => 'area'), $get)
            ->join(array('rm'  => 'regional_market'), 'a.id = rm.area_id'		, array())
            ->join(array('rm2' => 'regional_market'), 'rm.id = rm2.parent'		, array())
            ->join(array('sd'  => 'sub_district')	, 'rm2.id = sd.district'	, array())
            ->join(array('ac' => 'area_control')	, 'sd.id = ac.sub_district'	, array())
            ->join(array('sa' => 'sub_area')        , 'ac.sub_area_id = sa.id'  , array())
    		->joinLeft(array('s' => 'staff')			, 'sa.staff_id = s.id'		, array())
            ->where('sa.status = ?',1)
            ->group('sa.id')
            ->order(array('a.name ASC', 'rm.name ASC', 'rm2.name ASC', 'sa.name ASC'));

/*
        // Add Filter Store ID, Store Name, Staff Code, Staff Name [By Store Staff]
    	if ( (isset($params['store_id']) && $params['store_id']) || (isset($params['store_name']) && $params['store_name']) || (isset($params['staff_code']) && $params['staff_code']) || (isset($params['staff_name']) && $params['staff_name']) ) {
    		
    		$select->join(array('st' => 'store'), 'sd.id = st.sub_district', array());

    		if ( isset($params['store_id']) && $params['store_id'] ) {
				$select->where('st.id LIKE ?', "%".$params['store_id']."%");
			}

			if ( isset($params['store_name']) && $params['store_name'] ) {
				$select->where('st.name LIKE ?', "%".$params['store_name']."%");
			}

			if ( (isset($params['staff_code']) && $params['staff_code']) || (isset($params['staff_name']) && $params['staff_name']) ) {
				$select->join(array('ss' => 'store_staff'), 'st.id = ss.store_id', array());
				$select->join(array('s' => 'staff'), 'ss.staff_id = s.id', array());

				if ( isset($params['staff_code']) && $params['staff_code'] ) {
					$select->where('s.code LIKE ?', "%".$params['staff_code']."%");
				}

				if ( isset($params['staff_name']) && $params['staff_name'] ) {
					$select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%".$params['staff_name']."%");
				}

			}

    	}
*/
    	// Add Filter Store ID, Store Name
    	if ( (isset($params['store_id']) && $params['store_id']) || (isset($params['store_name']) && $params['store_name']) ) {
    		
    		$select->join(array('st' => 'store'), 'sd.id = st.sub_district', array());

    		if ( isset($params['store_id']) && $params['store_id'] ) {
				$select->where('st.id = ?', $params['store_id']);
			}

			if ( isset($params['store_name']) && $params['store_name'] ) {
				$select->where('st.name LIKE ?', "%".$params['store_name']."%");
			}
		}

    	// Add Filter Staff Code, Staff Name
		if ( (isset($params['staff_code']) && $params['staff_code']) || (isset($params['staff_name']) && $params['staff_name']) ) {
    		
    		

    		if ( isset($params['staff_code']) && $params['staff_code'] ) {
				$select->where('s.code LIKE ?', "%".$params['staff_code']."%");
			}

			if ( isset($params['staff_name']) && $params['staff_name'] ) {
				$select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%".$params['staff_name']."%");
			}
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

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('rm.id IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('rm.id = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter District
        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $select->where('rm2.id IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $select->where('rm2.id = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Sub District
        if (isset($params['sub_district']) && $params['sub_district']) {
            if (is_array($params['sub_district']) && count($params['sub_district']))
                $select->where('sd.id IN (?)', $params['sub_district']);
            elseif (is_numeric($params['sub_district']))
                $select->where('sd.id = ?', intval($params['sub_district']));
            else
                $select->where('1=0', 1);
        }

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm2.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function store_details($sub_area_id){ 
    	$db = Zend_Registry::get('db');

        $sub_select =  $db->select()->from(array('sa' => 'sub_area'), array('sub_area_id' => 'ac.sub_area_id'))
            ->join(array('ac' => 'area_control'), 'sa.id = ac.sub_area_id', array())
            ->where('sa.id = ?', $sub_area_id);

        $get = array(
        	'cnt_store_all' 	=> new Zend_Db_Expr("COUNT(st.id)"), 
        	'cnt_store_no_sale' => new Zend_Db_Expr("COUNT( CASE WHEN s.id IS NULL THEN st.id END )"), 
        	'cnt_sale' 			=> new Zend_Db_Expr("COUNT( CASE WHEN s.group_id = 9 THEN st.id END )"), 
        	'cnt_asm' 			=> new Zend_Db_Expr("COUNT( CASE WHEN s.group_id = 5 THEN st.id END )"), 
        	'cnt_asm_stand_by' 	=> new Zend_Db_Expr("COUNT( CASE WHEN s.group_id = 16 THEN st.id END )"), 
        	'cnt_am' 			=> new Zend_Db_Expr("COUNT( CASE WHEN s.group_id = 27 THEN st.id END )"), 
        	'cnt_rm' 			=> new Zend_Db_Expr("COUNT( CASE WHEN s.group_id = 28 THEN st.id END )") 
        );

        $select = $db->select()->from(array('st' => 'store'), $get)
            ->joinLeft(array('ss'  => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s' => 'staff'), 'ss.staff_id = s.id AND s.off_date IS NULL', array())

            ->where('st.agency IN (?)', $sub_select);

       	//echo $select; die;
        $result = $db->fetchRow($select);
        return $result;

    }

    function store_list($sub_area_id) {
    	$db = Zend_Registry::get('db');

    	$leader_list = implode(",", array(RM_ID,ASM_ID,ASMSTANDBY_ID,AM_ID));

        // $sub_select =  $db->select()->from(array('sa' => 'sub_area'), array('sub_district' => 'ac.sub_district'))
        //     ->join(array('ac' => 'area_control'), 'sa.id = ac.sub_area_id', array())
        //     ->where('sa.id = ?', $sub_area_id);

        $get = array(
        	'st_id' 	=> 'st.id',
            'st_code'   => 'st.store_code',
        	'st_name' 	=> 'st.name',
        	'st_type'	=> 'o.org_name', 
        	'st_status' => 'st.del',
        	'asm_id'	=> 's.id',
        	'asm_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        	'asm_group'	=> 'g.name'
        );

        $select = $db->select()->from(array('st' => 'store'), $get)
            ->join(array('o' => 'org'), 'st.org_dealer = o.org_id', array())
            ->joinLeft(array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s' => 'staff'), "ss.staff_id = s.id AND s.group_id IN (".$leader_list.") ", array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id', array())
            ->where('st.agency IN (?)', $sub_area_id)
            ->order(array('st_status ASC', 'st.id ASC'));

       	//echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function area_binding($sub_area_id) {
    	$db = Zend_Registry::get('db');

        $get = array(
        	'area_name' 		=> 'a.name',
        	'province_name' 	=> 'rm.name',
        	'district_name'		=> 'rm2.name', 
        	'sub_district_name' => 'sd.name',
        	'staff_id'			=> 's.id',
        	'staff_name'		=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        	'staff_email'		=> 's.email',
        	'staff_province'	=> 's_rm.name',
            'sub_area_id'       => 'sa.id',
            'sub_area_name'     => 'sa.name',
            'com_rate'          => 'sa.com_rate',
        );

        $select = $db->select()->from(array('a' => 'area'), $get)
            ->join(array('rm'  => 'regional_market'), 'a.id = rm.area_id'		, array())
            ->join(array('rm2' => 'regional_market'), 'rm.id = rm2.parent'		, array())
            ->join(array('sd'  => 'sub_district')	, 'rm2.id = sd.district'	, array())
            ->join(array('ac'  => 'area_control')   , 'sd.id = ac.sub_district'	, array())
            ->join(array('sa'  => 'sub_area')       , 'ac.sub_area_id = sa.id'  , array())
    		->joinLeft(array('s' => 'staff')		, 'sa.staff_id = s.id'		, array())
    		->joinLeft(array('s_rm' => 'regional_market'), 's.regional_market = s_rm.id', array())
    		->where('sa.id = ?', $sub_area_id);

       	//echo $select; die;
        $data = $db->fetchAll($select);

        $result = array();
        if (!empty($data)) {
            $result['sub_district_name'] = "";

            for($i=0;$i<count($data);$i++) {
                $result['area_name']         = $data[$i]['area_name'];
                $result['province_name']     = $data[$i]['province_name'];
                $result['district_name']     = $data[$i]['district_name'];

                if ($result['sub_district_name'] == "") { $result['sub_district_name'] = $data[$i]['sub_district_name']; } 
                else { $result['sub_district_name'] = $result['sub_district_name']." / ".$data[$i]['sub_district_name']; }
                
                $result['staff_id']          = $data[$i]['staff_id'];
                $result['staff_name']        = $data[$i]['staff_name'];
                $result['staff_email']       = $data[$i]['staff_email'];
                $result['staff_province']    = $data[$i]['staff_province'];

                $result['sub_area_id']       = $data[$i]['sub_area_id'];
                $result['sub_area_name']     = $data[$i]['sub_area_name'];
                $result['com_rate']          = $data[$i]['com_rate'];
            }

        }

        return $result;

    }

// sale Only
    function getsaleAreaPromission($staff_id){

        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('sa' => 'sub_area'),array('a.id','a.name'))
            ->joinLeft(array('ac' => 'area_control'),'sa.id = ac.sub_area_id',array())
            ->joinLeft(array('sd' => 'sub_district'),'ac.sub_district = sd.id',array())
            ->joinLeft(array('rm' => 'regional_market'),'sd.district = rm.id',array())
            ->joinLeft(array('rm2' => 'regional_market'),'rm.parent = rm2.id',array())
            ->joinLeft(array('a' => 'area'),'rm2.area_id = a.id',array())
            ->where('sa.staff_id =?',$staff_id)
            ->group('a.id');
          

        $result = $db->fetchAll($select);

        return $result;

    }

}