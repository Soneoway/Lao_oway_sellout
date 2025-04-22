<?php
class Application_Model_ProductInfoTitle extends Zend_Db_Table_Abstract
{
    protected $_name = 'product_info_title';

//    function fetchPagination($page, $limit, &$total, $params){
//        $db = Zend_Registry::get('db');
//
//        $get = array(
//            'id'              => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS pit.id'),
//            'topic'           => 'pit.topic',
//            'created_at'      => 'pit.created_at',
//            'sort'            => 'pit.sort',
//            'enable'          => 'pit.enable',
//        );
//
//        $select = $db->select()
//            ->from(array('pit' => $this->_name), $get);
//
//        if (isset($params['topic']) and $params['topic'])
//            $select->where('pit.topic LIKE ?', '%'.$params['topic'].'%');
//
//        $select->order('pit.sort ASC');
//
//        if ($limit)
//            $select->limitPage($page, $limit);
//
//        $result = $db->fetchAll($select);
//        $total = $db->fetchOne("select FOUND_ROWS()");
//
//        return $result;
//    }
}
