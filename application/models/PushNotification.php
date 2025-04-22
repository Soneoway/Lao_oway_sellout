<?php
class Application_Model_PushNotification extends Zend_Db_Table_Abstract
{
	protected $_name = 'push_notification';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.id', 'p.title', 'p.message', 'p.action'));

        if (isset($params['title']) and $params['title'])
            $select->where('p.title LIKE ?', '%'.$params['title'].'%');

        if (isset($params['action']) and $params['action'])
            $select->where('p.action = ?', $params['action']);

        $select->order('p.id DESC');

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

            $data = $this->fetchAll(null, 'name');

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

    function getPushNotification() {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array(
                'id'                       => 'p.id',
                'title'                    => 'p.title',
                'message'                  => 'p.message',
                'file_link'                => 'p.file_link',
                'created_at'               => 'p.created_at',
            ))
            ->where('p.action = ?', 2)
            ->order('p.id DESC')
            ->limit(10, 0);

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

}                                                      
