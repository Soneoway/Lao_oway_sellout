<?php
class Application_Model_BypassImei extends Zend_Db_Table_Abstract
{
    protected $_name = 'bypass_imei';

    function getList($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'bi_id'     => 'bi.id',
            'bi_imei'   => 'bi.imei',
            'bi_remark' => 'bi.remark',
            'created_by'=> new Zend_Db_Expr("CONCAT('[', s.code, '] ', s.firstname, ' ', s.lastname)"),
            'created_at'=> 'bi.created_at',

        );

        $select = $db->select()
            ->from(array('bi' => 'bypass_imei'), $get)
            ->join(array('s'  => 'staff'), 'bi.created_by = s.id', array())
            ->order(array('bi.created_at DESC'));

        if ( isset($params['imei']) && $params['imei'] ) {
            $select->where('bi.imei IN (?)', $params['imei']);
        }

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }


}