<?php
class Application_Model_StoreVisit extends Zend_Db_Table_Abstract
{
  protected $_name = 'store_visit';


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
      'pre_id'			 => 'p.id',
      'store_code'   => 'st.store_code',
      'store_id'  	 => 'p.store',
      'add_id'       => 'p.created_by',
      'store_name' 	 => 'st.name',
      'num'				   => 'p.num',
      'total_visit'	 => new Zend_Db_Expr("COUNT(p.num)"),


      'staff_code'      => 's.code',
      'staff_name' 		  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
      'staff_position'  => 'gu.name',


      'add_time'		=> 'p.created_at',
      'confirm'			=> 'p.imei',
      'oppo_id'     => 'st.store_code',
      'provience'   => 'rm.name',
      'area_name'   => 'a.name',
    );

    $select = $db->select()
    ->from(array('p' => 'store_visit'),$get)
    ->joinleft(array('st' => 'store'),'st.id = p.store',array())
    ->joinleft(array('rm' => 'regional_market'),'st.regional_market = rm.id',array())
    ->joinleft(array('a'  => 'area'),'rm.area_id = a.id',array());

    $select->joinleft(array('s' =>'staff'),'s.id = p.created_by',array());
    $select->joinleft(array('gu' => 'group'),'s.group_id = gu.id',array());

    $select->where('p.created_at >=?',$from." 00:00:00");
    $select->where('p.created_at <=?',$to." 23:59:59");



    if(isset($params['store_id']) && $params['store_id']){
      $select->where('p.store =?',$params['store_id']);
    }

    if ( isset($params['store_name']) && $params['store_name'] ) {
      $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
    }


  if ($limit) {
    $select->limitPage($page, $limit);
  }

  $result = $db->fetchAll($select);
  $total = $db->fetchOne("select FOUND_ROWS()");


  return $result;
}

}