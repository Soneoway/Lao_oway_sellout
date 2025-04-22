<?php
class Application_Model_BmComTarget extends Zend_Db_Table_Abstract
{
	protected $_name = 'bm_com_target';

    function getBsStoreTarget($params) {

        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'target'        => 'bct.target',
            'target_focus'  => 'bct.target_focus',
        );

        $select = $db->select()
            ->from(array('st'  => 'store'), $get)
            ->joinLeft(array('bct'  => 'bm_com_target'), 'st.id = bct.store_id', array())
            ->where('st.id = ?', $params['store_id'])
            ->where('bct.from_date <= ?', $from)
            ->where('bct.to_date >= ?', $to);
        
        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

}