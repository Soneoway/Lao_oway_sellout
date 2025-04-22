<?php
class Application_Model_WhRegion extends Zend_Db_Table_Abstract
{
    protected $_name = 'region';
    protected $_schema = WAREHOUSE_DB;
    
    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->order('p.name', 'COLLATE utf8_unicode_ci ASC');

        if($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache() {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function get_all_cache() {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_all_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = array(
                        'name' => $item->name,
                        'area_id' => $item->area_id,
                        );
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_all_cache', array(), null);
        }
        return $result;
    }
}