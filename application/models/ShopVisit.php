<?php

class Application_Model_ShopVisit extends Zend_Db_Table_Abstract {

	protected $_name = 'shop_visit';

	function fetchPagination($page, $limit, &$total, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting('~E_ALL');

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get = array(

            'sv_id'		=> new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sv.id'),

            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_type'       => 'o.org_name',
            'market_name'   => 'mn.name',

            'staff_id'      => 's.id',
            'staff_code'	=> 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'	=> 'g.name',

            'sv_created_at' => 'sv.date_create',
            'sv_status'     => 'sv.status',  
            'sv_location'   => 'sv.location',
            'sv_remark'     => 'sv.remark',

            'sv_front_01'   => 'sv.img_left',
            'sv_front_02'   => 'sv.img_mid',
            'sv_front_03'   => 'sv.img_right',

            'sv_img_01'     => 'sv.img_table1',
            'sv_img_02'     => 'sv.img_table2',
            'sv_img_03'     => 'sv.img_table3',
            'sv_img_04'     => 'sv.img_table4',
            'sv_img_05'     => 'sv.img_table5',
            'sv_img_06'     => 'sv.img_table6',
        );

        $select = $db->select()
            ->from(array('sv' => 'shop_visit'), $get)
            ->join(array('st' => 'store')			, 'sv.store_id = st.id'         , array())
            ->join(array('o'  => 'org')				, 'st.org_dealer = o.org_id'	, array())
            ->join(array('rm' => 'regional_market')	, 'st.regional_market = rm.id'	, array())
            ->join(array('a'  => 'area')			, 'rm.area_id = a.id'			, array())
			->join(array('s'  => 'staff')			, 'sv.staff_id = s.id'			, array())
            ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
            ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
            ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
            ->where('sv.date_create >= ?', $from." 00:00:00")
            ->where('sv.date_create <= ?', $to." 23:59:59")
            ->order('sv.date_create DESC');

        // Add Filter Store ID 
        if (isset($params['store_id']) and $params['store_id'])
            $select->where('st.id = ?', $params['store_id']);

        // Add Filter Store Name 
        if (isset($params['store_name']) and $params['store_name'])
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');

        // Add Filter Staff Code 
        if (isset($params['staff_code']) and $params['staff_code'])
            $select->where('s.code = ?', $params['staff_code']);

        // Add Filter Staff Name 
        if (isset($params['staff_name']) and $params['staff_name'])
            $select->where('s.name LIKE ?', '%'.$params['staff_name'].'%');

        // Add Filter Store Type 
        if (isset($params['org']) && $params['org']) {
            if (is_array($params['org']) && count($params['org']))
                $select->where('st.org_dealer IN (?)', $params['org']);
            elseif (is_numeric($params['org']))
                $select->where('st.org_dealer = ?', intval($params['org']));
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

        // Add Filter Market Name
        if (isset($params['market_name']) and $params['market_name']) {
            if (is_array($params['market_name']) && count($params['market_name'])) { 
                $select->where('mn.id IN (?)', $params['market_name']);
            } elseif (is_numeric($params['market_name'])) {
                $select->where('mn.id = ?', intval($params['market_name']));
            } else {
                $select->where('1=0', 1);
            }
        }

        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }


        if ($limit)
            $select->limitPage($page, $limit);

        // echo $select; // die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;

    }

    function getVisitShopStock($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting('~E_ALL');

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];


        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',
            'market_name' => 'mn.name',

            'cat_name'  => 'ga.name',
            'good_code' => 'g.name',
            'good_name' => 'g.desc',
            'color_name'=> 'gc.name',

            'normal'    => 'si.count_normal',
            'demo'      => new Zend_Db_Expr("(CASE WHEN si.count_demo = 1 THEN 'Yes' ELSE 'No' END)"),
            'apk'       => new Zend_Db_Expr("(CASE WHEN si.count_apk = 1 THEN 'Yes' ELSE 'No' END)"),
        );

        $select = $db->select()
            ->from(array('sv' => 'shop_visit'), $get)
            ->join(array('st' => 'store')           , 'sv.store_id = st.id'         , array())
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('s'  => 'staff')           , 'sv.staff_id = s.id'          , array())
            ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
            ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
            ->join(array('si' => 'shop_visit_item') , 'sv.id = si.shop_visit_id'    , array())
            ->join(array('ga' => WAREHOUSE_DB.'.good_category') , 'si.category_id = ga.id'  , array())
            ->join(array('g'  => WAREHOUSE_DB.'.good')          , 'si.product_id = g.id'    , array())
            ->join(array('gc' => WAREHOUSE_DB.'.good_color')    , 'si.color_id = gc.id'     , array())
            ->where('sv.status IN (?)', array(1,2))
            ->where('sv.date_create >= ?', $to." 00:00:00")
            ->where('sv.date_create <= ?', $to." 23:59:59")
            ->order(array('a.name ASC','st.id ASC','g.name ASC','gc.name ASC'));

        if (isset($params['flag_demo']) and $params['flag_demo'] == 1) {
            $select->group(array('st.id','g.id'));
        }

        // Add Filter Store ID 
        if (isset($params['store_id']) and $params['store_id'])
            $select->where('st.id = ?', $params['store_id']);

        // Add Filter Store Name 
        if (isset($params['store_name']) and $params['store_name'])
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');

        // Add Filter Staff Code 
        if (isset($params['staff_code']) and $params['staff_code'])
            $select->where('s.code = ?', $params['staff_code']);

        // Add Filter Staff Name 
        if (isset($params['staff_name']) and $params['staff_name'])
            $select->where('s.name LIKE ?', '%'.$params['staff_name'].'%');

        // Add Filter Store Type 
        if (isset($params['org']) && $params['org']) {
            if (is_array($params['org']) && count($params['org']))
                $select->where('st.org_dealer IN (?)', $params['org']);
            elseif (is_numeric($params['org']))
                $select->where('st.org_dealer = ?', intval($params['org']));
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

        // Add Filter Market Name
        if (isset($params['market_name']) and $params['market_name']) {
            if (is_array($params['market_name']) && count($params['market_name'])) { 
                $select->where('mn.id IN (?)', $params['market_name']);
            } elseif (is_numeric($params['market_name'])) {
                $select->where('mn.id = ?', intval($params['market_name']));
            } else {
                $select->where('1=0', 1);
            }
        }

        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // echo $get; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    
}
