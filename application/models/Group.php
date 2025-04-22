<?php
class Application_Model_Group extends Zend_Db_Table_Abstract
{
	protected $_name = 'group';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');




        $select->order('p.id', 'COLLATE utf8_unicode_ci ASC');

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
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
    //for timing
    function get_cache_2(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache_2');

        if ($result === false) {
           
            $where = array();
            $where[] = $this->getAdapter()->quoteInto('id NOT IN (?)', array(ADMINISTRATOR_ID, HR_EXT_ID ,HR_ID , EXPORT_ID));
            
            $data = $this->fetchAll($where);

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache_2', array(), null);
        }
        return $result;
    }
}                                                      
