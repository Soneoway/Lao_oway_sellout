<?php
class Application_Model_SalesKnowledgeBase extends Zend_Db_Table_Abstract
{
	protected $_name = 'sales_knowledge_base';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $get = array(
            'id'             => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS k.id'),
            'title'          => 'k.title',
            'knowledge_type' => 'kt.name',
            'enable'         => 'k.enable',
            'visitors'       => new Zend_Db_Expr('COUNT(sks.id)'),
        );

        $select = $db->select()
            ->from(array('k' => $this->_name), $get)
            ->join(array('kt' => 'sales_knowledge_base_type'), 'kt.id = k.knowledge_type', array())
            ->joinLeft(array('sks' => 'sales_knowledge_statistic'), new Zend_Db_Expr('FIND_IN_SET(k.id, sks.id_info)'), array());

        if (isset($params['name']) and $params['name'])
            $select->where('k.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['knowledge_type']) and $params['knowledge_type'])
            $select->where('k.knowledge_type = ?', $params['knowledge_type']);

        if (isset($params['enable']) and $params['enable'])
            $select->where('k.enable = ?', $params['enable']);

        $select->group('k.id');
        $select->order('k.id DESC');

        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}
