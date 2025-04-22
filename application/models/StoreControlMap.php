<?php
class Application_Model_StoreControlMap extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_control_map';

    function store_list($store_control_id) {
        $db = Zend_Registry::get('db');

        /*
        $sub_select =  $db->select()->from(array('scm' => 'store_control_map'), array('rm_id' => 'rm.id'))
            ->join(array('st' => 'store')           , 'scm.store_id = st.id'        , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->where('scm.store_control_id = ?', $store_control_id);
        */

        $get = array(
            'st_area'    => 'a.name',
            'st_id'      => 'st.id',
            'st_name'    => 'st.name',
            'st_type'    => 'o.org_name', 
            'st_status'  => 'st.del',
            'sale_id'    => 's.id',
            'sale_code'  => 's.code',
            'sale_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sale_group' => 'g.name',
            'st_sellout' => new Zend_Db_Expr("COUNT(ts.imei)"),
        );

        $select = $db->select()->from(array('st' => 'store'), $get)
            ->join(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm'   => 'regional_market')   , 'st.regional_market = rm.id'  , array())
            ->join(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
            ->join(array('scm'  => 'store_control_map') , 'st.id = scm.store_id'        , array())
            ->joinLeft(array('ss' => 'store_staff')     , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s' => 'staff')            , 'ss.staff_id = s.id'          , array())
            ->joinLeft(array('g' => 'group')            , 's.group_id = g.id'           , array())
            ->joinLeft(array('t' => 'timing'), 
                "   st.id = t.store 
                    AND t.created_at >= '".date('Y-m-01 00:00:00')."' 
                    AND t.created_at <= '".date('Y-m-d 23:59:59')."' 
                ", array())
            ->joinLeft(array('ts'=> 'timing_sale'), 't.id = ts.timing_id', array())
            ->where('scm.store_control_id = ?', $store_control_id)
            //->where('st.regional_market IN (?)', $sub_select)
            ->group('st.id')
            ->order(array('st_status ASC', 'a.name ASC','st.id ASC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function getStoreControl($store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'store_id'  => 'scm.store_id',
            'sc_id'     => 'sc.id',
            'sc_name'   => 'sc.name',
        );

        $select = $db->select()
            ->from(array('scm' => 'store_control_map'), $get)
            ->join(array('sc' => 'store_control'), 'scm.store_control_id = sc.id' , array())
            ->where('scm.store_id IN (?)', $store_id);

        //echo $select;
        $data = $db->fetchAll($select);

        $result = array();
        for ($i=0;$i<count($data);$i++) { $result[ $data[$i]['store_id'] ] = $data[$i]['sc_name']; }

        return $result;
    }

    function getPCM($store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',
        );

        $select = $db->select()
            ->from(array('scm' => 'store_control_map'), $get)
            ->join(array('sc' => 'store_control')   , 'scm.store_control_id = sc.id'    , array())
            ->join(array('s' => 'staff')            , 'sc.staff_id = s.id'              , array())
            ->join(array('g' => 'group')            , 's.group_id = g.id'               , array())
            ->where('scm.store_id = ?', $store_id);

        //echo $select;
        $result = $db->fetchRow($select);
        return $result;
    }

}