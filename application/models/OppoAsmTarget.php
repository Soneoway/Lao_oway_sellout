<?php
class Application_Model_OppoAsmTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'oppo_asm_target';

    function gobal_asm_function($params){
        $db = Zend_Registry::get('db');

        $select_asm_id = $db->select()
           ->from(array('asm' => 'asm'),array('asm.staff_id'))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');

        $select_asm_code = $db->select()
           ->from(array('asm' => 'asm'),array('s.code'))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');

        $select_asm_name = $db->select()
           ->from(array('asm' => 'asm'),array("CONCAT(s.firstname, ' ',s.lastname)"))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');


        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'id'            =>  new Zend_Db_Expr("(".$select_asm_id.")"),
            'code'          =>  new Zend_Db_Expr("(".$select_asm_code.")"),
            'name'          =>  new Zend_Db_Expr("(".$select_asm_name.")")
        );

        $select =$db->select()
            ->from(array('a' => 'area'), $get)
            ->where('a.id NOT IN (120)');

        if(isset($params['rgm']) && $params['rgm']) {
            $select->joinLeft(array('asm' => 'asm'),'a.id = asm.area_id',array());
            $select->where('asm.staff_id =?',$params['rgm']);
        }

        if(isset($params['asm']) && $params['asm']) {
            $select->joinLeft(array('asm' => 'asm'),'a.id = asm.area_id',array());
            $select->where('asm.staff_id =?',$params['asm']);
        }

        $result = $db->fetchAll($select);

        // echo $select; die;

        return $result;

    }

    // Close By Khuan 5/12/2023
    // function getasmbyasmarea($params){
    //     $db = Zend_Registry::get('db');

    //      $get = array(
    //             'area_id' => 'a.id',
    //             'area_name' => 'a.name',
    //             'id'        => 's.id',
    //             'code'      => 's.code',
    //             'name'      => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
    //         );

    //     $select = $db->select()
    //             ->from(array('ss' => 'store_staff'), $get)
    //             ->join(array('s' => 'staff'),'s.id = ss.staff_id AND s.status = 1',array())
    //             ->join(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
    //             ->join(array('a' => 'area'),'a.id = rm.area_id',array())
    //             ->where('ss.is_leader =?',4)
    //             ->where('a.id NOT IN (48,49,72,73,74,75,76,77,78,79,80)')
    //             ->group('s.id')
    //             ->order('a.name ASC');

    //     if (isset($params['asm']) && $params['asm']) {
    //         $select->joinLeft(array('rm2' => 'regional_market') , 'a.id = rm2.area_id', array());
    //         $select->joinLeft(array('asm' => 'asm') , "(CASE WHEN asm.type = 2 THEN a.id ELSE rm2.id END) = asm.area_id", array());
    //         $select->where('asm.staff_id = ?', $params['asm']);
    //     }

        
    //     $result = $db->fetchAll($select);

    //     return $result;
    // }

   function getasmByArea($params) {
        $db = Zend_Registry::get('db');

        $sub_select_id = $db->select()
           ->from(array('asm' => 'asm'),array('asm.staff_id'))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');


        $sub_select_code = $db->select()
           ->from(array('asm' => 'asm'),array('s.code'))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');


         $sub_select_name = $db->select()
           ->from(array('asm' => 'asm'),array("CONCAT(s.firstname, ' ',s.lastname)"))
           ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id',array())
           ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id',array())
           ->where('ss.is_leader = 4')
           ->where('asm.area_id = a.id')
           ->group('asm.area_id');
        
        
         $get = array(
                'area_id'       => 'a.id',
                'area_name'     => 'a.name',
                'id'            => new Zend_Db_Expr("(".$sub_select_id.")"),
                'code'          => new Zend_Db_Expr("(".$sub_select_code.")"),
                'name'          => new Zend_Db_Expr("(".$sub_select_name.")"),

            );

         $select = $db->select()

         ->from(array('a' => 'area'),$get)
         ->where('a.id != 120')
         ->group('a.id');


        
        $result = $db->fetchAll($select);

        // echo $select; die;

        return $result;
    }}