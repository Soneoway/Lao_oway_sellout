<?php
class Application_Model_CustomerSurvey extends Zend_Db_Table_Abstract
{
    protected $_name = 'customer_survey';

    function fetchPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('cs' => $this->_name), array('cs.*'))
            ->order('cs.created_at DESC');

        if (isset($params['topic']) and $params['topic'])
            $select->where('cs.topic LIKE ?', '%' . $params['topic'] . '%');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}