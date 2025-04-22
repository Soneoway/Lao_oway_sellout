<?php
class Application_Model_CasualWorker extends Zend_Db_Table_Abstract
{
    protected $_name = 'casual_worker';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'))
            ->join(array('s' => 'staff'), 's.id=p.staff_id', array('s.code', 's.firstname', 's.lastname', 's.email', 's.regional_market', 's.phone_number'));

        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(s.firstname, " ", s.lastname) LIKE ?', '%'. $params['name'] .'%');

        if (isset($params['staff_id']) and $params['staff_id'])
            $select->where('p.staff_id = ?', $params['staff_id']);

        if (isset($params['area_id']) and $params['area_id'])
            $select->where('p.area_id = ?', $params['area_id']);

        $select->order('p.assigned_at', 'DESC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache() {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->staff_id] = array(
                                            'area_id' => $item->area_id,
                                            'status' => $item->status,
                                            );
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

}