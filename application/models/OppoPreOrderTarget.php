<?php
class Application_Model_OppoPreOrderTarget extends Zend_Db_Table_Abstract
{
	protected $_name = 'oppo_pre_order_target';


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
        ->from(array('aso' => 'oppo_pre_order_target'), array('aso.target'))
        ->where('aso.from_date >= ?', $last_start_03)
        ->where('aso.to_date <= ?', $last_end_03)
        ->where('aso.area_id = a.id');

        // Sub Query 02 : Last 2 Month
        $sub_select_02 = $db->select()
        ->from(array('aso2' => 'oppo_pre_order_target'), array('aso2.target'))
        ->where('aso2.from_date >= ?', $last_start_02)
        ->where('aso2.to_date <= ?', $last_end_02)
        ->where('aso2.area_id = a.id');

        // Sub Query 03 : Last 1 Month
        $sub_select_03 = $db->select()
        ->from(array('aso3' => 'oppo_pre_order_target'), array('aso3.target'))
        ->where('aso3.from_date >= ?', $last_start_01)
        ->where('aso3.to_date <= ?', $last_end_01)
        ->where('aso3.area_id = a.id');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'last_03'   => new Zend_Db_Expr("(".$sub_select_01.")"),
            'last_02'   => new Zend_Db_Expr("(".$sub_select_02.")"),
            'last_01'   => new Zend_Db_Expr("(".$sub_select_03.")"),
            'sellout'   => '0'
        );

        $select = $db->select()
        
        ->from(array('a' => 'area'), $get)
        ->group('a.id')
        ->order(array('a.name ASC'));
        
        //echo $select; die;
        $result_raw = $db->fetchAll($select);

        $result = array();
        for ($i=0;$i<count($result_raw);$i++) {

            $result[ $result_raw[$i]['area_id'] ]['last_03'] = $result_raw[$i]['last_03'];
            $result[ $result_raw[$i]['area_id'] ]['last_02'] = $result_raw[$i]['last_02'];
            $result[ $result_raw[$i]['area_id'] ]['last_01'] = $result_raw[$i]['last_01'];
            $result[ $result_raw[$i]['area_id'] ]['mkt_target'] = $result_raw[$i]['mkt_target'];
            $result[ $result_raw[$i]['area_id'] ]['salse_target'] = $result_raw[$i]['salse_target'];
        }

        return $result;
    }


// Get Pre-Order target
function getpreordertarget($params){

    $tmp_from = explode('/', $params['from']);
    $tmp_to = explode('/', $params['to']);

    $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
    $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

    $db = Zend_Registry::get('db');

    $sub_select_01 = $db->select()
    ->from(array('t' => 'timing'),array('sellout' => new Zend_Db_Expr("COUNT(t.id)")))
    ->join(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
    ->join(array('cpr' => 'custromer_pre_order'),'cpr.imei = ts.imei',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'), 'g.id = ts.product_id',array())
    ->join(array('s' => 'store'),'s.id = t.store',array())
    ->join(array('rm' =>'regional_market'),'rm.id = s.regional_market',array())

    ->where('NOT g.cat_id =?',15)
    ->where('t.created_at <= ?',$params['to'])
    ->where('t.created_at >= ?',$params['from'])
    ->where('cpr.imei IS NOT NULL')
    ->where('rm.area_id = a.id');

    $sub_select_02 = $db->select()
    ->from(array('t3' => 'timing'),array('product' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
    ->join(array('ts3' => 'timing_sale'),'ts3.timing_id = t3.id',array())
    ->join(array('cpr' => 'custromer_pre_order'),'cpr.imei = ts3.imei',array())
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
    ->join(array('cpr' => 'custromer_pre_order'),'cpr.imei = ts3.imei',array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts3.imei',array())
    ->join(array('s3' => 'store'),'s3.id = t3.store',array())
    ->join(array('rm3' =>'regional_market'),'rm3.id = s3.regional_market',array())

    ->where('t3.created_at <= ?',$params['to'])
    ->where('t3.created_at >= ?',$params['from'])
    ->where('rm3.area_id = a.id')
    ->where('ts3.is_hero = ?',1)
    ->where('cpr.imei IS NOT NULL');

    $sub_select_04 = $db->select()
    ->from(array('t3' => 'timing'),array('sellout' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
    ->join(array('ts3' => 'timing_sale'),'ts3.timing_id = t3.id',array())
    ->join(array('cpr' => 'custromer_pre_order'),'cpr.imei = ts3.imei',array())
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts3.imei',array()) 
    ->join(array('s3' => 'store'),'s3.id = t3.store',array())
    ->join(array('rm3' =>'regional_market'),'rm3.id = s3.regional_market',array())

    ->where('ts3.is_hero = ?',1)
    ->where('cpr.imei IS NOT NULL')
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
    ->from(array('oat' => 'oppo_pre_order_target'), $get)
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

}