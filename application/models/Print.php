<?php
class Application_Model_Print extends Zend_Db_Table_Abstract
{
	protected $_name = 'print_log';

	function getInvoiceData($id){
	    $db = Zend_Registry::get('db');
	    
        $select = $db->select()
        ->from(array('p'=> 'print_log'),array('p.imei','p.sn','p.customers','p.phone_number','p.num','p.discount','p.unit_price','p.total_price','p.payment','p.store','p.create_at','p.create_by'))
        ->joinLeft(array('st' => 'staff'),'st.id = p.create_by',array('s_number' =>'st.phone_number'))
        ->joinLeft(array('s' => 'store'),'s.id = p.store',array('store_name' => 's.name','address'=>'s.shipping_address','contact' => 's.phone_number'))
        ->joinLeft(array('cl' => WAREHOUSE_DB.'.good_color'),'cl.id = p.color_id',array('good_color' => 'cl.name'))
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = p.imei',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = p.good_id',array('procuct_name' => 'g.name'))
        ->joinLeft(array('ct' => WAREHOUSE_DB.'.good_category'),'ct.id = g.cat_id',array('cattagory' => 'ct.name'));
        $select->where('p.id = ?',$id);

        return $db->fetchAll($select);
	}

        function fetchPagination($page, $limit, &$total, $params){
                $db = Zend_Registry::get('db');

                  $select = $db->select()
            ->distinct()
            ->from(array('p' => 'print_log'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'),'p.imei','p.sn','p.customers','p.phone_number','p.create_at'))
            ->joinLeft(array('s' => 'store'),'s.id = p.store',array('store_name' => 's.name','store_id' => 's.id'))
            ->joinLeft(array('st' => 'staff'),'st.id = p.create_by',array('sale_name'=>'CONCAT(st.firstname," ",st.lastname)'))
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = p.good_id',array('product_name' => 'g.name'))
            ->joinLeft(array('cl' => WAREHOUSE_DB.'.good_color'),'cl.id = p.color_id',array('good_color' => 'cl.name'));

        

        if(isset($params['sn'])&&$params['sn']){
            $select->where('p.sn =?',$params['sn']);
        }

        if(isset($params['sn'])&&$params['sn']){
            $select->where('p.sn =?',$params['sn']);
        }

        if(isset($params['id'])&&$params['id']){
            $select->where('s.id =?',$params['id']);
        }

        if(isset($params['name'])&&$params['name']){
            $select->where('s.name LIKE ?','%'.$params['name'].'%');
        }

        if(isset($params['imei']) && $params['imei']){
                $imei = explode("\r\n", $params['imei']);
                $select->where('p.imei IN (?)',$imei);
        }

        if (isset($params['from']) && $params['from'] && DateTime::createFromFormat('d/m/Y', $params['from']))
            $select->where('p.create_at >= ?', DateTime::createFromFormat('d/m/Y', $params['from'])->format('Y-m-d 00:00:00'));

        if (isset($params['to']) && $params['to'] && DateTime::createFromFormat('d/m/Y', $params['to']))
            $select->where('p.create_at <= ?', DateTime::createFromFormat('d/m/Y', $params['to'])->format('Y-m-d 23:59:59'));

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
        }
}