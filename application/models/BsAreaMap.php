<?php
class Application_Model_BsAreaMap extends Zend_Db_Table_Abstract
{
    protected $_name = 'bs_area_map';

    public function getAllProvinceByArea($area_id) {

        $db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('bam2' => 'bs_area_map'), array('bs_area_id'))
            ->where('bam2.area_id = ?', $area_id);

        $get = array(
            'province_id' 	=> 'rm.id',
            'province_name' => 'rm.name',
            'area_owner' 	=> new Zend_Db_Expr("(CASE WHEN a.id = ".$area_id." THEN 'YES' ELSE 'NO' END)"),
        ); 

        $select = $db->select()
            ->from(array('bam' => 'bs_area_map'), $get)
            ->join(array('a' => 'area'), 'bam.area_id = a.id', array())
            ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id', array())
            ->where('bam.bs_area_id = (?)', $sub_select);
            //->where('bam.area_id = ?', $area_id);

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}