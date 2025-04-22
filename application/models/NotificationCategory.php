<?php
class Application_Model_NotificationCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'notification_category';

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

    function get_full_cache(){
        $cache  = Zend_Registry::get('cache');
        $result = $cache->load($this->_name.'_full_cache');

        if ($result === false) {

            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from(array('c' => $this->_name), array('c.id', 'child_name' => 'c.name'))
                ->joinLeft(array('p' => $this->_name), 'c.parent=p.id', array('parent_id' => 'p.id'))
                ->order(array('p.name', 'c.name'));

            $data = $db->fetchAll($select);

            $result = array();
            if ($data){
                $array = array();

                foreach ($data as $key => $value) {
                    $array[] = array('id' => $value['id'], 'parent_id' => $value['parent_id']); 
                }

                $result = $this->buildTree($array);

            }
            $cache->save($result, $this->_name.'_full_cache', array(), null);
        }
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

    function get_string_cache(){
        $cache  = Zend_Registry::get('cache');
        $result = $cache->load($this->_name.'_string_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                $tmp = array();

                foreach ($data as $key => $value) {
                    $tmp[ $value['id'] ] = $value->toArray();
                }

                $result = $this->buildString($tmp);
            }
            $cache->save($result, $this->_name.'_string_cache', array(), null);
        }
        return $result;
    }

    function buildTree(array $elements, $parentId = 0) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    function buildString($temp = array())
    {
        $result = array();

        foreach ($temp as $i => $details)
        {
            $parentID = $details['parent'];
            $tmpstring = ($details['name']);
            if ($parentID > 0 && isset($temp[$parentID]))
            {
                $temp[$parentID]['children'][] =& $temp[$i];
                while ($parentID > 0 && isset($temp[$parentID]))
                {
                    $tmpstring = $temp[$parentID]['name']." > ".$tmpstring;
                    $parentID = $temp[$parentID]['parent'];
                }
            }
            $result[ $i ] = $tmpstring;
        }

        return $result;
    }
}

