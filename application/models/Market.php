<?php
class Application_Model_Market extends Zend_Db_Table_Abstract
{
    protected $_name = 'market';
    protected $_schema = WAREHOUSE_DB;

    function getSOListBySale($params) {

    	$db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('cnt' => WAREHOUSE_DB.'.credit_note_tran'), array('A'=>new Zend_Db_Expr("IFNULL(SUM(cnt.use_discount),0)")))
            ->where('cnt.sales_order = cm.sn', 1);

        $get = array(
        	'd_id'			=> 'd.id',
        	'd_name'		=> 'd.title',
            'sn_ref'     	=> 'm.sn_ref',
            'inv_no'		=> 'm.invoice_number',
            'pay_time'		=> 'cm.pay_time',
            'created_name'	=> new Zend_Db_Expr("CONCAT(ws.firstname, ' ', ws.lastname)"),
            'm_total'		=> new Zend_Db_Expr("ROUND(ROUND(( TRUNCATE(( SUM( (ROUND((( m.price - ((m.price*IFNULL(m.sale_off_percent,0)/100)*100)/100 )/1.07),2)*m.num) ) - m.total_spc_discount + ( IFNULL(m.delivery_fee/1.07,0) ) ) ,2)*1.07 ),2)-(".$sub_select."),2)"),
        );

        $select = $db->select()
            ->from(array('cm' => WAREHOUSE_DB.'.checkmoney'), $get)
            ->join(array('m'  => WAREHOUSE_DB.'.market')		, 'cm.sn = m.sn'    		, array())
            ->join(array('d'  => WAREHOUSE_DB.'.distributor')	, 'm.d_id = d.id'  			, array())
            ->join(array('ws' => WAREHOUSE_DB.'.staff')			, 'm.salesman = ws.id'		, array())
            //->join(array('cs' => 'staff')						, 'm.sales_catty_id = cs.id', array())
            ->where('d.rank IN (?)', array(7,8,13,14))
            ->where('cm.type = ?', 2)
            ->where('cm.pay_time >= ?', $params['from']." 00:00:00")
            ->where('cm.pay_time <= ?', $params['to']." 23:59:59")
            ->where('m.sales_catty_id = ?', $params['staff_id'])
            ->where('m.canceled <> ?', 1)
            ->group('m.sn')
            ->order(array('m.pay_time DESC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function ADAO_fetchPagination($page, $limit, &$total, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting('~E_ALL');

        $now = date('Y-m-d');
        $last = date('Y-m-d', strtotime("-1 Days", strtotime($now)));

        $db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('kssl2' => WAREHOUSE_DB.'.kerry_shipment_status_log'), array('kssl2.id'))
            ->where('kssl2.sn_ref = m.sn_ref COLLATE utf8_unicode_ci', 1)
            ->group(array('kssl2.sn_ref','kssl2.shipment_status_date'))
            ->order(array('kssl2.shipment_status_date DESC'))
            ->limit(1);

        $get = array(
            'm_id'          => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS m.id'),
            'sn'            => 'm.sn',
            'sn_ref'        => 'm.sn_ref',
            'add_time'      => 'm.add_time',
            
            // 'phone'         => 'sad.phone',
            // 'address'       => 'sad.address',
            // 'contact_name'  => 'sad.contact_name',

            'cnt_phone'     => new Zend_Db_Expr("SUM(CASE WHEN m.cat_id = 11 THEN m.num ELSE 0 END)"),
            'cnt_acc'       => new Zend_Db_Expr("SUM(CASE WHEN m.cat_id = 12 THEN m.num ELSE 0 END)"),

            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'provice_id'    => 'spr.provice_id',
            'provice_name'  => 'spr.provice_name',
            'amphure_name'  => 'sam.amphure_name',
            'district_name' => 'sdi.district_name',
            'zipcode'       => 'szi.zipcode',

            'company_logistics' => new Zend_Db_Expr(
                "   (CASE 
                        WHEN dsa.company = 3 THEN 'Genious' 
                        WHEN dsa.company = 5 THEN 'NKC' 
                        WHEN dsa.company = 6 THEN 'YAS' 
                        WHEN dsa.company = 9 THEN 'J&T' 
                        ELSE 
                            (CASE 
                                WHEN ktr.delivery_type = 1 THEN 'Kerry' 
                                WHEN ktr.delivery_type = 2 THEN 'J&T' 
                                ELSE '-' 
                            END)
                    END)
                "),

            'con_no'   => 'ktr.tracking_no',
            // 'weight'             => 'dor.weight',
            // 'number_of_package'  => 'dor.number_of_package',

            'status'        => 'ktr.status',
            // 'send_date'     => 'ktr.send_date',
            // 'status_code'   => 'ktr.status_code',

            'd_id'          => 'dis.id',
            'd_name'        => 'dis.title',

            'kerry_status_code' => 'kssl.shipment_status_id',

        );

        $select = $db->select()
            ->from(array('m' => WAREHOUSE_DB.'.market'), $get)
            ->join(array('dis' => WAREHOUSE_DB.'.distributor')        , 'dis.id = m.d_id'                     , array())
            ->join(array('rm'  => 'regional_market')                  , 'dis.region = rm.id'                  , array())
            ->join(array('a'   => 'area')                             , 'rm.area_id = a.id'                   , array())

            ->join(array('sad' => WAREHOUSE_DB.'.shipping_address')   , 'sad.id = m.shipping_address'         , array())
            ->join(array('sam' => WAREHOUSE_DB.'.shipping_amphures')  , 'sam.amphure_id = sad.amphures_id'    , array())
            ->join(array('sdi' => WAREHOUSE_DB.'.shipping_districts') , 'sdi.district_code = sad.districts_id', array())
            ->join(array('spr' => WAREHOUSE_DB.'.shipping_provinces') , 'spr.provice_id = sad.province_id'    , array())
            ->join(array('szi' => WAREHOUSE_DB.'.shipping_zipcodes')  , 'szi.zip_id = sad.zipcodes'           , array())
            ->join(array('dsa' => WAREHOUSE_DB.'.delivery_sales')     , 'dsa.sales_sn = m.sn'                 , array())
            ->join(array('dor' => WAREHOUSE_DB.'.delivery_order')     , 'dor.id = dsa.delivery_order_id'      , array())
            ->join(array('ktr' => WAREHOUSE_DB.'.kerry_transaction')  , 
                "   ktr.sn = m.sn 
                    AND ktr.type = 1 
                    AND ktr.status = 7 
                    AND ktr.is_co IS NULL 
                    AND ktr.outmysql_time >= '".$last." 00:00:00' 
                    AND ktr.outmysql_time <= '".$last." 23:59:59' 
                ", array())

            ->joinLeft(array('kssl' => WAREHOUSE_DB.'.kerry_shipment_status_log'), 
                "   kssl.id = (".$sub_select.") 
                    AND LENGTH(kssl.shipment_status_id) < 10 
                    AND kssl.shipment_status_id = 'POD' 
                    AND kssl.from IN (2,3) 
                    AND kssl.shipment_status_date >= '".$now." 00:00:00' 
                    AND kssl.shipment_status_date <= '".$now." 23:59:59' 
                ", array())
            // ->where('a.id = ?', 52)
            ->group('m.sn')
            ->order('m.add_time DESC');

        // Add Filter SO 
        if (isset($params['sn']) and $params['sn'])
            $select->where('m.sn_ref LIKE ?', '%'.$params['sn'].'%');

        if (isset($params['d_id']) and $params['d_id'])
            $select->where('dis.id = ?', $params['d_id']);

        if (isset($params['d_name']) and $params['d_name'])
            $select->where('dis.title LIKE ?', '%'.$params['d_name'].'%');

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('dis.region IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('dis.region = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Status
        if (isset($params['status_id']) && $params['status_id']) {
            if ( $params['status_id'] == 1 ) { $select->where('kssl.shipment_status_id IS NULL', 1); } 
            if ( $params['status_id'] == 2 ) { $select->where('kssl.shipment_status_id = ?', 'POD'); } 
        }

        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        if ($limit)
            $select->limitPage($page, $limit);

        // echo $select; die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;

    }

}