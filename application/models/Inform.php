<?php
class Application_Model_Inform extends Zend_Db_Table_Abstract
{
	protected $_name = 'inform';

    function fetchPagination($page, $limit, &$total, $params) {
        $db = Zend_Registry::get('db');
  if (isset($params['group_cat']) and $params['group_cat']){
            $select = $db->select();
            $select_fields = array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*' );

            if (isset($params['get_fields']) and is_array($params['get_fields']))
                foreach ($params['get_fields'] as $get_field)
                    array_push($select_fields, $get_field);
            else
                array_push($select_fields, 'p.*');

            $select
                ->from(array('p' => $this->_name),
                    $select_fields
                )
                ->joinLeft(array('o' => 'inform_team'), 'o.id = p.id', array())
                ->group('p.category_id');

        } 
        else
        {
             $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'))
            ->joinLeft(array('o' => 'inform_team'), 'o.id = p.id', array());
             $select->group('p.id');
        }
       
         
        
        if (isset($params['id']) and $params['id'])
            $select->where('p.id = ?', $params['id']);

        if (isset($params['title']) and $params['title'])
            $select->where('p.title LIKE ?', '%'.$params['title'].'%');

        if (isset($params['content']) and $params['content'])
            $select->where('p.content LIKE ?', '%'.$params['content'].'%');

        $select_tmp = $db->select();

        if (isset($params['team']) && $params['team'])
           {
              $select->where('o.object_id = ? ', $params['team']);
           }
           
          

        if (isset($conditions) && count($conditions))
            $select->where(implode(' ', $conditions));
        

        if (isset($params['created_from']) && $params['created_from']) {
            $select->where('p.created_at >= ?', $params['created_from']);
        }

        if (isset($params['created_to']) && $params['created_to']) {
            $select->where('p.created_at <= ?', $params['created_to']);
        }


    
        if (isset($params['status']) && $params['status'])
            $select->where('p.status = ?', $params['status']);
        
      
       

        if(isset($params['sort']) && $params['sort']) {
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            $collate = ' COLLATE utf8_unicode_ci ';
            $order_str = 'p.`'.$params['sort']. '` ' . $collate . $desc;

            $select->order(new Zend_Db_Expr($order_str));
        } else {
        	$select->order('created_at DESC');
        }

    
       

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
