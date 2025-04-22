<?php
class Application_Model_StorePriceTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_price_target';

    function checkTargetByStore($params, $store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'spt_id'        => 'spt.id',
            'store_id'      => 'spt.store_id',
            'target_price'  => 'spt.target_price',
        );

        $select = $db->select()
            ->from(array('spt' => 'store_price_target'), $get)
            ->where('spt.from_date >= ?', $params['from'])
            ->where('spt.to_date <= ?', $params['to'])
            ->where('spt.store_id = ?', $store_id);

        // echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

    function checkTargetByArea($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'target_price' => new Zend_Db_Expr("COALESCE(SUM(spt.target_price),0)"),
        );

        $select = $db->select()
            ->from(array('spt'=> 'store_price_target'), $get)
            ->join(array('st' => 'store')           , 'spt.store_id = st.id'        , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->where('spt.from_date >= ?', $params['from'])
            ->where('spt.to_date <= ?', $params['to'])
            ->where('a.id IN (?)', $params['area_id'])
            ->group('a.id');

        // echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getSelloutByStore($params, $store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'sellout_unit'  => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_price' => new Zend_Db_Expr("COALESCE(SUM(gkl.price),0)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('t'  => 'timing')      , 'st.id = t.store'     , array())
            ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id' , array())
            ->join(array('gkl' => 'good_kpi_log'), 
                "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                ", array())
            ->where('t.created_at >= ?', $params['from']." 00:00:00")
            ->where('t.created_at <= ?', $params['to']." 23:59:59")
            ->where('st.id = ?', $store_id)
            ->group('st.id');

        // echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

    function getLast3MonthBySaleStore($params, $sale_id, $area_id) {

        $db = Zend_Registry::get('db');

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

        // Range of Last 3 Month
        $tmp_start_03 = new DateTime( $last_start_02 );
        $tmp_start_03->modify( 'first day of previous month' );
        $last_start_03 = $tmp_start_03->format( 'Y-m-d' );
       
        $tmp_end_03 = new DateTime( $last_end_02 );
        $tmp_end_03->modify( 'last day of previous month' );
        $last_end_03 = $tmp_end_03->format( 'Y-m-d' );

        $get = array(
            'store_id'     => 'st.id',
            'store_name'   => 'st.name',

            'last_03'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN t.created_at >= '".$last_start_03." 00:00:00' AND t.created_at <= '".$last_end_03." 23:59:59' THEN gkl.price END ), 0)"),
            'last_02'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN gkl.price END ), 0)"),
            'last_01'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN gkl.price END ), 0)"),

            'sellout'      => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),
            'total_price'  => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN gkl.price END ), 0)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id', array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id', array())
            ->joinLeft(array('t'  => 'timing')      , 
                "   st.id = t.store 
                    AND t.created_at >= '".$last_start_03." 00:00:00' 
                    AND t.created_at <= '".$params['to']." 23:59:59' 
                ", array())
            ->joinLeft(array('ts'  => 'timing_sale') , 't.id = ts.timing_id', array())
            ->joinLeft(array('gkl' => 'good_kpi_log'), 
                "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                ", array())
            ->group('st.id')
            ->order(array('st.id ASC'));

        if ( $sale_id == 0 ) {
            $select->where('ss.id IS NULL', 1);
            $select->where('rm.area_id = ?', $area_id);
        } else {
            $select->where('s.id = ?', $sale_id);
            $select->where('rm.area_id = ?', $area_id);
        }
        
        // echo $select; echo "<br/><br/>"; //die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getSaleByArea($params) { 

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name', 
            'id'        => new Zend_Db_Expr("(CASE WHEN s.id IS NULL THEN CONCAT(0,'|',a.id) ELSE s.id END)"),
            'name'      => new Zend_Db_Expr("(CASE WHEN s.id IS NULL THEN CONCAT(a.name,' | No Sale') ELSE CONCAT(a.name, ' | ', s.code,' | ', s.firstname, ' ', s.lastname) END)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'          , array())
            // ->where('a.id IN (?)', $params['area_id'])
            ->group(array('staff_id','a.id'))
            ->order(array('a.name ASC','s.id ASC'));

        // Check Province Permission
        if ( isset($params['province_id']) &&  $params['province_id'] ) {
            $select->where('rm.id IN (?)', $params['province_id']);
        } else {
            $select->where('a.id IN (?)', $params['area_id']);
        }

        // Filter Sale Name
        if ( isset($params['sale_id']) &&  $params['sale_id'] ) {

            $chk_string = '|';
            $arr_area = $arr_sale = array();
            foreach ($params['sale_id'] as $key => $value) {
                if ( strpos($value, $chk_string ) != false ) {
                    $tmp = explode("|", $value);
                    $arr_area[] = $tmp[1];
                } else {
                    $arr_sale[] = $value;
                }
            }

            // print_r($arr_area); echo "<br/>";
            if ( !empty($arr_area) ) {
                if ( !empty($arr_sale) ) {
                    $select->where('( s.id IN (?)', $arr_sale);
                    $select->orWhere('ss.id IS NULL', 1);
                    $select->where('a.id IN (?) )', $arr_area);
                } else {
                    $select->where('ss.id IS NULL', 1);
                    $select->where('a.id IN (?)', $arr_area);
                }
            } else {
                $select->where('s.id IN (?)', $params['sale_id']);
            }

        }
        
        // echo $select; echo "<br/><br/>"; //die;
        $result = $db->fetchAll($select);

        return $result;

    }

    function getStorePriceOverViewTarget($params) { 

        $db = Zend_Registry::get('db');

        $get = array(
            'grand_area'    => new Zend_Db_Expr(
                "   (CASE 
                        WHEN a.name LIKE 'BKK-E1%' THEN 'BKK East-1' 
                        WHEN a.name LIKE 'BKK-E2%' THEN 'BKK East-2' 
                        WHEN a.name LIKE 'BKK-E3%' THEN 'BKK East-3' 
                        WHEN a.name LIKE 'BKK-E4%' THEN 'BKK East-4' 
                        WHEN a.name LIKE 'BKK-E5%' THEN 'BKK East-5' 
                        WHEN a.name LIKE 'BKK-W1%' THEN 'BKK West-1' 
                        WHEN a.name LIKE 'BKK-W2%' THEN 'BKK West-2' 
                        WHEN a.name LIKE 'BKK-W3%' THEN 'BKK West-3' 
                        ELSE a.name 
                    END)
                "),
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_type'       => 'o.org_name', 
            'st_status'     => new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Disabled' END)"),

            'staff_code'    => 's.code', 
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',

            'target_price'  => 'spt.target_price', 

        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'          , array())
            ->joinLeft(array('g'  => 'group')       , 's.group_id = g.id'           , array())
            ->joinLeft(array('spt'=> 'store_price_target'), 
                "   st.id = spt.store_id 
                    AND spt.from_date >= '".$params['from']."' 
                    AND spt.to_date <= '".$params['to']."'
                ", array())
            // ->where('a.id IN (?)', $params['area_id'])
            ->order(array('grand_area ASC', 'a.name ASC', 's.code ASC', 'st.id ASC'));

        // Check Province Permission
        if ( isset($params['province_id']) &&  $params['province_id'] ) {
            $select->where('rm.id IN (?)', $params['province_id']);
        } else {
            $select->where('a.id IN (?)', $params['area_id']);
        }

        // Filter Sale Name
        if ( isset($params['sale_id']) &&  $params['sale_id'] ) {

            $chk_string = '|';
            $arr_area = $arr_sale = array();
            foreach ($params['sale_id'] as $key => $value) {
                if ( strpos($value, $chk_string ) != false ) {
                    $tmp = explode("|", $value);
                    $arr_area[] = $tmp[1];
                } else {
                    $arr_sale[] = $value;
                }
            }

            // print_r($arr_area); echo "<br/>";
            if ( !empty($arr_area) ) {
                if ( !empty($arr_sale) ) {
                    $select->where('( s.id IN (?)', $arr_sale);
                    $select->orWhere('ss.id IS NULL', 1);
                    $select->where('a.id IN (?) )', $arr_area);
                } else {
                    $select->where('ss.id IS NULL', 1);
                    $select->where('a.id IN (?)', $arr_area);
                }
            } else {
                $select->where('s.id IN (?)', $params['sale_id']);
            }

        }

        // echo $select; die;
        $result = $db->fetchAll($select);

        return $result;

    }

    function getTargetByMonth($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
            'st_status' => new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Closed' END)"),
            'st_type'   => 'o.org_name',

            'staff_id'    => 's.id',
            'staff_code'  => 's.code',
            'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group' => 'g.name',
            'off_date'    => 's.off_date',

            'st_joined_at'    => new Zend_Db_Expr("FROM_UNIXTIME(ss.joined_at)"),
            'st_released_at'  => new Zend_Db_Expr("FROM_UNIXTIME(ss.released_at)"),

            'st_target_price' => new Zend_Db_Expr("COALESCE(spt.target_price,0)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('ss' => 'store_staff_log') , 'st.id = ss.store_id'         , array())
            ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())
            ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
            ->joinLeft(array('spt' => 'store_price_target'), 
                "   st.id = spt.store_id 
                    AND spt.from_date >= '".$params['from']."' 
                    AND spt.to_date <= '".$params['to']."' 
                ", array())
            ->where('ss.is_leader = ?', 1)
            // ->where('a.id IN (?)', $params['area_id'])
            ->where('FROM_UNIXTIME(ss.joined_at) <= ?', $params['to']." 23:59:59")
            ->where('( FROM_UNIXTIME(ss.released_at) >= ?', $params['from']." 00:00:00")
            ->orWhere('ss.released_at IS NULL)', 1)
            ->order(array('ss.joined_at ASC','st.id ASC'));

        // Check Province Permission
        if ( isset($params['province_id']) &&  $params['province_id'] ) {
            $select->where('rm.id IN (?)', $params['province_id']);
        } else {
            $select->where('a.id IN (?)', $params['area_id']);
        }

        // Filter Sale Name
        if ( isset($params['sale_id']) &&  $params['sale_id'] ) {
            $select->where('s.id IN (?)', $params['sale_id']);
        }

        // echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getSaleInfo($sale_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'staff_id'  => 's.id',
            'staff_code'=> 's.code',
            'staff_name'=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            // ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id'         , array())
            ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())
            ->where('ss.is_leader = ?', 1)
            ->where('s.id = ?', $sale_id)
            ->order(array('s.code ASC'));

        // echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

}