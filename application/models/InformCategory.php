<?php
class Application_Model_InformCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'inform_category';

    function fetchPagination($page, $limit, &$total, $params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['id']) and $params['id'])
            $select->where('p.id = ?', $params['id']);

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }


    function get_cache(){
        $cache  = Zend_Registry::get('cache');
        $result = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $key => $value) {
                    $result[ $value['id'] ] = $value['name'];
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

}

