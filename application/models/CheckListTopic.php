<?php
class Application_Model_CheckListTopic extends Zend_Db_Table_Abstract
{
	protected $_name = 'check_list_topic';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $get = array(
            'id'              => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS clt.id'),
            'name'            => 'clt.name',
            'description'     => 'clt.description',
            'from_date'       => 'clt.from_date',
            'to_date'         => 'clt.to_date',
            'count_questions' => new Zend_Db_Expr('COUNT(clq.id)'),
            'enable'          => 'clt.enable',
        );

        $select = $db->select()
            ->from(array('clt' => $this->_name), $get)
            ->joinLeft(array('clq' => 'check_list_questions'), 'clq.topic_id = clt.id AND clq.id <> 0', array());

        if (isset($params['name']) and $params['name'])
            $select->where('clt.name LIKE ?', '%'.$params['name'].'%');


        $select->order('FIELD(clt.id, 0) DESC');
        $select->order('clt.from_date DESC');
        $select->order('clt.enable ASC');
        $select->group('clt.id');

        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }


    public function MenuParenting($parent, $childPool)
    {
        foreach ($childPool as $child) {
            if ($parent['id'] == $child['parent_id']) {
                $parent['children'][] = $this->MenuParenting($child, $childPool);
            }
        }
        return $parent;
    }
}
