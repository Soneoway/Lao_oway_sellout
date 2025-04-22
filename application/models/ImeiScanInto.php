<?php
class Application_Model_ImeiScanInto extends Zend_Db_Table_Abstract
{
	protected $_name = 'imei_scan_into';

	function fetchPagination($page, $limit, &$total, $params){
		$db = Zend_Registry::get('db');

		$tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $imei_list = trim($params['imei_sn']);
		$imei_list = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $imei_list);
		$imei_list = explode("\n", $imei_list);
		$imei_list = array_filter($imei_list);

		$select = $db->select()
		->from(array('p' => $this->_name),
			array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

		$select->joinleft(array('st' => 'store'),'st.id = p.out_store_id',array('out_store_code' => 'st.store_code','out_store_name' => 'st.name'));
		$select->joinleft(array('sto' => 'store'),'sto.id = p.in_store_id',array('in_store_code' => 'sto.store_code','in_store_name' => 'sto.name'));
		$select->joinleft(array('sb' => 'sub_area'),'sb.id = st.agency',array('out_store_office' => 'sb.name'));
		$select->joinleft(array('sba' => 'sub_area'),'sba.id = sto.agency',array('in_store_office' => 'sba.name'));
		$select->joinleft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = p.imei_sn',array());
		$select->joinleft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array('model_name' => 'g.name'));
		$select->joinleft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array('brand_name' => 'b.name'));

		$select->where('p.created_at >= ?',$from.' 00:00:00');
		$select->where('p.created_at <= ?',$to.' 23:59:59');

		if(isset($params['befor_transfer']) && $params['befor_transfer']) {
			$select->where('st.id =?',$params['befor_transfer']);
		}

		if(isset($params['after_transfer']) && $params['after_transfer']) {
			$select->where('sto.id =?',$params['after_transfer']);
		}

		if(isset($params['good_id']) && $params['good_id']) {
			$select->where('p.good_id =?',$params['good_id']);
		}

		if(isset($params['office_befor']) && $params['office_befor']) {
			$select->where('sb.id =?',$params['office_befor']);
		}

		if(isset($params['office_after']) && $params['office_after']) {
			$select->where('sba.id =?',$params['office_after']);
		}

		if(isset($params['imei_sn']) && $params['imei_sn']) {
			$select->where('i.imei_sn IN (?)',$imei_list);
		}

		$select->limitPage($page, $limit);

		$result = $db->fetchAll($select);
		$total = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}

}

?>