<?php
class Application_Model_Guest extends Zend_Db_Table_Abstract
{
	protected $_name = 'guest';



    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.shop LIKE ?', '%'.$params['name'].'%');

      /*  $select->where('p.goal is null ' , null);
        $select->where('p.photo is  not null ' , null);
        $select->where('p.check <= "2015-06-16 05:10:00"');*/
      //  $select->where('p.region = "Bình Định"');
       // $select->where('p.goal = 0' , null);
      //  $select->where('p.photo is  not null ' , null);
        $select->where('p.goal is null ' , null);
        $select->where('p.photo is not null ' , null);
        $select->order(new Zend_Db_Expr('0*`id`+RAND()'));
        $select->limitPage($page, $limit);
          $select->where('p.check <= "2015-06-23 07:00:00"'); 
       

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function loadStaff($id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));
        $select->where('p.id = ? ', $id);
        $result = $select->fetchRow($select);
        return $result;
    }

}                                                      
