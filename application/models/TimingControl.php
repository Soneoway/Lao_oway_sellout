<?php
class Application_Model_TimingControl extends Zend_Db_Table_Abstract
{
    protected $_name = 'timing_control';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if($limit){
            $select = $db->select()
            ->from(array('p' => $this->_name),
             array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));
        }else {
            $select = $db->select()
            ->from(array('p' => $this->_name), array('p.*'));
        }

        $select->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 'i.imei_sn = p.imei',array());
        $select->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array('good_name' => 'g.name','brand_id' => 'g.brand_id'));
        $select->joinLeft(array('gcc' => WAREHOUSE_DB.'.good_color_combined'),'gcc.good_id = g.id',array());
        $select->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'),'gc.id = gcc.good_color_id',array('color_name' => 'gc.name'));
        $select->joinLeft(array('s' => HR_DB.'.staff'),'s.id = p.create_by',array('staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")));

        
        // if(isset($params['imei']) && $params['imei']){
        //     $imei = explode("\r\n", $params['imei']);
        //     $select->where('p.imei_log IN (?)',$imei);
        // }

        $select->group('p.imei');

        if($limit)
           $select->limitPage($page, $limit);

       $result = $db->fetchAll($select);

       if($limit)
           $total = $db->fetchOne("select FOUND_ROWS()");
       return $result;
   }

}