<?php
class Application_Model_OppoMktTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'oppo_mkt_target';

  function getmktbyasmarea($params){
        $db = Zend_Registry::get('db');

         $get = array(
                'area_id' => 'a.id',
                'area_name' => 'a.name',
                'id'        => 's.id',
                'code'      => 's.code',
                'name'      => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
            );

        $select = $db->select()
                ->from(array('ss' => 'store_staff'), $get)
                ->join(array('s' => 'staff'),'s.id = ss.staff_id AND s.status = 1',array())
                ->join(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
                ->join(array('a' => 'area'),'a.id = rm.area_id',array())
                ->where('ss.is_leader =?',5)
                ->where('a.id NOT IN (48,49,72,73,74,75,76,77,78,79,80)')
                ->group('s.id')
                ->order('a.name ASC');


        if (isset($params['asm']) && $params['asm']) {
            $select->joinLeft(array('rm2' => 'regional_market') , 'a.id = rm2.area_id', array());
            $select->joinLeft(array('asm' => 'asm') , "(CASE WHEN asm.type = 2 THEN a.id ELSE rm2.id END) = asm.area_id", array());
            $select->where('asm.staff_id = ?', $params['asm']);
        }

        
        $result = $db->fetchAll($select);

        return $result;
    }

    function getmktByArea($params) {
        $db = Zend_Registry::get('db');
         $get = array(
                'area_id'       => 'a.id',
                'area_name'     => 'a.name',
                'id'            => 's.id',
                'code'          => 's.code',
                'name'          => new Zend_Db_Expr("CONCAT(s.firstname, ' ',s.lastname)"),
                'target'        => 'pmt.target',
                'target_hero'   => 'pmt.target_hero',
            );

         $select = $db->select()
                ->from(array('ss' => 'store_staff'), $get)
                ->join(array('s' => 'staff'),'s.id = ss.staff_id AND s.status = 1',array())
                ->join(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
                ->joinLeft(array('t' => 'timing'),'t.store = ss.store_id',array())
                ->join(array('a' => 'area'),'a.id = rm.area_id',array())
                ->joinLeft(array('pmt' => 'oppo_pcm_target'),
                    "
                    pmt.staff_id = ss.staff_id
                    AND pmt.from_date >= '".$params['from']."'
                    AND pmt.to_date <= '".$params['to']."'
                    "
                    ,array())
                ->where('ss.is_leader =?',5)
                ->group('s.id')
                ->order('a.name ASC');

        
        $result = $db->fetchAll($select);

        return $result;
    }
}