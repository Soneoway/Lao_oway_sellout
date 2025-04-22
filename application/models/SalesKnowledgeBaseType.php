<?php
class Application_Model_SalesKnowledgeBaseType extends Zend_Db_Table_Abstract
{
	protected $_name = 'sales_knowledge_base_type';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('kt' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS kt.id'), 'kt.id', 'kt.name', 'kt.enable'));

        if (isset($params['name']) and $params['name'])
            $select->where('kt.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['enable']) and $params['enable'])
            $select->where('kt.enable = ?', $params['enable']);

        $select->order('kt.id DESC');

        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}                                                      
