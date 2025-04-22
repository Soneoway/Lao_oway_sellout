<?php
class Application_Model_CustromerPreOrder extends Zend_Db_Table_Abstract
{
  protected $_name = 'custromer_pre_order';


  function fetchPagination($page, $limit, &$total, $params) 
  {
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
      new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'),
      'pre_id'			=> 'p.id',
      'store_code'     => 'st.store_code',
      'store_id'  		=> 'p.store',
      'add_id'          => 'p.created_by',
      'age'           => 'p.age',
      'gender'        => 'p.gender',
      'store_name' 		=> 'st.name',
      'good_name'  		=> 'g.name',
      'good_id'       => 'g.id',
      'color_id'      => 'gc.id',
      'good_color' 		=> 'gc.name',
      'imei_sn'         => 'p.imei',
      'type'				=> 'p.deposit_money',
      'deposit_money' => 'p.deposit_money',
      'customers_name'    => 'p.customers_name',
      'customers_phone'   => 'p.customers_phone',
      'num'				=> 'p.num',

      'staff_code'    => 's.code',
      'staff_name' 		=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
      'staff_position'  => 'gu.name',


      'add_time'			=> 'p.created_at',
      'confirm'			=> 'p.imei',
      'oppo_id'     => 'st.store_code',
      'provience'   => 'rm.name',
      'area_name'        => 'a.name',

      'pcm_name'    => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
      'pcm_code'    => 's2.code',

      'asm_name'    => new Zend_Db_Expr("CONCAT(s3.firstname, ' ', s3.lastname)"),
      'asm_code'    => 's3.code',
    );

    $select = $db->select()
    ->from(array('p' => 'custromer_pre_order'),$get)
    ->joinleft(array('st' => 'store'),'st.id = p.store',array())
    ->joinleft(array('rm' => 'regional_market'),'st.regional_market = rm.id',array())
    ->joinleft(array('a'  => 'area'),'rm.area_id = a.id',array())

    ->joinleft(array('ss2' => 'store_staff'),'st.id = ss2.store_id and ss2.is_leader = 2',array())
    ->joinleft(array('s2' => 'staff'),'ss2.staff_id = s2.id',array())

    ->joinleft(array('ss3' => 'store_staff'),'st.id = ss3.store_id and ss3.is_leader = 4',array())
    ->joinleft(array('s3' => 'staff'),'ss3.staff_id = s3.id',array());

    $select->joinleft(array('s' =>'staff'),'s.id = p.created_by',array());
    $select->joinleft(array('g' => WAREHOUSE_DB.'.good'),'g.id = p.good',array());
    $select->joinleft(array('gc' => WAREHOUSE_DB.'.good_color'),'gc.id = p.color',array());
    $select->joinleft(array('gu' => 'group'),'s.group_id = gu.id',array());

    $select->where('p.created_at >=?',$from." 00:00:00");
    $select->where('p.created_at <=?',$to." 23:59:59");

       // Add Filter Model
    if (isset($params['good_id']) && $params['good_id']) {
      if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('p.good IN (?)', $params['good_id']);
      elseif (is_numeric($params['good_id']))
        $select->where('p.good = ?', intval($params['good_id']));
      else
        $select->where('1=0', 1);
    }
    // Add Filter Color
    if (isset($params['color_id']) && $params['color_id']) {
      if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('p.color IN (?)', $params['color_id']);
      elseif (is_numeric($params['color_id']))
        $select->where('p.color = ?', intval($params['color_id']));
      else
        $select->where('1=0', 1);
    }

    if(isset($params['store_id']) && $params['store_id']){
      $select->where('p.store =?',$params['store_id']);
    }

    if ( isset($params['store_name']) && $params['store_name'] ) {
      $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
    }

    if(isset($params['pc']) && $params['pc']){
      $select->where('p.created_by =?',$params['pc']);
    }

    if(isset($params['rd']) && $params['rd']){
      $select->joinleft(array('asm' => 'asm'),'asm.area_id = a.id',array());
      $select->where('asm.staff_id = ?',$params['rd']);
    }

    if(isset($params['salse']) && $params['salse']){
      $select->joinleft(array('ss' => 'store_staff'),'ss.store_id = p.store',array());
      $select->where('ss.staff_id = ?',$params['salse']);
    }
    if (isset($params['area_id']) && $params['area_id']) {
     if (is_array($params['area_id']) && count($params['area_id']))

      $select->where('a.id IN (?)', $params['area_id']);

    elseif (is_numeric($params['area_id']))

      $select->where('a.id = ?', intval($params['area_id']));

    else
      $select->where('1=0', 1);
  }

  if ($limit) {
    $select->limitPage($page, $limit);
  }

  $result = $db->fetchAll($select);
  $total = $db->fetchOne("select FOUND_ROWS()");


  return $result;
}

}