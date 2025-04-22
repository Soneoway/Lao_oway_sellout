<?php
class Application_Model_StoreLeader extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_leader';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['staff_id']) && $params['staff_id']) {
            $select->where('p.staff_id = ?', $params['staff_id']);
        }

        if ($limit) {
            $select->limitPage($page, $limit);
        }

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function fetchAllLeader($staff_id){
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id', ));

        $select
            ->distinct()
            ->join(array('s' => 'store'), 's.id=p.store_id', array('store_id' => 's.id', 'store_name' => 's.name'))
            ->join(array('r' => 'regional_market'), 'r.id=s.regional_market', array('region_id' => 'r.id', 'region_name' => 'r.name'))
            ->join(array('a' => 'area'), 'a.id=r.area_id', array('area_id' => 'a.id', 'area_name' => 'a.name'))
            ->join(array('lg' => 'store_leader_log'), 'lg.parent=p.id', array('log_id' => 'lg.id', 'joined_at' => 'lg.joined_at'));



        if (isset($staff_id) && $staff_id) {
            $select->where('p.staff_id = ?', $staff_id);
        }

        $result = $db->fetchAll($select);
        return $result;
    }

    function countStore($staff_id, $status)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('p' => $this->_name), array('total' => 'COUNT(DISTINCT p.store_id)'));

        $select->where('p.staff_id = ?', $staff_id);
        $select->where('p.status = ?', $status);
        
        $total = $db->fetchOne($select);
        return $total;
    }

    function getStoreList($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('ss' => 'store_leader'), array())
            ->joinLeft(array('st' => 'store'), 'ss.store_id = st.id', array('store_id' => 'st.id', 'store_name' => 'st.name'))
            ->where('ss.staff_id = ?', $params['staff_id'])
            ->order('st.name ASC');
            // die($select);
        $result = $db->fetchAll($select);
        return $result;
    }

}