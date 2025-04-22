<?php
class Application_Model_NewsInfo extends Zend_Db_Table_Abstract
{
	protected $_name = 'news_info';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('n' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS n.id'), 'n.id', 'n.name', 'n.type', 'n.enable'));

        if (isset($params['name']) and $params['name'])
            $select->where('n.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['type']) and $params['type'])
            $select->where('n.type = ?', $params['type']);

        if (isset($params['enable']) and $params['enable'])
            $select->where('n.enable = ?', $params['enable']);

        $select->order('n.id DESC');

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

    function getNewsInfo($display, $type) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('n' => $this->_name), array(
                'id'                       => 'n.id',
                'name'                       => 'n.name',
                'file_link'                  => 'n.file_link',
                'img_title'                  => 'n.img_title',
                'video_link'                 => 'n.video_link',
                'type'                       => 'n.type',
                'detail'                     => 'n.detail',
                'created_at'                 => 'n.created_at',
                'updated_at'                 => 'n.updated_at',
            ));

            $select->where('n.enable = ?', $display);

            if (isset($type) and $type)
                $select->where('n.type = ?', $type);

            $select->order('n.id DESC');

//        echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

}                                                      
