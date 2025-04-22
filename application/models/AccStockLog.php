<?php
class Application_Model_AccStockLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'acc_stock_log';

    function getStockLogList($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',

            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_type'       => 'o.org_name',

            'good_id'       => 'g.id',
            'good_code'     => 'g.name',
            'good_name'     => 'g.desc',

            'color_id'      => 'gc.id',
            'color_name'    => 'gc.name',
            
            'record_type'   => 'asl.type',
            'unit'          => 'asl.unit',
            'record_date'   => 'asl.created_at',

            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')                     , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market')         , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')                    , 'rm.area_id = a.id'           , array())
            ->join(array('ast'=> 'acc_stock')               , 'st.id = ast.store_id'        , array())
            ->join(array('g'  => WAREHOUSE_DB.'.good')      , 'ast.good_id = g.id'          , array())
            ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'ast.good_color_id = gc.id'   , array())
            ->join(array('asl'=> 'acc_stock_log')           , 'ast.id = asl.acc_stock_id'   , array())
            ->join(array('s'  => 'staff')                   , 'asl.created_by = s.id'       , array())
            ->where('st.id = ?', $params['store_id'])
            ->order(array('asl.created_at ASC'));

        if (isset($params['from']) && $params['from']) {
            $d1 = explode('/', $params['from']);
            $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
            $select->where('asl.created_at >= ?', $from." 00:00:00");
        }

        if (isset($params['to']) && $params['to']) {
            $d2 = explode('/', $params['to']);
            $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
            $select->where('asl.created_at <= ?', $to." 23:59:59");
        }

        // Add Filter Model
        if (isset($params['good_id']) && $params['good_id']) {
            if (is_array($params['good_id']) && count($params['good_id']))
                $select->where('ast.good_id IN (?)', $params['good_id']);
            elseif (is_numeric($params['good_id']))
                $select->where('ast.good_id = ?', intval($params['good_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Color
        if (isset($params['color_id']) && $params['color_id']) {
            if (is_array($params['color_id']) && count($params['color_id']))
                $select->where('ast.good_color_id IN (?)', $params['color_id']);
            elseif (is_numeric($params['color_id']))
                $select->where('ast.good_color_id = ?', intval($params['color_id']));
            else
                $select->where('1=0', 1);
        }

        // Permission for BM 
        if (isset($params['bm_id']) && $params['bm_id']) {
            $select->join(array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 3', array());
            $select->where('ss.staff_id = ?', $params['bm_id']);
        }

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

}