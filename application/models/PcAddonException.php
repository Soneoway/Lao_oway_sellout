<?php
class Application_Model_PcAddonException extends Zend_Db_Table_Abstract
{
    protected $_name = 'pc_addon_exception';

    function getPcExceptionStore($params) {

    	$db = Zend_Registry::get('db');

    	$get = array(
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_level'      => 'st.store_grade',
            'st_org_id'     => 'o.org_id',
            'st_org_name'   => 'o.org_name',
            'st_type_id'    => 'o.store_type_id',
        );

        $select = $db->select()
            ->from(array('pae' => 'pc_addon_exception'), $get)
            ->join(array('st'  => 'store')  , 'pae.store_id = st.id'    , array())
            ->join(array('o'   => 'org')    , 'st.org_dealer = o.org_id', array())
            ->where('staff_id = ?', $params['staff_id'])
            ->where('from_date >= ?', $params['from'])
            ->where('to_date <= ?', $params['to']);

        // echo $select; die;
        $result = $db->fetchRow($select);
        return $result;

    }

}