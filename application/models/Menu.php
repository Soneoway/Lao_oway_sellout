<?php
class Application_Model_Menu extends Zend_Db_Table_Abstract
{
	protected $_name = 'menu';

	function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('a' => $this->_name), 'p.parent_id = a.id', array('main_menu' =>
                'a.title'));

        if (isset($params['title']) and $params['title'])
            $select->where('p.title LIKE ?', '%' . $params['title'] . '%');


        if (isset($params['parent_id']) && $params['parent_id']) {
            $select->where('p.parent_id = ?', $params['parent_id']);
        }

        $select->order('p.parent_id', 'COLLATE utf8_unicode_ci ASC');

        $select->group('p.id');


        // if ($limit)
        //     $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent_id = ?', 0);
            $data = $this->fetchAll($where, 'title');

            $result = array();
            if ($data) {
                foreach ($data as $item) {
                    $result[$item->id] = $item->title;
                }
            }
            $cache->save($result, $this->_name . '_cache', array(), null);
        }
        return $result;
    }


    function get_parent()
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));


            $select->where('p.parent_id = ?', 0);

        $select->order('p.title', 'COLLATE utf8_unicode_ci ASC');

        $select->group('p.id');


        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
