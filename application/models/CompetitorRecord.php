<?php
class Application_Model_CompetitorRecord extends Zend_Db_Table_Abstract
{
    protected $_name = 'competitor_record';

    function fetchPagination($page, $limit, &$total, $params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        if ( (isset($params['export']) and $params['export']) ) {

                // Get Selected Product
                $sub_get = array(
                    'brand_id'      => 'cb.id',
                    'brand_name'    => 'cb.name',
                    'product_id'    => 'cp.id',
                    'product_name'  => 'cp.name',
                );

                $sub_select = $db->select()
                    ->from(array('cb' => 'competitor_brand'), $sub_get)
                    ->join(array('cp' => 'competitor_product'), 'cb.id = cp.brand_id', array())
                    ->order(array('cb.name ASC','cp.position ASC'));

                // Add Filter Brand 
                if (isset($params['brand_id']) && $params['brand_id']) {
                    if (is_array($params['brand_id']) && count($params['brand_id']))
                        $sub_select->where('cp.brand_id IN (?)', $params['brand_id']);
                    elseif (is_numeric($params['brand_id']))
                        $sub_select->where('cp.brand_id = ?', intval($params['brand_id']));
                    else
                        $sub_select->where('1=0', 1);
                }

                // Add Filter Product 
                if (isset($params['product_id']) && $params['product_id']) {
                    if (is_array($params['product_id']) && count($params['product_id']))
                        $sub_select->where('cp.id IN (?)', $params['product_id']);
                    elseif (is_numeric($params['product_id']))
                        $sub_select->where('cp.id = ?', intval($params['product_id']));
                    else
                        $sub_select->where('1=0', 1);
                }

                $sub_result = $db->fetchAll($sub_select);
            
                for ($i=0;$i<count($sub_result);$i++) {
                    $get_01[ $sub_result[$i]['brand_name']." : ".$sub_result[$i]['product_name'] ] = new Zend_Db_Expr("SUM(CASE WHEN cp.brand_id = ".$sub_result[$i]['brand_id']." AND cp.id = ".$sub_result[$i]['product_id']." THEN cr.unit ELSE 0 END)");
                }

        } else {

            if ((isset($params['get_total_count']) and $params['get_total_count'] == 0)) {
                $get_01['cr_id'] = 'cr.id';
            } else {
                $get_01['cr_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cr.id');
            }

        }

        $get_02 = array(
            'timing_date'   => new Zend_Db_Expr("DATE(cr.created_at)"),
            'area_name'     => 'a.name',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'city_type'     => 'rm2.city_type',
            'channel_type'  => new Zend_Db_Expr(
                                "(
                                    CASE 
                                        WHEN o.org_id IN (12,19,5,20,3,26,10) THEN 'OPR' 
                                        WHEN o.store_type_id = 2 THEN 'IND' 
                                        ELSE stt.store_type_name 
                                    END
                                )"),

            // 'staff_code'    => 's.code',
            // 'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            // 'staff_group'   => 'g.name',
            'total_unit'    => new Zend_Db_Expr("SUM(cr.unit)"),
            'com_1' => new Zend_Db_Expr("SUM(CASE WHEN cp.brand_id = 1 THEN cr.unit ELSE 0 END)"),  // Huawei
            'com_2' => new Zend_Db_Expr("SUM(CASE WHEN cp.brand_id = 2 THEN cr.unit ELSE 0 END)"),  // VIVO
            'com_3' => new Zend_Db_Expr("SUM(CASE WHEN cp.brand_id = 3 THEN cr.unit ELSE 0 END)"),  // Samsung
        );

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('cr' => $this->_name), $get)
            ->join(array('cp' => 'competitor_product')  , 'cr.product_id = cp.id'               , array())
            // ->joinLeft(array('s'  => 'staff')        , 'cr.staff_id = s.id'                  , array())
            // ->joinLeft(array('g'  => 'group')        , 's.group_id = g.id'                   , array())
            ->joinLeft(array('st' => 'store')           , 'cr.store_id = st.id'                 , array())
            ->joinLeft(array('o'  => 'org')             , 'st.org_dealer = o.org_id'            , array())
            ->joinLeft(array('stt'=> 'store_type')      , 'o.store_type_id = stt.store_type_id' , array())
            ->joinLeft(array('rm2'=> 'regional_market') , 'st.district = rm2.id'                , array())
            ->joinLeft(array('rm' => 'regional_market') , 'st.regional_market = rm.id'          , array())
            ->joinLeft(array('a'  => 'area')            , 'rm.area_id = a.id'                   , array());

        if ( isset($params['export']) and $params['export'] ) {
            
            // $select->group(array('DATE(cr.created_at)','st.id','s.id'));
            $select->group(array('DATE(cr.created_at)','st.id'));

        } else {

            // $select->group(array('DATE(cr.created_at)','st.id','s.id'));
            // $select->order(array('DATE(cr.created_at) DESC','a.name ASC','st.id ASC','s.code ASC'));

            $select->group(array('DATE(cr.created_at)','st.id'));
            $select->order(array('DATE(cr.created_at) DESC','a.name ASC','st.id ASC'));

        }

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id = ?', $params['store_id']);
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
        } 
/*
        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code LIKE ?', '%'.$params['staff_code'].'%');
        } 

        if ( isset($params['staff_name']) && $params['staff_name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
        } 
*/
        if ( isset($params['from']) && $params['from'] ) {
            $select->where('cr.created_at >= ?', $from." 00:00:00");
        } 

        if ( isset($params['to']) && $params['to'] ) {
            $select->where('cr.created_at <= ?', $to." 23:59:59");
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

        // Add Filter Brand 
        if (isset($params['brand_id']) && $params['brand_id']) {
            if (is_array($params['brand_id']) && count($params['brand_id']))
                $select->where('cp.brand_id IN (?)', $params['brand_id']);
            elseif (is_numeric($params['brand_id']))
                $select->where('cp.brand_id = ?', intval($params['brand_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Product 
        if (isset($params['product_id']) && $params['product_id']) {
            if (is_array($params['product_id']) && count($params['product_id']))
                $select->where('cp.id IN (?)', $params['product_id']);
            elseif (is_numeric($params['product_id']))
                $select->where('cp.id = ?', intval($params['product_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Status
        if (isset($params['status_id']) && $params['status_id']) {
            if (is_array($params['status_id']) && count($params['status_id']))
                $select->where('cp.status IN (?)', $params['status_id']);
            elseif (is_numeric($params['status_id']))
                $select->where('cp.status = ?', intval($params['status_id']));
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

        if (isset($params['get_total_count']) && $params['get_total_count'] == 0) { 
            $select_p = $db->select()
                ->from(array('pa' => $select), array(
                    'cnt_com1' => new Zend_Db_Expr("SUM(pa.com_1)"),
                    'cnt_com2' => new Zend_Db_Expr("SUM(pa.com_2)"),
                    'cnt_com3' => new Zend_Db_Expr("SUM(pa.com_3)"),
                    'cnt_total'=> new Zend_Db_Expr("SUM(pa.total_unit)"),
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


    function getStoreFocusList($params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('t' => 'timing'), array("COUNT(ts.imei)"))
            ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array())
            ->where('t.created_at >= ?', $from." 00:00:00")
            ->where('t.created_at <= ?', $to." 23:59:59")
            ->where('t.store = st.id');

        // OPPO
        if (isset($params['oppo_product_id']) && $params['oppo_product_id']) {
            $sub_select->where('ts.product_id IN (?)', $params['oppo_product_id']);
        }

        $filter_01 = $filter_02 = $filter_03 = "";

        // Huawei
        if (isset($params['huawei_product_id']) && $params['huawei_product_id']) {
            $filter_01 = " AND cr.product_id IN (".implode(",", $params['huawei_product_id']).")";
        }

        // VIVO
        if (isset($params['vivo_product_id']) && $params['vivo_product_id']) {
            $filter_02 = " AND cr.product_id IN (".implode(",", $params['vivo_product_id']).")";
        }

        // Samsung
        if (isset($params['samsung_product_id']) && $params['samsung_product_id']) {
            $filter_03 = " AND cr.product_id IN (".implode(",", $params['samsung_product_id']).")";
        }

        $get = array(
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',

            'sellout_oppo'      => new Zend_Db_Expr("(".$sub_select.")"),
            'sellout_huawei'    => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 1 ".$filter_01." THEN cr.unit END), 0)"),
            'sellout_vivo'      => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 2 ".$filter_02." THEN cr.unit END), 0)"),
            'sellout_samsung'   => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 3 ".$filter_03." THEN cr.unit END), 0)"),
        );

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('st' => 'store')                   , 'sf.store_id = st.id'         , array())
            ->join(array('o'  => 'org')                     , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market')         , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')                    , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('cr'  => 'competitor_record')  , 
                "   st.id = cr.store_id 
                    AND cr.created_at >= '".$from." 00:00:00' 
                    AND cr.created_at <= '".$to." 23:59:59' 
                ", array())
            ->joinLeft(array('cp' => 'competitor_product')  , 'cr.product_id = cp.id'       , array())
            ->group('st.id')
            ->order(array('a.name ASC','st.id ASC')); 

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id LIKE ?', '%'.$params['store_id'].'%');
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
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
/*
        // Add Filter Status
        if (isset($params['status_id']) && $params['status_id']) {
            if (is_array($params['status_id']) && count($params['status_id']))
                $select->where('cp.status IN (?)', $params['status_id']);
            elseif (is_numeric($params['status_id']))
                $select->where('cp.status = ?', intval($params['status_id']));
            else
                $select->where('1=0', 1);
        }
*/
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

        if (isset($params['get_total_count']) && $params['get_total_count'] == 0) { 
            $select_p = $db->select()
                ->from(array('pa' => $select), array(
                    'total_oppo'    => new Zend_Db_Expr("SUM(pa.sellout_oppo)"),
                    'total_huawei'  => new Zend_Db_Expr("SUM(pa.sellout_huawei)"),
                    'total_vivo'    => new Zend_Db_Expr("SUM(pa.sellout_vivo)"),
                    'total_samsung' => new Zend_Db_Expr("SUM(pa.sellout_samsung)"),
                ));
            //$select_p->group('pa.status_id');
            //echo $select_p; die;
            return $db->fetchRow($select_p);
        }

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Export Store Focus Details : Competitor Product 
    function getStoreFocusListDetails($params) {

        // echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        // Get Selected Product
        $sub_get = array(
            'brand_id'      => 'cb.id',
            'brand_name'    => 'cb.name',
            'product_id'    => 'cp.id',
            'product_name'  => 'cp.name',
        );

        // Competitor Product
        $sub_select_01 = $db->select()
            ->from(array('cb' => 'competitor_brand'), $sub_get)
            ->join(array('cp' => 'competitor_product'), 'cb.id = cp.brand_id', array())
            ->order(array('cb.name ASC','cp.position ASC'));

        if (
            ( isset($params['huawei_product_id']) && $params['huawei_product_id'] ) || 
            ( isset($params['vivo_product_id']) && $params['vivo_product_id'] ) || 
            ( isset($params['samsung_product_id']) && $params['samsung_product_id'] ) 
        ) {

            if ($params['huawei_product_id'] == '') { $params['huawei_product_id'] = array(); }
            if ($params['vivo_product_id'] == '') { $params['vivo_product_id'] = array(); }
            if ($params['samsung_product_id'] == '') { $params['samsung_product_id'] = array(); }

            $product_id = array_unique(array_merge($params['huawei_product_id'], $params['vivo_product_id'],$params['samsung_product_id']));

            $sub_select_01->where('cp.id IN (?)', $product_id);

        }

        // echo $sub_select_01; die;
        $sub_result = $db->fetchAll($sub_select_01);
    
        for ($i=0;$i<count($sub_result);$i++) {
            $get_01[ $sub_result[$i]['brand_name']." : ".$sub_result[$i]['product_name'] ] = new Zend_Db_Expr("SUM(CASE WHEN cp.brand_id = ".$sub_result[$i]['brand_id']." AND cp.id = ".$sub_result[$i]['product_id']." THEN cr.unit ELSE 0 END)");
        }

        $get_02 = array(
            'timing_date'   => new Zend_Db_Expr("DATE(cr.created_at)"),
            'area_name'     => 'a.name',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'city_type'     => 'rm2.city_type',
            'channel_type'  => new Zend_Db_Expr(
                                "(
                                    CASE 
                                        WHEN o.org_id IN (12,19,5,20,3,26,10) THEN 'OPR' 
                                        WHEN o.store_type_id = 2 THEN 'IND' 
                                        ELSE stt.store_type_name 
                                    END
                                )"),

            // 'staff_code'    => 's.code',
            // 'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            // 'staff_group'   => 'g.name',
            'total_unit'    => new Zend_Db_Expr("SUM(cr.unit)"),
            'com_1' => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 1 THEN cr.unit ELSE 0 END), 0)"),  // Huawei
            'com_2' => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 2 THEN cr.unit ELSE 0 END), 0)"),  // VIVO
            'com_3' => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN cp.brand_id = 3 THEN cr.unit ELSE 0 END), 0)"),  // Samsung
        );

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('st' => 'store')           , 'sf.store_id = st.id'                 , array())
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'            , array())
            ->join(array('stt'=> 'store_type')      , 'o.store_type_id = stt.store_type_id' , array())
            ->join(array('rm2'=> 'regional_market') , 'st.district = rm2.id'                , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'          , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                   , array())

            ->join(array('cr' => $this->_name)  , 
                "   st.id = cr.store_id 
                    AND cr.created_at >= '".$from." 00:00:00' 
                    AND cr.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('cp' => 'competitor_product'), 'cr.product_id = cp.id', array())

            // ->join(array('s'  => 'staff'), 'cr.staff_id = s.id' , array())
            // ->join(array('g'  => 'group'), 's.group_id = g.id'  , array())

            // ->group(array('DATE(cr.created_at)','st.id','s.id'))
            // ->order(array('DATE(cr.created_at) DESC','a.name ASC','st.id ASC','s.code ASC'));
            ->group(array('DATE(cr.created_at)','st.id'))
            ->order(array('DATE(cr.created_at) DESC','a.name ASC','st.id ASC'));

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id LIKE ?', '%'.$params['store_id'].'%');
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
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

        if (
            ( isset($params['huawei_product_id']) && $params['huawei_product_id'] ) || 
            ( isset($params['vivo_product_id']) && $params['vivo_product_id'] ) || 
            ( isset($params['samsung_product_id']) && $params['samsung_product_id'] ) 
        ) {

            if ($params['huawei_product_id'] == '') { $params['huawei_product_id'] = array(); }
            if ($params['vivo_product_id'] == '') { $params['vivo_product_id'] = array(); }
            if ($params['samsung_product_id'] == '') { $params['samsung_product_id'] = array(); }

            $product_id = array_unique(array_merge($params['huawei_product_id'], $params['vivo_product_id'],$params['samsung_product_id']));

            $select->where('cp.id IN (?)', $product_id);
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }


    // Export Store Focus Details : OPPO Product
    function getStoreFocusListOPPO($params) {

        //echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array();

        if ( isset($params['oppo_product_id']) && $params['oppo_product_id'] ) {

            // Get Selected Product
            $sub_get = array(
                'product_id'    => 'g.id',
                'product_name'  => 'g.name',
            );

            // Competitor Product
            $sub_select_01 = $db->select()
                ->from(array('g' => WAREHOUSE_DB.'.good'), $sub_get)
                ->where('g.id IN (?)', $params['oppo_product_id'])
                ->order(array('g.name ASC'));

            // echo $sub_select_01; die;
            $sub_result = $db->fetchAll($sub_select_01);
        
            for ($i=0;$i<count($sub_result);$i++) {
                $get_01[ "OPPO : ".$sub_result[$i]['product_name'] ] = new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id = ".$sub_result[$i]['product_id']." THEN ts.imei END)");
            }

        }
        
        $get_02 = array(
            'timing_date'   => new Zend_Db_Expr("DATE(t.created_at)"),
            'area_name'     => 'a.name',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'city_type'     => 'rm2.city_type',
            'channel_type'  => new Zend_Db_Expr(
                                "(
                                    CASE 
                                        WHEN o.org_id IN (12,19,5,20,3,26,10) THEN 'OPR' 
                                        WHEN o.store_type_id = 2 THEN 'IND' 
                                        ELSE stt.store_type_name 
                                    END
                                )"),

            // 'staff_code'    => 's.code',
            // 'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            // 'staff_group'   => 'g.name',
            'total_oppo'    => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('st' => 'store')           , 'sf.store_id = st.id'                 , array())
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'            , array())
            ->join(array('stt'=> 'store_type')      , 'o.store_type_id = stt.store_type_id' , array())
            ->join(array('rm2'=> 'regional_market') , 'st.district = rm2.id'                , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'          , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                   , array())

            ->join(array('t'  => 'timing'), 
                "   st.id = t.store
                    AND t.created_at >= '".$from." 00:00:00' 
                    AND t.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array())
            // ->join(array('s'  => 'staff')       , 't.staff_id = s.id'   , array())
            // ->join(array('g'  => 'group')       , 's.group_id = g.id'   , array())

            // ->where('ts.product_id IN (?)', $params['oppo_product_id'])
            // ->group(array('DATE(t.created_at)','st.id','s.id'))
            // ->order(array('DATE(t.created_at) DESC','a.name ASC','st.id ASC','s.code ASC'));
            ->group(array('DATE(t.created_at)','st.id'))
            ->order(array('DATE(t.created_at) DESC','a.name ASC','st.id ASC'));

        if ( isset($params['oppo_product_id']) && $params['oppo_product_id'] ) {
            $select->where('ts.product_id IN (?)', $params['oppo_product_id']);
        }

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id LIKE ?', '%'.$params['store_id'].'%');
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
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

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Store Focus Graph : All Sellout By OPPO
    function getSelloutByOPPO($params) {

        //echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'brand_name'    => new Zend_Db_Expr("'OPPO'"),
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('t'  => 'timing'), 
                "   sf.store_id = t.store
                    AND t.created_at >= '".$from." 00:00:00' 
                    AND t.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array());

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Store Focus Graph : All Sellout By Competitor Brand
    function getSelloutByCompetitor($params) {

        //echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'brand_name'    => 'cb.name',
            'sellout'       => new Zend_Db_Expr("SUM(cr.unit)"),
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("SUM(CASE WHEN cr.created_at >= '".$day." 00:00:00' AND cr.created_at <= '".$day." 23:59:59' THEN cr.unit ELSE 0 END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('cr'  => 'competitor_record'), 
                "   sf.store_id = cr.store_id
                    AND cr.created_at >= '".$from." 00:00:00' 
                    AND cr.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('cp' => 'competitor_product')  , 'cr.product_id = cp.id'   , array())
            ->join(array('cb' => 'competitor_brand')    , 'cp.brand_id = cb.id'     , array())

            ->group(array('cb.id'))
            ->order(array('cb.id ASC'));

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Store Focus Graph : Sellout OPPO By Model
    function getSelloutByModelOPPO($params) {

        //echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'model_name'    => new Zend_Db_Expr("CONCAT('[', g.name, '] ', g.desc)"),
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('t'  => 'timing'), 
                "   sf.store_id = t.store
                    AND t.created_at >= '".$from." 00:00:00' 
                    AND t.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id'     , array())
            ->join(array('g'  => WAREHOUSE_DB.'.good')  , 'ts.product_id = g.id'    , array())
            ->where('ts.product_id IN (?)', $params['oppo_product_id'])
            ->group('g.id')
            ->order('g.name ASC');

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Store Focus Graph : All Sellout Competitor Brand By Model
    function getSelloutByModelCompetitor($params) {

        //echo "<pre>"; print_r($params); echo "<br/>";

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'model_name'    => 'cp.name',
            'sellout'       => new Zend_Db_Expr("SUM(cr.unit)"),
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("SUM(CASE WHEN cr.created_at >= '".$day." 00:00:00' AND cr.created_at <= '".$day." 23:59:59' THEN cr.unit ELSE 0 END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->join(array('cr'  => 'competitor_record'), 
                "   sf.store_id = cr.store_id
                    AND cr.created_at >= '".$from." 00:00:00' 
                    AND cr.created_at <= '".$to." 23:59:59' 
                ", array())
            ->join(array('cp' => 'competitor_product')  , 'cr.product_id = cp.id'   , array())
            ->join(array('cb' => 'competitor_brand')    , 'cp.brand_id = cb.id'     , array())
            ->group(array('cp.id'))
            ->order(array('cp.id ASC'));


        $temp = array();
        $selected_model = array();

        $temp[] = $params['huawei_product_id'];
        $temp[] = $params['vivo_product_id'];
        $temp[] = $params['samsung_product_id'];

        foreach($temp as $tmp) {
            if(is_array($tmp)) {
                $selected_model = array_merge($selected_model, $tmp);
            }
        }

        // print_r($params['huawei_product_id']); echo "<br/>";
        // print_r($params['vivo_product_id']); echo "<br/>";
        // print_r($params['samsung_product_id']); echo "<br/>";
        // print_r($selected_model); echo "<br/>";

        $select->where('cr.product_id IN (?)', $selected_model);

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

}