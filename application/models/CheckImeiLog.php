<?php
class Application_Model_CheckImeiLog extends Zend_Db_Table_Abstract
{
	protected $_name = 'check_imei_log';

	public function getCheckImei($imei) {

    	$db = Zend_Registry::get('db');

        $get = array(
            'area_name'     => 'a.name', 
            'province'      => 'rm.name', 
            'store_code'    => 'st.store_code',
            'store_name'    => 'st.name', 

            'imei'          => 'i.imei_sn', 
            'good_name'     => 'g.name', 
            'color_name'    => 'gc.name', 
            'product_type'    => 'i.type', 

            'pre_order_status' => new Zend_Db_Expr("(CASE WHEN ts.pre_order_status = 1 THEN 'Yes' ELSE 'No' END)"),

            'verification_date'   => 't.created_at', 
            'activated_date'=> 'i.activated_date', 

            'verification_name'      => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
            'verification_position'    => 'gr.name',
            'verification_code'    => 's.code', 

            'cus_name'      => 'c.name', 
            'cus_phone'     => 'c.phone_number', 
            'cus_address'   => 'c.address', 
            'cus_email'     => 'c.email',

            'imei_area'  => 'a2.name',
            'imei_provience' => 'rm2.name',
            'imei_distributor'  => 'd.title',
            'imei_store'  => 'st2.name',
            'imei_warehouse' => 'w.name'

        ); 

        $select = $db->select()
            ->from(array('i'  => WAREHOUSE_DB.'.imei'), $get)
            // ->from(array('ts' => 'timing_sale'), $get)
            ->joinLeft(array('ts' => 'timing_sale')	, 'i.imei_sn = ts.imei'		, array())
            ->joinLeft(array('t'  => 'timing') 					, 'ts.timing_id = t.id'		, array())
            ->joinLeft(array('st' => 'store')				, 't.store = st.id'			, array())
            ->joinLeft(array('st2' => 'store')				, 'i.store_id = st2.id'			, array())
            ->joinLeft(array('rm' => 'regional_market')		, 'st.regional_market = rm.id', array())
            ->joinLeft(array('a'  => 'area')				, 'rm.area_id = a.id'		, array())
            
            // ->joinLeft(array('i'  => WAREHOUSE_DB.'.imei')	, 'ts.imei = i.imei_sn'		, array())

            ->joinLeft(array('g'  => WAREHOUSE_DB.'.good')	, 'i.good_id = g.id'		, array())
            ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array())
            ->joinLeft(array('s'  => 'staff')				, 't.staff_id = s.id'		, array())
            ->joinLeft(array('gr' => 'group')				, 's.group_id = gr.id'		, array())
            ->joinLeft(array('c'  => 'customer')			, 'ts.customer_id = c.id'	, array())

            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
            ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
            ->joinLeft(array('rm2' => HR_DB.'.regional_market'),'rm2.id = d.region',array())
            ->joinLeft(array('a2' => HR_DB.'.area'),'a2.id = rm2.id',array());

        $select->where('i.imei_sn in (?)',$imei);  

        // echo $select;die;
        $result = $db->fetchAll($select);
        //print_r($result);die;
        return $result;
    }

}
