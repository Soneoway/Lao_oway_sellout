<?php
class Application_Model_StaffTraining extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_training';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select();

        $select->from(array('p' => $this->_name),array(new Zend_Db_Expr(
            'SQL_CALC_FOUND_ROWS
             p.*'
        )));

        if(isset($params['name']) and $params['name'])
            $select->where('CONCAT(p.firstname," ",p.lastname) LIKE ?','%'.$params['name'].'%');

        if(isset($params['cmnd']) and $params['cmnd'])
            $select->where('p.cmnd = ?',$params['cmnd']);

        if(isset($params['area_id']) and $params['area_id'])
            $select->where('p.area_id = ?',$params['area_id']);

        if(isset($params['regional_market']) and $params['regional_market'])
            $select->where('p.regional_market = ?',$params['regional_market']);

        $select->where('p.del = ? OR p.del IS NULL',0);

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);


        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->firstname . ' ' . $item->lastname;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

}