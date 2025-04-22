<?php
class Application_Model_StoreControl extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_control';
    
    function store_control_list($params) {
    	$db = Zend_Registry::get('db');

        $get = array(
        	'sc_id'			=> 'sc.id',
        	'sc_name' 		=> 'sc.name',
        	'staff_id' 		=> 's.id',
        	'staff_code'	=> 's.code',
        	'staff_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        	'staff_group'	=> 'g.name', 
        );

        $select = $db->select()->from(array('sc' => 'store_control'), $get)
            ->joinLeft(array('s'  => 'staff')			    , 'sc.staff_id = s.id'			, array())
            ->joinLeft(array('g'  => 'group')			    , 's.group_id = g.id'			, array())
            ->joinLeft(array('scm'=> 'store_control_map')   , 'sc.id = scm.store_control_id', array())
            ->joinLeft(array('st' => 'store')               , 'scm.store_id = st.id'        , array())
            ->joinLeft(array('rm' => 'regional_market')	    , 'st.regional_market = rm.id'  , array())
            ->joinLeft(array('a'  => 'area')                , 'rm.area_id = a.id'           , array())
            ->group('sc.id')
            ->order('sc.name ASC');


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

    function cnt_store_by_area($store_control_id) {
    	$db = Zend_Registry::get('db');

        $get = array(
        	'area_name' => 'a.name',
        	'cnt_store'	=> new Zend_Db_Expr("COUNT(st.id)"),
        );

        $select = $db->select()->from(array('scm' => 'store_control_map'), $get)
            ->join(array('st' => 'store')			, 'scm.store_id = st.id'        , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->where('scm.store_control_id = ?', $store_control_id)
            ->group('a.id')
            ->order('a.name ASC');

       	//echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function store_control_binding($store_control_id) {
        $db = Zend_Registry::get('db');

        $get = array(
            'sc_id'             => 'sc.id',
            'sc_name'           => 'sc.name',
            'area_name'         => 'a.name',
            'staff_id'          => 's.id',
            'staff_name'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_email'       => 's.email',
            'staff_province'    => 's_rm.name',
        );

        $select = $db->select()->from(array('sc' => 'store_control'), $get)
            ->joinLeft(array('scm'  => 'store_control_map') , 'sc.id = scm.store_control_id'    , array())
            ->joinLeft(array('st'   => 'store')             , 'scm.store_id = st.id'            , array())
            ->joinLeft(array('rm'   => 'regional_market')   , 'st.regional_market = rm.id'      , array())
            ->joinLeft(array('a'    => 'area')              , 'rm.area_id = a.id'               , array())
            ->joinLeft(array('s'    => 'staff')             , 'sc.staff_id = s.id'              , array())
            ->joinLeft(array('s_rm' => 'regional_market')   , 's.regional_market = s_rm.id'     , array())
            ->where('sc.id = ?', $store_control_id)
            ->group('a.id')
            ->order('a.name ASC');

        // echo $select; die;
        $data = $db->fetchAll($select);

        $result = array();
        if (!empty($data)) {
            $result['area_name'] = "";

            for($i=0;$i<count($data);$i++) {
                $result['sc_id']            = $data[$i]['sc_id'];
                $result['sc_name']          = $data[$i]['sc_name'];

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

}