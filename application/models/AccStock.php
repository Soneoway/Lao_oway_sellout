<?php
class Application_Model_AccStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'acc_stock';

    function fetchPagination($page, $limit, &$total, $params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'     => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS st.id"),
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',

            'area_id'   => 'a.id',
            'area_name' => 'a.name',

            'good_id'   => 'g.id',
            'good_code' => 'g.name',
            'good_name' => 'g.desc',

            'color_id'  => 'gc.id',
            'color_name'=> 'gc.name',
            
            'stock'     => 'ast.stock',
            'updated_at'=> 'ast.updated_at',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')                     , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market')         , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')                    , 'rm.area_id = a.id'           , array())
            ->join(array('ast'=> 'acc_stock')               , 'st.id = ast.store_id'        , array())
            ->join(array('g'  => WAREHOUSE_DB.'.good')      , 'ast.good_id = g.id'          , array())
            ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'ast.good_color_id = gc.id'   , array())
            ->order(array('a.name ASC', 'st.id ASC', 'g.name ASC'));
            

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id = ?', $params['store_id']);
        }

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', "%".$params['store_name']."%");
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('st.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('st.regional_market = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter District
        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $select->where('st.district IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $select->where('st.district = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
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

        if ($limit)
            $select->limitPage($page, $limit);

        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}