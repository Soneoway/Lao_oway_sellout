<?php
class Application_Model_ProductInfo extends Zend_Db_Table_Abstract
{
	protected $_name = 'product_info';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.id', 'p.name', 'p.type', 'p.enable'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['type']) and $params['type'])
            $select->where('p.type = ?', $params['type']);

        if (isset($params['enable']) and $params['enable'])
            $select->where('p.enable = ?', $params['enable']);

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

    function getProductInfo($display, $type) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array(
                'id'                         => 'p.id',
                'name'                       => 'p.name',
                'file_link'                  => 'p.file_link',
                'img_title'                  => 'p.img_title',
                'video_link'                 => 'p.video_link',
                'type'                       => 'p.type',
                'detail'                     => 'p.detail',
                'created_at'                 => 'p.created_at',
                'updated_at'                 => 'p.updated_at',
            ));

            $select->where('p.enable = ?', $display);

            if (isset($type) and $type)
                $select->where('p.type = ?', $type);

            $select->order('p.id DESC');

        //echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

}                                                      
