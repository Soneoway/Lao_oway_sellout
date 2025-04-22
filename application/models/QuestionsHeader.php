<?php
class Application_Model_QuestionsHeader extends Zend_Db_Table_Abstract
{
	protected $_name = 'questions_header';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $get = array(
            'id'              => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS qh.id'),
            'name'            => 'qh.name',
            'from_date'       => 'qh.from_date',
            'to_date'         => 'qh.to_date',
            'count_questions' => new Zend_Db_Expr('COUNT(q.id)'),
            'enable'          => 'qh.enable',
            'question_type'   => 'qh.question_type',
        );

        $select = $db->select()
            ->from(array('qh' => $this->_name), $get)
            ->joinLeft(array('q' => 'questions'), 'q.head_id = qh.id', array());

        if (isset($params['name']) and $params['name'])
            $select->where('qh.name LIKE ?', '%'.$params['name'].'%');

        $select->group('qh.id');
        $select->order('qh.from_date DESC');
        $select->order('qh.enable ASC');

        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}
