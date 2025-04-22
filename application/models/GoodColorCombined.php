<?php
class Application_Model_GoodColorCombined extends Zend_Db_Table_Abstract
{
	protected $_name = 'good_color_combined';
    protected $_schema = WAREHOUSE_DB;

    function getColorByModel($good_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'gc.id',
            'name' => 'gc.name',
        );

        $select = $db->select()
            ->from(array('gc'  => WAREHOUSE_DB.'.good_color'), $get)
            ->join(array('gcc' => WAREHOUSE_DB.'.good_color_combined'), 'gc.id = gcc.good_color_id', array())
            ->where('gcc.good_id IN (?)', $good_id)
            ->group('gc.id')
            ->order('gc.name ASC');

        // echo $select; 
        $result = $db->fetchAll($select);
        return $result;

    }

     function getColorByModelNew($good_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'gc.id',
            'name' => 'gc.name',
        );

        $select = $db->select()
            ->from(array('gc'  => WAREHOUSE_DB.'.good_color'), $get)
            ->join(array('gcc' => WAREHOUSE_DB.'.good_color_combined'), 'gc.id = gcc.good_color_id', array())
            ->where('gcc.good_id IN (?)', $good_id)
            ->group('gc.id')
            ->order('gc.name ASC');

        // echo $select; 
        $result = $db->fetchAll($select);

        // echo $result;

        // $result = array();
        //     if ($data){
        //         foreach ($data as $item){
        //             $result[$item->id] = $item->name;
        //     }
        // }

        return $result;

    }

}
