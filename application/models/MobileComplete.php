<?php

class Application_Model_MobileComplete extends Zend_Db_Table_Abstract {

	protected $_name = 'mobile_complete';

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

            'mc_id'		=> new Zend_Db_Expr('SQL_CALC_FOUND_ROWS mc.id'),

            'area_id'	=> 'a.id',
            'area_name' => 'a.name',
            'st_id'		=> 'st.id',
            'st_name'	=> 'st.name',
            'st_type'	=> 'o.org_name',

            'staff_id'		=> 's.id',
            'staff_code'	=> 's.code',
            'staff_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'	=> 'gr.name',

            'imei'			=> 'ts.imei', 
            'product_code'	=> 'g.name',
            'product_name'	=> 'g.desc',
            'color_name'	=> 'gc.name',
            'price'			=> 'gkl.price',
            'timing_date'	=> 't.created_at',

            'imei_turn'		=> 'mc.imei_old',
            'brand_name'	=> 'mb.name',
            'model_name'	=> 'mm.name',
            'rom_name'		=> 'mrl.name',
            'eva_grade'		=> 'mc.grade',
            'eva_value'		=> 'mc.value',
            'eva_total'		=> 'mc.total',
            'mc_status'     => new Zend_Db_Expr(
                                "(  CASE 
                                        WHEN mc.status = 0 THEN 'Unknown' 
                                        WHEN mc.status = 1 THEN 'Waiting' 
                                        WHEN mc.status = 2 THEN 'Sent' 
                                        WHEN mc.status = 3 THEN 'Error' 
                                        WHEN mc.status = 4 THEN 'Not Picked Up' 
                                        ELSE '-'
                                    END)
                                "),
        );

        $select = $db->select()
            ->from(array('mc' => 'mobile_complete'), $get)
            ->join(array('mm' => 'mobile_model')	, 'mc.model_id = mm.id'			, array())
            ->join(array('mb' => 'mobile_brand')	, 'mm.brand_id = mb.id'			, array())
            ->join(array('mr' => 'mobile_roms')		, 'mc.rom_id = mr.id'			, array())
            ->join(array('mrl'=> 'mobile_rom_list')	, 'mr.rom_id = mrl.id'			, array())

            ->join(array('ts' => 'timing_sale')		, 'mc.imei = ts.imei'			, array())
            ->join(array('t'  => 'timing')			, 'ts.timing_id = t.id'			, array())
            ->join(array('st' => 'store')			, 't.store = st.id'				, array())
            ->join(array('o'  => 'org')				, 'st.org_dealer = o.org_id'	, array())
            ->join(array('rm' => 'regional_market')	, 'st.regional_market = rm.id'	, array())
            ->join(array('a'  => 'area')			, 'rm.area_id = a.id'			, array())
			->join(array('s'  => 'staff')			, 't.staff_id = s.id'			, array())
            ->join(array('gr' => 'group')			, 's.group_id = gr.id'			, array())

            ->join(array('g'  => WAREHOUSE_DB.'.good')		, 'ts.product_id = g.id', array())
            ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'ts.model_id = gc.id'	, array())

            ->joinLeft(array('gkl' => 'good_kpi_log'), 
                "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                ", array())
            ->where('t.created_at >= ?', $from." 00:00:00")
            ->where('t.created_at <= ?', $to." 23:59:59")
            ->order('t.created_at DESC');

        // Add Filter Store ID 
        if (isset($params['store_id']) and $params['store_id'])
            $select->where('st.id = ?', $params['store_id']);

        // Add Filter Store Name 
        if (isset($params['store_name']) and $params['store_name'])
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');

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

        // Add Filter Mobile Brand
        if (isset($params['brand_id']) && $params['brand_id']) {
            if (is_array($params['brand_id']) && count($params['brand_id']))
                $select->where('mb.id IN (?)', $params['brand_id']);
            elseif (is_numeric($params['brand_id']))
                $select->where('mb.id = ?', intval($params['brand_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Mobile Model
        if (isset($params['model_id']) && $params['model_id']) {
            if (is_array($params['model_id']) && count($params['model_id']))
                $select->where('mm.id IN (?)', $params['model_id']);
            elseif (is_numeric($params['model_id']))
                $select->where('mm.id = ?', intval($params['model_id']));
            else
                $select->where('1=0', 1);
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

        // check Permission Admin Brandshop
        if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
            $select->where('(st.org_dealer = ?', 18);
            $select->orWhere('o.store_type_id = ?)', 3);
        }

        if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
            $select->join(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());
            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
                        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3);
            $select->where($log_where);
        }

        if ($limit)
            $select->limitPage($page, $limit);

        // echo $select; die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;

    }

    function getImeiCondition($params) {

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
            'mc_id'		=> 'mc.id',

            'area_id'	=> 'a.id',
            'area_name' => 'a.name',
            'st_id'		=> 'st.id',
            'st_name'	=> 'st.name',
            'st_type'	=> 'o.org_name',
/*
            'staff_id'		=> 's.id',
            'staff_code'	=> 's.code',
            'staff_name'	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'	=> 'gr.name',

            'imei'			=> 'ts.imei', 
            'product_code'	=> 'g.name',
            'product_name'	=> 'g.desc',
            'color_name'	=> 'gc.name',
            'price'			=> 'gkl.price',
            'timing_date'	=> 't.created_at',
*/
            'imei_turn'		=> 'mc.imei_old',
            'brand_name'	=> 'mb.name',
            'model_name'	=> 'mm.name',
            'rom_name'		=> 'mrl.name',

            'can_turn_on' 	=> new Zend_Db_Expr("(CASE WHEN mc.turnon_id = 1 THEN 'Yes' ELSE 'No' END)"),
            'can_logout' 	=> new Zend_Db_Expr("(CASE WHEN mc.logout_id = 1 THEN 'Yes' ELSE 'No' END)"),

            's_tytpe'		=> 'mco.name',
            's_no'			=> new Zend_Db_Expr(
            					"	(CASE 
            							WHEN mc.scratch_number = 1 THEN '< 3' 
            							WHEN mc.scratch_number = 2 THEN '>=3' 
										ELSE '-' 
            						END)
            					"),
            's_size'		=> new Zend_Db_Expr(
            					"	(CASE 
            							WHEN mc.scratch_size = 1 THEN '< 3 cm' 
            							WHEN mc.scratch_size = 2 THEN '>=3 cm' 
										ELSE '-' 
            						END)
            					"),

            'problem_info' 	=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT mp.name)"),
            'acc_info' 		=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT ma.name)"),

            'eva_grade'		=> 'mc.grade',
            'eva_value'		=> 'mc.value',
            'oppo_support'	=> 'mc.oppo_support',
            'shop_support'	=> 'mc.po_support',
            'eva_total'		=> 'mc.total',

            'p_grade'       => new Zend_Db_Expr("COALESCE(mc.partner_grade, '')"),
            'p_price'       => 'mc.partner_price',
            'p_date'         => new Zend_Db_Expr("COALESCE(mc.partner_check_date, '')"),
        );

        $select = $db->select()
            ->from(array('mc' => 'mobile_complete'), $get)
            ->join(array('mm' => 'mobile_model')	, 'mc.model_id = mm.id'			, array())
            ->join(array('mb' => 'mobile_brand')	, 'mm.brand_id = mb.id'			, array())
            ->join(array('mr' => 'mobile_roms')		, 'mc.rom_id = mr.id'			, array())
            ->join(array('mrl'=> 'mobile_rom_list')	, 'mr.rom_id = mrl.id'			, array())

            ->join(array('mco'=> 'mobile_condition'), 'mc.condition_id = mco.id'	, array())
            ->join(array('mcb'=> 'mobile_country_buy'),'mc.buy_id = mcb.id'			, array())

            ->join(array('mgp'=> 'mobile_group_problem'),'mc.id = mgp.complete_id'	, array())
            ->join(array('mp' => 'mobile_problem')	, 'mgp.problem_id = mp.id'		, array())

            ->join(array('mga'=> 'mobile_group_accessory'),'mc.id = mga.complete_id', array())
            ->join(array('ma' => 'mobile_accessory'), 'mga.accessory_id = ma.id'	, array())

            ->join(array('ts' => 'timing_sale')		, 'mc.imei = ts.imei'			, array())
            ->join(array('t'  => 'timing')			, 'ts.timing_id = t.id'			, array())
            ->join(array('st' => 'store')			, 't.store = st.id'				, array())
            ->join(array('o'  => 'org')				, 'st.org_dealer = o.org_id'	, array())
            ->join(array('rm' => 'regional_market')	, 'st.regional_market = rm.id'	, array())
            ->join(array('a'  => 'area')			, 'rm.area_id = a.id'			, array())
/*
			->join(array('s'  => 'staff')			, 't.staff_id = s.id'			, array())
            ->join(array('gr' => 'group')			, 's.group_id = gr.id'			, array())

            ->join(array('g'  => WAREHOUSE_DB.'.good')		, 'ts.product_id = g.id', array())
            ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'ts.model_id = gc.id'	, array())

            ->joinLeft(array('gkl' => 'good_kpi_log'), 
                "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                ", array())
*/
            ->where('t.created_at >= ?', $from." 00:00:00")
            ->where('t.created_at <= ?', $to." 23:59:59")
            ->group('mc.id')
            ->order('t.created_at DESC');

        // Add Filter Store ID 
        if (isset($params['store_id']) and $params['store_id'])
            $select->where('st.id = ?', $params['store_id']);

        // Add Filter Store Name 
        if (isset($params['store_name']) and $params['store_name'])
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');

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

        // Add Filter Mobile Brand
        if (isset($params['brand_id']) && $params['brand_id']) {
            if (is_array($params['brand_id']) && count($params['brand_id']))
                $select->where('mb.id IN (?)', $params['brand_id']);
            elseif (is_numeric($params['brand_id']))
                $select->where('mb.id = ?', intval($params['brand_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Mobile Model
        if (isset($params['model_id']) && $params['model_id']) {
            if (is_array($params['model_id']) && count($params['model_id']))
                $select->where('mm.id IN (?)', $params['model_id']);
            elseif (is_numeric($params['model_id']))
                $select->where('mm.id = ?', intval($params['model_id']));
            else
                $select->where('1=0', 1);
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

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}
