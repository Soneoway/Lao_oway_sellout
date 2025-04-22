<?php
class Application_Model_OppoSaleTarget extends Zend_Db_Table_Abstract
{
protected $_name = 'oppo_sale_target';
function GetSelloutData($params){
$db = Zend_Registry::get('db');
$get = array(
'area_id'       => 'a.id',
'area_name'     => 'a.name',
'code'          => 's.code',
'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname,' ',lastname)"),
'provience'     => 'rm.name',
'sub_area'      => 'sba.name',
'sale_target'   => 'ost.target',
'target_hero'   => 'ost.target_hero',
'sale_from'     => 'ost.from_date',
'sale_to'       => 'ost.to_date',
'approve_by'    => 'ost.rd_approve_by',
'approve_at'    => 'ost.rd_approve_at',
'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN t.sub_area = sba.id AND t.created_at >= '".$params['from']."' AND t.created_at <= '".$params['to']."' THEN t.id END)"),
'activate'      => new Zend_Db_Expr("COUNT(CASE WHEN t.sub_area = sba.id AND i.activated_date IS NOT NULL AND t.created_at >= '".$params['from']."' AND t.created_at <= '".$params['to']."' THEN i.imei_sn END)"),
'hero_product_sellout' => new Zend_Db_Expr("COUNT(CASE WHEN t.sub_area = sba.id AND ts.is_hero = 1  AND t.created_at >= '".$params['from']."' AND t.created_at <= '".$params['to']."' THEN i.imei_sn END)"),
'hero_product_activate' => new Zend_Db_Expr("COUNT(CASE WHEN t.sub_area = sba.id AND ts.is_hero = 1  AND i.activated_date IS NOT NULL AND t.created_at >= '".$params['from']."' AND t.created_at <= '".$params['to']."' THEN i.imei_sn END)"),
);
$select = $db->select()
->from(array('ss' => 'store_staff'), $get)
->joinLeft(array('s' => 'staff'),'s.id = ss.staff_id',array())
->joinLeft(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
->joinLeft(array('sba' => 'sub_area'),'sba.staff_id = s.id',array())
->joinLeft(array('t' => 'timing'),'t.store = ss.store_id',array())
->joinLeft(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
->joinLeft(array('ost' => 'oppo_sale_target'),
"
ost.sub_area_id = sba.id
AND ost.from_date >='".$params['from']."'
AND ost.to_date <='".$params['to']."'
"
,array())
// ->where('s.status =?',1)
->where('ss.is_leader =?',1)
->group('sba.name')
->order('a.name ASC');
if (isset($params['asm']) && $params['asm']) {
$select->join(array('rm2' => 'regional_market') , 'a.id = rm2.area_id', array());
$select->join(array('asm' => 'asm') , "(CASE WHEN asm.type = 2 THEN a.id ELSE rm2.id END) = asm.area_id", array());
$select->where('asm.staff_id = ?', $params['asm']);
}
//echo $select; die;

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

function getListByArea($params) {
$db = Zend_Registry::get('db');
$sub_select = $db->select()
->from(array('ss' => 'store_staff'), array('cnt' => new Zend_Db_Expr("COUNT(DISTINCT s.id)") ))
->join(array('s'  => 'staff'), 'ss.staff_id = s.id AND s.group_id = 9 AND s.off_date IS NULL', array())
->join(array('st' => 'store'), 'ss.store_id = st.id', array())
->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
->where('ss.is_leader = 1')
->where('rm.area_id = a.id');
$sub_select_02 = $db->select()
->from(array('ost2' => 'oppo_sale_target'), array('cnt' => new Zend_Db_Expr("COALESCE(SUM(ost2.target),0)") ))
->where('ost2.from_date >= ?', $params['from'])
->where('ost2.to_date <= ?', $params['to'])
->where('a.id = ost2.area_id');
$sub_select_03 = $db->select()
->from(array('t' => 'timing'),array('sellout' => "COUNT(t.id)"))
->join(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
->join(array('ss' => 'store_staff'),'ss.store_id = t.store',array())
->join(array('s' => 'staff'),'s.id = ss.staff_id AND s.group_id = 9 AND s.off_date IS NULL',array())
->join(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
->where('t.created_at >=?',$params['from'])
->where('rm.area_id = a.id')
->where('ss.staff_id = ost.staff_id');
$sub_select_04 = $db->select()
->from(array('t5' => 'timing'),array('Activate' => "COUNT(i.imei_sn)"))
->join(array('ts5' => 'timing_sale'),'ts5.timing_id = t5.id',array())
->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts5.imei',array())
->join(array('ss5' => 'store_staff'),'ss5.store_id = t5.store',array())
->join(array('s5' => 'staff'),'s5.id = ss5.staff_id AND s5.group_id = 9 AND s5.off_date IS NULL',array())
->join(array('rm5' => 'regional_market'),'rm5.id = s5.regional_market',array())
->where('i.activated_date IS NOT NULL')
->where('t5.created_at >=?',$params['from'])
->where('rm5.area_id = a.id')
->where('ss5.staff_id = ost.staff_id');
$sub_select_05 = $db->select()
->from(array('t6' => 'timing'),array('Activate' => "COUNT(i2.imei_sn)"))
->join(array('ts6' => 'timing_sale'),'ts6.timing_id = t6.id',array())
->join(array('i2' => WAREHOUSE_DB.'.imei'),'i2.imei_sn = ts6.imei',array())
->join(array('ss6' => 'store_staff'),'ss6.store_id = t6.store',array())
->join(array('s6' => 'staff'),'s6.id = ss6.staff_id AND s6.group_id = 9 AND s6.off_date IS NULL',array())
->join(array('rm6' => 'regional_market'),'rm6.id = s6.regional_market',array())
->join(array('go' => WAREHOUSE_DB.'.good'),'go.id = ts6.product_id',array())
->where('go.hero_product =?',1)
->where('t6.created_at >=?',$params['from'])
->where('rm6.area_id = a.id')
->where('ss6.staff_id = ost.staff_id');
$sub_select_06 = $db->select()
->from(array('t7' => 'timing'),array('Activate' => "COUNT(i3.imei_sn)"))
->join(array('ts7' => 'timing_sale'),'ts7.timing_id = t7.id',array())
->join(array('i3' => WAREHOUSE_DB.'.imei'),'i3.imei_sn = ts7.imei',array())
->join(array('ss7' => 'store_staff'),'ss7.store_id = t7.store',array())
->join(array('s7' => 'staff'),'s7.id = ss7.staff_id AND s7.group_id = 9 AND s7.off_date IS NULL',array())
->join(array('rm7' => 'regional_market'),'rm7.id = s7.regional_market',array())
->join(array('go2' => WAREHOUSE_DB.'.good'),'go2.id = ts7.product_id',array())
->where('go2.hero_product =?',1)
->where('i3.activated_date IS NOT NULL')
->where('t7.created_at >=?',$params['from'])
->where('rm7.area_id = a.id')
->where('ss7.staff_id = ost.staff_id');
$get = array(
'area_id'       => 'a.id',
'area_name'     => 'a.name',
'total_target'  => 'oat.target',
'staff_code'    => 'ost.staff_id',
'sale_target'   => 'ost.Target',
'sale_from'     => 'ost.from_date',
'sale_to'       => 'ost.to_date',
'approve_by'    => 'ost.rd_approve_by',
'approve_at'    => 'ost.rd_approve_at',
'target_hero'    => 'ost.target_hero',
// 'provience'     => 'rm.name',
// 'sub_area'      => 'sba.name',
'total_sale'    => new Zend_Db_Expr("(".$sub_select.")"),
'target'        => new Zend_Db_Expr("(".$sub_select_02.")"),
'sellout'       => new Zend_Db_Expr("(".$sub_select_03.")"),
'sale'          => new Zend_Db_Expr("COUNT(DISTINCT ost.staff_id)"),
'cnt_rd'        => new Zend_Db_Expr("COUNT(ost.rd_approve_by)"),
'cnt_sd'        => new Zend_Db_Expr("COUNT(ost.sd_approve_by)"),
'activate'      => new Zend_Db_Expr("(".$sub_select_04.")"),
'hero_product_sellout'  => new Zend_Db_Expr("(".$sub_select_05.")"),
'hero_product_activate'  => new Zend_Db_Expr("(".$sub_select_06.")"),
);
$select = $db->select()
->from(array('a' => 'area'), $get)
->joinLeft(array('oat'  => 'oppo_area_target'),
"   a.id = oat.area_id
AND oat.from_date >= '".$params['from']."'
AND oat.to_date <= '".$params['to']."'
", array())
->joinLeft(array('ost'  => 'oppo_sale_target'),
"   a.id = ost.area_id
AND ost.from_date >= '".$params['from']."'
AND ost.to_date <= '".$params['to']."'
", array())

// ->joinLeft(array('ssl' => 'store_staff_log'),'ssl.staff_id = ost.staff_id',array())
// ->joinLeft(array('t' => 'timing'),'t.store = ssl.store_id',array())
// ->joinLeft(array('sto' => 'store'),'sto.id = t.store',array())
// ->joinLeft(array('rmg' => 'regional_market'),'rmg.id = sto.regional_market',array())
// ->where('FROM_UNIXTIME(ssl.joined_at) >= t.created_at')
// ->where('ssl.released_at IS NULL OR FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\') <= t.created_at')
->where('a.id NOT IN (48,49,72,73,74,75,76,77,78,79,80)')
->group('a.id')
->order('a.name ASC');
if(isset($params['sale_target']) && $params['sale_target'] == 1){
$select->joinLeft(array('s' => 'staff'),'s.id = ost.staff_id',array('s.code','staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")))
->joinLeft(array('rm' => 'regional_market'),'rm.id = s.regional_market',array('provience' => new Zend_Db_Expr("rm.name")))
->joinLeft(array('sba' => 'sub_area'),'sba.staff_id = s.id',array('sub_area' => new Zend_Db_Expr("sba.name")))
->where('s.status =?',1)
->group('s.id');
}
if (isset($params['asm']) && $params['asm']) {
$select->join(array('rm' => 'regional_market') , 'a.id = rm.area_id', array());
$select->join(array('asm' => 'asm') , "(CASE WHEN asm.type = 2 THEN a.id ELSE rm.id END) = asm.area_id", array());
$select->where('asm.staff_id = ?', $params['asm']);
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
//echo $select; echo "<br/>";
$result = $db->fetchAll($select);
return $result;
}

function getLast3MonthBySaleStore($params) {

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
			'staff_id'     => 's.id',
			'staff_code'   => 's.code',
			'staff_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
			'sub_area_name' => 'sba.name',
			'sub_id' 		=> 'sba.id',
			'last_01'			=> new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <='".$last_end_01." 23:59:59' THEN t.id END)")


		);

		$sub_select_05 = $db->select()
		->from(array('rm2' => 'regional_market'),array('rm2.id'))
		->where('rm2.area_id =?',$params['area_id']);

		$select = $db->select()
		->from(array('sba' => 'sub_area'),$get)
		->joinLeft(array('act' => 'area_control'),'act.sub_area_id = sba.id',array())
		->joinLeft(array('sud' => 'sub_district'),'sud.id = act.sub_district',array())
		->joinLeft(array('rm' => 'regional_market'),'rm.id = sud.district',array())
		->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
		->joinLeft(array('st' => 'store'),'st.agency = sba.id',array())
		->joinLeft(array('t' => 'timing'),'t.store = st.id',array())
		->joinLeft(array('s' => 'staff'),'s.id = sba.staff_id',array())

		->where('rm.parent IN (?)',$sub_select_05)
		->group('sba.id');

			// echo $select; die();


		$result = $db->fetchAll($select);
		return $result;
	}


function getSaleTarget($params) {
$db = Zend_Registry::get('db');

$get = array(
'ost_id'        => 'ost.id',
'staff_id'      => 'ost.staff_id',
'target'        => 'ost.target',
'target_hero'   => 'ost.target_hero',
'target_price'  => 'ost.target_price',
'com'           => 'ost.com',
'staff_code'    => 's.code',
'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
'staff_phone'   => 's.phone_number',
'group_name'    => 'g.name',
'area_name'     => 'a.name',
'set_date'      => 'ost.created_at',
'remark'        => 'ost.remark',
'rd_approve_by' => 'ost.rd_approve_by',
'rd_approve_at' => 'ost.rd_approve_at',
'sd_approve_by' => 'ost.sd_approve_by',
'sd_approve_at' => 'ost.sd_approve_at',
'sub_id'		=> 'ost.sub_area_id',
);

$select = $db->select()
->from(array('ost' => 'oppo_sale_target'), $get)
->joinLeft(array('s' => 'staff'), 'ost.staff_id = s.id' , array())
->joinLeft(array('g' => 'group'), 's.group_id = g.id'   , array())
->joinLeft(array('a' => 'area') , 'ost.area_id = a.id'  , array())
->where('ost.from_date >= ?', $params['from'])
->where('ost.to_date <= ?', $params['to'])
->where('ost.area_id = ?', $params['area_id']);
// ->group('ost.sub_area_id')
// ->order(array('a.name ASC', 's.code ASC'));
// echo $select;
$result_raw = $db->fetchAll($select);
$result = array();

for ($i=0;$i<count($result_raw);$i++) {
$result[ $result_raw[$i]['sub_id'] ]['ost_id']        = $result_raw[$i]['ost_id'];
$result[ $result_raw[$i]['sub_id'] ]['target']        = $result_raw[$i]['target'];
$result[ $result_raw[$i]['sub_id'] ]['target_hero']   = $result_raw[$i]['target_hero'];
$result[ $result_raw[$i]['sub_id'] ]['target_price']  = $result_raw[$i]['target_price'];
$result[ $result_raw[$i]['sub_id'] ]['com']           = $result_raw[$i]['com'];
$result[ $result_raw[$i]['sub_id'] ]['staff_code']    = $result_raw[$i]['staff_code'];
$result[ $result_raw[$i]['sub_id'] ]['staff_name']    = $result_raw[$i]['staff_name'];
$result[ $result_raw[$i]['sub_id'] ]['group_name']    = $result_raw[$i]['group_name'];
$result[ $result_raw[$i]['sub_id'] ]['staff_phone']   = $result_raw[$i]['staff_phone'];
$result[ $result_raw[$i]['sub_id'] ]['area_name']     = $result_raw[$i]['area_name'];
$result[ $result_raw[$i]['sub_id'] ]['set_date']      = $result_raw[$i]['set_date'];
$result[ $result_raw[$i]['sub_id'] ]['remark']        = $result_raw[$i]['remark'];
$result[ $result_raw[$i]['sub_id'] ]['rd_approve_by'] = $result_raw[$i]['rd_approve_by'];
$result[ $result_raw[$i]['sub_id'] ]['rd_approve_at'] = $result_raw[$i]['rd_approve_at'];
$result[ $result_raw[$i]['sub_id'] ]['sd_approve_by'] = $result_raw[$i]['sd_approve_by'];
$result[ $result_raw[$i]['sub_id'] ]['sd_approve_at'] = $result_raw[$i]['sd_approve_at'];
$result[ $result_raw[$i]['sub_id'] ]['sub_id']		   = $result_raw[$i]['sub_id'];
}
// print($result); die();
return $result;

}


function getLeaderTarget($params, $type) {
$db = Zend_Registry::get('db');
$get = array(
'area_id'       => 'a.id',
'area_name'     => 'a.name',
'staff_id'      => 's.id',
'staff_code'    => 's.code',
'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
'staff_phone'   => 's.phone_number',
'group_name'    => 'g.name',
'target'        => 'oat.target',
'otr_id'        => 'otr.id',
'otr_remark'    => 'otr.remark',
);
$select = $db->select()
->from(array('s' => 'staff'), $get)
->join(array('g' => 'group'), 's.group_id = g.id', array())
->join(array('asm' => 'asm'), 's.id = asm.staff_id', array())
->join(array('rm' => 'regional_market'), "asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)", array())
->join(array('a' => 'area') , 'rm.area_id = a.id', array())
->joinLeft(array('oat' => 'oppo_area_target'),
"   a.id = oat.area_id
AND oat.from_date >= '".$params['from']."'
AND oat.to_date <= '".$params['to']."'
", array())
->joinLeft(array('otr' => 'oppo_target_remark'),
"   a.id = otr.area_id
AND s.id = otr.staff_id
AND otr.from_date >= '".$params['from']."'
AND otr.to_date <= '".$params['to']."'
", array())
->where('a.id = ?', $params['area_id'])
->group('s.id')
->order(array('a.name ASC', 's.code ASC'));
if ($type == 1) {
$select->where('s.group_id IN (?)', array(RM_ID,RMSTANDBY_ID));
}
else {
$select->where('s.group_id IN (?)', array(ASM_ID,ASMSTANDBY_ID));
}
//echo $select;
$result = $db->fetchAll($select);
return $result;
}
function getListBySaleAchieve($params) {
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
$db = Zend_Registry::get('db');
// Sum Store Price Target
$get_01 = array(
'st_area_id'    => 'a_01.id',
's_id'          => 's_01.id',
'store_target'  => new Zend_Db_Expr("SUM(COALESCE(opt.target,0))"),
'store_target_hero' => new Zend_Db_Expr("SUM(COALESCE(opt.target_hero, 0))"),
'store_target_price' => new Zend_Db_Expr("SUM(COALESCE(opt.target_price, 0))"),
);
$sub_select_01 = $db->select()
->from(array('st_01'  => 'store'), $get_01)
->join(array('rm_01' => 'regional_market') , 'st_01.regional_market = rm_01.id'  , array())
->join(array('a_01'  => 'area')            , 'rm_01.area_id = a_01.id'           , array())
->join(array('ss_01' => 'store_staff')     , 'st_01.id = ss_01.store_id AND ss_01.is_leader = 1'    , array())
->join(array('s_01'  => 'staff')           , 'ss_01.staff_id = s_01.id AND s_01.group_id <> 27'     , array())
->join(array('opt'  => 'oppo_pc_target')   , 'st_01.id = opt.store_id'          , array())
->where('opt.from_date >= ?', $params['from'])
->where('opt.to_date <= ?', $params['to'])
->group(array('a_01.id', 's_01.id'));
$get = array(
'area_id'       => 'a.id',
'area_name'     => 'a.name',
'staff_id'      => 's.id',
'staff_code'    => 's.code',
'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
'staff_group'   => 'g.name',
'sale_target'       => 'ost.target',
'sale_target_hero'  => 'ost.target_hero',
'sale_target_price' => 'ost.target_price',
'cnt_store'     => new Zend_Db_Expr("COUNT(DISTINCT st.id)"),
// 'cnt_pc'          => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN s2.pc_stand_by = 0 THEN s2.id END)"),
// 'cnt_pc_stand_by' => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN s2.pc_stand_by = 1 THEN s2.id END)"),
// Total Sellout Last 3 Month
'last_03'       => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN t.created_at >= '".$last_start_03." 00:00:00' AND t.created_at <= '".$last_end_03." 23:59:59' THEN ts.imei END )"),
'last_02'       => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN ts.imei END )"),
'last_01'       => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN ts.imei END )"),
// Current Total Sellout
'sellout'       => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),
'achieve'       => new Zend_Db_Expr("((COUNT(DISTINCT CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END ) / ost.target) * 100 )"),
// Current Hero Product Sellout
'sellout_hero'  => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN ts.product_id = 345 AND t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),
'achieve_hero'  => new Zend_Db_Expr("((COUNT(DISTINCT CASE WHEN ts.product_id = 345 AND t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END ) / ost.target_hero) * 100 )"),
// Current Total Product Price
'sellout_price'  => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN gkl.price END),0)"),
// 'store_target_flag' => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN opt.target IS NOT NULL THEN opt.id END)"),
// 'store_target_hero_flag' => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN opt.target_hero IS NOT NULL THEN opt.id END)"),
// 'store_target_price_flag' => new Zend_Db_Expr("COALESCE(SUM(CASE WHEN opt.target_price <> 0 THEN opt.target_price END),0)"),
'store_target_flag' => 'AAA.store_target',
'store_target_hero_flag' => 'AAA.store_target_hero',
'store_target_price_flag' => 'AAA.store_target_price',
);
$select = $db->select()
->from(array('st'  => 'store'), $get)
->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id AND ss.is_leader = 1'    , array())
->join(array('s'  => 'staff')           , 'ss.staff_id = s.id AND s.group_id <> 27'     , array())
->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
->joinLeft(array('ost' => 'oppo_sale_target'),
"   a.id = ost.area_id
AND s.id = ost.staff_id
AND ost.from_date >= '".$params['from']."'
AND ost.to_date <= '".$params['to']."'
", array())
->joinLeft(array('AAA' => $sub_select_01), "AAA.s_id = s.id AND AAA.st_area_id = a.id", array())
/*
->joinLeft(array('opt' => 'oppo_pc_target'),
"   st.id = opt.store_id
AND opt.from_date >= '".$params['from']."'
AND opt.to_date <= '".$params['to']."'
", array())
*/
// ->joinLeft(array('ss2' => 'store_staff'), 'st.id = ss2.store_id AND ss2.is_leader = 0'  , array())
// ->JoinLeft(array('s2'  => 'staff')      , 'ss2.staff_id = s2.id'        , array())
->JoinLeft(array('t'   => 'timing'),
"   st.id = t.store
AND t.created_at >= '".$last_start_03." 00:00:00'
AND t.created_at <= '".$params['to']." 23:59:59'
", array())
->JoinLeft(array('ts'  => 'timing_sale'), 't.id = ts.timing_id'         , array())
->JoinLeft(array('gkl' => 'good_kpi_log'),
"   gkl.good_id = ts.product_id
AND gkl.color_id = ts.model_id
AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
", array())
->group(array('s.id','a.id'))
->order(array('a.name ASC','achieve DESC'));
// Filter Area
if (is_array($params['area_id']) && count($params['area_id']))
$select->where('a.id IN (?)', $params['area_id']);
elseif (is_numeric($params['area_id']))
$select->where('a.id = ?', intval($params['area_id']));
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
$tmp = $db->fetchAll($select);
$result = array();
foreach ($tmp as $key => $value) {
$result[$value['area_id']][] = $value;
}
$result = array_values($result);
return $result;
}
// Get Sale No Store but have Target
function getSaleNoStoreTarget($params) {
$db = Zend_Registry::get('db');
$get = array(
'ost_id'        => 'ost.id',
'staff_id'      => 's.id',
'staff_code'    => 's.code',
'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
'com'           => 'ost.com',
'target'        => 'ost.target',
'target_hero'   => 'ost.target_hero',
'target_price'  => 'ost.target_price',
'set_date'      => 'ost.created_at',
'remark'        => 'ost.remark',
'rd_approve_by' => 'ost.rd_approve_by',
'rd_approve_at' => 'ost.rd_approve_at',
);
$sub_select = $db->select()
->from(array('ss2' => 'store_staff'), array('s_id' => new Zend_Db_Expr("DISTINCT ss2.staff_id") ))
->join(array('st' => 'store'), 'ss2.store_id = st.id', array())
->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
->where('ss2.is_leader = 1')
->where('rm.area_id <> ?', $params['area_id'])
->where('ss2.staff_id = s.id');
$select = $db->select()
->from(array('ost' => 'oppo_sale_target'), $get)
->join(array('s' => 'staff'), 'ost.staff_id = s.id', array())
->joinLeft(array('ss' => 'store_staff'), 's.id = ss.staff_id AND ss.is_leader = 1', array())
->where('ost.area_id = ?', $params['area_id'])
->where('(ss.staff_id IS NULL OR s.id IN (?) )', $sub_select)
->where('ost.from_date >= ?', $params['from'])
->where('ost.to_date <= ?', $params['to'])
->group('s.id')
->order(array('s.code ASC'));
//echo $select;
$result = $db->fetchAll($select);
return $result;
}
}