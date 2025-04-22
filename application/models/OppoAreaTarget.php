<?php
class Application_Model_OppoAreaTarget extends Zend_Db_Table_Abstract
{
	protected $_name = 'oppo_area_target';

    function getLast3MonthByArea($params) {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $db = Zend_Registry::get('db');

        // Range of Last 1 Month
        $tmp_start_01 = new DateTime( $params['target_from'] );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );

        $tmp_end_01 = new DateTime( $params['target_from'] );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        // Range of Last 2 Month
        $tmp_start_02 = new DateTime( $last_start_01 );
        $tmp_start_02->modify( 'first day of previous month' );
        $last_start_02 = $tmp_start_02->format( 'Y-m-d' );

        $tmp_end_02 = new DateTime( $last_end_01 );
        $tmp_end_02->modify( 'last day of previous month' );
        $last_end_02 = $tmp_end_02->format( 'Y-m-d' );

        // Range of Last 3 Month
        $tmp_start_03 = new DateTime( $last_start_02 );
        $tmp_start_03->modify( 'first day of previous month' );
        $last_start_03 = $tmp_start_03->format( 'Y-m-d' );

        $tmp_end_03 = new DateTime( $last_end_02 );
        $tmp_end_03->modify( 'last day of previous month' );
        $last_end_03 = $tmp_end_03->format( 'Y-m-d' );

        $lastDayThisMonth = date("Y-m-t");
        $now_date = date("Y-m-01");

          // Sub Query 01 : Last 3 Month
        $sub_select_01 = $db->select()
        ->from(array('aso' => 'oppo_area_target'), array('aso.target'))
        ->where('aso.from_date >= ?', $last_start_03)
        ->where('aso.to_date <= ?', $last_end_03)
        ->where('aso.area_id = a.id');

        // Sub Query 02 : Last 2 Month
        $sub_select_02 = $db->select()
        ->from(array('aso2' => 'oppo_area_target'), array('aso2.target'))
        ->where('aso2.from_date >= ?', $last_start_02)
        ->where('aso2.to_date <= ?', $last_end_02)
        ->where('aso2.area_id = a.id');

        // Sub Query 03 : Last 1 Month
        $sub_select_03 = $db->select()
        ->from(array('aso3' => 'oppo_area_target'), array('aso3.target'))
        ->where('aso3.from_date >= ?', $last_start_01)
        ->where('aso3.to_date <= ?', $last_end_01)
        ->where('aso3.area_id = a.id');

        // Sub Query 01 : Sellout Last 3 Month
        $sub_select_06 = $db->select()
        ->from(array('t' => 'timing'),array('COUNT(t.id)'))
        ->joinLeft(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
        ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())
        ->where('t.created_by != 6535')
        ->where('ts.imei NOT IN (select imei from timing_control)')
        ->where('t.created_at >= ?', $last_start_03.' 00:00:00')
        ->where('t.created_at <= ?', $last_end_03.' 23:59:59')
        ->where('rm.area_id = a.id');

        // Sub Query 02 : Sellout Last 2 Month
        $sub_select_07 = $db->select()
        ->from(array('t' => 'timing'),array('COUNT(t.id)'))
        ->joinLeft(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
        ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())
        ->where('t.created_by != 6535')
        ->where('ts.imei NOT IN (select imei from timing_control)')
        ->where('t.created_at >= ?', $last_start_02.' 00:00:00')
        ->where('t.created_at <= ?', $last_end_02.' 23:59:59')
        ->where('rm.area_id = a.id');

        // Sub Query 03 : Sellout Last 1 Month
        $sub_select_08 = $db->select()
        ->from(array('t' => 'timing'),array('COUNT(t.id)'))
        ->joinLeft(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
        ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())
        ->where('t.created_by != 6535')
        ->where('ts.imei NOT IN (select imei from timing_control)')
        ->where('t.created_at >= ?', $last_start_01.' 00:00:00')
        ->where('t.created_at <= ?', $last_end_01.' 23:59:59')
        ->where('rm.area_id = a.id');

        // Sub Query  : Sellout This Month
        $sub_select_09 = $db->select()
        ->from(array('t' => 'timing'),array('COUNT(t.id)'))
        ->joinLeft(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
        ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())
        ->where('t.created_by != 6535')
        ->where('ts.imei NOT IN (select imei from timing_control)')
        ->where('t.created_at >= ?', $from.' 00:00:00')
        ->where('t.created_at <= ?', $to.' 23:59:59')
        ->where('rm.area_id = a.id');

        $sub_select_04 = $db->select()
        ->from(array('mkt' => 'oppo_mkt_target'),array('SUM(mkt.target)'))
        ->joinLeft(array('s' => 'staff'),'s.id = mkt.staff_id',array())
        ->joinLeft(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
        ->where('mkt.from_date >= ?',$now_date)
        ->where('mkt.to_date <= ?', $lastDayThisMonth)
        ->where('rm.area_id = a.id');

        $sub_select_05 = $db->select()
        ->from(array('salse' => 'oppo_sale_target'),array('SUM(salse.target)'))
        ->where('salse.from_date >=?',$now_date)
        ->where('salse.to_date <=?',$lastDayThisMonth)
        ->where('salse.area_id = a.id');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'last_03'   => new Zend_Db_Expr("(".$sub_select_01.")"),
            'last_02'   => new Zend_Db_Expr("(".$sub_select_02.")"),
            'last_01'   => new Zend_Db_Expr("(".$sub_select_03.")"),

            'mkt_target'    => new Zend_Db_Expr("(".$sub_select_04.")"),
            'salse_target'    => new Zend_Db_Expr("(".$sub_select_05.")"),

            'sellout_last_03' => new Zend_Db_Expr("(".$sub_select_06.")"),
            'sellout_last_02' => new Zend_Db_Expr("(".$sub_select_07.")"),
            'sellout_last_01' => new Zend_Db_Expr("(".$sub_select_08.")"),

            'sellout'   => new Zend_Db_Expr("(".$sub_select_09.")")
        );

        $select = $db->select()
        ->from(array('a' => 'area'), $get)
        ->group('a.id')
        ->order(array('a.name ASC'));

        $result_raw = $db->fetchAll($select);
        $result = array();
        for ($i=0;$i<count($result_raw);$i++) {

            $result[ $result_raw[$i]['area_id'] ]['last_03'] = $result_raw[$i]['last_03'];
            $result[ $result_raw[$i]['area_id'] ]['last_02'] = $result_raw[$i]['last_02'];
            $result[ $result_raw[$i]['area_id'] ]['last_01'] = $result_raw[$i]['last_01'];

            $result[ $result_raw[$i]['area_id'] ]['sellout_last_03'] = $result_raw[$i]['sellout_last_03'];
            $result[ $result_raw[$i]['area_id'] ]['sellout_last_02'] = $result_raw[$i]['sellout_last_02'];
            $result[ $result_raw[$i]['area_id'] ]['sellout_last_01'] = $result_raw[$i]['sellout_last_01'];

            $result[ $result_raw[$i]['area_id'] ]['mkt_target'] = $result_raw[$i]['mkt_target'];
            $result[ $result_raw[$i]['area_id'] ]['salse_target'] = $result_raw[$i]['salse_target'];
            $result[ $result_raw[$i]['area_id'] ]['sellout'] = $result_raw[$i]['sellout'];

        }

        return $result;
    }

    function getOverViewTarget($params) {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $db = Zend_Registry::get('db');

        $sub_select_01 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.store = st.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_02 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.store = st.id')
        ->where('t.staff_id = s2.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_03 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.staff_id = s2.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_04 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("SUM(gkl.price)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->join(array('gkl' => 'good_kpi_log'), 
            "   gkl.good_id = ts.product_id 
            AND gkl.color_id = ts.model_id 
            AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
            AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
            ", array())
        ->where('t.staff_id = s2.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'oat_target'    => 'oat.target',
            'oat_target_hero'=>'oat.target_hero',
            'oit_target'    => 'oit.target',

            'sale_code'     => 's.code',
            'sale_name'     => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
            'sale_group'    => 'g.name', 
            'ost_target'    => 'ost.target', 
            'ost_target_hero'   => 'ost.target_hero', 
            'ost_target_price'  => 'ost.target_price', 

            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_del'        => 'st.del', 
            'st_type'       => 'o.org_name',
            'mt_name'       => 'mt.name',
            'mn_id'         => 'mn.id', 
            'mn_name'       => 'mn.name',

            'pc_id'         => 's2.id',
            'pc_code'       => 's2.code',
            'pc_name'       => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"), 
            'pc_group'      => 'g2.name', 
            'pc_stand_by'   => new Zend_Db_Expr("(CASE WHEN s2.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),
            'work_day_month'=> new Zend_Db_Expr("(TIMESTAMPDIFF(MONTH, s2.joined_at, '".$to."'))"), 

            'opt_target'    => 'opt.target', 
            'opt_target_hero'   => 'opt.target_hero', 
            'opt_target_price'  => 'opt.target_price', 

            'sellout'               => new Zend_Db_Expr("(".$sub_select_01.")"),
            'pc_sellout'            => new Zend_Db_Expr("(".$sub_select_02.")"),
            'pc_sellout_all_store'  => new Zend_Db_Expr("(".$sub_select_03.")"),
            'pc_price_all_store'    => new Zend_Db_Expr("(".$sub_select_04.")"),
        );

        $select = $db->select()
        ->from(array('a' => 'area'), $get)
        ->join(array('rm' => 'regional_market') , 'a.id = rm.area_id'           , array())
        ->join(array('st' => 'store')           , 'rm.id = st.regional_market'  , array())
        ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
        ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
        ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
        ->join(array('mt' => 'market_type')     , 'mn.market_type_id = mt.id'   , array())

        ->joinLeft(array('oat' => 'oppo_area_target'), 
            "   a.id = oat.area_id 
            AND oat.from_date >= '".$params['target_from']."' 
            AND oat.to_date <= '".$params['target_to']."' 
            ", array())

        ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1'    , array())
        ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id AND s.off_date IS NULL'   , array())
        ->joinLeft(array('g'  => 'group')       , 's.group_id = g.id'                           , array())
        ->joinLeft(array('ost' => 'oppo_sale_target'),
            "   s.id = ost.staff_id 
            AND ost.from_date >= '".$params['target_from']."' 
            AND ost.to_date <= '".$params['target_to']."' 
            ", array())

        ->joinLeft(array('ss2'=> 'store_staff') , 'st.id = ss2.store_id AND ss2.is_leader = 0'  , array())
        ->joinLeft(array('s2' => 'staff')       , 'ss2.staff_id = s2.id AND s2.off_date IS NULL', array())
        ->joinLeft(array('g2' => 'group')       , 's2.group_id = g2.id'                         , array())
        ->joinLeft(array('opt' => 'oppo_pc_target'), 
            "   st.id = opt.store_id 
            AND opt.from_date >= '".$params['target_from']."' 
            AND opt.to_date <= '".$params['target_to']."' 
            ", array())

        ->joinLeft(array('oit' => 'oppo_individual_target'), 'oit.staff_id = s2.id',array())
        ->joinLeft(array('ssl' => 'store_staff_log'),'ssl.staff_id = oit.staff_id',array())

        ->where('a.id NOT IN (48,49,72)')
            // ->where('st.rank <> 3')
        ->where('st.del IS NULL')
            // ->group('st.id')
        ->order(array('a.name ASC','s.code ASC','st.id ASC','s2.code ASC'));

        if (isset($params['area_rd']) && $params['area_rd']) {
            $select->where('a.id IN (?)', $params['area_rd']);
        } 

        // Add Filter Area
        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                $select->where('a.id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                $select->where('a.id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

// PC Target
    function getPCTracking($params) {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $db = Zend_Registry::get('db');

        $sub_select_01 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.store = st.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_02 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.store = st.id')
        ->where('t.staff_id = s.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_03 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->where('t.staff_id = s.id')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

// Activate 
        $sub_select_04 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
        ->where('t.staff_id = s.id')
        ->where('i.activated_date IS NOT NULL')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_05 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
        // ->join(array('go' => WAREHOUSE_DB.'.good'),'go.id = ts.product_id',array())
        ->where('t.staff_id = s.id')
        ->where('ts.is_hero =?',1)
        // ->where('go.hero_product =?',1)
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $sub_select_06 = $db->select()
        ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
        ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
        ->where('ts.is_hero =?',1)
        // ->join(array('go' => WAREHOUSE_DB.'.good'),'go.id = ts.product_id',array())
        ->where('t.staff_id = s.id')
        // ->where('go.hero_product =?',1)
        ->where('i.activated_date IS NOT NULL')
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59");

        $get = array(

            'area_id' => 'a.id',

            'pc_name' => new Zend_Db_Expr("CONCAT(s.firstname,'',s.lastname)"),
            'pc_code' => 's.code',
            'st_id'   => 'st.id',
            'st_name' => 'st.name',
            'pc_group'  => 'g.name',
            'pc_target' => 'oit.target',
            'province'  => 'rm.name',
            'target_hero' => 'oit.target_hero',

            'sellout'     => new Zend_Db_Expr("(".$sub_select_01.")"),
            'pc_sellout'            => new Zend_Db_Expr("(".$sub_select_02.")"),
            'pc_sellout_all_store'  => new Zend_Db_Expr("(".$sub_select_03.")"),
            'activate'  => new Zend_Db_Expr("(".$sub_select_04.")"),
            'hero_product_sellout' => new Zend_Db_Expr("(".$sub_select_05.")"),
            'hero_product_activate' => new Zend_Db_Expr("(".$sub_select_06.")"),

        );

        $select = $db->select()
        ->from(array('a' => 'area'), $get)
        ->join(array('rm' => 'regional_market'),'rm.area_id = a.id',array())
        ->join(array('st' => 'store'),'rm.id = st.regional_market',array())
        ->join(array('oat' => 'oppo_area_target'),
            " a.id = oat.area_id 
            AND oat.from_date >= '".$params['target_from']."'
            AND oat.to_date <= '".$params['target_to']."'
            ",array())
        ->joinLeft(array('ss' => 'store_staff'),'st.id = ss.store_id',array())
        ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id AND s.off_date IS NULL',array())
        ->joinLeft(array('g' => 'group'),'s.group_id = g.id',array())
        ->joinLeft(array('oit' => 'oppo_individual_target'),'oit.staff_id = s.id',array())
        ->joinLeft(array('ssl' => 'store_staff_log'),'ssl.staff_id = oit.staff_id',array())

        ->where('st.del IS NULL')
        ->where('s.group_id =?',4)
        ->where('s.status =?',1)
        ->group('s.id')
        ->order(array('a.name ASC','s.code ASC','st.id ASC'));


        if (isset($params['area_rd']) && $params['area_rd']) {
            $select->where('a.id IN (?)', $params['area_rd']);
        }
        // Add Filter Area
        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                $select->where('a.id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                $select->where('a.id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getDailySelloutByPC($params) {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $db = Zend_Registry::get('db');

        $day_loop = (( strtotime($to) - strtotime($from) ) / (24*60*60)) + 1;

        $get_01 = array(
            'ga_name'       => 'ga.name',
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
            'staff_group'   => 'g.name', 
            'joined_at'     => 's.joined_at',
            'off_date'      => 's.off_date',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_type'       => 'o.org_name',
            'total_sellout' => new Zend_Db_Expr("COUNT(ts.imei)"), 

            'cnt_focus_01'  => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id = 345 THEN ts.imei END)"), 
            'cnt_focus_others' => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id NOT IN (345) THEN ts.imei END)"), 
            
            'month_diff'    => new Zend_Db_Expr(
                "
                (
                CASE 
                WHEN s.off_date IS NULL THEN 
                TIMESTAMPDIFF(MONTH, DATE(s.joined_at), '".$to."') 
                ELSE 
                TIMESTAMPDIFF(MONTH, DATE(s.joined_at), s.off_date) 
                END
                )
                "),
        );

        for ($i=0;$i<$day_loop;$i++) { 
            $day = date('Y-m-d', strtotime("+".$i." Day" , strtotime($from)));

            $get_02['sellout_'.$i] = new Zend_Db_Expr(
                "COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
        ->from(array('s'  => 'staff'), $get)
        ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
        ->join(array('t'  => 'timing')          , 's.id = t.staff_id'           , array())
        ->join(array('st' => 'store')           , 't.store = st.id'             , array())
        ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
        ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
        ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
        ->join(array('gal'=> 'grand_area_list') , 'a.id = gal.area'             , array())
        ->join(array('ga' => 'grand_area')      , 'gal.grand_area_id = ga.id'   , array())
        ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())
        ->where('s.group_id = ?', PGPB_ID)
        ->where('st.rank <> ?', 3)
        ->where('t.created_at >= ?', $from." 00:00:00")
        ->where('t.created_at <= ?', $to." 23:59:59")
            //->group(array('s.id','st.id'))
        ->order(array('ga.name ASC','a.name ASC','s.code ASC'));

        // Add Filter Area
        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                $select->where('a.id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                $select->where('a.id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['by_shop']) && $params['by_shop']) { 
            $select->group(array('st.id')); 
            for ($i=0;$i<$day_loop;$i++) { $select->orHaving("sellout_".$i." >= ?", 15); }
        } else { 
            $select->group(array('s.id','st.id')); 
            for ($i=0;$i<$day_loop;$i++) { $select->orHaving("sellout_".$i." >= ?", 10); }
        }

        //echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}

function getStoreTargetBySale($params) {

    $tmp_from = explode('/', $params['from']);
    $tmp_to = explode('/', $params['to']);

    $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
    $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

    $db = Zend_Registry::get('db');

    $get = array(
    // 'grand_area'    => new Zend_Db_Expr(
    //     "   (
    //             CASE 
    //                 WHEN a.name LIKE 'BKK-E1%' THEN 'BKK East-1' 
    //                 WHEN a.name LIKE 'BKK-E2%' THEN 'BKK East-2' 
    //                 WHEN a.name LIKE 'BKK-E3%' THEN 'BKK East-3' 
    //                 WHEN a.name LIKE 'BKK-E4%' THEN 'BKK East-4' 
    //                 WHEN a.name LIKE 'BKK-E5%' THEN 'BKK East-5' 
    //                 WHEN a.name LIKE 'BKK-W1%' THEN 'BKK West-1' 
    //                 WHEN a.name LIKE 'BKK-W2%' THEN 'BKK West-2' 
    //                 WHEN a.name LIKE 'BKK-W3%' THEN 'BKK West-3' 
    //                 ELSE a.name 
    //             END
    //         )
    //     "),
        'area_id'       => 'a.id',
        'area_name'     => 'a.name',
        'sale_code'     => 's.code',
        'sale_name'     => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'oppo_id'       => 'st.oppo_id',
        'store_code'    => 'st.store_code',
        'province'  => 'rm.name',
        'st_target_price' => 'opt.target',
        'target_hero' => 'opt.target_hero',
        'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN NOT g.cat_id = 15 THEN ts.imei END)"), 
        'sellout_price' => new Zend_Db_Expr("COALESCE(SUM(gkl.price), 0)"), 
        'activate'     => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN i.imei_sn END)"), 
        'hero_product_activate'     => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND ts.is_hero = 1 THEN i.imei_sn END)"),
        'hero_product_sellout'     => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND ts.is_hero = 1 THEN i.imei_sn END)"),
    );

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())

    ->joinLeft(array('ss' => 'store_staff')     , 'st.id = ss.store_id AND ss.is_leader = 1', array())
    ->joinLeft(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())

    ->joinLeft(array('opt' => 'oppo_pc_target'), 
        "   st.id = opt.store_id 
        AND opt.from_date >= '".$params['target_from']."' 
        AND opt.to_date <= '".$params['target_to']."'
        ", array())
    
    ->joinLeft(array('t' => 'timing'), 
        "   st.id = t.store 
        AND t.created_at >= '".$from." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        ", array())
    ->joinLeft(array('ts'  => 'timing_sale'), 't.id = ts.timing_id'     , array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
    ->joinLeft(array('gkl' => 'good_kpi_log'), 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id 
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    // ->where('opt.from_date >= ?', $params['target_from'])
    // ->where('opt.to_date <= ?', $params['target_to'])
    ->where('st.del IS NULL')
    ->group(array('st.id'))
    ->order(array('a.name ASC','st.id ASC'));

// Add Filter Area
    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $select->where('a.id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $select->where('a.id = ?', intval($params['area']));
        else
            $select->where('1=0', 1);
    }

//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}

// Get Area target
function getareatarget($params){

    $tmp_from = explode('/', $params['from']);
    $tmp_to = explode('/', $params['to']);

    $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
    $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

    $db = Zend_Registry::get('db');

    $sub_select_01 = $db->select()
    ->from(array('t' => 'timing'),array('sellout' => new Zend_Db_Expr("COUNT(t.id)")))
    ->join(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'), 'g.id = ts.product_id',array())
    ->join(array('s' => 'store'),'s.id = t.store',array())
    ->join(array('rm' =>'regional_market'),'rm.id = s.regional_market',array())
    ->where('NOT g.cat_id =?',15)
    ->where('t.created_at <= ?',$params['to'])
    ->where('t.created_at >= ?',$params['from'])
    ->where('rm.area_id = a.id');

    $sub_select_02 = $db->select()
    ->from(array('t3' => 'timing'),array('product' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
    ->join(array('ts3' => 'timing_sale'),'ts3.timing_id = t3.id',array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts3.imei',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'), 'g.id = ts3.product_id',array())
    ->join(array('s3' => 'store'),'s3.id = t3.store',array())
    ->join(array('rm3' =>'regional_market'),'rm3.id = s3.regional_market',array())
    ->where('NOT g.cat_id =?',15)
    ->where('i.activated_date IS NOT NULL')
    ->where('t3.created_at <= ?',$params['to'])
    ->where('t3.created_at >= ?',$params['from'])
    ->where('rm3.area_id = a.id');

    $sub_select_03 = $db->select()
    ->from(array('t3' => 'timing'),array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
    ->join(array('ts3' => 'timing_sale'),'ts3.timing_id = t3.id',array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts3.imei',array())
    ->join(array('s3' => 'store'),'s3.id = t3.store',array())
    ->join(array('rm3' =>'regional_market'),'rm3.id = s3.regional_market',array())
    // ->join(array('go' => WAREHOUSE_DB.'.good'),'go.id = ts3.product_id',array())
    // ->where('NOT go.cat_id =?',15)
    // ->where('go.hero_product =?',1)
    ->where('t3.created_at <= ?',$params['to'])
    ->where('t3.created_at >= ?',$params['from'])
    ->where('rm3.area_id = a.id')
    ->where('ts3.is_hero = ?',1);

    $sub_select_04 = $db->select()
    ->from(array('t3' => 'timing'),array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
    ->join(array('ts3' => 'timing_sale'),'ts3.timing_id = t3.id',array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts3.imei',array())
    ->join(array('s3' => 'store'),'s3.id = t3.store',array())
    ->join(array('rm3' =>'regional_market'),'rm3.id = s3.regional_market',array())
    // ->join(array('go' => WAREHOUSE_DB.'.good'),'go.id = ts3.product_id',array())
    // ->where('NOT go.cat_id =?',15)
    // ->where('go.hero_product =?',1)
    ->where('ts3.is_hero = ?',1)
    ->where('i.activated_date IS NOT NULL')
    ->where('t3.created_at <= ?',$params['to'])
    ->where('t3.created_at >= ?',$params['from'])
    ->where('rm3.area_id = a.id');

    $get = array(
        'area_name'     => 'a.name',
        'area_target'   => 'oat.target',
        'target_hero'   => 'oat.target_hero',
        'from'   => 'oat.from_date',
        'to'            => 'oat.to_date',
        'rd_name'       => new Zend_Db_Expr("CONCAT(st.firstname, ' ', st.lastname)"),
        'sellout'       => new Zend_Db_Expr("(".$sub_select_01.")"),
        'activate'       => new Zend_Db_Expr("(".$sub_select_02.")"),
        'hero_product_sellout'       => new Zend_Db_Expr("(".$sub_select_03.")"),
        'hero_product_activate'       => new Zend_Db_Expr("(".$sub_select_04.")"),
        'rd_code'       => 'st.code',
    );

    $select = $db->select()
    ->from(array('oat' => 'oppo_area_target'), $get)
    ->join(array('asm' => 'asm'),'asm.area_id = oat.area_id',array())
    ->join(array('st' => 'staff'),'st.id = asm.staff_id',array())
    ->join(array('a' => 'area'),'a.id = oat.area_id',array())

    ->where('oat.from_date >=?',$params['from'])
    ->where('oat.to_date <=?',$params['to'])
    ->where('asm.partner =?',0)
    ->where('st.group_id =?',28);

    if (isset($params['area_rd']) && $params['area_rd']) {
        $select->where('a.id IN (?)', $params['area_rd']);
    }
// Add Filter Area
    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $select->where('a.id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $select->where('a.id = ?', intval($params['area']));
        else
            $select->where('1=0', 1);
    }
    $result = $db->fetchAll($select);
    return $result;
}

function getheroproductold(){
    $db = Zend_Registry::get('db');

    $get = array(
        'hero_product_name'     => 'g.name',
    );

    $select = $db->select()
    ->from(array('g' => WAREHOUSE_DB.'.good'), $get)
    ->where('g.hero_product =?',1);

    $result = $db->fetchRow($select);

    return $result;
}function getheroproduct(){
    $db = Zend_Registry::get('db');

    $get = array(
        'hero_product_name'     => 'hrpd.name',
    );

    $select = $db->select()
    ->from(array('hrpd' => WAREHOUSE_DB.'.hero_product'), $get)
    ->where('hrpd.status = 1 ');

    $result = $db->fetchRow($select);

    return $result;
}

function getMtkSelloutData($params) {
    $tmp_from = explode('/', $params['from']);
    $tmp_to = explode('/', $params['to']);

    $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
    $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
    $db = Zend_Registry::get('db');

    $get = array(
        'area_name'     => 'a.name',
        'id'            => 'st.id',
        'code'          => 'st.code',
        'name'          => new Zend_Db_Expr("CONCAT(st.firstname, ' ',st.lastname)"),
        'target'        => 'mkt.target',
        'target_hero'   => 'mkt.target_hero',

        'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN t.mkt_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' THEN t.id END)"),
        'activate'      => new Zend_Db_Expr("COUNT(CASE WHEN t.mkt_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND i.activated_date IS NOT NULL THEN t.id END)"),
        'hero_product'      => new Zend_Db_Expr("COUNT(CASE WHEN t.mkt_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 THEN t.id END)"),
        'hero_product_activate' => new Zend_Db_Expr("COUNT(CASE WHEN t.mkt_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 AND i.activated_date IS NOT NULL THEN i.imei_sn END)"),

    );

    $select = $db->select()
    ->from(array('ss'=>'store_staff'),$get)
    ->joinLeft(array('st' => 'staff'),'st.id = ss.staff_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = st.regional_market',array())
    ->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
    ->joinLeft(array('t' => 'timing'),'t.store = ss.store_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn  = ts.imei',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('mkt' => 'oppo_mkt_target'),
        "
        mkt.staff_id = ss.staff_id
        AND mkt.from_date >= '".$params['from']."'
        AND mkt.to_date <= '".$params['to']."'
        "
        ,array())


    ->where('st.status = 1')
    ->where('ss.is_leader = 5')
    ->group('st.id');


    $result = $db->fetchAll($select);

    return $result;

}

function getAsmSelloutData($params) {
    $tmp_from = explode('/', $params['from']);
    $tmp_to = explode('/', $params['to']);

    $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
    $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
    $db = Zend_Registry::get('db');

    $get = array(
        'area_name'     => 'a.name',
        'id'            => 'st.id',
        'code'          => 'st.code',
        'name'          => new Zend_Db_Expr("CONCAT(st.firstname, ' ',st.lastname)"),
        'target'        => 'asm.target',
        'target_hero'   => 'asm.target_hero',

        'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN t.asm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' THEN t.id END)"),
        'activate'      => new Zend_Db_Expr("COUNT(CASE WHEN t.asm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND i.activated_date IS NOT NULL THEN t.id END)"),
        'hero_product'      => new Zend_Db_Expr("COUNT(CASE WHEN t.asm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 THEN t.id END)"),
        'hero_product_activate' => new Zend_Db_Expr("COUNT(CASE WHEN t.asm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 AND i.activated_date IS NOT NULL THEN i.imei_sn END)"),

    );

    $select = $db->select()
    ->from(array('ss'=>'store_staff'),$get)
    ->joinLeft(array('st' => 'staff'),'st.id = ss.staff_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = st.regional_market',array())
    ->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
    ->joinLeft(array('t' => 'timing'),'t.store = ss.store_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn  = ts.imei',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('asm' => 'oppo_asm_target'),
        "
        asm.staff_id = ss.staff_id
        AND asm.from_date >= '".$params['from']."'
        AND asm.to_date <= '".$params['to']."'
        "
        ,array())


    ->where('st.status = 1')
    ->where('ss.is_leader = 4')
    ->group('st.id');


    $result = $db->fetchAll($select);

    return $result;

}

}