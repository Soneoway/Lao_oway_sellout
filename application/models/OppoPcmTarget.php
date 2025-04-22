<?php
class Application_Model_OppoPcmTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'oppo_pcm_target';

    function getpcmbyasmarea($params){
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
                ->where('ss.is_leader =?',2)
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

    function getPcmByArea($params) {
        $db = Zend_Registry::get('db');
         $get = array(
                'area_id'       => 'a.id',
                'area_name'     => 'a.name',
                'id'            => 's.id',
                'code'          => 's.code',
                'name'          => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
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
                ->where('ss.is_leader =?',2)
                ->group('s.id')
                ->order('a.name ASC');

        
        $result = $db->fetchAll($select);

        return $result;
    }

     function getExportdata($params) {
        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
        $db = Zend_Registry::get('db');


        $sub_select_05 = $db->select()
            ->from(array('ss2' => 'store_staff'),array('PC' => "COUNT(ss2.staff_id)"))
            ->joinLeft(array('sf' => 'staff'),'sf.id = ss2.staff_id AND ss2.is_leader = 0',array())
            ->where('ss2.staff_id = ss.staff_id');

        $get = array(
                // 'area_id'       => 'a.id',
                'area_name'     => 'a.name',
                'id'            => 'st.id',
                'code'          => 'st.code',
                'name'          => new Zend_Db_Expr("CONCAT(st.firstname,' ',st.lastname)"),
                'target'        => 'pmt.target',
                'target_hero'   => 'pmt.target_hero',

                'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN t.pcm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' THEN t.id END)"),

                'activate'      => new Zend_Db_Expr("COUNT(CASE WHEN t.pcm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND i.activated_date IS NOT NULL THEN t.id END)"),

                'hero_product'      => new Zend_Db_Expr("COUNT(CASE WHEN t.pcm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 THEN t.id END)"),

                'hero_product_activate' => new Zend_Db_Expr("COUNT(CASE WHEN t.pcm_id = st.id AND t.created_at >='".$params['from']."' AND t.created_at <='".$params['to']."' AND ts.is_hero = 1 AND i.activated_date IS NOT NULL THEN i.imei_sn END)"),
                // // 'hero_product_activate'      => new Zend_Db_Expr("(".$sub_select_03.")"),

                'count'     => new Zend_Db_Expr("(".$sub_select_05.")")
            );

        $select = $db->select()
                ->from(array('ss'=>'store_staff'),$get)
                ->joinLeft(array('st' => 'staff'),'st.id = ss.staff_id',array())
                ->joinLeft(array('rm' => 'regional_market'),'rm.id = st.regional_market',array())
                ->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
                ->joinLeft(array('t' => 'timing'),'t.store = ss.store_id',array())
                ->joinLeft(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array())
                ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn  = ts.imei',array())
                ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
                ->joinLeft(array('pmt' => 'oppo_pcm_target'),
                    "
                    pmt.staff_id = ss.staff_id
                    AND pmt.from_date >= '".$params['from']."'
                    AND pmt.to_date <= '".$params['to']."'
                    "
                    ,array())

                ->where('st.status = 1')
                ->where('ss.is_leader = 2')
                ->group('st.id');
                // ->order('a.name ASC');

        
        $result = $db->fetchAll($select);

        return $result;

    }

     function getPcmInfo($pcm_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'staff_id'  => 's.id',
            'staff_code'=> 's.code',
            'staff_name'=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id'         , array())
            ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())
            ->where('ss.is_leader = ?', 2)
            ->where('s.id = ?', $pcm_id)
            ->order(array('s.code ASC'));

        // echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

        function getLast3MonthByArea($params) {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $db = Zend_Registry::get('db');


        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'sellout'   => '0'
        );

        $select = $db->select()
            ->from(array('ss' => 'store_staff'), $get)
            ->joinLeft(array('s' => 'staff'),'s.id = ss.staff_id AND s.status = 1',array())
            ->joinLeft(array('rm' => 'regional_market'),'rm.id = s.regional_market',array())
            ->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
            ->group('a.id')
            ->order(array('a.name ASC'));
        
        //echo $select; die;
        $result_raw = $db->fetchAll($select);


        return $result_raw;
    }

}