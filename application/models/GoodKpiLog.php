<?php
class Application_Model_GoodKpiLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'good_kpi_log';

    public function fetchGoodKpi($page, $limit, &$total, $params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('gkl' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS gkl.id'), 'gkl.kpi', 'gkl.com_rate', 'gkl.com_rate_aec', 'gkl.price', 'gkl.type', 'gkl.from_date', 'gkl.to_date'))
        ->joinLeft(array('g'    => WAREHOUSE_DB.'.good')       , 'gkl.good_id = g.id'    , array('good_id' => 'g.id', 'good_name' => 'g.name' , 'brand_id' => 'g.brand_id'))
        ->joinLeft(array('gc'   => WAREHOUSE_DB.'.good_color') , 'gkl.color_id = gc.id'  , array('color_name' => 'gc.name'));

        if (isset($params['from']) and $params['from']) {
            $d = explode('/', $params['from']);
            $from = $d[2].'-'.$d[1].'-'.$d[0];

            $select->where('gkl.to_date >= ?', $from);
        }
        if (isset($params['to']) and $params['to']) {
            $d = explode('/', $params['to']);
            $to = $d[2].'-'.$d[1].'-'.$d[0];
            
            $select->where('gkl.from_date <= ?', $to);
        } 

        if (isset($params['type']) && $params['type'])
            $select->where('gkl.type = ?', intval($params['type']));
        
        if (isset($params['name']) && $params['name'])
            $select->where('g.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['price']) && $params['price'])
            $select->where('gkl.price LIKE ?', '%'.$params['price'].'%');

        $select->order(array('g.name ASC', 'to_date ASC', 'gc.name ASC', 'gkl.type ASC'));


        if ($limit)
            $select->limitPage($page, $limit);

        //echo $select; //die;
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    public function get_list($params) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('gkl' => 'good_kpi_log'), array('gkl.*'))
        ->joinLeft(array('g'    =>  WAREHOUSE_DB.'.good')       , 'gkl.good_id = g.id'  , array('good_name' => 'g.name'))
        ->joinLeft(array('gc'   =>  WAREHOUSE_DB.'.good_color') , 'gkl.color_id = gc.id', array('color_name' => 'gc.name'));

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $select->where( 'gkl.from_date <= ?', $params['from']);
        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $select->where( 'gkl.to_date >= ?', $params['to']);
        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {

            $sub_select_before = $db->select()
            ->from(array('gkl1'=>'good_kpi_log'),'id')
            ->where('gkl1.from_date < ?', $params['from'])
            ->where('gkl1.to_date < ?', $params['from']);

            $sub_select_after = $db->select()
            ->from(array('gkl2'=>'good_kpi_log'),'id')
            ->where('gkl2.from_date > ?', $params['to'])
            ->where('gkl2.to_date > ?', $params['to']);

            $select->where('gkl.id NOT IN (?)', $sub_select_before);
            $select->where('gkl.id NOT IN (?)', $sub_select_after);
            
        }

        $select->order(array('g.name ASC','gc.name ASC'));

        //echo $select;die;
        $result = $db->fetchAll($select);

        return $result;
    }

    // PC Commission 
    public function report_kpiPC($data,$params) {

        $db = Zend_Registry::get('db');

        // Check Punish Memo By Store [PC]
        $select_pms = $db->select()
        ->from(array('pms' => 'punish_memo_store'), array('pms.*' ))
        ->where('pms.flag_pc = ?', 1)
        ->where('pms.from_date >= ?', $params['from'])
        ->where('pms.to_date <= ?', $params['to']);

        //echo $select_pms;
        $result_pms = $db->fetchAll($select_pms);

        $punish_store = '';
        foreach ($result_pms as $key => $value) { $punish_store .= $value['store'].","; }
        $punish_store = rtrim($punish_store, ",");

        //echo "Punish Store List : ".$punish_store;

        $punish_sql_unit = '';
        $punish_sql_price = '';
        if ( $punish_store != '') {
            $punish_sql_unit = "
            WHEN t.created_at >= '".$params['from']." 00:00:00' 
            AND t.created_at <= '".$params['to']." 23:59:59' 
            AND t.store IN (".$punish_store.") THEN NULL 
            ";
            $punish_sql_price = "
            WHEN t.created_at >= '".$params['from']." 00:00:00' 
            AND t.created_at <= '".$params['to']." 23:59:59' 
            AND t.store IN (".$punish_store.") THEN 0 
            ";
        }

        if (is_null($data)) {

            if ( !isset($params['bm_flag']) ) { $params['bm_flag'] = 0; }

            $temp2 = array(
                'total_sellout' => new Zend_Db_Expr(
                    "   COUNT(
                    CASE 
                    WHEN 
                    t.created_at >= '2017-12-01 00:00:00'
                    AND t.created_at <= '2017-12-07 23:59:59'
                    AND t.staff_id = 10458
                    THEN 
                    NULL 

                    WHEN 
                    t.created_at >= '2018-01-01 00:00:00'
                    AND t.created_at <= '2018-01-27 23:59:59'
                    AND t.staff_id IN (9712, 15997, 30952, 30962)
                    THEN 
                    NULL 
                    WHEN 
                    t.created_at >= '2018-09-01 00:00:00'
                    AND t.created_at <= '2018-09-15 23:59:59'
                    AND t.staff_id IN (26374)
                    THEN 
                    NULL 


                    WHEN 
                    t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
                    THEN 
                    (CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN NULL ELSE ts.imei END) 
                    WHEN 
                    t.staff_id IN (30506) AND s.group_id = 30 
                    THEN 
                    (CASE WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-13 23:59:59' THEN NULL ELSE ts.imei END) 

                    ".$punish_sql_unit."

                    ELSE 
                    ts.imei 
                    END
                    )
                    "), 
                
                'total_kpi'     => new Zend_Db_Expr(
                    "   SUM(
                    CASE 
                    WHEN 
                    t.created_at >= '2016-11-01 00:00:00' 
                    AND t.created_at <= '2016-11-30 23:59:59'
                    AND s.code = '5801961'
                    THEN 
                    gkl.kpi*2 
                    WHEN 
                    t.created_at >= '2016-11-01 00:00:00' 
                    AND t.created_at <= '2016-11-30 23:59:59'
                    AND ts.product_id = 142 
                    AND a.id = 55 
                    THEN 
                    gkl.kpi*2 
                    WHEN 
                    t.created_at >= '2016-12-01 00:00:00' 
                    AND t.created_at <= '2017-01-31 23:59:59'
                    AND ts.product_id = 142 
                    AND a.id = 13 
                    THEN 
                    gkl.kpi*2
                    WHEN 
                    t.created_at >= '2016-12-24 00:00:00' 
                    AND t.created_at <= '2016-12-25 23:59:59'
                    THEN 
                    gkl.kpi*2 
                    WHEN 
                    t.created_at >= '2016-12-31 00:00:00' 
                    AND t.created_at <= '2016-12-31 23:59:59'
                    THEN 
                    gkl.kpi*2 
                    WHEN 
                    t.created_at >= '2017-12-01 00:00:00'
                    AND t.created_at <= '2017-12-07 23:59:59'
                    AND t.staff_id = 10458
                    THEN 
                    0 
                    WHEN 
                    t.created_at >= '2018-01-01 00:00:00'
                    AND t.created_at <= '2018-01-27 23:59:59'
                    AND t.staff_id IN (9712, 15997, 30952, 30962)
                    THEN 
                    0 
                    WHEN 
                    t.created_at >= '2018-09-01 00:00:00'
                    AND t.created_at <= '2018-09-15 23:59:59'
                    AND t.staff_id IN (26374)
                    THEN 
                    0 

                    WHEN 
                    t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
                    THEN 
                    (CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN 0 ELSE gkl.kpi END)
                    WHEN 
                    t.staff_id IN (30506) AND s.group_id = 30 
                    THEN 
                    (CASE WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-13 23:59:59' THEN 0 ELSE gkl.kpi END)  

                    ".$punish_sql_price."

                    ELSE 
                    gkl.kpi
                    END
                    )

                    "), 
                'com_pc'        => new Zend_Db_Expr(
                    "   SUM(
                    CASE
                    WHEN 
                    i.distributor_id = 11293 
                    AND i.good_id = 140 
                    AND t.created_at >= '2017-03-22 00:00:00' 
                    AND t.created_at <= '2017-03-31 23:59:59' 
                    THEN 
                    '20'
                    WHEN 
                    t.created_at >= '2017-12-01 00:00:00'
                    AND t.created_at <= '2017-12-07 23:59:59'
                    AND t.staff_id = 10458
                    THEN 
                    0 
                    WHEN 
                    t.created_at >= '2018-01-01 00:00:00'
                    AND t.created_at <= '2018-01-27 23:59:59'
                    AND t.staff_id IN (9712, 15997, 30952, 30962)
                    THEN 
                    0 
                    WHEN 
                    t.created_at >= '2018-04-25 00:00:00'
                    AND t.created_at <= '2018-04-26 23:59:59'
                    AND ts.product_id = 310 
                    AND ts.pre_order_status = 1 
                    AND ts.warrant_no IS NOT NULL 
                    THEN 
                    400
                    WHEN 
                    t.created_at >= '2018-04-27 00:00:00'
                    AND t.created_at <= '2018-04-30 23:59:59'
                    AND ts.product_id = 310 
                    AND ts.pre_order_status = 1 
                    AND ts.warrant_no IS NOT NULL 
                    AND t.store IN (
                    17693,12138,10382,4232,12049,9078,16568,13548,20801,8560,
                    4917,13710,11632,13314,1925,9696,9189,9176,11998,1534,
                    6,1012,459,21079,7827,456,17365,768,16411,16,
                    13458,1363,6037,9448,7077,39,5441,1718,6242,4581,
                    17400,1720,5832,8680,1529,20609,9177,6505,1920,15243,
                    956,13228,1719,17458)
                    THEN 
                    400

                    WHEN 
                    ts.product_id = 345 
                    AND t.created_at >= '2018-08-30 00:00:00' 
                    AND t.created_at <= '2018-08-30 23:59:59' 
                    AND t.staff_id IN (34199, 38851)
                    THEN 
                    250 
                    WHEN 
                    t.created_at >= '2018-09-01 00:00:00'
                    AND t.created_at <= '2018-09-15 23:59:59'
                    AND t.staff_id IN (26374)
                    THEN 
                    0 

                    WHEN 
                    t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
                    THEN 
                    (CASE 
                    WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN 
                    0 
                    ELSE 
                    CASE WHEN ts.product_id IN (323,338) AND (SELECT COUNT(ps.imei_sn) FROM warehouse.packed_sim AS ps WHERE ps.imei_sn = ts.imei AND ps.sim_activated_at IS NOT NULL) > 0 THEN 40 ELSE gkl.com_rate END
                    END) 

                    WHEN 
                    ts.product_id IN (323,338) 
                    AND (SELECT COUNT(ps.imei_sn) FROM warehouse.packed_sim AS ps WHERE ps.imei_sn = ts.imei AND ps.sim_activated_at IS NOT NULL) > 0 
                    THEN 
                    40 

                    WHEN 
                    t.staff_id IN (30506) AND s.group_id = 30 
                    THEN 
                    (CASE 
                    WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-13 23:59:59' THEN 
                    0 
                    ELSE 
                    CASE WHEN ts.product_id IN (323,338) AND (SELECT COUNT(ps.imei_sn) FROM warehouse.packed_sim AS ps WHERE ps.imei_sn = ts.imei AND ps.sim_activated_at IS NOT NULL) > 0 THEN 40 ELSE gkl.com_rate END
                    END) 

                    WHEN 
                    ts.product_id IN (323,338) 
                    AND (SELECT COUNT(ps.imei_sn) FROM warehouse.packed_sim AS ps WHERE ps.imei_sn = ts.imei AND ps.sim_activated_at IS NOT NULL) > 0 
                    THEN 
                    40 

                    ".$punish_sql_price."
                    ELSE 
                    gkl.com_rate 
                    END
                    )
                    "),
'com_pc_aec'        => new Zend_Db_Expr(
    "   SUM(
    CASE
    WHEN 
    i.distributor_id = 11293 
    AND i.good_id = 140 
    AND t.created_at >= '2017-03-22 00:00:00' 
    AND t.created_at <= '2017-03-31 23:59:59' 
    THEN 
    '30'
    WHEN 
    t.created_at >= '2017-12-01 00:00:00'
    AND t.created_at <= '2017-12-07 23:59:59'
    AND t.staff_id = 10458
    THEN 
    0
    WHEN 
    t.created_at >= '2018-01-01 00:00:00'
    AND t.created_at <= '2018-01-27 23:59:59'
    AND t.staff_id IN (9712, 15997, 30952, 30962)
    THEN 
    0 
    WHEN 
    t.created_at >= '2018-09-01 00:00:00'
    AND t.created_at <= '2018-09-15 23:59:59'
    AND t.staff_id IN (26374)
    THEN 
    0 

    WHEN 
    t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
    THEN 
    (CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN 0 ELSE gkl.com_rate_aec END) 
    WHEN 
    t.staff_id IN (30506) AND s.group_id = 30 
    THEN 
    (CASE WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-13 23:59:59' THEN 0 ELSE gkl.com_rate_aec END) 

    ".$punish_sql_price."
    ELSE 
    gkl.com_rate_aec 
    END
    )
    "),
'total_price'   => new Zend_Db_Expr(
    "   SUM(
    CASE
    WHEN 
    t.created_at >= '2017-12-01 00:00:00'
    AND t.created_at <= '2017-12-07 23:59:59'
    AND t.staff_id = 10458
    THEN 
    0 
    WHEN 
    t.created_at >= '2018-01-01 00:00:00'
    AND t.created_at <= '2018-01-27 23:59:59'
    AND t.staff_id IN (9712, 15997, 30952, 30962)
    THEN 
    0 
    WHEN 
    t.created_at >= '2018-09-01 00:00:00'
    AND t.created_at <= '2018-09-15 23:59:59'
    AND t.staff_id IN (26374)
    THEN 
    0 

    WHEN 
    t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
    THEN 
    (CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN 0 ELSE gkl.price END) 
    WHEN 
    t.staff_id IN (30506) AND s.group_id = 30 
    THEN 
    (CASE WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-31 23:59:59' THEN 0 ELSE gkl.price END)

    ".$punish_sql_price."
    ELSE 
    gkl.price 
    END
    )
    "), 
);

} else {

    for ($i=0;$i<count($data);$i++) {

        $temp2[$data[$i]['good_name']."_".$data[$i]['color_name']."_".$data[$i]['id']] = new Zend_Db_Expr(
            "CONCAT(
            SUM(
            IF(
            ts.product_id='".$data[$i]['good_id']."' 
            AND ts.model_id = '".$data[$i]['color_id']."' 
            AND t.created_at >= '".$params['from']." 00:00:00' 
            AND t.created_at <= '".$params['to']." 23:59:59' 
            AND t.created_at >= '".$data[$i]['from_date']." 00:00:00' 
            AND t.created_at <= '".$data[$i]['to_date']." 23:59:59'
            ,1,0)
            )
            , '|', ".$data[$i]['kpi'].", '|', ".$data[$i]['price'].") ");
    }

}

$temp1 = array(
    'area_id'       =>  'a.id',
    'area_name'     =>  'a.name',
    'staff_id'      =>  's.id',
    'staff_code'    =>  's.code',
    'staff_joined'  =>  's.joined_at',
    'staff_created' =>  's.created_at',
    'staff_offdate' =>  's.off_date',
    'staff_name'    =>  "CONCAT(s.firstname,' ',s.lastname)",
            /*'store_name'    => 'st.name',
            'sale_name'     => "CONCAT(s2.firstname,' ',s2.lastname)",*/
            'st_id'         =>  'st.id',
            'mn_id'         =>  'sm.market_name_id',
            'pc_stand_by'   =>  's.pc_stand_by',
            'cnt_store'     =>  new Zend_Db_Expr("COUNT(DISTINCT st.id)"),
            'st_level'      =>  'st.store_grade',
            'st_org_id'     =>  'o.org_id',
            'st_org_name'   =>  'o.org_name',
            'st_type_id'    =>  'o.store_type_id',
        );

$temp = $temp1 + $temp2;

$sub_select = $db->select()
->from(array('s1'=>'staff'),'id')
->where('s1.id <> t.sales_id')
->where('s1.group_id <> ?', 4);
        //print_r($temp); die;

        // Filter date
$str_to = "";
$str_from = "";
if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
    $str_from = "AND t.created_at >= '". $params['from']. " 00:00:00' ";
} elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
    $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
} elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
    $str_from = "AND t.created_at >= '". $params['from']." 00:00:00' ";
    $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
}

$select = $db->select()
->from(array('s' => 'staff'), $temp)
->joinLeft(array('t'    => 'timing'), "s.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> '' ".$str_from.$str_to  ,array())
->joinLeft(array('st'   => 'store')             , 't.store = st.id AND st.rank <> 5', array())
->joinLeft(array('sm'   => 'store_market')      , 'st.id = sm.store_id'         , array())
->joinLeft(array('r'    => 'regional_market')   , 'st.regional_market = r.id'   , array())
->joinLeft(array('d'    => 'regional_market')   , 'st.district = d.id'          , array())
->joinLeft(array('a'    => 'area')              , 'r.area_id = a.id'            , array())
->join(array('ts'       =>  'timing_sale')      , 't.id = ts.timing_id '        , array())
->join(array('i' => WAREHOUSE_DB.'.imei'), 
    "   ts.imei = i.imei_sn 
    AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
    AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')      
    ", array())
/*
            ->join(array('i' => WAREHOUSE_DB.'.imei'), 
                "   ts.imei = i.imei_sn 
                    AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                    AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
                ", array())
*/
            //->joinLeft(array('s2'   => 'staff')             , 't.sales_id = s2.id'          , array())
                ->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
            // ->where('s.group_id = ?', 4)
            //->where('( s.group_id = ?', 4)
            //->orWhere('s.id IN (?) )', $sub_select)
            // ->group(array('s.id', 'a.id'))
                ->order(array('a.name ASC', 's.code ASC'));

                if (is_null($data)) { 
                    $select->join(array('gkl' => 'good_kpi_log'), 
                        "   gkl.good_id = ts.product_id 
                        AND gkl.color_id = ts.model_id 
                        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                        ", array());

                    if ( isset($params['bm_flag']) && $params['bm_flag'] ) {

                        switch ($params['bm_flag']) {
                            case 1: 
                            $select->where('s.group_id = ?', BM_ID); 
                            $select->group(array('s.id'));
                            break;
                            case 2: 
                            $select->where('s.group_id = ?', PGPB_ID); 
                            $select->group(array('s.id', 'a.id'));
                            break;
                            default: 
                            $select->group(array('s.id', 'a.id')); 
                            break;
                        }

                    } else {

                        $select->where('s.group_id = ?', PGPB_ID);
                // $select->where('st.org_dealer <> 16');


                // For OPPO Staff Tools API 
                        if ( isset($params['staff_id']) && $params['staff_id'] ) {
                            $select->where('s.id = ?', $params['staff_id']);
                        } else {
                    // $select->where('st.org_dealer <> 16');
                        }

                        $select->group(array('s.id', 'a.id'));
                    }

                } else {
                    $select->group(array('s.id', 'a.id'));
                }

        // For PC Commission Total 
                if ( isset($params['report_type']) && $params['report_type'] && in_array($params['export'], array(6,7,8,9))) {
                    if ($params['report_type'] == 'BKK') { $select->where('a.name LIKE ?', "BKK%"); }
                    else { $select->where('a.name NOT LIKE ?', "BKK%"); }
                }

        // For BM OPPO Commission
                if ( isset($params['store_id']) && $params['store_id'] ) {
                    $select->where('st.id = ?', $params['store_id']);
                }

                if ( isset($params['name']) && $params['name'] ) {
                    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
                }

                if ( isset($params['staff_code']) && $params['staff_code'] ) {
                    $select->where('s.code = ?', $params['staff_code']);
                }

                if ( isset($params['staff_id']) && $params['staff_id'] ) {
                    $select->where('s.id = ?', $params['staff_id']);
                }

                if ( isset($params['phone_number']) && $params['phone_number'] ) {
                    $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
                }

                if (isset($params['report_type']) && $params['report_type']) {

                    if ( $params['report_type'] == 'BKK' ) { $select->where("a.name LIKE 'BKK%'", 1); }
                    else { $select->where("a.name NOT LIKE 'BKK%'", 1); }

                } else {

            // Add Filter Area
                    if (isset($params['area_id']) && $params['area_id']) {
                        if (is_array($params['area_id']) && count($params['area_id']))
                            $select->where('r.area_id IN (?)', $params['area_id']);
                        elseif (is_numeric($params['area_id']))
                            $select->where('r.area_id = ?', intval($params['area_id']));
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

            // Add Filter Store
                    if (isset($params['store']) && $params['store']) {
                        if (is_array($params['store']) && count($params['store']))
                            $select->where('t.store IN (?)', $params['store']);
                        elseif (is_numeric($params['store']))
                            $select->where('t.store = ?', intval($params['store']));
                        else
                            $select->where('1=0', 1);
                    }

                }

        // Add Check ASM Table
                if (isset($params['asm']) && $params['asm'] ) {
                    $QAsm = new Application_Model_Asm();
                    $list_regions = $QAsm->get_cache($params['asm']);
                    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

                    if (count($list_regions) > 0)
                        $select->where( 'st.district IN (?)', $list_regions);
                    else
                        $select->where('1=0', 1);
                }

                if (isset($params['store_type']) && $params['store_type']) {
                    if (is_array($params['store_type']) && count($params['store_type']))
                        $select->where('st.org_dealer IN (?)', $params['store_type']);
                    elseif (is_numeric($params['store_type']))
                        $select->where('st.org_dealer = ?', intval($params['store_type']));
                    else
                        $select->where('1=0', 1);
                }

                $main_select = $db->select()
                ->from(array('A' => $select, $temp));

        // echo $main_select; //die; 
                $log = $db->fetchAll($main_select);
        //print_r($log);die;

                return $log;
            }

    // PC Commission [Target]
            public function report_kpiTarget($params) {

        // Range of Last 1 Month
                $tmp_start_01 = new DateTime( $params['from'] );
                $tmp_start_01->modify( 'first day of previous month' );
                $last_start_01 = $tmp_start_01->format( 'Y-m-d' );

                $tmp_end_01 = new DateTime( $params['from'] );
                $tmp_end_01->modify( 'last day of previous month' );
                $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        // Range of Last 2 Month
                $tmp_start_02 = new DateTime( $last_start_01 );
                $tmp_start_02->modify( 'first day of previous month' );
                $last_start_02 = $tmp_start_02->format( 'Y-m-d' );

                $tmp_end_02 = new DateTime( $last_end_01 );
                $tmp_end_02->modify( 'last day of previous month' );
                $last_end_02 = $tmp_end_02->format( 'Y-m-d' );


                $db = Zend_Registry::get('db');

                $temp = array(
                    'area_name'     => 'a.name',
                    'province_name' => 'rm.name',
                    'district_name' => 'rm2.name',
                    'store_id'      => 'st.id',
                    'store_name'    => 'st.name',
                    'store_type'    => 'o.org_name',
                    'store_target'  => 'spt.target_price',
                    'joined_date'   => new Zend_Db_Expr("FROM_UNIXTIME(sst.joined_at)"),
                    'released_date' => new Zend_Db_Expr("FROM_UNIXTIME(sst.released_at)"),

                    'staff_id'      => 's.id',
                    'staff_code'    => 's.code',
                    'staff_created' => 's.created_at',
                    'staff_offdate' => 's.off_date',
                    'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
                    'pc_stand_by'   => new Zend_Db_Expr("( CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END )"),
                    'pc_target'     => 'oit.target_price',

                    'last_02'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN ts.imei END )"),
                    'last_01'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN ts.imei END )"),
                    'sellout'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),

                    'last_02_price'   => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN gkl.price END),0)"),
                    'last_01_price'   => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN gkl.price END),0)"),
                    'sellout_price'   => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN gkl.price END),0)"),

                    'sellout_f11pro' => new Zend_Db_Expr("COUNT( CASE WHEN ts.product_id IN (371,390,392) AND t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),

                );

                $select = $db->select()
                ->from(array('s' => 'staff'), $temp)
                ->join(array('sst'  =>  'store_staff_log'), 
                    "   s.id = sst.staff_id 
                    AND FROM_UNIXTIME(sst.joined_at) <= '".$params['to']." 23:59:59' 
                    AND (FROM_UNIXTIME(sst.released_at) >= '".$params['from']." 00:00:00' OR sst.released_at IS NULL)
                    ", array())
                ->join(array('st'   =>  'store')            , 'sst.store_id = st.id'        , array())
                ->join(array('o'    =>  'org')              , 'st.org_dealer = o.org_id'    , array()) 
                ->join(array('rm'   =>  'regional_market')  , 'st.regional_market = rm.id'  , array()) 
                ->join(array('a'    =>  'area')             , 'rm.area_id = a.id'           , array()) 
                ->join(array('rm2'  =>  'regional_market')  , 'st.district = rm2.id'        , array()) 
                ->joinLeft(array('spt' => 'store_price_target'), 
                    "   st.id = spt.store_id 
                    AND spt.from_date <= '".$params['from']."' 
                    AND spt.to_date >= '".$params['to']."' 
                    ", array())
                ->joinLeft(array('oit' => 'oppo_individual_target'), 
                    "   s.id = oit.staff_id 
                    AND oit.from_date <= '".$params['from']."' 
                    AND oit.to_date >= '".$params['to']."' 
                    ", array())
                ->joinLeft(array('t' => 'timing'), 
                    "   st.id = t.store 
                    AND s.id = t.staff_id 
                    AND t.created_at >= '".$last_start_02." 00:00:00' 
                    AND t.created_at <= '".$params['to']." 23:59:59' 
                    ", array()) 
                ->joinLeft(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array()) 
                ->joinLeft(array('gkl' => 'good_kpi_log'), 
                    "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                    ", array())

                ->where('s.group_id = ?', PGPB_ID)
                ->group(array('s.id', 'st.id', 'a.id'))
                ->order('s.code ASC');

                if ( isset($params['name']) && $params['name'] ) {
                    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
                }

                if ( isset($params['staff_code']) && $params['staff_code'] ) {
                    $select->where('s.code = ?', $params['staff_code']);
                }

                if ( isset($params['phone_number']) && $params['phone_number'] ) {
                    $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
                if (isset($params['store']) && $params['store']) {
                    if (is_array($params['store']) && count($params['store']))
                        $select->where('st.id IN (?)', $params['store']);
                    elseif (is_numeric($params['store']))
                        $select->where('st.id = ?', intval($params['store']));
                    else
                        $select->where('1=0', 1);
                }

        // Add Check ASM Table
                if (isset($params['asm']) && $params['asm'] ) {
                    $QAsm = new Application_Model_Asm();
                    $list_regions = $QAsm->get_cache($params['asm']);
                    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

                    if (count($list_regions) > 0)
                        $select->where( 'st.district IN (?)', $list_regions);
                    else
                        $select->where('1=0', 1);
                }

                if (isset($params['store_type']) && $params['store_type']) {
                    if (is_array($params['store_type']) && count($params['store_type']))
                        $select->where('st.org_dealer IN (?)', $params['store_type']);
                    elseif (is_numeric($params['store_type']))
                        $select->where('st.org_dealer = ?', intval($params['store_type']));
                    else
                        $select->where('1=0', 1);
                }

        //echo $select; die;
                $result = $db->fetchAll($select);
                return $result;
            }

    // PC Commission [Target F1s]
            public function report_kpiTargetHeroProduct($params) {

                $db = Zend_Registry::get('db');

                $temp = array(
                    'area_name'     => 'a.name',
                    'province_name' => 'r.name',
                    'district_name' => 'd.name',
                    'staff_id'      => 's.id',
                    'staff_code'    => 's.code',
                    'staff_created' => 's.created_at',
                    'staff_offdate' => 's.off_date',
                    'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
                    'store_id'      => 'st.id',
                    'store_name'    => 'st.name',
                    'store_type'    => 'o.org_name',
                    'punish'        => "stg.punish",
                    'reward'        => "stg.reward",
                    'total_sellout' => new Zend_Db_Expr("COUNT( CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END )"),
                    'total_sellout_hero_product' => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id = 142 AND i.activated_date IS NOT NULL THEN i.imei_sn END)"),
                    'total_sellout_no_ac' => new Zend_Db_Expr("COUNT(ts.imei)"),
                    'total_sellout_hero_product_no_ac' => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id = 142 THEN ts.imei END)"),
                );

                $sub_select = $db->select()
                ->from(array('s1'=>'staff'),'id')
                ->where('s1.id <> t.sales_id')
                ->where('s1.group_id <> ?', 4);
        //print_r($temp); die;

        // Filter date
                $str_to = "";
                $str_from = "";
                if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
                    $str_from = "AND t.created_at >= '". $params['from']. " 00:00:00' ";
                } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
                    $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
                } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
                    $str_from = "AND t.created_at >= '". $params['from']." 00:00:00' ";
                    $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
                }

                $select = $db->select()
                ->from(array('s' => 'staff'), $temp)
                ->joinLeft(array('t'    => 'timing'), "s.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> '' ".$str_from.$str_to  ,array())
                ->joinLeft(array('st'   => 'store')             , 't.store = st.id'             , array())
                ->joinLeft(array('r'    => 'regional_market')   , 'st.regional_market = r.id'   , array())
                ->joinLeft(array('d'    => 'regional_market')   , 'st.district = d.id'          , array())
                ->joinLeft(array('a'    => 'area')              , 'r.area_id = a.id'            , array())
                ->join(array('ts'       =>  'timing_sale')      , 't.id = ts.timing_id', array())
                ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 
                    "   ts.imei = i.imei_sn 
                    AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
                    AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')      
                    ", array())
/*
            ->join(array('i' => WAREHOUSE_DB.'.imei'), 
                "   ts.imei = i.imei_sn 
                    AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                    AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
                ", array())
*/
                ->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
                ->joinLeft(array('stg'  => 'store_target')      , 
                    "   st.id = stg.store_id AND 
                    stg.good_id = 142 AND 
                    stg.from_date <= t.created_at AND 
                    stg.to_date >= t.created_at
                    "
                    , array())
                ->where('( s.group_id = ?', 4)
                ->orWhere('s.id IN (?) )', $sub_select)
                ->group(array('s.id', 'st.id', 'a.id'))
                ->order('s.code ASC');

                if ( isset($params['name']) && $params['name'] ) {
                    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
                }

                if ( isset($params['staff_code']) && $params['staff_code'] ) {
                    $select->where('s.code = ?', $params['staff_code']);
                }

                if ( isset($params['phone_number']) && $params['phone_number'] ) {
                    $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
                }

        // Add Filter Area
                if (isset($params['area_id']) && $params['area_id']) {
                    if (is_array($params['area_id']) && count($params['area_id']))
                        $select->where('r.area_id IN (?)', $params['area_id']);
                    elseif (is_numeric($params['area_id']))
                        $select->where('r.area_id = ?', intval($params['area_id']));
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

        // Add Filter Store
                if (isset($params['store']) && $params['store']) {
                    if (is_array($params['store']) && count($params['store']))
                        $select->where('t.store IN (?)', $params['store']);
                    elseif (is_numeric($params['store']))
                        $select->where('t.store = ?', intval($params['store']));
                    else
                        $select->where('1=0', 1);
                }

        // Add Check ASM Table
                if (isset($params['asm']) && $params['asm'] ) {
                    $QAsm = new Application_Model_Asm();
                    $list_regions = $QAsm->get_cache($params['asm']);
                    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

                    if (count($list_regions) > 0)
                        $select->where( 'st.district IN (?)', $list_regions);
                    else
                        $select->where('1=0', 1);
                }

                if (isset($params['store_type']) && $params['store_type']) {
                    if (is_array($params['store_type']) && count($params['store_type']))
                        $select->where('st.org_dealer IN (?)', $params['store_type']);
                    elseif (is_numeric($params['store_type']))
                        $select->where('st.org_dealer = ?', intval($params['store_type']));
                    else
                        $select->where('1=0', 1);
                }

                $main_select = $db->select()
                ->from(array('A' => $select, $temp));

        //echo $main_select;die;
                $log = $db->fetchAll($main_select);
        //print_r($log);die;

                return $log;
            }

    // PC Commission [Target By Store]
            public function report_kpiTargetByStore($params) {

                $db = Zend_Registry::get('db');

                $get = array(
                    'area_name'     => 'a.name',
                    'store_id'      => 'st.id',
                    'store_name'    => 'st.name',
                    'store_type'    => 'o.org_name',
                    'store_target'  => 'spt.target_price',
                    'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
                    'sellout_price' => new Zend_Db_Expr("COALESCE(SUM(gkl.price),0)"),
                );

                $select = $db->select()
                ->from(array('st' => 'store'), $get)
                ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'                , array())
                ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'              , array())
                ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                       , array())
                ->joinLeft(array('spt'  => 'store_price_target'), 
                    "   st.id = spt.store_id 
                    AND spt.from_date <= '".$params['from']."' 
                    AND spt.to_date >= '".$params['to']."' 
                    ", array())
                ->joinLeft(array('t' => 'timing'), 
                    "   st.id = t.store 
                    AND t.created_at >= '".$params['from']." 00:00:00' 
                    AND t.created_at <= '".$params['to']." 23:59:59' 
                    ", array()) 
                ->joinLeft(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array()) 
                ->joinLeft(array('gkl' => 'good_kpi_log'), 
                    "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                    ", array())
                ->group('st.id')
                ->order(array('a.name ASC', 'st.id ASC'));
/*
        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
        }
*/
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Check ASM Table
        if (isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store_type']) && $params['store_type']) {
            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
            else
                $select->where('1=0', 1);
        }

        //echo $select;die;
        $result = $db->fetchAll($select);
        return $result;
    }

    // Sale Commission BKK ORG [old]
    // Cancel Since 01/07/2016 
    public function report_kpiSaleBKK_ORG($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('s' => 'staff'), array(
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        ))
        ->join(array('t'  => 'timing')          , 's.id = t.sales_id'  , array('timing_date' => 't.created_at'))
        ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id', array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('i' => WAREHOUSE_DB.'.imei'), 
            "   ts.imei = i.imei_sn 
            AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
            AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
            ", array())
        ->join(array('st' => 'store')           , 't.store = st.id'             , array())
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_name' => 'a.name'))
        ->join(array('stg'=> 'sales_target')    , "s.id = stg.staff_id AND stg.month = ".$params['month']." ", array('target' => 'stg.target', 'month' => 'stg.month'))
        ->where('st.org_dealer NOT IN (0,1)')
        ->where('a.id IN (73,74,75,76,77,78)')
        ->group('s.id','a.id');

        // Filter
        if (isset($params['from']) && $params['from']) {
            $select->where('t.created_at >= ?', $params['from']." 00:00:00");
        }

        if (isset($params['to']) && $params['to']) {
            $select->where('t.created_at <= ?', $params['to']." 23:59:59");
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {

            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
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


        //echo $select;die;
        $result = $db->fetchAll($select);

        return $result;
    }

    // Sale Commission BKK Dealer
    public function report_kpiSaleBKK_Dealer($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('s' => 'staff'), array(
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)") 
        ))
        ->join(array('g' => 'group'), 's.group_id = g.id', array('group_id' => 'g.id', 'group_name' => 'g.name'))
        ->join(array('t'  => 'timing')          , 's.id = t.sales_id'   , array())
        ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('st' => 'store')           , 't.store = st.id'     , array('store_id' => 'st.id', 'store_name' => 'st.name'))
        ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array('org_name' => 'o.org_name'))
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_id' => 'a.id', 'area_name' => 'a.name'))
        ->join(array('gkl'=> 'good_kpi_log')    , 
            "   ts.product_id = gkl.good_id 
            AND ts.model_id = gkl.color_id 
            AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
            AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
            ", 
                /*
                array(
                    'com_rate' => new Zend_Db_Expr("SUM( CASE WHEN gkl.price > 8000 THEN '80' ELSE '60' END )"),
                    'total_price' => new Zend_Db_Expr("SUM(gkl.price)")  
                ))*/
                array(
                    'com_rate' => new Zend_Db_Expr(
                        "SUM(
                        CASE WHEN 
                        t.created_at >= '2016-05-01 00:00:00' AND 
                        t.created_at <= '2016-05-31 23:59:59' AND 
                        s.code IN (
                        '5901088', '5901090', '5901091', '5901093', '5901094', '5901095', 
                        '5901096', '5901098', '5901099', '5901100', '5901102', '5901103', 
                        '5901108', '5901110', '5901111', '5901113', '5901115', '5901116', 
                        '5901117', '5901118', '5901119', '5901121', '5901169', '5901170', 
                        '5901334') 
                        THEN
                        '0'
                        ELSE

                        CASE WHEN 
                        t.staff_id = t.sales_id 
                        THEN 
                        CASE WHEN  
                        (   SELECT COUNT(ss.id) 
                        FROM hr.store_staff_log AS ss 
                        WHERE ss.store_id = t.store
                        AND ss.is_leader = 0
                        AND FROM_UNIXTIME(joined_at, '%Y-%m-%d 00:00:00') <= t.created_at
                        AND ( FROM_UNIXTIME(released_at, '%Y-%m-%d 23:59:59') >= t.created_at OR released_at IS NULL )
                        ) > 0
                        THEN
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '30' 
                        ELSE '20' END 
                        ELSE 
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '120' 
                        ELSE '80' END 
                        END
                        ELSE 
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '30' 
                        ELSE '20' END 
                        END

                        END
                        )
                        "),
                    'total_price' => new Zend_Db_Expr("SUM(gkl.price)") 
                ))

        ->join(array('i' => WAREHOUSE_DB.'.imei'), 
            "   ts.imei = i.imei_sn 
            AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
            AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00') 
            ", array())
            // ->where('st.org_dealer = 1')
            //->where('a.id IN (73,74,75,76,77,78,79)')
        ->group(array('s.id','st.id'))
        ->order(array('a.name ASC','st.name ASC', 'staff_name ASC'));

        // Filter
        if (isset($params['from']) && $params['from']) {
            $select->where('t.created_at >= ?', $params['from']." 00:00:00");
        }

        if (isset($params['to']) && $params['to']) {
            $select->where('t.created_at <= ?', $params['to']." 23:59:59");
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {

            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select;die;
        $result = $db->fetchAll($select);

        return $result;
    }

    // Sale Commission BKK [via Scan]
    public function report_kpiSaleScanBKK_Dealer($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('s' => 'staff'), array(
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)") 
        ))
        ->join(array('t'  => 'timing')          , 's.id = t.sales_id'   , array())
        ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('st' => 'store')           , 't.store = st.id'     , array('store_id' => 'st.id', 'store_name' => 'st.name'))
        ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array('store_type_id' => 'o.store_type_id', 'org_name' => 'o.org_name'))
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_name' => 'a.name'))
        ->join(array('gkl'=> 'good_kpi_log')    , 
            "   ts.product_id = gkl.good_id 
            AND ts.model_id = gkl.color_id 
            AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
            AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
            ", 
                /*
                array(
                    'com_rate' => new Zend_Db_Expr("SUM( CASE WHEN gkl.price > 8000 THEN '80' ELSE '60' END )"),
                    'total_price' => new Zend_Db_Expr("SUM(gkl.price)")  
                ))*/
                array(
                    'com_rate' => new Zend_Db_Expr(
                        "SUM(
                        CASE WHEN 
                        t.created_at >= '2016-05-01 00:00:00' AND 
                        t.created_at <= '2016-05-31 23:59:59' AND 
                        s.code IN (
                        '5901088', '5901090', '5901091', '5901093', '5901094', '5901095', 
                        '5901096', '5901098', '5901099', '5901100', '5901102', '5901103', 
                        '5901108', '5901110', '5901111', '5901113', '5901115', '5901116', 
                        '5901117', '5901118', '5901119', '5901121', '5901169', '5901170', 
                        '5901334') 
                        THEN
                        '0'
                        ELSE
                        CASE WHEN 
                        t.staff_id = t.sales_id 
                        THEN 
                        CASE WHEN  
                        (   SELECT COUNT(ss.id) 
                        FROM hr.store_staff_log AS ss 
                        WHERE ss.store_id = t.store
                        AND ss.is_leader = 0
                        AND FROM_UNIXTIME(joined_at, '%Y-%m-%d 00:00:00') <= t.created_at
                        AND ( FROM_UNIXTIME(released_at, '%Y-%m-%d 23:59:59') >= t.created_at OR released_at IS NULL )
                        ) > 0
                        THEN
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '30' 
                        ELSE '20' END 
                        ELSE 
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '120' 
                        ELSE '80' END 
                        END
                        ELSE
                        CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                        '30' 
                        ELSE '20' END 
                        END

                        END
                        )
                        "),
                    'total_price' => new Zend_Db_Expr("SUM(gkl.price)") 
                ))

        ->join(array('i' => WAREHOUSE_DB.'.imei'), 
            "   ts.imei = i.imei_sn 
            AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
            AND DATE(i.activated_date) >= (
            CASE 
            WHEN s.group_id = 9 THEN DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d') 
            WHEN s.group_id = 5 THEN DATE_FORMAT(DATE(t.created_at) - INTERVAL 10 DAY, '%Y-%m-%d') 
            ELSE 1 END
            )    
            AND (
            CASE WHEN o.store_type_id <> 1 AND o.org_id <> 19 
            THEN DATE(t.created_at) <= (
            CASE 
            WHEN i.stock_shop_scan IS NULL AND s.group_id = 9 THEN DATE_FORMAT(DATE(i.out_date) + INTERVAL 3 DAY, '%Y-%m-%d')
            WHEN i.stock_shop_scan IS NULL AND s.group_id = 5 THEN DATE_FORMAT(DATE(i.out_date) + INTERVAL 10 DAY, '%Y-%m-%d') 
            WHEN i.stock_shop_scan IS NOT NULL AND s.group_id = 9 THEN DATE_FORMAT(DATE(i.stock_shop_scan) + INTERVAL 3 DAY, '%Y-%m-%d') 
            WHEN i.stock_shop_scan IS NOT NULL AND s.group_id = 5 THEN DATE_FORMAT(DATE(i.stock_shop_scan) + INTERVAL 10 DAY, '%Y-%m-%d') 
            ELSE 1 END )
            ELSE 1=1 END ) 
            ", array())
            // ->where('st.org_dealer = 1')
        ->where('a.id IN (73,74,75,76,77,78,79)')
        ->group(array('s.id','st.id'))
        ->order(array('a.name ASC','st.name ASC'));

        // Filter
        if (isset($params['from']) && $params['from']) {
            $select->where('t.created_at >= ?', $params['from']." 00:00:00");
        }

        if (isset($params['to']) && $params['to']) {
            $select->where('t.created_at <= ?', $params['to']." 23:59:59");
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {

            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select;die;
        $result = $db->fetchAll($select);

        return $result;
    }

    // Sale Commission UpCountry 
    public function report_kpiSale($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('st' => 'store'), array())
        ->join(array('t'  => 'timing')          , 'st.id = t.store'   , array())
        ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_id' => 'a.id', 'area_name' => 'a.name'))
        ->join(array('gkl'=> 'good_kpi_log')    , 
            "   ts.product_id = gkl.good_id 
            AND ts.model_id = gkl.color_id 
            AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
            AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
            ", 
            array(
                'com_rate' => new Zend_Db_Expr(
                    "SUM(
                    CASE WHEN 
                    t.created_at >= '2016-05-01 00:00:00' AND 
                    t.created_at <= '2016-05-31 23:59:59' AND 
                    s.code IN (
                    '5901088', '5901090', '5901091', '5901093', '5901094', '5901095', 
                    '5901096', '5901098', '5901099', '5901100', '5901102', '5901103', 
                    '5901108', '5901110', '5901111', '5901113', '5901115', '5901116', 
                    '5901117', '5901118', '5901119', '5901121', '5901169', '5901170', 
                    '5901334') 
                    THEN
                    '0'
                    WHEN 
                    t.created_at >= '2016-07-23 00:00:00' AND 
                    t.created_at <= '2016-07-23 23:59:59' AND 
                    a.id IN (41, 53) AND
                    s.code IN (
                    '5800892', '5701988', '5600297', '5800953', '5700314', '5800342',
                    '5702085', '5801863', '5500558', '5700933', '5700053', '5800978',
                    '5600600', '5800788', '5800264', '5600009', '5600121', '5701029', 
                    '5400213', '5802213', '5700638', '5600327', '5700853', '5801485', 
                    '5700560', '5400290', '5800882', '5701749', '5701746', '5702569', 
                    '5702190', '5800504', '5700415', '5700247', '5600428', '5600478', 
                    '5801163', '5800109', '5801218', '5700306', '5800604') 
                    THEN
                    '0'
                    ELSE
                    CASE WHEN 
                    t.staff_id = t.sales_id 
                    THEN 
                    CASE WHEN  
                    (   SELECT COUNT(ss.id) 
                    FROM hr.store_staff_log AS ss 
                    WHERE ss.store_id = t.store
                    AND ss.is_leader = 0
                    AND FROM_UNIXTIME(joined_at, '%Y-%m-%d 00:00:00') <= t.created_at
                    AND ( FROM_UNIXTIME(released_at, '%Y-%m-%d 23:59:59') >= t.created_at OR released_at IS NULL )
                    ) > 0
                    THEN
                    CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                    '30' 
                    ELSE '20' END 
                    ELSE 
                    CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                    '120' 
                    ELSE '80' END 
                    END
                    ELSE 
                    CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                    '30' 
                    ELSE '20' END 
                    END

                    END
                    )
                    "),
                'total_price' => new Zend_Db_Expr(
                    "SUM(
                    CASE WHEN 
                    t.created_at >= '2016-07-23 00:00:00' AND 
                    t.created_at <= '2016-07-23 23:59:59' AND 
                    a.id IN (41, 53) AND
                    s.code IN (
                    '5800892', '5701988', '5600297', '5800953', '5700314', '5800342',
                    '5702085', '5801863', '5500558', '5700933', '5700053', '5800978',
                    '5600600', '5800788', '5800264', '5600009', '5600121', '5701029', 
                    '5400213', '5802213', '5700638', '5600327', '5700853', '5801485', 
                    '5700560', '5400290', '5800882', '5701749', '5701746', '5702569', 
                    '5702190', '5800504', '5700415', '5700247', '5600428', '5600478', 
                    '5801163', '5800109', '5801218', '5700306', '5800604') 
                    THEN
                    '0' 
                    ELSE
                    gkl.price
                    END
                    )
                    ") 
            ))
->join(array('i' => WAREHOUSE_DB.'.imei'), 
    "   ts.imei = i.imei_sn 
    AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
    AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    ", array())
->joinLeft(array('s' => 'staff'), 't.sales_id = s.id', array(
    'staff_id'   => 's.id',
    'staff_code' => 's.code',
    'group_id'   => 's.group_id',
    'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
))
->joinLeft(array('gr' => 'group'), 's.group_id = gr.id', array('gr_id' => 'gr.id', 'group_name' => 'gr.name'))
            //->where('a.id NOT IN (48,49,73,74,75,76,77,78,79)')
->group(array('s.id','a.id'))
->order(array("FIND_IN_SET(gr.id, '9,27,16,5,35,28')", "s.code ASC"));

        // Filter
if (isset($params['from']) && $params['from']) {
    $select->where('t.created_at >= ?', $params['from']." 00:00:00");
}

if (isset($params['to']) && $params['to']) {
    $select->where('t.created_at <= ?', $params['to']." 23:59:59");
}

if ( isset($params['name']) && $params['name'] ) {
    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
}

if ( isset($params['staff_code']) && $params['staff_code'] ) {
    $select->where('s.code = ?', $params['staff_code']);
}

if ( isset($params['phone_number']) && $params['phone_number'] ) {
    $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
if (isset($params['store']) && $params['store']) {
    if (is_array($params['store']) && count($params['store']))
        $select->where('t.store IN (?)', $params['store']);
    elseif (is_numeric($params['store']))
        $select->where('t.store = ?', intval($params['store']));
    else
        $select->where('1=0', 1);
}

        // Add Filter Store Type
if (isset($params['store_type']) && $params['store_type']) {

    if (is_array($params['store_type']) && count($params['store_type']))
        $select->where('st.org_dealer IN (?)', $params['store_type']);
    elseif (is_numeric($params['store_type']))
        $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select;die;
$result = $db->fetchAll($select);

return $result;
}

    // Sale Commission UpCountry [via Scan]
public function report_kpiSaleScan($params) {
    $db = Zend_Registry::get('db');

    $select = $db->select()
    ->from(array('s' => 'staff'), array(
        'staff_id'   => 's.id',
        'staff_code' => 's.code',
        'group_id'   => 's.group_id',
        'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
    ))
    ->join(array('t'  => 'timing')          , 's.id = t.sales_id'   , array())
    ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
    ->join(array('st' => 'store')           , 't.store = st.id'     , array())
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id', array('store_type_id' => 'o.store_type_id'))
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_name' => 'a.name'))
    ->join(array('gr' => 'group')           , 's.group_id = gr.id'   , array('gr_id' => 'gr.id', 'group_name' => 'gr.name'))
    ->join(array('gkl'=> 'good_kpi_log')    , 
        "   ts.product_id = gkl.good_id 
        AND ts.model_id = gkl.color_id 
        AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
        AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
        ", 
        array(
            'com_rate' => new Zend_Db_Expr(
                "SUM(
                CASE WHEN 
                t.created_at >= '2016-05-01 00:00:00' AND 
                t.created_at <= '2016-05-31 23:59:59' AND 
                s.code IN (
                '5901088', '5901090', '5901091', '5901093', '5901094', '5901095', 
                '5901096', '5901098', '5901099', '5901100', '5901102', '5901103', 
                '5901108', '5901110', '5901111', '5901113', '5901115', '5901116', 
                '5901117', '5901118', '5901119', '5901121', '5901169', '5901170', 
                '5901334') 
                THEN
                '0'
                WHEN 
                t.created_at >= '2016-07-23 00:00:00' AND 
                t.created_at <= '2016-07-23 23:59:59' AND 
                a.id IN (41, 53) AND
                s.code IN (
                '5800892', '5701988', '5600297', '5800953', '5700314', '5800342',
                '5702085', '5801863', '5500558', '5700933', '5700053', '5800978',
                '5600600', '5800788', '5800264', '5600009', '5600121', '5701029', 
                '5400213', '5802213', '5700638', '5600327', '5700853', '5801485', 
                '5700560', '5400290', '5800882', '5701749', '5701746', '5702569', 
                '5702190', '5800504', '5700415', '5700247', '5600428', '5600478', 
                '5801163', '5800109', '5801218', '5700306', '5800604') 
                THEN
                '0'
                ELSE
                CASE WHEN 
                t.staff_id = t.sales_id 
                THEN 
                CASE WHEN  
                (   SELECT COUNT(ss.id) 
                FROM hr.store_staff_log AS ss 
                WHERE ss.store_id = t.store
                AND ss.is_leader = 0
                AND FROM_UNIXTIME(joined_at, '%Y-%m-%d 00:00:00') <= t.created_at
                AND ( FROM_UNIXTIME(released_at, '%Y-%m-%d 23:59:59') >= t.created_at OR released_at IS NULL )
                ) > 0
                THEN
                CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                '30' 
                ELSE '20' END 
                ELSE 
                CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                '120' 
                ELSE '80' END 
                END
                ELSE 
                CASE WHEN ts.product_id = 17 OR ts.product_id = 128 OR ts.product_id = 142 THEN 
                '30' 
                ELSE '20' END 
                END

                END
                )
                "),
            'total_price' => new Zend_Db_Expr(
                "SUM(
                CASE WHEN 
                t.created_at >= '2016-07-23 00:00:00' AND 
                t.created_at <= '2016-07-23 23:59:59' AND 
                a.id IN (41, 53) AND
                s.code IN (
                '5800892', '5701988', '5600297', '5800953', '5700314', '5800342',
                '5702085', '5801863', '5500558', '5700933', '5700053', '5800978',
                '5600600', '5800788', '5800264', '5600009', '5600121', '5701029', 
                '5400213', '5802213', '5700638', '5600327', '5700853', '5801485', 
                '5700560', '5400290', '5800882', '5701749', '5701746', '5702569', 
                '5702190', '5800504', '5700415', '5700247', '5600428', '5600478', 
                '5801163', '5800109', '5801218', '5700306', '5800604') 
                THEN
                '0' 
                ELSE
                gkl.price
                END
                )
                ") 
        ))

    ->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= (
        CASE 
        WHEN s.group_id = 9 THEN DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d') 
        WHEN s.group_id = 5 THEN DATE_FORMAT(DATE(t.created_at) - INTERVAL 10 DAY, '%Y-%m-%d') 
        ELSE 1 END
        )     
        AND (
        CASE WHEN o.store_type_id <> 1 AND o.org_id <> 19 
        THEN DATE(t.created_at) <= (
        CASE 
        WHEN i.stock_shop_scan IS NULL AND s.group_id = 9 THEN DATE_FORMAT(DATE(i.out_date) + INTERVAL 3 DAY, '%Y-%m-%d')
        WHEN i.stock_shop_scan IS NULL AND s.group_id = 5 THEN DATE_FORMAT(DATE(i.out_date) + INTERVAL 10 DAY, '%Y-%m-%d') 
        WHEN i.stock_shop_scan IS NOT NULL AND s.group_id = 9 THEN DATE_FORMAT(DATE(i.stock_shop_scan) + INTERVAL 3 DAY, '%Y-%m-%d') 
        WHEN i.stock_shop_scan IS NOT NULL AND s.group_id = 5 THEN DATE_FORMAT(DATE(i.stock_shop_scan) + INTERVAL 10 DAY, '%Y-%m-%d') 
        ELSE 1 END )
        ELSE 1=1 END ) 
        ", array())
    ->where('a.id NOT IN (48,49,73,74,75,76,77,78,79)')
    ->group(array('s.id','a.id'));

        // Filter
    if (isset($params['from']) && $params['from']) {
        $select->where('t.created_at >= ?', $params['from']." 00:00:00");
    }

    if (isset($params['to']) && $params['to']) {
        $select->where('t.created_at <= ?', $params['to']." 23:59:59");
    }

    if ( isset($params['name']) && $params['name'] ) {
        $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
    }

    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }

    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('t.store IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('t.store = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }

        // Add Filter Store Type
    if (isset($params['store_type']) && $params['store_type']) {

        if (is_array($params['store_type']) && count($params['store_type']))
            $select->where('st.org_dealer IN (?)', $params['store_type']);
        elseif (is_numeric($params['store_type']))
            $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select;die;
    $result = $db->fetchAll($select);

    return $result;
}

    // function comparea sellout of F1 Model for PC-KPI
public function compare_f1_sellout($staff_code,$params) {
    $db = Zend_Registry::get('db');

    $select_first = $db->select()
    ->from(array('t' => 'timing'), array('t.staff_id'))
    ->joinLeft(array('ts' => 'timing_sale') , 
        't.id = ts.timing_id AND ts.product_id = 17' , array('sellout_01' => new Zend_Db_Expr("COUNT(ts.imei)")))
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
        AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
        ", array())
    ->join(array('st' => 'store')           , 't.store = st.id'             , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array('area_name' => 'a.name'))
    ->where("t.created_at >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(?),INTERVAL 1 DAY),INTERVAL -2 MONTH),'%Y-%m-%d 00:00:00')", $params['from'])
    ->where("t.created_at <= DATE_FORMAT(LAST_DAY(DATE_ADD(?,INTERVAL -1 MONTH)), '%Y-%m-%d 23:59:59')", $params['from'])
    ->group(array('t.staff_id'));

    $select_second = $db->select()
    ->from(array('t' => 'timing'), array('t.staff_id'))
    ->joinLeft(array('ts' => 'timing_sale') , 
        't.id = ts.timing_id AND ts.product_id = 17' , array('sellout_02' => new Zend_Db_Expr("COUNT(ts.imei)")))
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
        AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
        ", array())
    ->join(array('st' => 'store')           , 't.store = st.id'             , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array('area_name' => 'a.name'))
    ->where("t.created_at >= ?", $params['from']." 00:00:00")
    ->where("t.created_at <= ?", $params['to']." 23:59:59")
    ->group(array('t.staff_id'));

    $main_select = $db->select()
    ->from(array('s' => 'staff'), array( 'staff_id' => 's.id', 'staff_code' => 's.code'))
    ->joinLeft(array('AAA' => $select_first), 'AAA.staff_id = s.id ', array('sellout_01' => 'COALESCE(AAA.sellout_01,0)'))
    ->joinLeft(array('BBB' => $select_second), 'BBB.staff_id = s.id ', array('sellout_02' => 'COALESCE(BBB.sellout_02,0)'))
    ->where('s.code = ?', $staff_code);

        //echo $main_select; die;
    $result = $db->fetchRow($main_select);

    return $result;

}

public function getStaffGradeList($params) {

    $db = Zend_Registry::get('db');

    $select = $db->select()
    ->from(array('gs' => 'grade_staff'), array('gs.*'))
    ->join(array('gl' => 'grade_level') , 'gs.level_id = gl.id' , array('grade_name' => 'gl.name', 'percent' => 'gl.percent'))
    ->join(array('s'  => 'staff')       , 'gs.staff_id = s.id'  , array('staff_code' => 's.code'));


    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $select->where( 'gs.from_date <= ?', $params['from']);
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $select->where( 'gs.to_date >= ?', $params['to']);
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {

        $sub_select_before = $db->select()
        ->from(array('gs1'=>'grade_staff'),'id')
        ->where('gs1.from_date <= ?', $params['from'])
        ->where('gs1.to_date <= ?', $params['from']);

        $sub_select_after = $db->select()
        ->from(array('gs2'=>'grade_staff'),'id')
        ->where('gs2.from_date >= ?', $params['to'])
        ->where('gs2.to_date >= ?', $params['to']);

        $select->where('gs.id NOT IN (?)', $sub_select_before);
        $select->where('gs.id NOT IN (?)', $sub_select_after);

    }

        //echo $select;die;
    $result = $db->fetchAll($select);

    return $result;
}

public function comission_eol($params) {

    $db = Zend_Registry::get('db');

    $get = array(
        'area_name' => 'a.name',
        'staff_id'   => 's.id', 
        'staff_code' => 's.code',
        'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'total_unit' => new Zend_Db_Expr("COUNT(ts.imei)"),
    );

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('t'  => 'timing')      , 'st.id = t.store'     , array())
    ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d') 
        ", array())
    ->join(array('s'  => 'staff')               , 't.staff_id = s.id'           , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')  , 'i.good_id = g.id'            , array())
    ->join(array('rm' => 'regional_market')     , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')                , 'rm.area_id = a.id'           , array())
    ->where('st.rank <> ?', 5)
    ->where('ts.product_id IN (?)', array(371))
            // ->where('t.created_at >= ?', $params['from']." 00:00:00")
            // ->where('t.created_at <= ?', $params['to']." 23:59:59")
    ->where('t.created_at >= ?', '2019-04-06 00:00:00')
    ->where('t.created_at <= ?', '2019-04-06 23:59:59')
    ->group('s.id');

        // echo $select; die;
    $result = $db->fetchAll($select);

    return $result;
}

public function getAllSelloutByStaff($params) {

    $db = Zend_Registry::get('db');

    $select = $db->select()
    ->from(array('s' => 'staff'), array(
        'total_sellout' => new Zend_Db_Expr(
            "   COUNT(
            CASE 
            WHEN 
            t.created_at >= '2017-11-01 00:00:00' 
            AND t.created_at <= '2017-11-30 23:59:59'
            AND t.store IN (8949,8943,8370,8474,12056,12057,14322,14323,20367,10698)
            THEN 
            NULL
            WHEN 
            t.created_at >= '2018-02-01 00:00:00'
            AND t.created_at <= '2018-02-28 23:59:59'
            AND t.store IN (
            4489,3744,11874,10390,16014,1637,218,21124,3690,12329,
            8477,19677,7342,7370,21066,13291,16583,18082,17222,11076,
            16630,18715,18714,9330,1122,1148)
            THEN 
            NULL 
            WHEN 
            t.created_at >= '2018-03-01 00:00:00'
            AND t.created_at <= '2018-03-31 23:59:59'
            AND t.store IN (11874,3744,14613,9875,9507,16558,18712)
            THEN 
            NULL 
            WHEN 
            t.created_at >= '2018-09-01 00:00:00'
            AND t.created_at <= '2018-09-15 23:59:59'
            AND t.staff_id IN (26374)
            THEN 
            NULL 

            WHEN 
            t.staff_id IN (4296,11698,27830,30171,35852) AND s.group_id = 30 
            THEN 
            (CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN NULL ELSE ts.imei END) 
            WHEN 
            t.staff_id IN (30506) AND s.group_id = 30 
            THEN 
            (CASE WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-13 23:59:59' THEN NULL ELSE ts.imei END) 

            ELSE 
            ts.imei 
            END
            )
            ") 
    ))
    ->join(array('t'    => 'timing')        , 's.id = t.staff_id'   , array())
    ->join(array('ts'   => 'timing_sale')   , 't.id = ts.timing_id' , array())
    ->join(array('st'   => 'store')         , 't.store = st.id'     , array())
    ->join(array('i'    => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d') 
        ", array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)") ))
    ->join(array('gkl'=> 'good_kpi_log')    , 
        "   ts.product_id = gkl.good_id 
        AND ts.model_id = gkl.color_id 
        AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
        AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
        ", 
        array('total_price' => new Zend_Db_Expr("SUM(gkl.price)") ))
    ->where('st.rank <> ?', 5)
    ->where('t.created_at >= ?', $params['from']." 00:00:00")
    ->where('t.created_at <= ?', $params['to']." 23:59:59")
    ->where('s.code = ?', $params['staff_code'])
    ->group('s.id');

        //echo $select;die;
    $result = $db->fetchRow($select);

    return $result;
}

    // Get Reward Target 
public function getTargetReward($params) {
    $db = Zend_Registry::get('db');

    $temp = array(
        'staff_id'  => 's.id', 
        'kpi'       => 'gkl.kpi', 
    );

    $sub_select = $db->select()
    ->from(array('s1'=>'staff'),'id')
    ->where('s1.id <> t.sales_id')
    ->where('s1.group_id <> ?', 4);
        //print_r($temp); die;

        // Filter date
    $str_to = "";
    $str_from = "";
    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $str_from = "AND t.created_at >= '". $params['from']. " 00:00:00' ";
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $str_from = "AND t.created_at >= '". $params['from']." 00:00:00' ";
        $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
    }

    $select = $db->select()
    ->from(array('s' => 'staff'), $temp)
    ->joinLeft(array('t'    => 'timing'), "s.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> '' ".$str_from.$str_to  ,array())
    ->joinLeft(array('st'   => 'store')             , 't.store = st.id'             , array())
    ->joinLeft(array('r'    => 'regional_market')   , 'st.regional_market = r.id'   , array())
    ->joinLeft(array('d'    => 'regional_market')   , 'st.district = d.id'          , array())
    ->joinLeft(array('a'    => 'area')              , 'r.area_id = a.id'            , array())
    ->join(array('ts'       =>  'timing_sale')      , 't.id = ts.timing_id '        , array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
        AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
        ", array())
    ->join(array('gkl'=> 'good_kpi_log')    , 
        "   ts.product_id = gkl.good_id 
        AND ts.model_id = gkl.color_id 
        AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
        AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
        ", 
        array())
    ->where('( s.group_id = ?', 4)
    ->orWhere('s.id IN (?) )', $sub_select)
    ->where('s.id = ?', $params['staff_id'])
    ->order('t.created_at DESC')
    ->limit($params['limit'],0);


    if ( isset($params['name']) && $params['name'] ) {
        $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
    }

    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }

    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
    }

        // Add Filter Area
    if (isset($params['area_id']) && $params['area_id']) {
        if (is_array($params['area_id']) && count($params['area_id']))
            $select->where('r.area_id IN (?)', $params['area_id']);
        elseif (is_numeric($params['area_id']))
            $select->where('r.area_id = ?', intval($params['area_id']));
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

        // Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('t.store IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('t.store = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }

        // Add Check ASM Table
    if (isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

        if (count($list_regions) > 0)
            $select->where( 'st.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }

    if (isset($params['store_type']) && $params['store_type']) {
        if (is_array($params['store_type']) && count($params['store_type']))
            $select->where('st.org_dealer IN (?)', $params['store_type']);
        elseif (is_numeric($params['store_type']))
            $select->where('st.org_dealer = ?', intval($params['store_type']));
        else
            $select->where('1=0', 1);
    }

    $main_select = $db->select()
    ->from(array('A' => $select), array('total_kpi' => new Zend_Db_Expr("SUM(A.kpi)") ));

        //echo $main_select;die;
    $log = $db->fetchRow($main_select);
        //print_r($log);die;

    return $log;

}

    // Get Reward Target F1s
public function getTargetHeroProductReward($params) {
    $db = Zend_Registry::get('db');

    $temp = array(
        'staff_id'  => 's.id', 
        'kpi'       => 'gkl.kpi', 
    );

    $sub_select = $db->select()
    ->from(array('s1'=>'staff'),'id')
    ->where('s1.id <> t.sales_id')
    ->where('s1.group_id <> ?', 4);
        //print_r($temp); die;

        // Filter date
    $str_to = "";
    $str_from = "";
    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $str_from = "AND t.created_at >= '". $params['from']. " 00:00:00' ";
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $str_from = "AND t.created_at >= '". $params['from']." 00:00:00' ";
        $str_to = "AND t.created_at <= '". $params['to']. " 23:59:59' ";
    }

    $select = $db->select()
    ->from(array('s' => 'staff'), $temp)
    ->joinLeft(array('t'    => 'timing'), "s.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> '' ".$str_from.$str_to  ,array())
    ->joinLeft(array('st'   => 'store')             , 't.store = st.id'             , array())
    ->joinLeft(array('r'    => 'regional_market')   , 'st.regional_market = r.id'   , array())
    ->joinLeft(array('d'    => 'regional_market')   , 'st.district = d.id'          , array())
    ->joinLeft(array('a'    => 'area')              , 'r.area_id = a.id'            , array())
    ->join(array('ts'       =>  'timing_sale')      , 't.id = ts.timing_id AND ts.product_id = 142', array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
        AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
        ", array())
    ->join(array('gkl'=> 'good_kpi_log')    , 
        "   ts.product_id = gkl.good_id 
        AND ts.model_id = gkl.color_id 
        AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
        AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
        ", 
        array())
    ->where('( s.group_id = ?', 4)
    ->orWhere('s.id IN (?) )', $sub_select)
    ->where('s.id = ?', $params['staff_id'])
    ->order('t.created_at DESC')
    ->limit($params['limit'],0);


    if ( isset($params['name']) && $params['name'] ) {
        $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
    }

    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }

    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
    }

        // Add Filter Area
    if (isset($params['area_id']) && $params['area_id']) {
        if (is_array($params['area_id']) && count($params['area_id']))
            $select->where('r.area_id IN (?)', $params['area_id']);
        elseif (is_numeric($params['area_id']))
            $select->where('r.area_id = ?', intval($params['area_id']));
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

        // Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('t.store IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('t.store = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }

        // Add Check ASM Table
    if (isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

        if (count($list_regions) > 0)
            $select->where( 'st.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }

    if (isset($params['store_type']) && $params['store_type']) {
        if (is_array($params['store_type']) && count($params['store_type']))
            $select->where('st.org_dealer IN (?)', $params['store_type']);
        elseif (is_numeric($params['store_type']))
            $select->where('st.org_dealer = ?', intval($params['store_type']));
        else
            $select->where('1=0', 1);
    }

    $main_select = $db->select()
    ->from(array('A' => $select), array('total_kpi' => new Zend_Db_Expr("SUM(A.kpi)") ));

        //echo $main_select;die;
    $log = $db->fetchRow($main_select);
        //print_r($log);die;

    return $log;

}

public function get_sale_com_target($area_id,$staff_id, $from, $to) { 

    $db = Zend_Registry::get('db');
/*
        $select = $db->select()
            ->from(array('s' => 'staff'), array('sale_target' => 'sct.target'))
            ->join(array('sct' => 'sale_com_target'), 's.id = sct.staff_id', array())
            ->where('sct.from_date >= ?', $from)
            ->where('sct.to_date >= ?', $to)
            ->where('sct.staff_id = ?', $staff_id)
            ->where('sct.area_id = ?', $area_id);
*/
            $select = $db->select()
            ->from(array('s' => 'staff'), array('sale_target' => 'ost.target', 'com' => 'ost.com'))
            ->join(array('ost' => 'oppo_sale_target'), 's.id = ost.staff_id', array())
            ->where('ost.from_date >= ?', $from)
            ->where('ost.to_date <= ?', $to)
            //->where('ost.com = ?', 1)
            ->where('ost.staff_id = ?', $staff_id)
            ->where('ost.area_id = ?', $area_id);

        //echo $select;
            $result = $db->fetchRow($select);
            return $result;

        }

        public function com_asm_rate($score) { 

            if ($score >= 100) { return 0.9; }
            if ($score >= 80) { return 0.8; }
            if ($score >= 60) { return 0.7; }
            if ($score >= 48) { return 0.5; }
            if ($score >= 42) { return 0.3; }
            return 0;
        }

        public function com_rm_rate($score) { 

            if ($score >= 100) { return 0.8; }
            if ($score >= 80) { return 0.6; }
            if ($score >= 60) { return 0.5; }
            if ($score >= 48) { return 0.3; }
            if ($score >= 42) { return 0.2; }
            return 0;
        }

        public function com_asm_rate_2017($score) { 

            if ($score >= 60) { return 1; }
            if ($score >= 50) { return 0.8; }
            if ($score >= 40) { return 0.6; }
            return 0.2;
        }

        public function com_asm_rate_2018($score) { 

            if ($score >= 90) { return 1.3; }
            if ($score >= 80) { return 1.2; }
            if ($score >= 70) { return 1; }
            if ($score >= 60) { return 0.9; }
            if ($score >= 50) { return 0.7; }
            if ($score >= 40) { return 0.5; }
            return 0.2;
        }

        public function get_sale_com_asm($area_id, $from, $to) { 

            $db = Zend_Registry::get('db');

            $select = $db->select()
            ->from(array('sca' => 'sale_com_asm'), array('asm_score' => 'sca.score'))
            ->where('sca.from_date >= ?', $from)
            ->where('sca.to_date >= ?', $to)
            ->where('sca.area_id = ?', $area_id);

        //echo $select;
            $result['score'] = $db->fetchOne($select);

            if ($from >= '2018-07-01') {
                $result['gfk'] = $this->com_asm_rate_2018($result['score']);
            } else if ($from >= '2017-01-01') {
                $result['gfk'] = $this->com_asm_rate_2017($result['score']);
            } else {
                $result['gfk'] = $this->com_asm_rate($result['score']);
            }

            return $result;

        }

        public function get_sale_com_rm($staff_id,$area_id, $from, $to) { 

            $db = Zend_Registry::get('db');

            $select = $db->select()
            ->from(array('scr' => 'sale_com_rm'), array('asm_score' => 'scr.score'))
            ->where('scr.from_date >= ?', $from)
            ->where('scr.to_date >= ?', $to)
            ->where('scr.area_id = ?', $area_id)
            ->where('scr.staff_id = ?', $staff_id);

        //echo $select;
            $result['score'] = $db->fetchOne($select);
        //$result['gfk'] = $this->com_rm_rate($result['score']);

            if ($from >= '2018-07-01') {
                $result['gfk'] = $this->com_asm_rate_2018($result['score']);
            } else if ($from >= '2017-01-01') {
                $result['gfk'] = $this->com_asm_rate_2017($result['score']);
            } else {
                $result['gfk'] = $this->com_rm_rate($result['score']);
            }

            return $result;

        }

    // Sale Commission UpCountry 2017
        public function com_sale_bkk_2017($params) {
            $db = Zend_Registry::get('db');

        // Check Punish Memo By Store [Sale]
            $select_pms = $db->select()
            ->from(array('pms' => 'punish_memo_store'), array('pms.*' ))
            //->where('pms.flag_sale = ?', 1)
            ->where('pms.from_date >= ?', $params['from'])
            ->where('pms.to_date <= ?', $params['to']);

            $bkk_list = array(
                81,82,83,85,86,90,91,92,94,95,97,98,99,100,
                101,103,104,105,106,107,108,110,115,116,117);

        // Sale BKK Rate
            $hero_rate = 0;
            $normal_rate = 0;

            if (isset($params['com_leader']) && $params['com_leader'] == ASM_ID) {

                if ( in_array($params['area_id'][0], $bkk_list) ) { $hero_rate = 20; $normal_rate = 10; } 
                else { $hero_rate = 30; $normal_rate = 15; }

                $select_pms->where('pms.flag_asm = ?', 1);

            } else if (isset($params['com_leader']) && $params['com_leader'] == RM_ID) {

                if ( in_array($params['area_id'][0], $bkk_list) ) { $hero_rate = 15; $normal_rate = 10; } 
                else { $hero_rate = 20; $normal_rate = 10; }

                $select_pms->where('pms.flag_rd = ?', 1);

            } else {
                $select_pms->where('pms.flag_sale = ?', 1);
            }

        //echo $select_pms;
            $result_pms = $db->fetchAll($select_pms);

            $punish_store = '';
            foreach ($result_pms as $key => $value) { $punish_store .= $value['store'].","; }
            $punish_store = rtrim($punish_store, ",");

        //echo "Punish Store List : ".$punish_store;

            $punish_sql_unit = '';
            $punish_sql_price = '';
            if ( $punish_store != '') {
                $punish_sql_unit = "
                WHEN t.created_at >= '".$params['from']." 00:00:00' 
                AND t.created_at <= '".$params['to']." 23:59:59' 
                AND t.store IN (".$punish_store.") THEN NULL 
                ";
                $punish_sql_price = "
                WHEN t.created_at >= '".$params['from']." 00:00:00' 
                AND t.created_at <= '".$params['to']." 23:59:59' 
                AND t.store IN (".$punish_store.") THEN 0 
                ";
            }

            $select = $db->select()
            ->from(array('st' => 'store'), array())
            ->join(array('t'  => 'timing')          , 'st.id = t.store', array())
            ->join(array('ts' => 'timing_sale'), 
                't.id = ts.timing_id', 
                array('total_unit' => new Zend_Db_Expr(
                    "   COUNT(
                    CASE 
                    WHEN 1=0 THEN NULL 

                    ".$punish_sql_unit." 

                    ELSE 
                    ts.imei 
                    END
                    )
                    ")
            )
            )
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_id' => 'a.id', 'area_name' => 'a.name'))
            
            ->join(array('gkl'=> 'good_kpi_log')    , 
                "   ts.product_id = gkl.good_id 
                AND ts.model_id = gkl.color_id 
                AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
                AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
                ", 
                array('total_price' => new Zend_Db_Expr(
                    "   SUM(
                    CASE 
                    WHEN 1=0 THEN NULL 

                    ".$punish_sql_price." 

                    ELSE 
                    gkl.price
                    END
                    )
                    ")
            )
            );


        //if ( !in_array($params['area_id'][0], $bkk_list) && !isset($params['com_leader']) ) {
            if ( !isset($params['com_leader']) ) {

            // UPC + BKK Sale
                $select->join(array('ac' => 'area_control'), 'st.sub_district = ac.sub_district', array());
                $select->join(array('sa' => 'sub_area'), 'ac.sub_area_id = sa.id', array());
                $select->join(array('i' => WAREHOUSE_DB.'.imei'), 
                    "   ts.imei = i.imei_sn 
                    AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                    AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
                    ", array(
                        'com_rate' => new Zend_Db_Expr(
                            "SUM(
                            CASE 
                            WHEN t.created_at <= '2017-02-28 23:59:59' THEN 

                            CASE WHEN ts.product_id = 128 OR ts.product_id = 142 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '80'
                            WHEN sa.com_rate = 2 THEN '120'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN

                            CASE WHEN ts.product_id = 142 OR ts.product_id = 208 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '80'
                            WHEN sa.com_rate = 2 THEN '120'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2017-03-18 00:00:00' AND t.created_at <= '2017-10-31 23:59:59' THEN

                            CASE WHEN ts.product_id = 208 OR ts.product_id = 210 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '80'
                            WHEN sa.com_rate = 2 THEN '120'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 



                            WHEN t.created_at >= '2017-11-01 00:00:00' AND t.created_at <= '2017-11-30 23:59:59' THEN

                            CASE WHEN ts.product_id = 208 OR ts.product_id = 299 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2017-12-01 00:00:00' AND t.created_at <= '2018-03-31 23:59:59' THEN

                            CASE WHEN ts.product_id = 299 OR ts.product_id = 301 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 299 OR ts.product_id = 301 OR 
                            ts.product_id = 310 OR ts.product_id = 311 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 310 OR ts.product_id = 311 OR 
                            ts.product_id = 312 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 310 OR ts.product_id = 311 THEN
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 310 OR ts.product_id = 345 THEN
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-30 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 345 THEN
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2019-02-10 23:59:59' THEN

                            CASE WHEN 
                            ts.product_id = 345 OR ts.product_id = 353 THEN
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2019-02-11 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN

                            CASE 
                            WHEN ts.product_id = 353 THEN 
                            '100' 
                            WHEN ts.product_id = 345 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END 
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-04-30 23:59:59' THEN

                            CASE 
                            WHEN ts.product_id = 353 THEN 
                            '100' 
                            WHEN ts.product_id = 345 OR ts.product_id = 371 THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END 
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 

                            ".$punish_sql_price."

                            ELSE 
                            CASE 
                            WHEN ts.product_id IN (353,399,403) THEN 
                            '100' 
                            WHEN ts.product_id IN (371,390,392,395) THEN 
                            CASE 
                            WHEN sa.com_rate = 1 THEN '40'
                            WHEN sa.com_rate = 2 THEN '60'
                            END 
                            ELSE  
                            CASE 
                            WHEN sa.com_rate = 1 THEN '10'
                            WHEN sa.com_rate = 2 THEN '20'
                            END
                            END 
                            END
                            )
                            "),
));

} else {

            // ASM, RM
    $select->join(array('i' => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
        AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
        ", array(
            'com_rate' => new Zend_Db_Expr(
                "SUM(
                CASE 
                WHEN t.created_at <= '2017-02-28 23:59:59' THEN 

                CASE WHEN ts.product_id = 128 OR ts.product_id = 142 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN

                CASE WHEN ts.product_id = 142 OR ts.product_id = 208 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2017-03-18 00:00:00' AND t.created_at <= '2017-10-31 23:59:59' THEN

                CASE WHEN ts.product_id = 208 OR ts.product_id = 210 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2017-11-01 00:00:00' AND t.created_at <= '2017-11-30 23:59:59' THEN
                CASE WHEN ts.product_id = 208 OR ts.product_id = 299 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2017-12-01 00:00:00' AND t.created_at <= '2018-03-31 23:59:59' THEN
                CASE WHEN ts.product_id = 299 OR ts.product_id = 301 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 299 OR ts.product_id = 301 OR 
                ts.product_id = 310 OR ts.product_id = 311 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 310 OR ts.product_id = 311 OR 
                ts.product_id = 312 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 310 OR ts.product_id = 311 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 310 OR ts.product_id = 345 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-30 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 345 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 345 OR ts.product_id = 353 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-04-30 23:59:59' THEN
                CASE WHEN 
                ts.product_id = 345 OR ts.product_id = 353 OR ts.product_id = 371 THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 

                ".$punish_sql_price."

                ELSE 
                CASE WHEN 
                ts.product_id IN (353,399,403,371,390,392,395) THEN '".$hero_rate."' 
                ELSE '".$normal_rate."' END 
                END
                )
                "),
        ));

}

$select->where('st.rank <> ?', 5);
        //->where('a.id IN (73,74,75,76,77,78,79,80)')
        //->group(array('s.id','a.id'))

if (isset($params['com_leader']) && $params['com_leader']) {
    $select->join(array('asm' => 'asm'), "(CASE WHEN asm.type = 2 THEN a.id ELSE rm.id END) = asm.area_id", array());
    $select->join(array('s' => 'staff'), 'asm.staff_id = s.id', array());
    $select->where('TRIM(s.code) = ?', $params['asm_code']);
    $select->group(array('a.id'));
} else {
    $select->joinLeft(array('s' => 'staff'), 't.sales_id = s.id',array(
        'staff_id'   => 's.id',
        'staff_code' => 's.code',
        'group_id'   => 's.group_id',
        'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
    ));
    $select->joinLeft(array('gr' => 'group'), 's.group_id = gr.id', array('gr_id' => 'gr.id', 'group_name' => 'gr.name'));
    $select->group(array('s.id','a.id'));
    $select->order(array("FIND_IN_SET(gr.id, '9,27,16,5,28')", "s.code ASC"));
}

        // ASM / RM Exception (Only Jan 2017)
if ( 
    (isset($params['from']) && $params['from'] == '2017-01-01') && 
    (isset($params['to']) && $params['to'] == '2017-01-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], array('5902867', '6000620','5700415','5600121') ) )
) {
            // Jan 2017
    if ($params['asm_code'] == '5902867') {
        $select->where('t.created_at >= ?', "2017-01-19 00:00:00");
        $select->where('t.created_at <= ?', "2017-01-31 23:59:59");
    } else {
        $select->where('t.created_at >= ?', "2017-01-09 00:00:00");
        $select->where('t.created_at <= ?', "2017-01-31 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2017-02-01') && 
    (isset($params['to']) && $params['to'] == '2017-02-28') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], array('6000430','6001647','5802915','5904291','5902867') ) )
) {

            // Feb 2017
    if ($params['asm_code'] == '6000430') {
        $select->where('t.created_at >= ?', "2017-02-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-02-28 23:59:59");
    } else if ( $params['asm_code'] == '6001647' ) {
        $select->where('t.created_at >= ?', "2017-02-03 00:00:00");
        $select->where('t.created_at <= ?', "2017-02-28 23:59:59");
    } else if ( $params['asm_code'] == '5802915' ) {
        $select->where('t.created_at >= ?', "2017-02-09 00:00:00");
        $select->where('t.created_at <= ?', "2017-02-28 23:59:59");
    } else if ( $params['asm_code'] == '5904291' ) {
        $select->where('t.created_at >= ?', "2017-02-09 00:00:00");
        $select->where('t.created_at <= ?', "2017-02-28 23:59:59");
    } else if ( $params['asm_code'] == '5902867' ) {
        $select->where('t.created_at >= ?', "2017-02-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-02-28 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2017-03-01') && 
    (isset($params['to']) && $params['to'] == '2017-03-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], array('5900810','6001821','6003443','5800109','5903169') ) )
) {

            // Mar 2017
    if ($params['asm_code'] == '5900810') {
        $select->where('t.created_at >= ?', "2017-03-09 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-31 23:59:59");
    } else if ( $params['asm_code'] == '6001821' ) {
        $select->where('t.created_at >= ?', "2017-03-10 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-31 23:59:59");
    } else if ( $params['asm_code'] == '6003443' ) {
        $select->where('t.created_at >= ?', "2017-03-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-31 23:59:59");
    } else if ( $params['asm_code'] == '5800109' && $params['area_id'][0] == '14') {
                // Trang
        $select->where('t.created_at >= ?', "2017-03-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-07 23:59:59");
    } else if ( $params['asm_code'] == '5800109' && $params['area_id'][0] == '50') {
                // Pattaya
        $select->where('t.created_at >= ?', "2017-03-18 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-31 23:59:59");
    } else if ( $params['asm_code'] == '5903169' ) {
        $select->where('t.created_at >= ?', "2017-03-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-03-31 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2017-04-01') && 
    (isset($params['to']) && $params['to'] == '2017-04-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5803098','5901833','5802914','5900294','5800604','5901385','5700572','5600899') ) )
) {

            // April 2017
    if ( in_array($params['asm_code'], array('5803098','5901833') ) ) {
        $select->where('t.created_at >= ?', "2017-04-05 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-30 23:59:59");
    } else if ( $params['asm_code'] == '5900294' ) {
        $select->where('t.created_at >= ?', "2017-04-10 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-30 23:59:59");
    } else if ( $params['asm_code'] == '5800604' && $params['area_id'][0] == '68' ) {
                // Yala 
        $select->where('t.created_at >= ?', "2017-04-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-14 23:59:59");
    } else if ( $params['asm_code'] == '5800604' && $params['area_id'][0] == '51' ) {
                // Rayong 
        $select->where('t.created_at >= ?', "2017-04-15 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-30 23:59:59");
    } else if ( $params['asm_code'] == '5901385' ) {
        $select->where('t.created_at >= ?', "2017-04-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-30 23:59:59");
    } else if ( $params['asm_code'] == '5700572' ) {
        $select->where('t.created_at >= ?', "2017-04-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-14 23:59:59");
    } else if ( $params['asm_code'] == '5600899' ) {
        $select->where('t.created_at >= ?', "2017-04-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-16 23:59:59");
    } else if ( $params['asm_code'] == '5802914' ) {
        $select->where('t.created_at >= ?', "2017-04-17 00:00:00");
        $select->where('t.created_at <= ?', "2017-04-30 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2017-05-01') && 
    (isset($params['to']) && $params['to'] == '2017-05-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], array('6004979','5800342') ) )
) {

            // May 2017
    if ( $params['asm_code'] == '6004979' ) {
        $select->where('t.created_at >= ?', "2017-05-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-05-31 23:59:59");
    } else if ( $params['asm_code'] == '5800342' ) {
        $select->where('t.created_at >= ?', "2017-05-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-05-20 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-06-01') && 
    (isset($params['to']) && $params['to'] == '2017-06-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('6007150','6007397','6006782','6006783','6000747','5801867','5700053','5904478') ) )
) {

            // June 2017
    if ( $params['asm_code'] == '6007150' ) {
        $select->where('t.created_at >= ?', "2017-06-10 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-30 23:59:59");
    } else if ( in_array($params['asm_code'], array('6007397','5801867','5904478') )) {
        $select->where('t.created_at >= ?', "2017-06-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-30 23:59:59");
    } else if ( in_array($params['asm_code'], array('6006782','6006783') )) {
        $select->where('t.created_at >= ?', "2017-06-05 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-30 23:59:59");
    } else if ( in_array($params['asm_code'], array('6000747') )) {
        $select->where('t.created_at >= ?', "2017-06-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-20 23:59:59");
    } else if ( $params['asm_code'] == '5700053' && $params['area_id'][0] == '41' ) {
                // Province : Kanchanaburi 
        $select->where('t.created_at >= ?', "2017-06-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-06 23:59:59");
    } else if ( $params['asm_code'] == '5700053' && $params['area_id'][0] == '50' ) {
                // Province : Pattaya, Sattahip
        $select->where('t.created_at >= ?', "2017-06-07 00:00:00");
        $select->where('t.created_at <= ?', "2017-06-30 23:59:59");
    } 
} else if (
    (isset($params['from']) && $params['from'] == '2017-07-01') && 
    (isset($params['to']) && $params['to'] == '2017-07-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5803018') ) )
) {

            // July 2017
    if ( $params['asm_code'] == '5803018' ) {
        $select->where('t.created_at >= ?', "2017-07-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-07-31 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-08-01') && 
    (isset($params['to']) && $params['to'] == '2017-08-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5903712', '6009013','5701029','5802914') ) )
) {

            // August 2017
    if ( $params['asm_code'] == '5903712' ) {
        $select->where('t.created_at >= ?', "2017-08-12 00:00:00");
        $select->where('t.created_at <= ?', "2017-08-31 23:59:59");
    } else if ( $params['asm_code'] == '6009013' ) {
        $select->where('t.created_at >= ?', "2017-08-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-08-31 23:59:59");
    } else if ( $params['asm_code'] == '5701029' ) {
        $select->where('t.created_at >= ?', "2017-08-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-08-03 23:59:59");
    } else if ( $params['asm_code'] == '5802914' ) {
        $select->where('t.created_at >= ?', "2017-08-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-08-20 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-09-01') && 
    (isset($params['to']) && $params['to'] == '2017-09-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5701979') ) )
) {

            // September 2017
    if ( $params['asm_code'] == '5701979' ) {
        $select->where('t.created_at >= ?', "2017-09-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-09-20 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-10-01') && 
    (isset($params['to']) && $params['to'] == '2017-10-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5400213','6006627','5801422','5902256','5903274') ) )
) {

            // October 2017
    if ( $params['asm_code'] == '5400213' && in_array($params['area_id'][0], array(13,56))) {
        $select->where('t.created_at >= ?', "2017-10-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-20 23:59:59");
    } else if ( $params['asm_code'] == '5400213' && $params['area_id'][0] == '53' ) {
        $select->where('t.created_at >= ?', "2017-10-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-31 23:59:59");
    } else if ( $params['asm_code'] == '6006627' ) {
        $select->where('t.created_at >= ?', "2017-10-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-14 23:59:59");
    } else if ( $params['asm_code'] == '5801422' ) {
        $select->where('t.created_at >= ?', "2017-10-15 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-31 23:59:59");
    } else if ( $params['asm_code'] == '5902256' ) {
        $select->where('t.created_at >= ?', "2017-10-07 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-31 23:59:59");
    } else if ( $params['asm_code'] == '5801422' && $params['area_id'][0] == '108' ) {
        $select->where('t.created_at >= ?', "2017-10-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-14 23:59:59");
    } else if ( $params['asm_code'] == '5903274' && $params['area_id'][0] == '108' ) {
        $select->where('t.created_at >= ?', "2017-10-16 00:00:00");
        $select->where('t.created_at <= ?', "2017-10-31 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-11-01') && 
    (isset($params['to']) && $params['to'] == '2017-11-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5904715','5901999','5802610') ) )
) {

            // November 2017
    if ( $params['asm_code'] == '5904715' && $params['area_id'][0] == '112' ) {
        $select->where('t.created_at >= ?', "2017-11-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-11-14 23:59:59");
    } else if ( $params['asm_code'] == '5901999' && $params['area_id'][0] == '112' ) {
        $select->where('t.created_at >= ?', "2017-11-15 00:00:00");
        $select->where('t.created_at <= ?', "2017-11-30 23:59:59");
    } else if ( $params['asm_code'] == '5901999' && $params['area_id'][0] == '83' ) {
        $select->where('t.created_at >= ?', "2017-11-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-11-30 23:59:59");
    } else if ( $params['asm_code'] == '5802610' && $params['area_id'][0] == '105' ) {
        $select->where('t.created_at >= ?', "2017-11-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-11-29 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2017-12-01') && 
    (isset($params['to']) && $params['to'] == '2017-12-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5901966','5902125','6006782','6003359','5700638','5500558','6002560') ) )
) {

            // December 2017
    if ( $params['asm_code'] == '5901966' ) {
        $select->where('t.created_at >= ?', "2017-12-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-06 23:59:59");
    } else if ( $params['asm_code'] == '5902125' ) {
        $select->where('t.created_at >= ?', "2017-12-07 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-31 23:59:59");
    } else if ( $params['asm_code'] == '6006782' && $params['area_id'][0] == '54' ) {
        $select->where('t.created_at >= ?', "2017-12-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-20 23:59:59");
    } else if ( $params['asm_code'] == '6006782' && $params['area_id'][0] == '33' ) {
        $select->where('t.created_at >= ?', "2017-12-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-31 23:59:59");
    } else if ( $params['asm_code'] == '6003359' ) {
        $select->where('t.created_at >= ?', "2017-12-09 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-31 23:59:59");
    } else if ( $params['asm_code'] == '5700638' ) {
        $select->where('t.created_at >= ?', "2017-12-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-08 23:59:59");
    } else if ( $params['asm_code'] == '5500558' ) {
        $select->where('t.created_at >= ?', "2017-12-01 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-20 23:59:59");
    } else if ( $params['asm_code'] == '6002560' ) {
        $select->where('t.created_at >= ?', "2017-12-21 00:00:00");
        $select->where('t.created_at <= ?', "2017-12-31 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2018-03-01') && 
    (isset($params['to']) && $params['to'] == '2018-03-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('6003260','5902867','6006782') ) )
) {

            // March 2018
    if ( $params['asm_code'] == '6003260' ) {
        $select->where('t.created_at >= ?', "2018-03-01 00:00:00");
        $select->where('t.created_at <= ?', "2018-03-20 23:59:59");
    } else if ( $params['asm_code'] == '5902867' ) {
        $select->where('t.created_at >= ?', "2018-03-23 00:00:00");
        $select->where('t.created_at <= ?', "2018-03-31 23:59:59");
    } else if ( $params['asm_code'] == '6006782' ) {
        $select->where('t.created_at >= ?', "2018-03-01 00:00:00");
        $select->where('t.created_at <= ?', "2018-03-20 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2018-06-01') && 
    (isset($params['to']) && $params['to'] == '2018-06-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('6004142') ) )
) {

            // June 2018
    if ( $params['asm_code'] == '6004142' ) {
        $select->where('t.created_at >= ?', "2018-06-01 00:00:00");
        $select->where('t.created_at <= ?', "2018-06-18 23:59:59");
    } 

} else if (
    (isset($params['from']) && $params['from'] == '2018-11-01') && 
    (isset($params['to']) && $params['to'] == '2018-11-30') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array('5903274','6100799') ) )
) {

            // November 2018
    if ( $params['asm_code'] == '5903274' ) {
        $select->where('t.created_at >= ?', "2018-11-01 00:00:00");
        $select->where('t.created_at <= ?', "2018-11-10 23:59:59");
    } else if ( $params['asm_code'] == '6100799' ) {
        $select->where('t.created_at >= ?', "2018-11-11 00:00:00");
        $select->where('t.created_at <= ?', "2018-11-30 23:59:59");
    }

} else if (
    (isset($params['from']) && $params['from'] == '2019-01-01') && 
    (isset($params['to']) && $params['to'] == '2019-01-31') && 
    (isset($params['asm_code']) && in_array($params['asm_code'], 
        array(
            '5902864','5902125','5801683','6000704','5600983','6005850','6004592','6002815','6003263','6106523',
            '5800493','5901839','5900356','5600600','6009478','5700415','6011088','5900025','6106449','5901343',
            '5600715') ) )
) {

            // Jan 2019
            if ( $params['asm_code'] == '5902864' ) {                                           // BKK-W2B 
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-20 23:59:59");
            } else if ( $params['asm_code'] == '5902125' && $params['area_id'][0] == 104 ) {    // BKK-W2B 
                $select->where('t.created_at >= ?', "2019-01-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5902125' && $params['area_id'][0] == 107 ) {    // BKK-W3B 
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5801683' && $params['area_id'][0] == 94 ) {     // BKK-E4A 
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5801683' && $params['area_id'][0] == 95 ) {     // BKK-E4B 
                $select->where('t.created_at >= ?', "2019-01-07 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '6000704' && $params['area_id'][0] == 95 ) {     // BKK-E4B 
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-06 23:59:59");
            } else if ( $params['asm_code'] == '5600983' && $params['area_id'][0] == 56 ) {     // Phayao
                $select->where('t.created_at >= ?', "2019-01-09 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '6005850' && $params['area_id'][0] == 56 ) {     // Phayao
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-08 23:59:59");
            } else if ( $params['asm_code'] == '6004592' && $params['area_id'][0] == 62 ) {     // Buriram
                $select->where('t.created_at >= ?', "2019-01-10 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '6002815' && $params['area_id'][0] == 62 ) {     // Buriram
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-09 23:59:59");
            } else if ( $params['asm_code'] == '6003263' && $params['area_id'][0] == 53 ) {     // Nakhon Pathom
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-09 23:59:59");
            } else if ( $params['asm_code'] == '6106523' && $params['area_id'][0] == 46 ) {     // Udon Thani
                $select->where('t.created_at >= ?', "2019-01-16 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5800493' && $params['area_id'][0] == 46 ) {     // Udon Thani
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-15 23:59:59");
            } else if ( $params['asm_code'] == '5901839' && $params['area_id'][0] == 51 ) {     // Rayong
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-20 23:59:59");
            } else if ( $params['asm_code'] == '5900356' && $params['area_id'][0] == 64 ) {     // Surin
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-09 23:59:59");
            } else if ( $params['asm_code'] == '5900356' && $params['area_id'][0] == 38 ) {     // Ubon Ratchathani
                $select->where('t.created_at >= ?', "2019-01-10 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5600600' && $params['area_id'][0] == 38 ) {     // Ubon Ratchathani
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-09 23:59:59");
            } else if ( $params['asm_code'] == '6009478' && $params['area_id'][0] == 47 ) {     // Sakon Nakhon
                $select->where('t.created_at >= ?', "2019-01-10 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5700415' && $params['area_id'][0] == 47 ) {     // Sakon Nakhon
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-09 23:59:59");
            } else if ( $params['asm_code'] == '6011088' && $params['area_id'][0] == 14 ) {     // Trang 
                $select->where('t.created_at >= ?', "2019-01-11 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5900025' && $params['area_id'][0] == 14 ) {     // Trang 
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-10 23:59:59");
            } else if ( $params['asm_code'] == '6106449' && $params['area_id'][0] == 40 ) {     // Nakhon Ratchasima
                $select->where('((rm.id = ?', 3458);
                $select->where('t.created_at >= ?', "2019-01-21 00:00:00");
                $select->where('t.created_at <= ?)', "2019-01-31 23:59:59");
                $select->orWhere('(rm.id = ?', 6109);
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?))', "2019-01-31 23:59:59");
            } else if ( $params['asm_code'] == '5901343' && $params['area_id'][0] == 40 ) {     // Nakhon Ratchasima
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-01-20 23:59:59");
            } else if ( $params['asm_code'] == '5600715' && $params['area_id'][0] == 64 ) {     // Surin
                $select->where('((rm.id IN (?)', array(3444,5822));
                $select->where('t.created_at >= ?', "2019-01-01 00:00:00");
                $select->where('t.created_at <= ?)', "2019-01-31 23:59:59");
                $select->orWhere('(rm.id IN (?)', array(3427,6104));
                $select->where('t.created_at >= ?', "2019-01-10 00:00:00");
                $select->where('t.created_at <= ?))', "2019-01-31 23:59:59");
            }

        } else if (
            (isset($params['from']) && $params['from'] == '2019-03-01') && 
            (isset($params['to']) && $params['to'] == '2019-03-31') && 
            (isset($params['asm_code']) && in_array($params['asm_code'], 
                array('5801683','6006487','6002998','5701111') ) )
        ) {

            // March 2019
            if ( $params['asm_code'] == '5801683' && $params['area_id'][0] == 97 ) {            // BKK-E5A
                $select->where('t.created_at >= ?', "2019-03-09 00:00:00");
                $select->where('t.created_at <= ?', "2019-03-31 23:59:59");
            } else if ( $params['asm_code'] == '6006487' && $params['area_id'][0] == 97 ) {     // BKK-E5A
                $select->where('t.created_at >= ?', "2019-03-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-03-08 23:59:59");
            } else if ( $params['asm_code'] == '6002998' && $params['area_id'][0] == 99 ) {     // BKK-W1B
                $select->where('t.created_at >= ?', "2019-03-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-03-20 23:59:59");
            } else if ( $params['asm_code'] == '5701111' && $params['area_id'][0] == 99 ) {     // BKK-W1B
                $select->where('t.created_at >= ?', "2019-03-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-03-31 23:59:59");
            } else {
                $select->where('t.created_at >= ?', "2019-03-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-03-31 23:59:59");
            }

        } else if (
            (isset($params['from']) && $params['from'] == '2019-04-01') && 
            (isset($params['to']) && $params['to'] == '2019-04-30') && 
            (isset($params['asm_code']) && in_array($params['asm_code'], 
                array('6007150','6010500','6004191','5900235') ) )
        ) {

            // Apr 2019
            if ( $params['asm_code'] == '6007150' && $params['area_id'][0] == 39 ) {            // Chonburi
                $select->where('t.created_at >= ?', "2019-04-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-04-20 23:59:59");
            } else if ( $params['asm_code'] == '6010500' && $params['area_id'][0] == 39 ) {     // Chonburi
                $select->where('t.created_at >= ?', "2019-04-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-04-30 23:59:59");
            } else if ( $params['asm_code'] == '6007150' && $params['area_id'][0] == 51 ) {     // Rayong
                $select->where('t.created_at >= ?', "2019-04-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-04-30 23:59:59");
            } else if ( $params['asm_code'] == '6004191' && $params['area_id'][0] == 51 ) {     // Rayong
                $select->where('t.created_at >= ?', "2019-04-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-04-20 23:59:59");
            } else if ( $params['asm_code'] == '5900235' && $params['area_id'][0] == 51 ) {     // Rayong
                $select->where('t.created_at >= ?', "2019-04-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-04-30 23:59:59");
            }

        } else if (
            (isset($params['from']) && $params['from'] == '2019-06-01') && 
            (isset($params['to']) && $params['to'] == '2019-06-30') && 
            (isset($params['asm_code']) && in_array($params['asm_code'], 
                array('6006654','6009478','5600009','6101914') ) )
        ) {

            // Jun 2019
            if ( $params['asm_code'] == '6006654' && $params['area_id'][0] == 47 ) {            // Sakon Nakhon
                $select->where('t.created_at >= ?', "2019-06-21 00:00:00");
                $select->where('t.created_at <= ?', "2019-06-30 23:59:59");
            } else if ( $params['asm_code'] == '6009478' && $params['area_id'][0] == 47 ) {     // Sakon Nakhon
                $select->where('t.created_at >= ?', "2019-06-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-06-20 23:59:59");
            } else if ( $params['asm_code'] == '5600009' && $params['area_id'][0] == 55 ) {     // Lampang
                $select->where('t.created_at >= ?', "2019-06-01 00:00:00");
                $select->where('t.created_at <= ?', "2019-06-18 23:59:59");
            } else if ( $params['asm_code'] == '6101914' && $params['area_id'][0] == 55 ) {     // Lampang
                $select->where('t.created_at >= ?', "2019-06-19 00:00:00");
                $select->where('t.created_at <= ?', "2019-06-30 23:59:59");
            } 

        } else {
            // Filter
            if (isset($params['from']) && $params['from']) {
                $select->where('t.created_at >= ?', $params['from']." 00:00:00");
            }

            if (isset($params['to']) && $params['to']) {
                $select->where('t.created_at <= ?', $params['to']." 23:59:59");
            }   
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {

            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select; echo "<br/>"; //die;
        
        if (isset($params['com_leader']) && $params['com_leader']) { 
            $result = $db->fetchRow($select); 
        } else {
            $result = $db->fetchAll($select);
        }
        
        return $result;
    }

    // Sale Leader Commission UpCountry 2017
    public function com_sale_leader_bkk_2017($params) {
        $db = Zend_Registry::get('db');

        $bkk_list = array(73,74,75,76,77,78,79,80);

        if ( in_array($params['area_id'], $bkk_list) ) { $hero_rate = 30; $normal_rate = 10; } 
        else { $hero_rate = 60; $normal_rate = 15; }

        $select = $db->select()
        ->from(array('s' => 'staff'), array(
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'group_id'   => 's.group_id',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
        ))
        ->join(array('t'  => 'timing')          , 's.id = t.leader_id'   , array())
        ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array('total_unit' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('st' => 'store')           , 't.store = st.id'     , array())
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'   , array('area_id' => 'a.id', 'area_name' => 'a.name'))
        ->join(array('gr' => 'group')           , 's.group_id = gr.id'   , array('gr_id' => 'gr.id', 'group_name' => 'gr.name'))
        ->join(array('gkl'=> 'good_kpi_log')    , 
            "   ts.product_id = gkl.good_id 
            AND ts.model_id = gkl.color_id 
            AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
            AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
            ", 
            array('total_price' => new Zend_Db_Expr("SUM(gkl.price)")))
        ->join(array('i' => WAREHOUSE_DB.'.imei'), 
            "   ts.imei = i.imei_sn 
            AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
            AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
            ", array(
                'com_rate' => new Zend_Db_Expr(
                    "SUM(
                    CASE 
                    WHEN t.created_at <= '2017-02-28 23:59:59' THEN 

                    CASE WHEN ts.product_id = 128 OR ts.product_id = 142 THEN 
                    '".$hero_rate."' 
                    ELSE '".$normal_rate."' END 

                    WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN

                    CASE WHEN ts.product_id = 142 OR ts.product_id = 208 THEN 
                    '".$hero_rate."' 
                    ELSE '".$normal_rate."' END 

                    ELSE 
                    CASE WHEN ts.product_id = 208 OR ts.product_id = 210 THEN 
                    '".$hero_rate."' 
                    ELSE '".$normal_rate."' END 
                    END
                    )
                    "),
            ))
        ->where('st.rank <> ?', 5)
        ->group(array('s.id','a.id'))
        ->order(array("FIND_IN_SET(gr.id, '9,27,16,5,35,28')", "s.code ASC"));

        // Filter
        if (isset($params['from']) && $params['from']) {
            $select->where('t.created_at >= ?', $params['from']." 00:00:00");
        }

        if (isset($params['to']) && $params['to']) {
            $select->where('t.created_at <= ?', $params['to']." 23:59:59");
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('t.store IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('t.store = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {

            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
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

        //echo $select; //die;
        $result = $db->fetchAll($select);

        return $result;
    }

    // Get Com Rate By Sub Area
    public function getComRateBySubArea($sub_district) {

        $db = Zend_Registry::get('db');

        $get = array('com_rate' => 'sa.com_rate');

        $select = $db->select()
        ->from(array('ac' => 'area_control'), $get)
        ->join(array('sa' => 'sub_area'), 'ac.sub_area_id = sa.id', array())
        ->where('ac.sub_district = ?', $sub_district);

        //echo $select;
        $result = $db->fetchRow($select);
        return $result;

    }

    // Get Brand Shop's Commission 
    public function getComBM($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',
            'st_target' => 'bct.target',
            'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"), 
            'price'     => new Zend_Db_Expr("SUM(gkl.price)"),
        );

        $select = $db->select()
        ->from(array('st' => 'store'), $get)
        ->join(array('o'  => 'org')        , 'st.org_dealer = o.org_id'    , array())
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())

        ->joinLeft(array('bct'=>'bm_com_target'), 
            "   st.id = bct.store_id
            AND bct.from_date >= '".$from."' 
            AND bct.to_date <= '".$to."' 
            ", array())
        ->joinLeft(array('t'  =>'timing')       , 
            "   st.id = t.store
            AND t.created_at >= '".$from." 00:00:00' 
            AND t.created_at <= '".$to." 23:59:59'
            ", array())
        ->joinLeft(array('ts' =>'timing_sale')  , 't.id = ts.timing_id', array())
        ->joinLeft(array('gkl' => 'good_kpi_log'), 
            "   gkl.good_id = ts.product_id 
            AND gkl.color_id = ts.model_id 
            AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
            AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
            ", array())

        ->where('st.del IS NULL')
        ->where('st.rank <> ?', 5)
        ->where('st.org_dealer IN (?)', array(18,21,22,23,31,33,34,37,39,40) )
        ->group('st.id')
        ->order(array('a.name ASC', 'st.id ASC'));

        // Filter 
        if ( isset($params['id']) && $params['id'] ) {
            $select->where('st.id LIKE ?', '%'.$params['id'].'%');
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['it_junction']) && $params['it_junction'] ) {
            $select->where('st.it_junction = ?', $params['it_junction']);
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

        // Add Filter Store
        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $select->where('st.id IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $select->where('st.id = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Type
        if (isset($params['org']) && $params['org']) {

            if (is_array($params['org']) && count($params['org']))
                $select->where('st.org_dealer IN (?)', $params['org']);
            elseif (is_numeric($params['org']))
                $select->where('st.org_dealer = ?', intval($params['org']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Market Type, Market Name
        if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {

            $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
            $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
            //$select->joinLeft( array('mt' => 'market_type') , 'mn.market_type_id = mt.id'   , array('market_type' => 'mt.name'));

            // filter Market Type
            if (isset($params['market_type']) and $params['market_type']) {
                if (is_array($params['market_type']) && count($params['market_type'])) { 

                    $select->where('mn.market_type_id IN (?)', $params['market_type']);

                } elseif (is_numeric($params['market_type'])) {

                    $select->where('mn.market_type_id = ?', intval($params['market_type']));

                } else {
                    $select->where('1=0', 1);
                }
            }

            // Filter Market Name
            if (isset($params['market_name']) and $params['market_name']) {
                if (is_array($params['market_name']) && count($params['market_name'])) { 

                    $select->where('mn.id IN (?)', $params['market_name']);

                } elseif (is_numeric($params['market_name'])) {

                    $select->where('mn.id = ?', intval($params['market_name']));

                } else {
                    $select->where('1=0', 1);
                }
            }
        }

        // Add Filter Sale Code, Sale Name 
        if ( (isset($params['staff_name']) and $params['staff_name']) || (isset($params['staff_code']) && $params['staff_code']) ) {
            $select->join( array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array());
            $select->join( array('s' => 'staff'), 'ss.staff_id = s.id', array());

            // Filter Sale Name
            if (isset($params['staff_name']) && $params['staff_name'])
                $select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", '%'.$params['staff_name'].'%');

            // Filter Sale Code
            if (isset($params['staff_code']) && $params['staff_code'])
                $select->where('s.code LIKE ?', '%'.$params['staff_code'].'%');
        }

        // Add Filter Sale More Than
        if (isset($params['sales_from']) && $params['sales_from'])
            $select->having('sellout >= ?', $params['sales_from']);

        // Add Filter Sale Less Than
        if (isset($params['sales_from']) && $params['sales_from'])
            $select->having('sellout <= ?', $params['sales_from']);

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    // ASM's GFK Calculation
    public function gfk_calculation($from, $to) {

        $db = Zend_Registry::get('db');

        // Check Punish Memo By Store [GFK]
        $select_pms = $db->select()
        ->from(array('pms' => 'punish_memo_store'), array('pms.*' ))
        ->where('pms.flag_gfk = ?', 1)
        ->where('pms.from_date >= ?', $from)
        ->where('pms.to_date <= ?', $to);

        //echo $select_pms; die;
        $result_pms = $db->fetchAll($select_pms);

        $punish_store = '';
        foreach ($result_pms as $key => $value) { $punish_store .= $value['store'].","; }
        $punish_store = rtrim($punish_store, ",");

        //echo "Punish Store List : ".$punish_store;

        $punish_sql_unit = '';
        if ( $punish_store != '') {
            $punish_sql_unit = "
            WHEN t.created_at >= '".$from." 00:00:00' 
            AND t.created_at <= '".$to." 23:59:59' 
            AND t.store IN (".$punish_store.") THEN NULL 
            ";
        }

        $get = array(
            'area_id'   => new Zend_Db_Expr("IFNULL(a.id, 'Total')"),
            'area_name' => 'a.name',
            'sellout'   => new Zend_Db_Expr(
                "   COUNT(
                CASE 
                WHEN 1=0 THEN NULL 
                ".$punish_sql_unit." 
                ELSE 
                ts.imei 
                END
                )
                "),

            'gfk'       => 'ag.gfk' 
        );

        $select = $db->select()
        ->from(array('st' => 'store'), $get)
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
        ->join(array('t'  => 'timing')          , 'st.id = t.store'             , array())
        ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())
        ->join(array('i'=> WAREHOUSE_DB.'.imei'), 
            "   ts.imei = i.imei_sn 
            AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
            AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
            ", array())
        ->joinLeft(array('ag' => 'area_gfk'), 'a.id = ag.area_id', array())
        ->where('a.id NOT IN (?)', array(48,49,72))
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59")
        ->group(new Zend_Db_Expr('a.id WITH ROLLUP'));

        // echo $select; die;
        $result_temp = $db->fetchAll($select);

        // Get Balance Unit Data 
        $select_gbu = $db->select()
        ->from(array('gbu' => 'gfk_balance_unit'), array('gbu.*'))
        ->where('gbu.from_date >= ?', $from)
        ->where('gbu.to_date <= ?', $to)
        ->order(array('gbu.area_id ASC', 'gbu.b_type ASC'));

        // echo $select_gbu; die;
        $result_gbu = $db->fetchAll($select_gbu);

        // GFK Balance Unit
        $result = array();
        $total_plus = $total_minus = $i = 0;

        foreach ($result_temp as $key => $value) {

            $result[$i]['area_id']   = $value['area_id'];
            $result[$i]['area_name'] = $value['area_name'];
            $result[$i]['sellout']   = $value['sellout'];
            $result[$i]['gfk']       = $value['gfk'];

            foreach ($result_gbu as $key2 => $value2) {

                // Check Balance Area
                if ( $result[$i]['area_id'] == $value2['area_id'] ) {

                    // Check Balance Type
                    if ( $value2['b_type'] == 0 ) {
                        $result[$i]['sellout'] = $result[$i]['sellout'] - $value2['unit'];
                        $total_minus = $total_minus + $value2['unit'];
                    } else {
                        $result[$i]['sellout'] = $result[$i]['sellout'] + $value2['unit'];
                        $total_plus = $total_plus + $value2['unit'];
                    }

                } 

            }

            // Last Row
            if ( $result[$i]['area_id'] == 'Total' ) {

                $result[$i]['area_name'] = '';
                $result[$i]['gfk'] = '';
                $result[$i]['sellout'] = $value['sellout'] + $total_plus - $total_minus;
                
            }

            $i++;
        }

        // echo "<pre>"; print_r($result_temp);
        // echo "<pre>"; print_r($result_gbu);
        // echo "<pre>"; print_r($result);

        // die;

        return $result;

    }

    //khuan
    public function AchieveRemark($params){
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        // //first day of this month
        $first_day = $d[2].'-'.$d[1].'-'.'01';

        //last Day of thi month
        $last_day = date("Y-m-t", strtotime($from));


        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $db = Zend_Registry::get('db');

        // To day Sellout 
        $sub_select = $db->select()
        ->from(array('t1' => 'timing'),array('COUNT(t1.id)'))
        ->join(array('rm1' => 'regional_market'),'rm1.id = t1.regional_id',array())
        ->where('t1.created_at >=?',$from.' 00:00:00')
        ->where('t1.created_at <=?', $from.' 23:59:59')
        ->where('rm1.area_id = a.id');

        // Monthly Sellout
        $sub_select_01 = $db->select()
        ->from(array('t1' => 'timing'),array('COUNT(t1.id)'))
        ->join(array('rm1' => 'regional_market'),'rm1.id = t1.regional_id',array())
        ->where('t1.created_at >=?',$first_day.' 00:00:00')
        ->where('t1.created_at <=?', $from.' 23:59:59')
        ->where('rm1.area_id = a.id');

        //hero sellout To day
        $sub_select_02 = $db->select()
        ->from(array('t1' => 'timing'),array('COUNT(t1.id)'))
        ->join(array('rm1' => 'regional_market'),'rm1.id = t1.regional_id',array())
        ->joinLeft(array('ts1' => 'timing_sale'),'ts1.timing_id = t1.id',array())
        ->joinLeft(array('g1' => WAREHOUSE_DB.'.good'),'g1.id = ts1.product_id',array())
        ->where('t1.created_at >=?',$from.' 00:00:00')
        ->where('t1.created_at <=?', $from.' 23:59:59')
        ->where('g1.hero_product =?',1)
        ->where('rm1.area_id = a.id');

        //monthly hero product sellout
        $sub_select_03 = $db->select()
        ->from(array('t1' => 'timing'),array('COUNT(t1.id)'))
        ->join(array('rm1' => 'regional_market'),'rm1.id = t1.regional_id',array())
        ->joinLeft(array('ts1' => 'timing_sale'),'ts1.timing_id = t1.id',array())
        ->joinLeft(array('g1' => WAREHOUSE_DB.'.good'),'g1.id = ts1.product_id',array())
        ->where('t1.created_at >=?',$first_day.' 00:00:00')
        ->where('t1.created_at <=?', $from.' 23:59:59')
        ->where('g1.hero_product =?',1)
        ->where('rm1.area_id = a.id');


        $get = array(
            'area_name' => 'a.name',
            'score' => 'gfk.gfk',
            // 'sellin' => new Zend_Db_Expr('('.$sub_select.')'),
            'to_day_sell' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$from." 00:00:00' AND t.created_at <= '".$from." 23:59:59' THEN t.id END)+(".$sub_select.")"),
            'monthly_sell' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$first_day." 00:00:00' AND t.created_at <= '".$from." 23:59:59' THEN t.id END)+(".$sub_select_01.")"),
            'to_day_hero_sell' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$from." 00:00:00' AND t.created_at <= '".$from." 23:59:59' AND g.hero_product = 1 THEN t.id END)+(".$sub_select_02.")"),
            'monthly_hero_sell' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$first_day." 00:00:00' AND t.created_at <= '".$from." 23:59:59' AND g.hero_product = 1 THEN t.id END)+(".$sub_select_03.")"),
            'all_model_target' => 'atg.target',
            'hero_model_target' => 'atg.target_hero',
            'sell_amount_to_day_all_model' => new Zend_Db_Expr("SUM(CASE WHEN t.created_at >='".$from." 00:00:00' AND t.created_at <= '".$from." 23:59:59' THEN kpi.price END)"),
            'sell_amount_of_monthly_all_model' => new Zend_Db_Expr("SUM(CASE WHEN t.created_at >='".$first_day." 00:00:00' AND t.created_at <= '".$from." 23:59:59' THEN kpi.price END)"),
            'sell_amount_to_day_hero' => new Zend_Db_Expr("SUM(CASE WHEN t.created_at >='".$from." 00:00:00' AND t.created_at <= '".$from." 23:59:59' AND g.hero_product = 1 THEN kpi.price END)"),
            'sell_amount_of_monthly_hero' => new Zend_Db_Expr("SUM(CASE WHEN t.created_at >='".$first_day." 00:00:00' AND t.created_at <= '".$from." 23:59:59' AND g.hero_product = 1 THEN kpi.price END)"),
        );

        $select = $db->select()
        ->from(array('a' => 'area'),$get)
        ->joinLeft(array('gfk' => 'area_gfk'),'gfk.area_id = a.id',array())
        ->joinLeft(array('rm' =>'regional_market'),'a.id = rm.area_id',array())
        ->joinLeft(array('s' => 'store'),'rm.id = s.regional_market',array())
        ->joinLeft(array('t' => 'timing'),'s.id = t.store',array())
        ->joinLeft(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'ts.product_id = g.id',array())
        ->joinLeft(array('atg' => 'oppo_area_target'),"a.id = atg.area_id AND atg.from_date >='".$first_day."' AND atg.to_date <='".$last_day."'",array())
        ->joinLeft(array('kpi' => 'good_kpi_log'),'kpi.good_id = ts.product_id AND ts.model_id = kpi.color_id',array())
        ->where('a.id NOT IN (?)',array(120))
        ->group('a.id');

          // echo $select; die();

         $result = $db->fetchAll($select);

         // print_r($result); exit();

            return $result;
        }

    // Get Area GFK
        public function getAreaGFK($params) {

            $d = explode('/', $params['from']);
            $from = $d[2].'-'.$d[1].'-'.$d[0];

            $d = explode('/', $params['to']);
            $to = $d[2].'-'.$d[1].'-'.$d[0];

            $db = Zend_Registry::get('db');

        // Check Punish Memo By Store [GFK]
            $select_pms = $db->select()
            ->from(array('pms' => 'punish_memo_store'), array('pms.*' ))
            ->where('pms.flag_gfk = ?', 1)
            ->where('pms.from_date >= ?', $from)
            ->where('pms.to_date <= ?', $to);

        //echo $select_pms;
            $result_pms = $db->fetchAll($select_pms);

            $punish_store = '';
            foreach ($result_pms as $key => $value) { $punish_store .= $value['store'].","; }
            $punish_store = rtrim($punish_store, ",");

        //echo "Punish Store List : ".$punish_store;

            $punish_sql_unit = '';
            if ( $punish_store != '') {
                $punish_sql_unit = "
                WHEN t.created_at >= '".$from." 00:00:00' 
                AND t.created_at <= '".$to." 23:59:59'
                AND t.store IN (".$punish_store.") THEN NULL 
                ";
            }

            $get = array(
                'area_id'   => 'a.id',
                'area_name' => 'a.name',
                'sellout'   => new Zend_Db_Expr(
                    "   SUM(
                    CASE 
                    WHEN 
                    t.created_at >= '2017-11-01 00:00:00' 
                    AND t.created_at <= '2017-11-30 23:59:59'
                    AND t.store IN (8949,8943,8370,8474,12056,12057,14322,14323,20367,10698)
                    THEN 
                    NULL
                    WHEN 
                    t.created_at >= '2018-02-01 00:00:00'
                    AND t.created_at <= '2018-02-28 23:59:59'
                    AND t.store IN (
                    4489,3744,11874,10390,16014,1637,218,21124,3690,12329,
                    8477,19677,7342,7370,21066,13291,16583,18082,17222,11076,
                    16630,18715,18714,9330,1122,1148)
                    THEN 
                    NULL 
                    WHEN 
                    t.created_at >= '2018-03-01 00:00:00'
                    AND t.created_at <= '2018-03-31 23:59:59'
                    AND t.store IN (9875,9507,16558,18712)
                    THEN 
                    NULL

                    ".$punish_sql_unit."

                    ELSE 

                    gkl.price

                    END
                    )
                    "),

            /* fix 
            ts.imei 
            */
            'gfk'       => 'ag.gfk', 
            'score'     => 'sca.score'
        );

            $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('t'  => 'timing')          , 'st.id = t.store'             , array())
            ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())
            ->joinLeft(array('gkl' => 'good_kpi_log'),'ts.product_id = gkl.good_id AND ts.model_id = gkl.color_id',array())

            /*fix 
                     AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                    AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
            */


                    ->join(array('i'=> WAREHOUSE_DB.'.imei'), 
                        "   ts.imei = i.imei_sn
                        ", array())
                    ->joinLeft(array('ag' => 'area_gfk'), 'a.id = ag.area_id', array())
                    ->joinLeft(array('sca'=> 'sale_com_asm'), 
                        "   a.id = sca.area_id 
                        AND sca.from_date >= '".$from." 00:00:00'
                        AND sca.to_date <= '".$to." 23:59:59'
                        ", array())
                    ->where('a.id NOT IN (?)', array(48,49,72))
                    ->where('t.created_at >= ?', $from." 00:00:00")
                    ->where('t.created_at <= ?', $to." 23:59:59")
                    ->group('a.id')
                    ->order('sca.score DESC');

        //echo $select; die;
                    $result_temp = $db->fetchAll($select);

        // Get Balance Unit Data 
                    $select_gbu = $db->select()
                    ->from(array('gbu' => 'gfk_balance_unit'), array('gbu.*'))
                    ->where('gbu.from_date >= ?', $from)
                    ->where('gbu.to_date <= ?', $to)
                    ->order(array('gbu.area_id ASC', 'gbu.b_type ASC'));

        // echo $select_gbu; die;
                    $result_gbu = $db->fetchAll($select_gbu);

        // GFK Balance Unit
                    $result = array();
                    $total_plus = $total_minus = $i = 0;

                    foreach ($result_temp as $key => $value) {

                        $result[$i]['area_id']        = $value['area_id'];
                        $result[$i]['area_name']      = $value['area_name'];
                        $result[$i]['sellout_actual'] = $value['sellout'];
                        $result[$i]['sellout']        = $value['sellout'];
                        $result[$i]['gfk']            = $value['gfk'];
                        $result[$i]['score']          = $value['score'];

                        foreach ($result_gbu as $key2 => $value2) {

                // Check Balance Area
                            if ( $result[$i]['area_id'] == $value2['area_id'] ) {

                    // Check Balance Type
                                if ( $value2['b_type'] == 0 ) {
                                    $result[$i]['sellout'] = $result[$i]['sellout'] - $value2['unit'];
                                    $total_minus = $total_minus + $value2['unit'];
                                } else {
                                    $result[$i]['sellout'] = $result[$i]['sellout'] + $value2['unit'];
                                    $total_plus = $total_plus + $value2['unit'];
                                }

                            } 

                        }

                        $i++;
                    }

        // echo "<pre>"; print_r($result_temp);
        // echo "<pre>"; print_r($result_gbu);
        // echo "<pre>"; print_r($result);

        // die;

                    return $result;

                }

    // Get RM Punish by Memo 
                public function get_punish_memo($params) {

                    $db = Zend_Registry::get('db');

                    $get = array(
                        'area_id'   => 'pm.area_id',
                        'staff_id'  => 'pm.staff_id',
                        'price'     => 'pm.price',
                        'p_name'    => 'pl.name',
                    );

                    $select = $db->select()
                    ->from(array('pm' => 'punish_memo'), $get)
                    ->join(array('pl' => 'punish_list') , 'pm.punish_id = pl.id'  , array())
                    ->where('pm.staff_id = ?', $params['asm_id'])
                    ->where('pm.area_id = ?', $params['area_id'][0])
                    ->where('pm.from_date >= ?', $params['from'])
                    ->where('pm.to_date <= ?', $params['to'])
                    ->order('pl.id ASC');

        //echo $select; die;
                    $result = $db->fetchAll($select);
                    return $result;

                }

    // Get PC Un-Punish by Memo [Selllout Less Than 15]
                public function get_pc_unpunish($params) {

                    $db = Zend_Registry::get('db');

                    $get = array(
                        'staff_id'  => 's.id',
                        'staff_code'=> 's.code',
                        'con_01'    => 'su.condition_01',
                        'con_02'    => 'su.condition_02',
                        'unpunish_type' => 'ul.name',
                    );

                    $select = $db->select()
                    ->from(array('su' => 'staff_unpunish')  , $get)
                    ->join(array('ul' => 'unpunish_list')   , 'su.type_id = ul.id'  , array())
                    ->join(array('s' => 'staff')            , 'su.staff_id = s.id'  , array())
                    ->where('su.from_date >= ?', $params['from'])
                    ->where('su.to_date <= ?', $params['to']);

        //echo $select; die;
                    $result = $db->fetchAll($select);
                    return $result;

                }


    // Get Area Brand Shop Manager's Commission 
                public function getComABM($params) {

                    set_time_limit(0);
                    ini_set('memory_limit', '-1');
                    error_reporting(~E_ALL);
                    ini_set("display_error", '0');

                    $d1 = explode('/', $params['from']);
                    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

                    $d2 = explode('/', $params['to']);
                    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

                    $db = Zend_Registry::get('db');

                    $get = array(
                        'staff_code'    => 's.code',
                        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
                        'staff_group_id'=> 'gr.id',
                        'staff_group'   => 'gr.name',
                        'area_id'       => 'a.id',
                        'area_name'     => 'a.name',
                        'province_id'   => 'rm.id',
                        'province_name' => 'rm.name',
                        'st_id'         => 'st.id',
                        'st_name'       => 'st.name', 
                        'st_type'       => 'o.org_name',
                        'st_status'     => new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Disabled' END)"),
                        'good_name'     => 'g.name', 
                        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
                        'flag'          => new Zend_Db_Expr("(CASE WHEN asm.area_id IS NULL THEN 'NO' ELSE 'YES' END)"),
                        'com_rate'      => new Zend_Db_Expr(
                            "SUM(
                            CASE 
                            WHEN t.created_at >= '2018-01-01 00:00:00' AND t.created_at <= '2018-01-31 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '30' ELSE '15' END
                            END

                            WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '30' ELSE '15' END
                            END 

                            WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '30' ELSE '15' END
                            END 

                            WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '30' ELSE '15' END
                            END 

                            WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '30' ELSE '15' END
                            END 

                            WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-30 23:59:59' THEN

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '30' ELSE '15' END
                            END 

                            ELSE 

                            CASE 
                            WHEN a.name LIKE 'BKK%' THEN 
                            CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '20' ELSE '10' END
                            ELSE  
                            CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '30' ELSE '15' END
                            END

                            END
                            )
                            "),
                    );

$select = $db->select()
->from(array('bal' => 'bs_area_log'), $get)
->join(array('s'   => 'staff')          , 'bal.staff_id = s.id'             , array())
->join(array('gr'  => 'group')          , 's.group_id = gr.id'              , array())
->join(array('bam' => 'bs_area_map')    , 'bal.bs_area_id = bam.bs_area_id' , array())
->join(array('a'   => 'area')           , 'bam.area_id = a.id'              , array())
->join(array('rm'  => 'regional_market'), 'a.id = rm.area_id'               , array())
->join(array('st'  => 'store')          , 'rm.id = st.regional_market'      , array())
->join(array('o'   => 'org'), 
    "   st.org_dealer = o.org_id 
    AND (o.store_type_id = 3 OR o.org_id = 18)
    ", array())
->join(array('t'   => 'timing')         , 'st.id = t.store'                 , array())
->join(array('ts'  => 'timing_sale')    , 't.id = ts.timing_id'             , array())
->join(array('i'=> WAREHOUSE_DB.'.imei'), 
    "   ts.imei = i.imei_sn 
    AND i.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
    AND i.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    ", array())
->join(array('g' => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
->joinLeft(array('asm' => 'asm'), 
    "   (CASE WHEN asm.type = 2 THEN a.id ELSE rm.id END) = asm.area_id 
    AND asm.staff_id = s.id 
    ", array())
->where('st.rank <> ?', 5)
->where('bal.from_date <= t.created_at', 1)
->where('(bal.to_date >= t.created_at', 1)
->orWhere('bal.to_date IS NULL)', 1)
->group(array('s.id','st.id','g.id'))
->order(array('s.code ASC','a.name ASC','st.id ASC','g.name ASC'));

        // Check for ASM Share
if ( isset($params['asm_id']) && $params['asm_id'] ) {

    $sub_select = $db->select()
    ->from(array('bam2' => 'bs_area_map'), array('bs_area_id'))
    ->where('area_id = ?', $params['area_id']);

    $select->where('bam.bs_area_id = ?', $sub_select);
    $select->where('t.created_at >= ?', $params['from']." 00:00:00");
    $select->where('t.created_at <= ?', $params['to']." 23:59:59");

    unset($params['area_id']);
} else {
    $select->where('t.created_at >= ?', $from." 00:00:00");
    $select->where('t.created_at <= ?', $to." 23:59:59");
}

if ( isset($params['name']) && $params['name'] ) {
    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
}

if ( isset($params['staff_code']) && $params['staff_code'] ) {
    $select->where('s.code = ?', $params['staff_code']);
}

if ( isset($params['phone_number']) && $params['phone_number'] ) {
    $select->where('s.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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

        // Add Filter Store
if (isset($params['store']) && $params['store']) {
    if (is_array($params['store']) && count($params['store']))
        $select->where('st.id IN (?)', $params['store']);
    elseif (is_numeric($params['store']))
        $select->where('st.id = ?', intval($params['store']));
    else
        $select->where('1=0', 1);
}

if (isset($params['store_type']) && $params['store_type']) {
    if (is_array($params['store_type']) && count($params['store_type']))
        $select->where('st.org_dealer IN (?)', $params['store_type']);
    elseif (is_numeric($params['store']))
        $select->where('st.org_dealer = ?', intval($params['store_type']));
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

    // Sellin Commission for Sale
public function sellin_com_sale($params) {

    $db = Zend_Registry::get('db');

    $get_01 = array(
        'area_id'     => 'a.id',
        'area_name'   => 'a.name',
        'staff_id'    => 's.id',
        'staff_code'  => 's.code',
        'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'staff_group' => 'g.name',
        'sellin'      => new Zend_Db_Expr("SUM(m.num)"),
        'm_total'     => new Zend_Db_Expr("ROUND(ROUND(( TRUNCATE(( SUM( (ROUND((( m.price - ((m.price*IFNULL(m.sale_off_percent,0)/100)*100)/100 )/1.07),2)*m.num) ) - m.total_spc_discount + ( IFNULL(m.delivery_fee/1.07,0) ) ) ,2)*1.07 ),2)-(SELECT IFNULL(SUM(cnt.use_discount),0) FROM warehouse.credit_note_tran AS cnt WHERE cnt.sales_order = cm.sn),2)"),

    );

    if ( isset($params['dash_sale']) && $params['dash_sale'] ) {

        $get_02 = array(
            'sn_ref'        => 'm.sn_ref',
            'sellin_hero'   => new Zend_Db_Expr(
                "   COALESCE(
                SUM(
                CASE 
                WHEN 
                cm.pay_time >= '2018-04-01 00:00:00' AND 
                cm.pay_time <= '2018-04-30 23:59:59' AND 
                m.good_id IN (299,301,310,311)
                THEN 
                m.num 
                WHEN 
                cm.pay_time >= '2018-05-01 00:00:00' AND 
                cm.pay_time <= '2018-05-31 23:59:59' AND 
                m.good_id IN (310,311,312)
                THEN 
                m.num 
                END)
                ,0)
                "),
            'sellin_normal' => new Zend_Db_Expr(
                "   COALESCE(
                SUM(
                CASE 
                WHEN 
                cm.pay_time >= '2018-04-01 00:00:00' AND 
                cm.pay_time <= '2018-04-30 23:59:59' AND 
                m.good_id NOT IN (299,301,310,311)
                THEN 
                m.num 
                WHEN 
                cm.pay_time >= '2018-05-01 00:00:00' AND 
                cm.pay_time <= '2018-05-31 23:59:59' AND 
                m.good_id NOT IN (310,311,312)
                THEN 
                m.num 
                END)
                ,0)
                "),

            'com_rate'      => new Zend_Db_Expr(
                "   COALESCE(
                SUM(
                CASE 
                WHEN 
                cm.pay_time >= '2018-04-01 00:00:00' AND 
                cm.pay_time <= '2018-04-30 23:59:59' AND 
                m.good_id IN (299,301,310,311)
                THEN 
                m.num*10 
                WHEN 
                cm.pay_time >= '2018-05-01 00:00:00' AND 
                cm.pay_time <= '2018-05-31 23:59:59' AND 
                m.good_id IN (310,311,312)
                THEN 
                m.num*10 
                ELSE 
                m.num*5 
                END)
                ,0)
                "),
        );

    } else {

        $get_02 = array('good_id' => 'm.good_id');

    }

    $get = $get_01 + $get_02;

    $select = $db->select()
    ->from(array('cm' => WAREHOUSE_DB.'.checkmoney'), $get)
    ->join(array('m' => WAREHOUSE_DB.'.market')      , 'cm.sn = m.sn', array())
    ->join(array('d' => WAREHOUSE_DB.'.distributor') , 'm.d_id = d.id AND d.rank IN (7,8,13,14)', array())
    ->join(array('s' => 'staff'), 'm.sales_catty_id = s.id', array())
    ->join(array('g' => 'group'), 's.group_id = g.id', array())
    ->join(array('rm' => 'regional_market'), 's.regional_market = rm.id', array())
    ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
    ->where('cm.type = ?', 2)
    ->where('g.id = ?', SALES_ID)
    ->where('m.canceled <> ?', 1)
    ->where('m.cat_id = ?', PHONE_CAT_ID)
    ->where('cm.pay_time >= ?', $params['from']." 00:00:00")
    ->where('cm.pay_time <= ?', $params['to']." 23:59:59");
            //->group(array('s.id','m.sn','m.good_id'));

    if ( isset($params['name']) && $params['name'] ) {
        $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
    }

    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }

    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
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
            $select->where('s.regional_market IN (?)', $params['regional_market']);
        elseif (is_numeric($params['regional_market']))
            $select->where('s.regional_market = ?', intval($params['regional_market']));
        else
            $select->where('1=0', 1);
    }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

        if (count($list_regions) > 0)
            $select->where( 's.regional_market IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }


    if ( isset($params['dash_sale']) && $params['dash_sale'] ) {

        $select->group(array('s.id','m.sn'));

            // echo $select; die;
        $result = $db->fetchAll($select);

    } else {

        $select->group(array('s.id','m.sn','m.good_id'));

        $temp = array(
            'area_id'       => 'AAA.area_id',
            'area_name'     => 'AAA.area_name',
            'staff_id'      => 'AAA.staff_id',
            'staff_code'    => 'AAA.staff_code',
            'staff_name'    => 'AAA.staff_name',
            'staff_group'   => 'AAA.staff_group',
            'sum_sellin'    => new Zend_Db_Expr("SUM(AAA.sellin)"),
            'sum_m_total'   => new Zend_Db_Expr("SUM(AAA.m_total)"),
            'com_rate'      => new Zend_Db_Expr(
                "
                SUM(
                CASE 
                WHEN AAA.good_id IN ('299','301','310','311') THEN 
                AAA.sellin*10
                ELSE 
                AAA.sellin*5
                END
                )
                "),
        );

        $main_select = $db->select()
        ->from(array('AAA' => $select), $temp)
        ->group(array('AAA.area_id','AAA.staff_id'));

            // echo $main_select; die; 
        $result = $db->fetchAll($main_select);
    } 

    return $result;

}

public function am_operator_rate($achieve_rate) {

    if ($achieve_rate > 120) { return 1.5; }
    if ($achieve_rate > 100) { return 1.2; }
    if ($achieve_rate >= 80) { return 1; }
    return 0;

}

    // Get AM Commission
public function com_am($params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'staff_id'    => 's.id',
        'staff_code'  => 's.code',
        'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'staff_group' => 'g.name',
        'base_kpi'    => 'ab.kpi',
        'target'      => new Zend_Db_Expr("(CASE WHEN amt.org_id <> o.org_id THEN 0 ELSE amt.target END)"),
        'channel'     => 'o.org_name',
            //'sellout'     => new Zend_Db_Expr("COUNT(ts.imei)"),
        'sellout'     => new Zend_Db_Expr(
            "   COUNT(
            CASE 
            WHEN s.id = 12873 AND o.org_id = 17 AND t.created_at >= '2019-02-01 00:00:00' AND t.created_at <= '2019-02-14 23:59:59' THEN 
            NULL 
            WHEN s.id = 2940 AND o.org_id = 45 AND t.created_at >= '2019-02-01 00:00:00' AND t.created_at <= '2019-02-14 23:59:59' THEN 
            NULL 
            WHEN s.id = 5661 AND o.org_id IN (17,45) AND t.created_at >= '2019-02-15 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN 
            NULL 

            WHEN s.id = 11429 AND o.org_id = 4 AND i.good_id <> 403 AND t.created_at >= '2019-06-01 00:00:00' AND t.created_at <= '2019-06-30 23:59:59' THEN
            NULL 
            WHEN s.id = 3082 AND o.org_id IN (9,44) AND i.good_id <> 403 AND t.created_at >= '2019-06-01 00:00:00' AND t.created_at <= '2019-06-30 23:59:59' THEN
            NULL 

            ELSE 
            ts.imei 
            END 
            )
            "),
    );

    $select = $db->select()
    ->from(array('s'  => 'staff'), $get)
    ->join(array('g'  => 'group')       , 's.group_id = g.id'           , array())
    ->join(array('amt'=> 'am_target'), 
        "   s.id = amt.staff_id 
        AND amt.from_date >= '".$params['from']."' 
        AND amt.to_date <= '".$params['to']."'
        ", array())
    ->join(array('ab' => 'am_base'), 
        "   s.id = ab.staff_id 
        AND ab.org_id = amt.org_id 
        AND ab.from_date <= '".$params['from']."' 
        AND ab.to_date >= '".$params['to']."'
        ", array())

    ->join(array('o'  => 'org')         , 
        "   ( 
        CASE 
        WHEN amt.org_id = 12 THEN o.org_id IN (12,19,10,41) 
        WHEN amt.org_id = 5 THEN o.org_id IN (5,20,43) 
        WHEN amt.org_id = 2 THEN o.org_id IN (2,30) 
        WHEN amt.org_id = 3 THEN o.org_id IN (3,26,42) 
        ELSE o.org_id = amt.org_id 
        END 
        )

        ", array())
    ->join(array('st' => 'store')       , 'o.org_id = st.org_dealer'    , array())
    ->join(array('t'  => 'timing')      , 'st.id = t.store'             , array())
    ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn'   , array())
    ->join(array('dma'=> 'distributor_map_am'), 
        "   ( 
        CASE 
        WHEN dma.org_id = 12 THEN o.org_id IN (12,19,10,41) 
        WHEN dma.org_id = 5 THEN o.org_id IN (5,20,43) 
        WHEN dma.org_id = 2 THEN o.org_id IN (2,30) 
        WHEN dma.org_id = 3 THEN o.org_id IN (3,26,42) 
        ELSE o.org_id = dma.org_id 
        END 
        )
        AND i.distributor_id = dma.d_id
        ", array())
    ->where('st.rank <> ?', 5)
    ->where('t.created_at >= ?', $params['from']." 00:00:00")
    ->where('t.created_at <= ?', $params['to']." 23:59:59")
    ->group('o.org_id')
    ->order('o.org_name ASC');

    if (isset($params['am_id']) && $params['am_id']) {
        $select->where('s.id = ?', $params['am_id']);
    }
/*
        if ( ( isset($params['export']) && $params['export'] == 1 ) || ( isset($params['export_flag']) && in_array($params['export_flag'], array(1,3))) ) {

            $focus_target = array(8747,2713,12873,2940,21141,42787,24222);
            if ( in_array($params['am_id'], $focus_target) ) { $select->where('i.good_id = ?', 403); }
            
        }
*/
        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function com_bm_rate($score) { 

        if ($score >= 100) { return 1.5; }
        if ($score >= 80) { return 1; }
        if ($score >= 60) { return 0.6; }
        return 0;
    }

    public function com_bm_acc_rate($group_id) { 

        switch ($group_id) {
            case PGPB_ID:   $acc_rate = 1.1;    break;
            case BM_ID:     $acc_rate = 1.5;    break;
            default:        $acc_rate = 0;      break;
        }

        return $acc_rate;
    }

    public function get_bm_exception() { 

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('be' => 'bm_exception'), array('staff_id' => 'be.staff_id'));

        //echo $select;die;
        $result = $db->fetchAll($select);

        return $result;
        
    }

    // Get BM-Dealer Commission By PC
    public function getComBMbyPC($params) {

        // print_r($params); echo "<br/>";

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');

        $get = array(
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',

            'bm_id'     => 's.id',
            'bm_code'   => 's.code',
            'bm_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),

            'st_target' => 'bct.target',
            'sellout'   => new Zend_Db_Expr(
                "   COUNT(
                CASE 
                WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN 
                CASE
                WHEN s.code = '6004261' THEN 
                CASE WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-20 23:59:59' THEN ts.imei END 
                WHEN s.code = '5802577' THEN 
                CASE WHEN t.created_at >= '2018-08-21 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN ts.imei END 
                WHEN s.code = '6101184' THEN 
                CASE WHEN t.created_at >= '2018-08-21 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6102015' THEN 
                CASE WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6104538' THEN 
                CASE WHEN t.created_at >= '2018-08-28 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6009703' THEN 
                CASE WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-23 23:59:59' THEN ts.imei END
                WHEN s.code = '6104467' THEN 
                CASE WHEN t.created_at >= '2018-08-24 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN ts.imei END 
                ELSE 
                ts.imei
                END
                WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-09-30 23:59:59' THEN 
                CASE 
                WHEN s.code = '6102683' THEN 
                CASE WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-09-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6105065' THEN 
                CASE WHEN t.created_at >= '2018-09-21 00:00:00' AND t.created_at <= '2018-09-30 23:59:59' THEN ts.imei END
                WHEN s.code = '6002516' THEN 
                CASE WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-09-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6105082' THEN 
                CASE WHEN t.created_at >= '2018-09-21 00:00:00' AND t.created_at <= '2018-09-30 23:59:59' THEN ts.imei END
                ELSE 
                ts.imei 
                END

                WHEN t.created_at >= '2018-10-01 00:00:00' AND t.created_at <= '2018-10-31 23:59:59' THEN 
                CASE 
                WHEN s.code = '6103094' THEN 
                CASE WHEN t.created_at >= '2018-10-01 00:00:00' AND t.created_at <= '2018-10-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6104765' THEN 
                CASE WHEN t.created_at >= '2018-10-23 00:00:00' AND t.created_at <= '2018-10-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6000061' THEN 
                CASE WHEN t.created_at >= '2018-10-25 00:00:00' AND t.created_at <= '2018-10-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6105082' THEN 
                CASE WHEN t.created_at >= '2018-10-01 00:00:00' AND t.created_at <= '2018-10-23 17:59:59' THEN ts.imei END
                WHEN s.code = '6009267' THEN 
                CASE WHEN t.created_at >= '2018-10-26 00:00:00' AND t.created_at <= '2018-10-31 23:59:59' THEN ts.imei END
                ELSE 
                ts.imei 
                END

                WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2018-12-31 23:59:59' THEN 
                CASE 
                WHEN s.code = '6001488' THEN 
                CASE WHEN t.created_at >= '2018-12-21 00:00:00' AND t.created_at <= '2018-12-31 23:59:59' THEN ts.imei END
                WHEN s.code = '5700058' THEN 
                CASE WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2018-12-20 23:59:59' THEN ts.imei END
                ELSE 
                ts.imei 
                END

                WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN 
                CASE 
                WHEN s.code = '6000900' AND st.id = 3921 THEN 
                CASE WHEN t.created_at >= '2019-01-21 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6105333' AND st.id = 3921 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6105333' AND st.id = 18961 THEN 
                CASE WHEN t.created_at >= '2019-01-21 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6102387' AND st.id = 18961 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6101932' AND st.id = 23615 THEN 
                CASE WHEN t.created_at >= '2019-01-21 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6201253' AND st.id = 18644 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6009397' AND st.id = 19840 THEN 
                CASE WHEN t.created_at >= '2019-01-22 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6009911' AND st.id = 19840 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-20 23:59:59' THEN ts.imei END
                WHEN s.code = '6011694' AND st.id = 23491 THEN 
                CASE WHEN t.created_at >= '2019-01-19 00:00:00' AND t.created_at <= '2019-01-31 23:59:59' THEN ts.imei END
                WHEN s.code = '6106523' AND st.id = 23491 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-18 23:59:59' THEN ts.imei END
                WHEN s.code = '6200327' AND st.id = 18644 THEN 
                CASE WHEN t.created_at >= '2019-01-01 00:00:00' AND t.created_at <= '2019-01-20 23:59:59' THEN ts.imei END
                ELSE 
                ts.imei 
                END

                WHEN t.created_at >= '2019-02-01 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN 

                CASE 
                WHEN s.code = '6201255' AND st.id = 23662 THEN 
                CASE WHEN t.created_at >= '2019-02-05 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN ts.imei END
                WHEN s.code = '6200647' AND st.id = 15839 THEN 
                CASE WHEN t.created_at >= '2019-02-01 00:00:00' AND t.created_at <= '2019-02-19 23:59:59' THEN ts.imei END
                WHEN s.code = '6101912' AND st.id = 15839 THEN 
                CASE WHEN t.created_at >= '2019-02-20 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN ts.imei END
                WHEN s.code = '5901459' AND st.id = 21067 THEN 
                CASE WHEN t.created_at >= '2019-02-04 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN ts.imei END
                WHEN s.code = '6102779' AND st.id = 23218 THEN 
                CASE WHEN t.created_at >= '2019-02-05 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN ts.imei END
                ELSE 
                ts.imei 
                END

                WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-03-31 23:59:59' THEN 

                CASE 
                WHEN s.code = '6100125' AND st.id = 23590 THEN 
                CASE WHEN t.created_at >= '2019-03-14 00:00:00' AND t.created_at <= '2019-03-31 23:59:59' THEN ts.imei END

                ELSE 
                ts.imei 
                END

                ELSE 
                ts.imei
                END 
                )
                "), 

'price' => new Zend_Db_Expr("SUM(gkl.price)"),
);

$select = $db->select()
->from(array('st' => 'store'), $get)
->join(array('o'  => 'org')        , 'st.org_dealer = o.org_id'    , array())
->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 3', array())
->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'          , array())
->joinLeft(array('bct'=>'bm_com_target'), 
    "   st.id = bct.store_id
    AND bct.from_date >= '".$params['from']."' 
    AND bct.to_date <= '".$params['to']."' 
    ", array())
->joinLeft(array('t'  =>'timing')       , 
    "   st.id = t.store
    AND t.created_at >= '".$params['from']." 00:00:00' 
    AND t.created_at <= '".$params['to']." 23:59:59'
    ", array())
->joinLeft(array('ts' =>'timing_sale')  , 't.id = ts.timing_id', array())
->joinLeft(array('gkl' => 'good_kpi_log'), 
    "   gkl.good_id = ts.product_id 
    AND gkl.color_id = ts.model_id 
    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
    ", array())

->where('st.del IS NULL')
->where('st.rank <> ?', 5)
->where('st.org_dealer IN (?)', array(18,21,22,23,31,33,34,37,39,40,47) )
->group(array('st.id','s.id'))
->order(array('a.name ASC', 'st.id ASC'));

        // echo $select; die;
$result = $db->fetchAll($select);
return $result;

}

    // Get BM-OPPO Commission [ACC]
public function getAccComBMbyOppo($params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'area_name'     => 'a.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_type'       => 'o.org_name',

        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'group_id'      => 'g.id',
        'group_name'    => 'g.name',
        'pc_stand_by'   => 's.pc_stand_by',
/*
            'acc_price'     => new Zend_Db_Expr(
                "   ROUND( TRUNCATE(( SUM( (ROUND((( m.price - ((m.price*IFNULL(m.sale_off_percent,0)/100)*100)/100 )/1.07),2)*m.num) ) - m.total_spc_discount + ( IFNULL(m.delivery_fee/1.07,0) ) ) ,2),2)
                "),
*/
                'acc_price' => new Zend_Db_Expr(
                    "   ROUND( TRUNCATE(( SUM( 
                    CASE WHEN (m.good_id NOT IN (375,376,377,378,379,380,381,382,383,384,386,387,404,405,406,407,408,409,410,411,
                    419,420,421,422,425,426,427,428,429,430,431,432)) THEN 
                    (ROUND((( m.price - ((m.price*IFNULL(m.sale_off_percent,0)/100)*100)/100 )/1.07),2)*m.num) 
                    ELSE 0 END) - m.total_spc_discount + ( IFNULL(m.delivery_fee/1.07,0) ) ) ,2),2)
                    "),
            );

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id'         , array())
    ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())
    ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
            // ->join(array('d'  => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'  , array())
    ->join(array('m'  => WAREHOUSE_DB.'.market'), 
        "   st.d_id = m.d_id 
        AND m.finance_confirm_date >= '".$params['from']." 00:00:00'
        AND m.finance_confirm_date <= '".$params['to']." 23:59:59'
        ", array())

    ->where('ss.is_leader IN (?)', array(0,3))
    ->where('st.del IS NULL')
    ->where('st.rank <> ?', 5)
    ->where('m.warehouse_id IN (?)', array(9,11,14,16,18,19,20,23,34,35,72,97,99,104,106,112,127,145,146,147,149,150,151,152,155,156,157,158,159,161,163,165,166,168) )
    ->where('m.canceled <> ?', 1)
    ->where('m.status = ?', 1)
    ->where('m.cat_id = ?', ACCESS_CAT_ID)
    ->where('m.isbacks <> ?', 1)
            // ->where('a.id IN (?)', array(38,95,117))
    ->group(array('st.id','s.id'))
    ->order(array('a.name ASC', 'st.id ASC', 'g.name ASC', 's.code ASC'));

        // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;

}

    // Get BM-OPPO Commission [Mobile]
public function getComBMbyOppo($params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'area_name'     => 'a.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',

        'st_target'     => 'bct.target',
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"), 
        'price'         => new Zend_Db_Expr("ROUND((SUM(gkl.price)*100)/107,2)"), 

            /*
            'price'         => new Zend_Db_Expr(
                "   ROUND( TRUNCATE(( SUM( (ROUND((( m.price - ((m.price*IFNULL(m.sale_off_percent,0)/100)*100)/100 )/1.07),2)*m.num) ) - m.total_spc_discount + ( IFNULL(m.delivery_fee/1.07,0) ) ) ,2),2)
                "),*/
            );

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('rm' => 'regional_market')     , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')                , 'rm.area_id = a.id'           , array())
    ->join(array('t'  =>'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$params['from']." 00:00:00' 
        AND t.created_at <= '".$params['to']." 23:59:59'
        ", array())
    ->join(array('ts' =>'timing_sale'), 't.id = ts.timing_id', array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), 
        "   st.d_id = i.distributor_id 
        AND ts.imei = i.imei_sn 
        AND ts.product_id = i.good_id 
        AND ts.model_id = i.good_color 
        "  , array())
            // ->join(array('m'  => WAREHOUSE_DB.'.market'), 
            //     "   i.sales_sn = m.sn
            //         AND i.good_id = m.good_id 
            //         AND i.good_color = m.good_color 
            //         AND m.invoice_time >= '".$params['from']." 00:00:00'
            //         AND m.invoice_time <= '".$params['to']." 23:59:59'
            //     ", array())
    ->join(array('gkl' => 'good_kpi_log'), 
        "   gkl.good_id = i.good_id 
        AND gkl.color_id = i.good_color  
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())

    ->joinLeft(array('bct'=>'bm_com_target'), 
        "   st.id = bct.store_id
        AND bct.from_date >= '".$params['from']."' 
        AND bct.to_date <= '".$params['to']."' 
        ", array())

    ->where('st.del IS NULL')
    ->where('st.rank <> ?', 5)
            // ->where('m.warehouse_id IN (?)', array(9,11,14,16,18,19,20,23,34,35,72,97,99,104,106,112,127) )
            // ->where('m.canceled <> ?', 1)
            // ->where('m.status = ?', 1)
            // ->where('m.cat_id = ?', PHONE_CAT_ID)
            // ->where('m.isbacks <> ?', 1)
            // ->where('m.canceled <> ?', 1)
    ->where('i.old_data IS NULL', 1)
    ->where('st.id = ?', $params['store_id'])
    ->group(array('st.id'))
    ->order(array('a.name ASC', 'st.id ASC'));

        // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;

}

    // Get Commission Acc Film Focus By Staff 
public function getAccComFF($params) {

    $ff_list = array(
        375,376,377,378,379,380,381,382,383,384,386,387,404,405,406,407,408,409,410,411,
        419,420,421,422,425,426,427,428,429,430,431,432,
    );

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'staff_code' => new Zend_Db_Expr("m.staff_code"), 
        'com_rate'   => new Zend_Db_Expr(
            "   COALESCE(SUM( 
            CASE 
            WHEN m.good_id IN (375,376,377,378,379,386,404,405,406,407) THEN m.num*40 
            WHEN m.good_id IN (380,381,382,383,384,387,408,409,410,411) THEN m.num*120 
            WHEN m.good_id IN (419,420,421,422) THEN m.num*150 
            WHEN m.good_id IN (425,426,427,428,429,430,431,432) THEN m.num*200 
            ELSE 0 
            END
            ),0)
            "), 
    );

    $select = $db->select()
    ->from(array('m' => WAREHOUSE_DB.'.market'), $get)
    ->where('m.invoice_number IS NOT NULL', 1)
    ->where('m.canceled <> ?', 1)
    ->where('m.status = ?', 1)
    ->where('m.total > ?', 0)
    ->where('m.outmysql_time IS NOT NULL', 1)
    ->where('m.good_id IN (?)', $ff_list)
    ->where('m.staff_code = ?', $params['staff_code'])
    ->where('m.outmysql_time >= ?', $params['from']." 00:00:00")
    ->where('m.outmysql_time <= ?', $params['to']." 23:59:59")
    ->group('m.staff_code');

        // echo $select."<br/>"; 
    $result = $db->fetchRow($select);
    return $result;

}

    // Get Sale's Sellout By Store
public function getSelloutByStore($params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'unit'      => new Zend_Db_Expr("COUNT(ts.imei)"), 
        'price'     => new Zend_Db_Expr("COALESCE(SUM(gkl.price),0)"), 
    );

    $select = $db->select()
    ->from(array('t' => 'timing'), $get)
    ->join(array('ts' =>'timing_sale'), 't.id = ts.timing_id', array())
    ->join(array('gkl' => 'good_kpi_log'), 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id  
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->where('t.store IN (?)', $params['store_list'])
    ->where('t.created_at >= ?', $params['from']." 00:00:00")
    ->where('t.created_at <= ?', $params['to']." 23:59:59");

        // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;

}

    // Get Sale's Sellout By Sale
public function getSelloutBySale($params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');

    $get = array(
        'unit'      => new Zend_Db_Expr("COUNT(ts.imei)"), 
        'price'     => new Zend_Db_Expr("COALESCE(SUM(gkl.price),0)"), 
    );

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->join(array('t'  => 'timing')          , 'st.id = t.store'             , array())
    ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())
    ->join(array('gkl' => 'good_kpi_log'), 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id  
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->where('st.rank <> ?', 5)
    ->where('t.sales_id = ?', $params['sale_id'])
    ->where('a.id = ?', $params['area_id'])
    ->where('t.created_at >= ?', $params['from']." 00:00:00")
    ->where('t.created_at <= ?', $params['to']." 23:59:59");

        // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;

}

public function getSaleAchieveRate($score) {

    if ($score >= 150) { return 0.002; }
    if ($score >= 120) { return 0.0015; }
    if ($score >= 100) { return 0.001; }
        // if ($score >= 70) { return 1; }

    return 1;
    
}

public function comission_extra($params) {

        // TME Store : Updated 11 Jul 2019
    $store_tme = array(
        '21622','21635','21637','21681','23019','23020','23023','23024','23025','23026',
        '24517','24519','24535','24538','24539','24545','24547','24556','24558','24562',
        '24564','24577','24578','24583','24593','24594','24595','24596','24604','24605',
        '24606','24607','24608','24609','24611','24612','24613','24614','24615','24616',
        '24618','24620','24621','24623','24625','24627','24628','24630','24631','24634',
        '24642','24647','24649','24656','24660','24662','24663','24665',
        '24673','24674','24683','24536','24675','24619','24677','24676','24546','24557',
        '24563','24575','24686','24689','24629','24682','24685','24637','24668','24694',
        '24644','24670','24684','24671','24693','24635','24639','24681','24688','24669',
        '24687','24640','24638','24610' 
    );

    $db = Zend_Registry::get('db');

    $get = array(
        'staff_id'  => 't.sales_id',
        'total_unit'=> new Zend_Db_Expr("COUNT(ts.imei)"),
        'com_rate'  => new Zend_Db_Expr("SUM(gkl.com_rate)"),
    );

    $select = $db->select()
    ->from(array('t'   => 'timing'), $get)
    ->join(array('ts'  => 'timing_sale'), 't.id = ts.timing_id', array())
    ->join(array('gkl' => 'good_kpi_log'), 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id 
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->join(array('i'   => WAREHOUSE_DB.'.imei'), 
        "   ts.imei = i.imei_sn 
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d') 
        ", array())
    ->where('t.sales_id = ?', $params['sale_id'])
    ->where('t.store IN (?)', $store_tme)
    ->where('t.created_at >= ?', '2019-06-01 00:00:00')
    ->where('t.created_at <= ?', '2019-06-02 23:59:59')
    ->group('t.sales_id');

        // echo $select; die;
    $result = $db->fetchRow($select);

    return $result;
}

}