<?php
class Application_Model_Feedback extends Zend_Db_Table_Abstract
{
	protected $_name = 'feedback';

	function fetchPagination($page, $limit, &$total, $params){
		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('f' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS f.id'), 'f.*'));
        $select->joinLeft(array('s'  => 'staff'), 'f.created_by = s.id', 
        		array('name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)") )); 
        $select->joinLeft(array('g'  => 'group'), 's.group_id = g.id', 
            array('group_name' => 'g.name'));
        $select->joinLeft(array('sr'  => 'staff'), 'f.read_by = sr.id', 
        		array('reader_name' => new Zend_Db_Expr("CONCAT(sr.firstname, ' ', sr.lastname)") ));
       	$select->joinLeft(array('st'  => 'store'), 'f.store_id = st.id', 
        		array('store_name' => 'st.name'));
        $select->joinLeft(array('re'  => 'regional_market'), 'st.regional_market = re.id',array());      
        $select->joinLeft(array('a'  => 'area'), 're.area_id = a.id',array('area'=>'a.name'));      
       	if (in_array($userStorage->group_id, array(PGPB_ID,SALES_ID))) {
       		$select->where('f.created_by = ?',$params['staff_id']);
       	}
       	if (isset($params['store_id']) and $params['store_id']) {
       		$select->where('f.store_id in (?)',$params['store_id']);
       	}

       	if (isset($params['store_name']) and $params['store_name']) {
       		$select->where('st.name like ?','%'.$params['store_name'].'%');
       	}
       	if (isset($params['from']) and $params['from']){
            $date = explode('/', $params['from']);
            $from = $date[2].'-'.$date[1].'-'.$date[0]. ' 00:00:00';
            $select->where('f.created_at >= ?',$from  );
        }
        if (isset($params['to']) and $params['to']){
            $date = explode('/', $params['to']);
            $to = $date[2].'-'.$date[1].'-'.$date[0]. ' 23:59:59';
            $select->where('f.created_at <= ?', $to );
        }
        if (isset($params['area_id']) and $params['area_id']) {
       		$select->where('a.id in (?)',$params['area_id']);
       	}
        $select->order('f.id DESC');

        $select->limitPage($page, $limit);
        // die($select);
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function viweFeedback($params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('f' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS f.id'), 'f.*'));
        $select->joinLeft(array('s'  => 'staff'), 'f.created_by = s.id', 
        		array('name' => 's.firstname')); 
        $select->joinLeft(array('sr'  => 'staff'), 'f.read_by = sr.id', 
        		array('reader_name' => 'sr.firstname','sr.lastname'));     
       	$select->joinLeft(array('st'  => 'store'), 'f.store_id = st.id', 
        		array('store_name' => 'st.name'));  
        $select->where('f.id = ?',$params['id']);    
        $select->order('f.id', 'COLLATE utf8_unicode_ci ASC');

        $select->limitPage($page, $limit);
        // echo $select;die;
        $result = $db->fetchRow($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
