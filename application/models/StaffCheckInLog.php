<?php

class Application_Model_StaffCheckInLog extends Zend_Db_Table_Abstract 
{

    function fetchPagination($page, $limit, &$total, $params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'),
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group_id'=> 'g.id',
            'staff_group'   => 'g.name',
            'area_name'     => 'a.name',
            'staff_offdate' => new Zend_Db_Expr("(CASE WHEN s.off_date IS NULL THEN 'Active' ELSE s.off_date END)"),
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g' => 'group')            , 's.group_id = g.id'           , array())
            ->join(array('rm' => 'regional_market') , 's.regional_market = rm.id'   , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->order(array('a.name ASC', 's.code ASC'));


        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('s.code LIKE ?', '%'.$params['staff_code'].'%');
        } 

        if ( isset($params['staff_name']) && $params['staff_name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
        } 

        // Add Filter Staff Off Date
        if (isset($params['work_status']) && $params['work_status']) {

            if ( in_array(0, $params['work_status']) && in_array(1, $params['work_status']) ) {  }
            elseif ( in_array(0, $params['work_status']) )
                $select->where('s.off_date IS NULL');
            elseif ( in_array(1, $params['work_status']) ) 
                $select->where('s.off_date IS NOT NULL');
        }

        // Add Filter Staff Group
        if (isset($params['staff_group']) && $params['staff_group']) {
            if (is_array($params['staff_group']) && count($params['staff_group']))
                $select->where('s.group_id IN (?)', $params['staff_group']);
            elseif (is_numeric($params['staff_group']))
                $select->where('s.group_id = ?', intval($params['staff_group']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 's.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Permission for TMS Admin
        if ( isset($params['tms_id']) && $params['tms_id'] ) {
            $select->where( 's.group_id IN (?)', array(37,38) );
        }

        // Permission for AM + AM Admin
        if ( isset($params['am_id']) && $params['am_id'] ) {
            $select->where( 's.group_id IN (?)', array(27) );
        }

        // Permission for Admin Brandshop
        if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
            $select->where( 's.group_id IN (?)', array(30,32) );
        }

        // if (isset($params['get_total_count']) && $params['get_total_count'] == 0) { 
        //     $select_p = $db->select()
        //         ->from(array('pa' => $select), array(
        //             'cnt_finish' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 1 THEN pa.ti_id END )"),
        //             'cnt_reject' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 2 THEN pa.ti_id END )"),
        //             'cnt_wait' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 0 THEN pa.ti_id END )"),
        //             'cnt_ip' => new Zend_Db_Expr("COUNT( CASE WHEN pa.status_id = 3 THEN pa.ti_id END )"),
        //         ));
        //     //$select_p->group('pa.status_id');
        //     //echo $select_p; die;
        //     return $db->fetchRow($select_p);
        // }

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    public function getTableNameByGroup($group_id) { 

        $table = array();

        switch ($group_id) {

            case PGPB_ID : 
                $table = array(
                    'check_in'      =>  'pc_check_in_log', 
                    'leave'         =>  'pc_leave_log', 
                    'table_group'   =>  'PC', 
                    'group_id'      =>  4,
                    'pic_path'      =>  'oppo_pc_new',
                ); 
                break;

            case 31 : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'Sale Event',
                    'group_id'      =>  31,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case 33 : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'RM Assistant',
                    'group_id'      =>  33,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case 34 : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'ASM Assistant',
                    'group_id'      =>  34,
                    'pic_path'      =>  'oppo_sales_new',
                ); 
                break;

            case RM_ID : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'RM',
                    'group_id'      =>  28,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case ASM_ID : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'ASM',
                    'group_id'      =>  5,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case RMSTANDBY_ID : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'RM Stand By',
                    'group_id'      =>  35,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case ASMSTANDBY_ID : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'ASM Stand By',
                    'group_id'      =>  16,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case SALES_ID : 
                $table = array(
                    'check_in'      =>  'sales_check_in_log', 
                    'leave'         =>  'sales_leave_log', 
                    'table_group'   =>  'SALES',
                    'group_id'      =>  9,
                    'pic_path'      =>  'oppo_sales_new',
                );
                break;

            case ABM_ID : 
                $table = array(
                    'check_in'      =>  'bm_check_in_log', 
                    'leave'         =>  'bm_leave_log', 
                    'table_group'   =>  'ABM',
                    'group_id'      =>  32,
                    'pic_path'      =>  'oppo_bm',
                ); 
                break;

            case BM_ID : 
                $table = array(
                    'check_in'      =>  'bm_check_in_log', 
                    'leave'         =>  'bm_leave_log', 
                    'table_group'   =>  'BM',
                    'group_id'      =>  30,
                    'pic_path'      =>  'oppo_bm',
                );
                break;

            // PCM Leader 
            case 36 : 
                $table = array(
                    'check_in'      =>  'pcm_check_in_log', 
                    'leave'         =>  'pcm_leave_log', 
                    'table_group'   =>  'PCM Leader',
                    'group_id'      =>  36,
                    'pic_path'      =>  'oppo_pcm',
                );
                break;

            case TRAINING_TEAM_ID : 
                $table = array(
                    'check_in'      =>  'pcm_check_in_log', 
                    'leave'         =>  'pcm_leave_log', 
                    'table_group'   =>  'PCM',
                    'group_id'      =>  17,
                    'pic_path'      =>  'oppo_pcm',
                );
                break;

            // TMS, TMS Leader
            case 37 : 
                $table = array(
                    'check_in'      =>  'tms_check_in_log', 
                    'leave'         =>  'tms_leave_log', 
                    'table_group'   =>  'TMS',
                    'group_id'      =>  37,
                    'pic_path'      =>  'oppo_tms',
                );
                break;

            case 38 : 
                $table = array(
                    'check_in'      =>  'tms_check_in_log', 
                    'leave'         =>  'tms_leave_log', 
                    'table_group'   =>  'TMS Leader',
                    'group_id'      =>  38,
                    'pic_path'      =>  'oppo_tms',
                );
                break;

            // Sale Admin
            case SALES_ADMIN_ID : 
                $table = array(
                    'check_in'      =>  'admin_check_in_log', 
                    'leave'         =>  'admin_leave_log', 
                    'table_group'   =>  'Sale Admin',
                    'group_id'      =>  12,
                    'pic_path'      =>  'oppo_admin',
                );
                break;

            // AM
            case AM_ID : 
                $table = array(
                    'check_in'      =>  'am_check_in_log', 
                    'leave'         =>  'am_leave_log', 
                    'table_group'   =>  'AM',
                    'group_id'      =>  27,
                    'pic_path'      =>  'oppo_am',
                );
                break;

        }

        return $table;
    }

    function getStaffInfo($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_area' => 'a.name',
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'joined_at'  => 's.joined_at',
            'created_at' => 's.created_at',
            'off_date'   => 's.off_date',
            'staff_group'=> 'g.name',
            'staff_area' => 'a.name',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g' => 'group'), 's.group_id = g.id', array())
            ->join(array('rm' => 'regional_market') , 's.regional_market = rm.id'   , array())
            ->join(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
            ->where('s.code = ?', $params['staff_code']);

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 's.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Permission for TMS Admin
        if ( isset($params['tms_id']) && $params['tms_id'] ) {
            $select->where( 's.group_id IN (?)', array(37,38) );
        }

        // Permission for AM + AM Admin
        if ( isset($params['am_id']) && $params['am_id'] ) {
            $select->where( 's.group_id IN (?)', array(27) );
        }

        //echo $select;
        $result = $db->fetchRow($select);

        if ( isset($result['staff_id']) ) {
            // Get First Check In
            $table = $this->getTableNameByGroup($params['group_id']);

            $get_chk = array(
                'first_checkin' => 'chk.check_in',
                'table_group'   => new Zend_Db_Expr("'".$table['table_group']."'"),
            );

            $select_chk = $db->select()
                ->from(array('chk' => $table['check_in']), $get_chk)
                ->where('chk.staff_id = ?', $result['staff_id'])
                ->order('chk.check_in ASC')
                ->limit(1);

            if ($params['group_id'] == PGPB_ID) {
                $select_chk->where('store_id <> 0');
            }

            $result_chk = $db->fetchRow($select_chk);

            $result['first_checkin'] = $result_chk['first_checkin'];
            $result['table_group'] = $result_chk['table_group'];

        } 

        return $result;
    }

    function getCheckInList($params){

        $db = Zend_Registry::get('db');

        $table = $this->getTableNameByGroup($params['group_id']);

        $get_01 = array(
            'check_in'          => 'chk.check_in',
            'check_out'         => 'chk.check_out',
            'chk_status'        => 'chk.status',
            'store_id'          => 'st.id',
            'store_name'        => 'st.name',
            'store_area'        => 'a.name',
            'approve_by'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'approve_group'     => 'g.name',
            'approve_date'      => 'chk.updated_at',
            'remark'            => 'chk.remark',
            'work_plan'         => 'chk.work_plan',
            'work_perform'      => 'chk.work_performance',
            'created_at'        => 'chk.created_at',
            'filepic'           => 'chk.filepic',
            'chkout_filepic'    => 'chk.filepic_chkout',
            'pic_path'          => new Zend_Db_Expr("'".$table['pic_path']."'"),
            'late_time'         => 'chk.late_time',

        );

        $get_02 = array();
        if ($table['check_in'] != 'pc_check_in_log') {
            $get_02 = array(
                'location_chkin'    => 'chk.location',
                'location_chkout'   => 'chk.location_chkout',
            );
        } 

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('chk' => $table['check_in']), $get)
            ->joinLeft(array('st' => 'store')   , 'chk.store_id = st.id'        , array())
            ->joinLeft(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->joinLeft(array('a'  => 'area')    , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('s'  => 'staff')   , 'chk.approve_by = s.id'       , array())
            ->joinLeft(array('g'  => 'group')   , 's.group_id = g.id'           , array())
            ->where('action_id = 1')
            ->where('new_check_in = ?', 'N')
            ->where('switch_to_leave = ?', 'N')
            ->where('chk.check_in >= ?', $params['period_from']." 00:00:00")
            ->where('chk.check_in <= ?', $params['period_to']. " 23:59:59")
            ->where('chk.staff_id = ?', $params['staff_id']);

        $result = $db->fetchAll($select);

        //echo "<pre>"; print_r($result); echo "<br/>";

        $data = array();
        for ($i=0;$i<count($result);$i++) {

            $day =  date('Y-m-d', strtotime($result[$i]['check_in']));
            $data[ $day ] = $result[$i];

        }

        // echo "<pre>"; print_r($data); echo "<br/>";
        return $data;
    }


    function getLeaveList($params){

        $db = Zend_Registry::get('db');

        $table = $this->getTableNameByGroup($params['group_id']);

        $get = array(
            'from_date'         => 'chk.from_date',
            'to_date'           => 'chk.to_date',
            'leave_remark'      => 'chk.leave_remark',
            'date_switch'       => 'chk.date_switch',
            'certificate_file'  => 'chk.certificate_file',
            'certificate_at'    => 'chk.certificate_at',
            'leave_status'      => 'chk.status',
            'approve_by'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'approve_group'     => 'g.name',
            'approve_date'      => 'chk.updated_at',
            'leave_type'        => new Zend_Db_Expr(
                                    "   CASE 
                                            WHEN chk.action_id = 1 THEN 'Sick Leave' 
                                            WHEN chk.action_id = 2 THEN 'Bussiness Leave' 
                                            WHEN chk.action_id = 3 THEN 'Annual Leave' 
                                            WHEN chk.action_id = 4 THEN 'Day Off' 
                                            WHEN chk.action_id = 5 THEN 'Switch Day Off' 
                                            WHEN chk.action_id = 6 THEN 'Ordination Leave' 
                                            WHEN chk.action_id = 7 THEN 'Maternity Leave' 
                                            WHEN chk.action_id = 8 THEN 'Other Leave' 
                                            WHEN chk.action_id = 9 THEN 'Sterile Leave' 
                                            WHEN chk.action_id = 10 THEN 'Marrirage Leave' 
                                            WHEN chk.action_id = 11 THEN 'Burial Leave' 
                                            WHEN chk.action_id = 12 THEN 'Military Training Leave' 
                                            WHEN chk.action_id = 13 THEN 'Foreigner go home' 
                                            WHEN chk.action_id = 14 THEN 'Wait Assign Shop' 
                                        END
                                    "),
            'remark'            => 'chk.remark',
            'pic_path'          => new Zend_Db_Expr("'".$table['pic_path']."'"),
            'date_switch'       => 'chk.date_switch',
        );

        $select = $db->select()
            ->from(array('chk' => $table['leave']), $get)
            ->joinLeft(array('s'  => 'staff'), 'chk.approve_by = s.id'  , array())
            ->joinLeft(array('g'  => 'group'), 's.group_id = g.id'      , array())
            ->where('new_check_in = ?', 'N')
            ->where('switch_to_check_in = ?', 'N')
            ->where('((chk.from_date >= ?', $params['period_from']." 00:00:00")
            ->where('chk.from_date <= ?)', $params['period_to']. " 23:59:59")
            ->orWhere('chk.to_date >= ?)', $params['period_from']." 00:00:00")
            //->where('chk.to_date <= ?))', $params['period_to']. " 23:59:59")
            ->where('chk.staff_id = ?', $params['staff_id']);

        //echo $select; die;
        $result = $db->fetchAll($select);

        //echo "<pre>"; print_r($result); echo "<br/>";

        $data = array();
        for ($i=0;$i<count($result);$i++) {

            $tmp_from = date('Y-m-d', strtotime($result[$i]['from_date']));
            $tmp_to = date('Y-m-d', strtotime($result[$i]['to_date']));

            $chk = ( (strtotime($tmp_to) - strtotime($tmp_from) ) / (24*60*60) ) + 1;

            for ($j=0;$j<$chk;$j++) {

                $falg = 0;
                $cnt = $chk - 1;

                $day =  date('Y-m-d', strtotime("+".$j." Day", strtotime($result[$i]['from_date'])));
                
                $data[ $day ] = $result[$i];

                //$data[ $day ]['from_date'] = $day." 09:00:00";
                //$data[ $day ]['to_date'] = $day." 18:00:00";

                $data[ $day ]['from_date'] = str_replace(" 00:00:00", " 09:00:00", $result[$i]['from_date']);
                $data[ $day ]['to_date'] = str_replace(" 00:00:00", " 18:00:00", $result[$i]['to_date']);

                if ($chk>1) {

                    if ($j==0) {
                        $time =  date('H:i:s', strtotime("+".$j." Day", strtotime($data[ $day ]['from_date'])));
                        // echo $time."<br/>";
                        $data[ $day ]['from_date'] = $day." ".$time;
                        $data[ $day ]['to_date'] = $day." 18:00:00";
                    }

                    else if ($j==$cnt) {
                        $time =  date('H:i:s', strtotime("+".$j." Day", strtotime($data[ $day ]['to_date'])));

                        $data[ $day ]['from_date'] = $day." 09:00:00";
                        $data[ $day ]['to_date'] = $day." ".$time;
                    }

                    else {

                        $data[ $day ]['from_date'] = $day." 09:00:00";
                        $data[ $day ]['to_date'] = $day." 18:00:00";

                    }

                } 

            }
            
        }

        // echo "<pre>"; print_r($data); echo "<br/>";
        return $data;
    }


    function checkTable($staff_id, $group_id) {

        $db = Zend_Registry::get('db');

        $table = $this->getTableNameByGroup($group_id);

        $get = array(
            'staff_id'      => 's.id',
            'table_group_id'=> new Zend_Db_Expr("'".$table['group_id']."'"),
            'table_group'   => new Zend_Db_Expr("'".$table['table_group']."'"),
            'last_action'   => new Zend_Db_Expr("(CASE WHEN COALESCE(chk.check_in,lea.to_date) >= COALESCE(lea.to_date,chk.check_in) THEN chk.check_in ELSE lea.to_date END)"),
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->joinLeft(array('chk' => $table['check_in']),"chk.staff_id = s.id AND chk.status = 'Y'", array())
            ->joinLeft(array('lea' => $table['leave']), "lea.staff_id = s.id AND lea.status = 'Y'", array())
            ->where('s.id = ?', $staff_id)
            ->order(array('chk.check_in DESC', 'lea.to_date DESC'))
            ->limit(1);

        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

    // Export Excel : Check In
    function exportCheckIn($params) {

        $db = Zend_Registry::get('db');

        $result_all = array();

        $period_from_reduce = date('Y-m-d', strtotime("-7 Day", strtotime($params['period_from']))); 
        $period_to_reduce = date('Y-m-d', strtotime("-7 Day", strtotime($params['period_to']))); 

        for ($i=0;$i<count($params['staff_group']);$i++) {

            $table = $this->getTableNameByGroup($params['staff_group'][$i]);

            if ($table['group_id'] == PGPB_ID) {

                $sub_select_training = $db->select()
                    ->from(array('tnd' => $table['check_in']), array('cnt'=> new Zend_Db_Expr("COUNT(tnd.id)") ))
                    ->where('tnd.created_at >= ?', $period_from_reduce." 00:00:00")
                    ->where('tnd.created_at <= ?', $period_to_reduce." 23:59:59")
                    ->where('tnd.store_id = ?', 0)
                    ->where('tnd.staff_id = s.id', 1);

            } else { $sub_select_training = '0'; }
            
            $get = array(
                'staff_area'    => 'a.name',     
                'staff_id'      => 's.id',
                'staff_code'    => 's.code',
                'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
                'staff_group_id'=> 'g.id',
                'staff_group'   => 'g.name',
                'table_group'   => new Zend_Db_Expr("'".$table['table_group']."'"),
                'joined_at'     => 's.joined_at',
                'off_date'      => 's.off_date',

                'work_day'          => new Zend_Db_Expr(
                    "COUNT(DISTINCT 
                            CASE 
                                WHEN chk.store_id <> 0 AND 4 = ".$table['group_id']." AND chk.check_out IS NOT NULL THEN DATE(chk.created_at) 
                                WHEN 4 <> ".$table['group_id']." AND chk.check_out IS NOT NULL THEN DATE(chk.created_at) 
                            END)
                    "),

                'work_day_no_out'   => new Zend_Db_Expr(
                    "COUNT(DISTINCT 
                            CASE 
                                WHEN chk.store_id <> 0 AND 4 = ".$table['group_id']." AND chk.check_out IS NULL THEN DATE(chk.created_at) 
                                WHEN 4 <> ".$table['group_id']." AND chk.check_out IS NULL THEN DATE(chk.created_at)
                            END)
                    "),

                'sum_late_time'     => new Zend_Db_Expr("COALESCE(SUM(chk.late_time),0)"),

                //'training_day'      => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN chk.store_id = 0 AND 4 = ".$table['group_id']." THEN DATE(chk.created_at) END)"),
                'training_day'      => new Zend_Db_Expr("(".$sub_select_training.")"),

                'sick_leave_1'      => new Zend_Db_Expr("COALESCE(BBB.sick_leave_1, 0)"),
                'sick_leave_2'      => new Zend_Db_Expr("COALESCE(BBB.sick_leave_2, 0)"),
                'personal_leave'    => new Zend_Db_Expr("COALESCE(BBB.personal_leave, 0)"),
                'vacation_leave'    => new Zend_Db_Expr("COALESCE(BBB.vacation_leave, 0)"),
                'off_date_leave'    => new Zend_Db_Expr("COALESCE(BBB.off_date_leave, 0)"),
                'ordination_leave'  => new Zend_Db_Expr("COALESCE(BBB.ordination_leave, 0)"),
                'maternity_leave'   => new Zend_Db_Expr("COALESCE(BBB.maternity_leave, 0)"),
                'other_leave'       => new Zend_Db_Expr("COALESCE(BBB.other_leave, 0)"),

				'sterile_leave'		=> new Zend_Db_Expr("COALESCE(BBB.sterile_leave, 0)"),
				'marrirage_leave'	=> new Zend_Db_Expr("COALESCE(BBB.marrirage_leave, 0)"),
				'burial_leave'		=> new Zend_Db_Expr("COALESCE(BBB.burial_leave, 0)"),
				'military_leave'	=> new Zend_Db_Expr("COALESCE(BBB.military_leave, 0)"),
				'foreigner_leave'	=> new Zend_Db_Expr("COALESCE(BBB.foreigner_leave, 0)"),
                'wait_assign_shop'  => new Zend_Db_Expr("COALESCE(BBB.wait_assign_shop, 0)"),
            );


            // Generate Leave Condition 
            $leave_array = array(
                'sick_leave_1'      => "lea.status <> 'N' AND lea.action_id = 1 AND lea.certificate_file IS NULL",      // Dont Have
                'sick_leave_2'      => "lea.status <> 'N' AND lea.action_id = 1 AND lea.certificate_file IS NOT NULL",  // Have
                'personal_leave'    => "lea.status <> 'N' AND lea.action_id = 2",
                'vacation_leave'    => "lea.status <> 'N' AND lea.action_id = 3",
                'off_date_leave'    => "lea.status <> 'N' AND lea.action_id IN (4,5)",
                'ordination_leave'  => "lea.status <> 'N' AND lea.action_id = 6",
                'maternity_leave'   => "lea.status <> 'N' AND lea.action_id = 7",
                'other_leave'       => "lea.status <> 'N' AND lea.action_id = 8",

				'sterile_leave'		=> "lea.status <> 'N' AND lea.action_id = 9",
				'marrirage_leave'	=> "lea.status <> 'N' AND lea.action_id = 10",
				'burial_leave'		=> "lea.status <> 'N' AND lea.action_id = 11",
				'military_leave'	=> "lea.status <> 'N' AND lea.action_id = 12",
				'foreigner_leave'	=> "lea.status <> 'N' AND lea.action_id = 13",
                'wait_assign_shop'  => "lea.status <> 'N' AND lea.action_id = 14",
            );

            $get_01 = array('staff_id' => 'lea.staff_id');

            foreach ($leave_array as $key => $value) {

                $get_02[$key] = new Zend_Db_Expr(
                    "
                        SUM( 
                            CASE WHEN ".$value." THEN 
                                CASE WHEN ((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60*24)) > 1 THEN 
                                    CASE 
                                        WHEN UNIX_TIMESTAMP(DATE(from_date)) <= UNIX_TIMESTAMP(DATE('".$params['period_from']."')) AND UNIX_TIMESTAMP(DATE(to_date)) >= UNIX_TIMESTAMP(DATE('".$params['period_to']."')) THEN
                                            (
                                                ((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60)) - 
                                                (15 * FLOOR((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24))) - 
                                                (CASE WHEN ((UNIX_TIMESTAMP(CONCAT(DATE('".$params['period_from']."'), ' 18:00:00')) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60)) > 5 THEN 
                                                    CEIL((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24))
                                                ELSE 
                                                    FLOOR((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24)) END)
                                            ) / 8
                                        WHEN UNIX_TIMESTAMP(DATE(from_date)) <= UNIX_TIMESTAMP(DATE('".$params['period_from']." 09:00:00')) THEN
                                            (
                                                ((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60)) - 
                                                (15 * FLOOR((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24))) - 
                                                (CASE WHEN ((UNIX_TIMESTAMP(CONCAT(DATE('".$params['period_from']." 09:00:00'), ' 18:00:00')) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60)) > 5 THEN 
                                                    CEIL((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24))
                                                ELSE 
                                                    FLOOR((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP('".$params['period_from']." 09:00:00')) / (60*60*24)) END)
                                            ) / 8
                                        WHEN UNIX_TIMESTAMP(DATE(to_date)) >= UNIX_TIMESTAMP(DATE('".$params['period_to']." 18:00:00')) THEN
                                            (
                                                ((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP(from_date)) / (60*60)) - 
                                                (15 * FLOOR((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP(from_date)) / (60*60*24))) - 
                                                (CASE WHEN ((UNIX_TIMESTAMP(CONCAT(DATE(from_date), ' 18:00:00')) - UNIX_TIMESTAMP(from_date)) / (60*60)) > 5 THEN 
                                                    CEIL((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP(from_date)) / (60*60*24))
                                                ELSE 
                                                    FLOOR((UNIX_TIMESTAMP('".$params['period_to']." 18:00:00') - UNIX_TIMESTAMP(from_date)) / (60*60*24)) END)
                                            ) / 8
                                        ELSE 
                                            (
                                                ((UNIX_TIMESTAMP(CONCAT(DATE(to_date),' 18:00:00')) - UNIX_TIMESTAMP(CONCAT(DATE(from_date),' 09:00:00'))) / (60*60)) - 
                                                (15 * FLOOR((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60*24))) - 
                                                (CASE WHEN ((UNIX_TIMESTAMP(CONCAT(DATE(from_date), ' 18:00:00')) - UNIX_TIMESTAMP(from_date)) / (60*60)) > 5 THEN 
                                                    CEIL((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60*24))
                                                ELSE 
                                                    FLOOR((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60*24)) END)
                                            ) / 8
                                    END 
                                ELSE 
                                    CASE WHEN HOUR(from_date) <= 12 AND HOUR(to_date) > 12 THEN
                                        CASE WHEN (((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60)) - 1 ) >= 8 THEN 
                                            1
                                        ELSE 
                                            (((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60)) - 1 ) / 8
                                        END 
                                    ELSE 
                                        CASE WHEN ((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60)) >= 8 THEN 
                                            1
                                        ELSE 
                                            ((UNIX_TIMESTAMP(to_date) - UNIX_TIMESTAMP(from_date)) / (60*60)) / 8
                                        END 
                                    END 
                                END
                            ELSE 
                                0 
                            END
                        )
                    ");

            }

            $get_sub_02 = $get_01 + $get_02;

            $sub_select_02 = $db->select()
                ->from(array('lea' => $table['leave']), $get_sub_02)
                ->where('lea.to_date >= ?', $params['period_from']." 00:00:00")
                ->where('lea.from_date <= ?', $params['period_to']." 23:59:59")
				->where('lea.status <> ?', 'N')
                ->where('lea.new_check_in <> ?', 'Y')
                ->group('lea.staff_id');

            $select = $db->select()
                ->from(array('s' => 'staff'), $get)
                ->join(array('g' => 'group'), 's.group_id = g.id', array())
                ->join(array('rm'=> 'regional_market'), 's.regional_market = rm.id', array())
                ->join(array('a' => 'area'), 'rm.area_id = a.id', array())

                ->joinLeft(array('chk' => $table['check_in']), 
                    "   chk.staff_id = s.id 
                        AND chk.created_at >= '".$params['period_from']." 00:00:00' 
                        AND chk.created_at <= '".$params['period_to']." 23:59:59' 
						AND chk.action_id = 1 
						AND chk.status <> 'N' 
                        AND chk.new_check_in <> 'Y' 
                    ", array())

                ->joinLeft(array('BBB' => $sub_select_02), 'BBB.staff_id = s.id', array())

                ->where('(s.off_date >= ?', $params['period_from'])
                ->orWhere('s.off_date IS NULL)')
                // ->where('s.status = ?', 1)
                // ->where('s.group_id = ?', $params['staff_group'][$i])
                ->where('s.joined_at <= ?', $params['period_to'])
                ->group('s.id')
                ->order(array('s.code ASC'));

            if ( !isset($params['change_position']) ) {
                $select->where('s.group_id = ?', $params['staff_group'][$i]);
            }

            if ( isset($params['staff_code']) && $params['staff_code'] ) {
                $select->where('s.code LIKE ?', '%'.$params['staff_code'].'%');
            } 

            if ( isset($params['staff_name']) && $params['staff_name'] ) {
                $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
            } 

            // Add Filter Staff Off Date
            if (isset($params['work_status']) && $params['work_status']) {

                if ( in_array(0, $params['work_status']) && in_array(1, $params['work_status']) ) {  }
                elseif ( in_array(0, $params['work_status']) )
                    $select->where('s.off_date IS NULL');
                elseif ( in_array(1, $params['work_status']) ) 
                    $select->where('s.off_date IS NOT NULL');
            }

            // Add Filter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('rm.area_id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('rm.area_id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }

            // Filter Leave Type
            if (isset($params['leave_type']) && $params['leave_type']) {
                if (is_array($params['leave_type']) && count($params['leave_type'])) {

                    for ($k=0;$k<count($params['leave_type']);$k++) {

                        switch ($params['leave_type'][$k]) {
                            case 1 : $select->orHaving('sick_leave_1 > 0');      break;
                            case 5 : $select->orHaving('sick_leave_2 > 0');      break;
                            
                            case 2 : $select->orHaving('personal_leave > 0');    break;
                            case 3 : $select->orHaving('vacation_leave > 0');    break;
                            case 4 : $select->orHaving('off_date_leave > 0');    break;
                            
                            case 6 : $select->orHaving('ordination_leave > 0');  break;
                            case 7 : $select->orHaving('maternity_leave > 0');   break;
                            case 8 : $select->orHaving('other_leave > 0');       break;

							case 9 : $select->orHaving('sterile_leave > 0');     break;
							case 10: $select->orHaving('marrirage_leave > 0');   break;
							case 11: $select->orHaving('burial_leave > 0');      break;
							case 12: $select->orHaving('military_leave > 0');    break;
							case 13: $select->orHaving('foreigner_leave > 0');   break;
                            case 14: $select->orHaving('wait_assign_shop > 0');  break;
                        }

                    }

                } else 
                    $select->where('1=0', 1);
            }

            // Filter Late Type
            if (isset($params['late_type']) && $params['late_type']) {
                if (is_array($params['late_type']) && count($params['late_type'])) {

                    for ($k=0;$k<count($params['late_type']);$k++) {

                        switch ($params['late_type'][$k]) {
                            case 1 : $select->having('sum_late_time > 0');  break;
                            case 2 : $select->having('sum_late_time = 0');  break;
                        }

                    }

                } else 
                    $select->where('1=0', 1);
            }

            // Permission for ASM / Sale Admin / Trainer
            if ( isset($params['asm']) && $params['asm'] ) {
                $QAsm = new Application_Model_Asm();
                $list_regions = $QAsm->get_cache($params['asm']);
                $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

                if (count($list_regions) > 0)
                    $select->where( 's.regional_market IN (?)', $list_regions);
                else
                    $select->where('1=0', 1);
            }

            // Permission for TMS Admin
            if ( isset($params['tms_id']) && $params['tms_id'] ) {
                $select->where( 's.group_id IN (?)', array(37,38) );
            }

            // Permission for AM + AM Admin
            if ( isset($params['am_id']) && $params['am_id'] ) {
                $select->where( 's.group_id IN (?)', array(27) );
            }

            // Permission for Admin Brandshop
            if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
                $select->where( 's.group_id IN (?)', array(30,32) );
            }

            //echo "<br/>"; echo $select; echo "<br/>"; 

            $result = $db->fetchAll($select);
            //echo "<br/>"; print_r($result); echo "<br/>"; 

            $result_all[$i] = $result;
        }

        //echo "<pre>"; print_r($result_all); die;
        return $result_all;
    }

    function checkDupRequest($params){

        $db = Zend_Registry::get('db');

        $table = $this->getTableNameByGroup($params['staff_group']);

        $get = array(
            'chk_dup' => new Zend_Db_Expr("COUNT(chk.id)"),
            'leave_time' => new Zend_Db_Expr("
                SUM(CASE WHEN DATE(lea.from_date) <> DATE(lea.to_date) THEN 

                    CASE WHEN DATE(chk.check_in) = DATE(lea.from_date) THEN

                        CASE WHEN HOUR(lea.from_date) <= 12 THEN

                            CASE WHEN (((UNIX_TIMESTAMP( CONCAT(DATE(lea.from_date), ' 18:00:00') ) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) - 1 ) >= 8 THEN 
                                1
                            ELSE 
                                (((UNIX_TIMESTAMP( CONCAT(DATE(lea.from_date), ' 18:00:00') ) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) - 1 ) / 8
                            END 

                        ELSE
                            CASE WHEN ((UNIX_TIMESTAMP( CONCAT(DATE(lea.from_date), ' 18:00:00') ) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) >= 8 THEN 
                                1
                            ELSE 
                                ((UNIX_TIMESTAMP( CONCAT(DATE(lea.from_date), ' 18:00:00') ) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) / 8
                            END 
                        END

                    WHEN DATE(chk.check_in) = DATE(lea.to_date) THEN 

                        CASE WHEN HOUR(lea.to_date) > 12 THEN

                            CASE WHEN (((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP( CONCAT(DATE(lea.to_date), ' 09:00:00') )) / (60*60)) - 1 ) >= 8 THEN 
                                1
                            ELSE 
                                (((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP( CONCAT(DATE(lea.to_date), ' 09:00:00') )) / (60*60)) - 1 ) / 8
                            END 

                        ELSE

                            CASE WHEN ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP( CONCAT(DATE(lea.to_date), ' 09:00:00') )) / (60*60)) >= 8 THEN 
                                1
                            ELSE 
                                ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP( CONCAT(DATE(lea.to_date), ' 09:00:00') )) / (60*60)) / 8
                            END 

                        END

                    ELSE 
                        CASE WHEN ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) >= 8 THEN 
                            1
                        ELSE 
                            ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) / 8
                        END 
                    END 

                ELSE 

                    CASE WHEN HOUR(lea.from_date) <= 12 AND HOUR(lea.to_date) > 12 THEN
                        CASE WHEN (((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) - 1 ) >= 8 THEN 
                            1
                        ELSE 
                            (((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) - 1 ) / 8
                        END 
                    ELSE 
                        CASE WHEN ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) >= 8 THEN 
                            1
                        ELSE 
                            ((UNIX_TIMESTAMP(lea.to_date) - UNIX_TIMESTAMP(lea.from_date)) / (60*60)) / 8
                        END 
                    END 

                END)

            "), 
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('chk' => $table['check_in']), 
                "   s.id = chk.staff_id 
                    AND chk.action_id = 1 
                    AND chk.status = 'Y' 
                    AND chk.check_in >= '".$params['period_from']."' 
                    AND chk.check_in <= '".$params['period_to']."' 
                ", array())
            ->join(array('lea' => $table['leave']), 
                "   s.id = lea.staff_id 
                    AND lea.status = 'Y' 
                    AND (DATE(lea.from_date) = DATE(chk.check_in) OR DATE(lea.to_date) = DATE(chk.check_in)) 
                ", array())
            ->where('s.code = ?', $params['staff_code'])
            ->group('s.id');

        // echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

    // Report / Channel PC Status 
    function CPS_fetchPagination($page, $limit, &$total, $params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'st_id'     => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS st.id'),
            'st_name'   => 'st.name',
            'st_type'   => 'o.org_name',
            'st_status' => new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Disabled' END)"),
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN pcl.check_in >= '".$day." 00:00:00' AND pcl.check_in <= '".$day." 23:59:59' THEN pcl.id END)");
        }

        $get = $get_01 + $get_02;

        $additional_list = array(19,41,20,43,26,42,27,32,38);

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('pcl' => 'pc_check_in_log'), 
                "   st.id = pcl.store_id 
                    AND pcl.action_id = 1 
                    AND pcl.check_in >= '".$from." 00:00:00' 
                    AND pcl.check_in <= '".$to." 23:59:59' 
                ", array())
            ->where('(o.store_type_id = ?', 1)
            ->orWhere('o.org_id IN (?))', $additional_list)
            ->group('st.id')
            ->order(array('a.name ASC', 'st.id ASC'));

        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id = ?', $params['store_id']);
        } 

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
        } 

        // Add Filter Store Type
        if (isset($params['org']) && $params['org']) {
            if (is_array($params['org']) && count($params['org']))
                $select->where('o.org_id IN (?)', $params['org']);
            elseif (is_numeric($params['org']))
                $select->where('o.org_id = ?', intval($params['org']));
            else
                $select->where('1=0', 1);
        }

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
                $select->where('rm.id IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('rm.id = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Market Type, Market Name
        if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {

            $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
            $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));

            // filter Market Type
            if (isset($params['market_type']) and $params['market_type']) {
                if (is_array($params['market_type']) && count($params['market_type'])) { 

                    $select->where('mn.market_type_id IN (?)', $params['market_type']);

                } elseif (is_numeric($params['market_type'])) {
                    
                    $select->where('mn.market_type_id = ?', intval($params['market_type']));

                } else { 
                    $select->where('1=0', 1);
                }
            }

            // Filter Market Name
            if (isset($params['market_name']) and $params['market_name']) {
                if (is_array($params['market_name']) && count($params['market_name'])) { 

                    $select->where('mn.id IN (?)', $params['market_name']);

                } elseif (is_numeric($params['market_name'])) {
                    
                    $select->where('mn.id = ?', intval($params['market_name']));

                } else {
                    $select->where('1=0', 1);
                }
            }
        }

        // check Permission AM
        if ( isset($params['am']) && $params['am'] ) {
            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            if (count($list_org) > 0)
                $select->where( 'st.org_dealer IN (?)', $list_org);
            else
                $select->where('1=0', 1);
        }

/*
        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 's.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }
*/

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        // echo $select; die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}
