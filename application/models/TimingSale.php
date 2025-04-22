<?php
class Application_Model_TimingSale extends Zend_Db_Table_Abstract
{
	protected $_name = 'timing_sale';

    function fetchDetailPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.imei'));

        $select
            ->join(array('t' => 'timing'), 't.id=p.timing_id', array())
            ->join(array('s' => 'store'), 't.store=s.id', array('store_name'=>'s.name'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.'.'distributor'), 's.d_id = d.id', array('center_d_title' => 'd.title'))
            ->join(array('i' => WAREHOUSE_DB.'.'.'imei'), 'p.imei=i.imei_sn', array('i.into_date', 'i.out_date', 'i.sales_sn', 'i.po_sn', 'i.return_sn', 'i.activated_date'))
            ->joinLeft(array('dd' => WAREHOUSE_DB.'.'.'distributor'), 'i.distributor_id=dd.id', array('wh_d_title' => 'dd.title'))
            ->join(array('g' => WAREHOUSE_DB.'.'.'good'), 'i.good_id = g.id', array('model'=>'g.name'))
            ->join(array('c' => WAREHOUSE_DB.'.'.'good_color'), 'i.good_color = c.id', array('color'=>'c.name'));

        if (isset($params['dealer_id'])) {
            if ($params['dealer_id'] != 'null') {
                $select->where('d.id = ?', $params['dealer_id']);
            } else {
                $select->where('d.id IS NULL', 1);
            }
        }

        if (isset($params['from']) && $params['from'] && isset($params['to']) && $params['to']) {
            $from = explode('/', $params['from']);
            $from = $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00';
            $to = explode('/', $params['to']);
            $to = $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59';       
        } else {
            return false;
        }

        if (isset($from) and $from)
            $select->where('t.from >= ?',$from);

        if (isset($to) and $to)
            $select->where('t.from <= ?', $to);

        $select->where('t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'');

        $select->order('i.out_date', 'ASC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function check_pc_stand_by($staff_id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('t' => 'timing'),array('store_id'=>'t.store','imei'=>'ts.imei','timing_date'=>'t.created_at'))
            ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array());

        $select->where('t.staff_id = ?', $staff_id); 
        $select->where('t.created_at >= ?', date('Y-m-d 00:00:00'));
        $select->order('t.created_at ASC');

        //echo $select; //die;
        $result = $db->fetchRow($select);
        return $result;

    }

}

