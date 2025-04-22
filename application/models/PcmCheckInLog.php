<?php

class Application_Model_PcmCheckInLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'pcm_check_in_log';

    function fetchPagination($page, $limit, &$totalpcm, $params)
    {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sf.id'),
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'area_name' => 'ar.name',
            'group_name' => 'g.name',
            'type_report' => new Zend_Db_Expr("'PCM'"),
        );
       //  echo "<pre>";
       // print_r($get);


        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->join(array('sa'=>$this->_name),'sa.staff_id=sf.id')
            ->join(array('g'=>'group'),'g.id = sf.group_id')
            ->join(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where(new Zend_Db_Expr("MID(sf.code,1,2) >= 50"))
            ->where(new Zend_Db_Expr("( sf.off_date >= '".$params['start_date']."' OR sf.off_date IS NULL )"))
            ->where('sf.group_id IN (17,36)')
            ->group('staff_code')
            ->order('staff_code ASC');
     
        if (isset($params['store_id']) && $params['store_id']) {
            $select->where('st.id LIKE ?', '%' . $params['store_id'] . '%');
        }

        if (isset($params['market_name']) && $params['market_name']) {
            $select->where('mn.name LIKE ?', '%' . $params['market_name'] . '%');
        }

        if (isset($params['store_name']) && $params['store_name']) {
            $select->where('st.name LIKE ?', '%' . $params['store_name'] . '%');
        }

        if (isset($params['store_code']) && $params['store_code']) {
            $select->where('s.code LIKE ?', '%' . $params['store_code'] . '%');
        }

        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('sf.code = ?', $params['staff_code']);
        }

        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where('CONCAT(sf.firstname, " ",sf.lastname) LIKE ?', '%' . $params['staff_name'] . '%');
        }

        if ((isset($params['off']) and intval($params['off']) > 0 ))
            $select->where('sf.off_date is '
                . ( $params['off'] == 2 ? 'not' : '')
                .' null');
        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }


        // check ermission ASM, ASM Stand by, Sale Admin, Traning
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

       // echo '<pre>';
       // print_r($params);
       // echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $totalpcm = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function getMarketName($staff_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'market_name' => 'mn.name',
        );

        $select = $db->select()
            ->from(array('ss' => 'store_staff'), $get)
            ->join(array('sm' => 'store_market'), 'sm.store_id = ss.store_id', array())
            ->join(array('mn' => 'market_name'), 'mn.id = sm.market_name_id', array())
            ->where('ss.staff_id = ?', $staff_id)
            ->group('mn.id');

        $result = $db->fetchAll($select);

        return $result;
    }

    function getStoreName($staff_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'store_name' => 'st.name',
        );

        $select = $db->select()
            ->from(array('ss' => 'store_staff'), $get)
            ->join(array('st' => 'store'), 'st.id = ss.store_id', array())
            ->where('ss.staff_id = ?', $staff_id);

        $result = $db->fetchAll($select);

        return $result;
    }
    // function getGroupId($group_id){
    //         $db = Zend_Registry::get('db');
    //                 $select=$db=>select()
    //                        ->from(array())

    // }
    function getStaffId($staff_code)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => 'sf.id',
            );

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->where('sf.code = ?', $staff_code);
  // echo $select;
        $result = $db->fetchRow($select);

        return $result;

    }
        
    function getDetails($sf_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => 'sf.id',
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'joined_at' => 'sf.joined_at',
            'off_date' => 'sf.off_date',
            'created_at' => new Zend_Db_Expr("DATE(sf.created_at)"),
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(scl.created_at) FROM pcm_check_in_log AS scl WHERE scl.staff_id = sf.id ORDER BY scl.created_at ASC LIMIT 1)'),
            'first_leave' => new Zend_Db_Expr('(SELECT DATE(sl.created_at) FROM pcm_leave_log AS sl WHERE sl.staff_id = sf.id ORDER BY sl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
//            ->where('sf.group_id = ?', '4')
//            ->where('sf.off_date IS NULL')
            ->where('sf.id = ?', $sf_id)
            ->order('staff_name ASC');
        // echo $select;
        $result = $db->fetchRow($select);

        return $result;

    }

    function getSalesCheckIn($staff_id, $store_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'day_month' => new Zend_Db_Expr('DATE_FORMAT(ci.created_at, "%d")'),
            'day_week' => new Zend_Db_Expr('DATE_FORMAT(ci.created_at, "%Y-%m-%d")'),
            'id_check_in' => 'ci.id',
            'area_name' => 'ar.name',
            'store_id' => 'st.id',
            'store_name' => 'st.name',
            't_check_in' => 'ci.check_in',
            't_check_out' => 'ci.check_out',
            'regional_name'=>'rm.name',
            'status_ap' => 'ci.status',
            'approve_time' => 'ci.updated_at',
            'remark' => 'ci.remark',
            'leave_remark' => new Zend_Db_Expr("null"),
            'mode_code' => new Zend_Db_Expr("'CHECKIN'"),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(scl.created_at) FROM pcm_check_in_log AS scl WHERE scl.staff_id = ci.staff_id ORDER BY scl.created_at ASC LIMIT 1)'),
        );
        // echo $get;
        // die;

        $select = $db->select()
            ->from(array('ci' => $this->_name), $get)
            ->joinLeft(array('sf' => 'staff'), 'sf.id = ci.staff_id', array())
            ->joinLeft(array('st' => 'store'), 'st.id = ci.store_id', array())
            ->joinleft(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->joinleft(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where('ci.staff_id = ?', $staff_id)
            ->where('ci.action_id = ?', '1')
            ->where('ci.new_check_in <> ?', 'Y')
//            ->where('ci.store_id = ?', $store_id)
            ->where("DATE(ci.created_at) >= ?", $start_date)
            ->where("DATE(ci.created_at) <= ?", $end_date);

// echo $select;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getSalesLeave($staff_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'day_month' => new Zend_Db_Expr('DATE_FORMAT(sl.created_at, "%d")'),
            'day_week' => new Zend_Db_Expr('DATE_FORMAT(sl.created_at, "%Y-%m-%d")'),
            'id_check_in' => 'sl.id',
            't_check_in' => new Zend_Db_Expr("null"),
            't_check_out' => new Zend_Db_Expr("null"),
            'action_id' => 'sl.action_id',
            'status_ap' => 'sl.status',
            'approve_time' => 'sl.updated_at',
            'remark' => 'sl.remark',
            'leave_remark' => 'sl.leave_remark',
            'certificate_file' => 'sl.certificate_file',
            'certificate_at' => 'sl.certificate_at',
            'mode_code' => new Zend_Db_Expr("'LEAVE'"),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(sl.created_at) FROM pcm_leave_log AS sl WHERE sl.staff_id = sf.id ORDER BY sl.created_at ASC LIMIT 1)'),
        );


        $select = $db->select()
            ->from(array('sl' => 'pcm_leave_log'), $get)
            ->join(array('sf' => 'staff'), 'sf.id = sl.staff_id', array())
            ->where('sl.staff_id = ?', $staff_id)
            ->where('sl.new_check_in <> ?', 'Y')
            ->where("DATE(sl.created_at) >= ?", $start_date)
            ->where("DATE(sl.created_at) <= ?", $end_date);


        $result = $db->fetchAll($select);
        return $result;
    }


    function CheckLeave($staff_id, $date_current)
    {
        $current_date = "'" . $date_current . "'";
        $db = Zend_Registry::get('db');

        $get = array(
            'action_id' => 'scl.action_id',
            'status_ap' => 'scl.status',
            'mode_code' => new Zend_Db_Expr("CONCAT('LEAVE')"),
            'leave_remark' => 'scl.leave_remark',
            'approve_time' => 'scl.updated_at',
            'remark' => 'scl.remark',
            'certificate_file' => 'scl.certificate_file',
            'certificate_at' => 'scl.certificate_at',
        );

        $select = $db->select()
            ->from(array('scl' => 'pcm_leave_log'), $get)
            ->where("scl.staff_id = ?", $staff_id)
            ->where("scl.action_id IN (?)", [1, 6, 7])
            ->where('scl.new_check_in <> ?', 'Y')
            ->where("DATE($current_date) >= ?", new Zend_Db_Expr("DATE(scl.from_date)"))
            ->where("DATE($current_date) <= ?", new Zend_Db_Expr("DATE(scl.to_date)"));

            // echo $select;

        $result = $db->fetchRow($select);

        return $result;
    }



 function loadPcmReportExcelAllCheckIn($area_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'        => 's.id',
            'staff_code'      => 's.code',
            'staff_name'      => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'joined_at'       => 's.joined_at',
            'off_date'        => 's.off_date',
        );

        $leave_summary = array(
            'staff_id'               => 'll.staff_id',
            'sick_leave'             => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 1 AND ll.status <> 'N' AND ll.certificate_file IS NULL THEN 
                                        CASE
	                                      WHEN 
	                                        ll.from_date < '$start_date' AND ll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        ll.from_date < '$start_date' THEN DATEDIFF(ll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        ll.to_date > '$end_date' THEN DATEDIFF('$end_date', ll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(ll.to_date, ll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'sick_leave_cert'        => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 1 AND ll.status <> 'N' AND ll.certificate_file IS NOT NULL THEN 
                                        CASE
	                                      WHEN 
	                                        ll.from_date < '$start_date' AND ll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        ll.from_date < '$start_date' THEN DATEDIFF(ll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        ll.to_date > '$end_date' THEN DATEDIFF('$end_date', ll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(ll.to_date, ll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'personal_leave'         => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 2 AND ll.status <> 'N' THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'vacation_leave'         => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 3 AND ll.status <> 'N' THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'day_off_approve'        => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 4 AND ll.status <> 'N'  THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'day_off_reject'         => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 4 AND ll.status = 'N' THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'switch_day_off'         => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 5 AND ll.status <> 'N' THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'switch_day_off_reject'  => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 5 AND ll.status = 'N' THEN DATEDIFF(ll.to_date, ll.from_date)+1 ELSE 0 END)"),
            'ordination_leave'       => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 6 AND ll.status <> 'N' THEN 
                                        CASE
	                                      WHEN 
	                                        ll.from_date < '$start_date' AND ll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        ll.from_date < '$start_date' THEN DATEDIFF(ll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        ll.to_date > '$end_date' THEN DATEDIFF('$end_date', ll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(ll.to_date, ll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'maternity_leave'        => new Zend_Db_Expr("SUM(CASE WHEN ll.action_id = 7 AND ll.status <> 'N' THEN 
                                        CASE
	                                      WHEN 
	                                        ll.from_date < '$start_date' AND ll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        ll.from_date < '$start_date' THEN DATEDIFF(ll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        ll.to_date > '$end_date' THEN DATEDIFF('$end_date', ll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(ll.to_date, ll.from_date) + 1
	                                      END 
	                                      ELSE 0 END)"),
        );

        $check_in_summary = array(
            'staff_id'        => 'bl.staff_id',
            'status_approve'  => new Zend_Db_Expr("SUM(CASE WHEN bl.status = 'Y' THEN 1 ELSE 0  END)"),
            'status_reject'   => new Zend_Db_Expr("SUM(CASE WHEN bl.status = 'N' THEN 1 ELSE 0  END)"),
            'status_wait'     => new Zend_Db_Expr("SUM(CASE WHEN bl.status = 'W' THEN 1 ELSE 0  END)"),
            'training'        => new Zend_Db_Expr("0"),
        );
        // print_r($check_in_summary );

        $AAA = $db
            ->select()
            ->from(array('ll' => 'pcm_leave_log'), $leave_summary)
            ->where("DATE(ll.created_at) >= ?", $start_date)
            ->where("DATE(ll.created_at) <= ?", $end_date)
            ->where('ll.status <> ?', 'N')
//            ->where('ll.new_check_in <> ?', 'Y')
            ->group('ll.staff_id');
// echo $AAA;
// die;
        $BBB = $db
            ->select()
            ->from(array('bl' => 'pcm_check_in_log'), $check_in_summary)
            ->where("DATE(bl.created_at) >= ?", $start_date)
            ->where("DATE(bl.created_at) <= ?", $end_date)
            ->where('bl.status <> ?', 'N')
//            ->where('bl.new_check_in <> ?', 'Y')
            ->group('bl.staff_id');
// echo $BBB;
// die;
        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
            ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
            ->join(array('rm'   => 'regional_market'), 'rm.id = s.regional_market', array())
            ->where("rm.area_id IN (?)", $area_id)
            ->where("s.group_id IN (17,36)" )
            ->where(new Zend_Db_Expr("( s.off_date >= '$start_date' OR s.off_date IS NULL )"))
            ->where(new Zend_Db_Expr("MID(s.code,1,2) >= 50"))
            ->order(array('s.code ASC'));
// echo $select;
// die;
        $result = $db->fetchAll($select);


        return $result;
    }
    

    function loadApproveWaitingExcel($area_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'        => 's.id',
            'staff_code'      => 's.code',
            'area_name'       => 'ar.name',
            'staff_name'      => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'off_date'        => 's.off_date',
           
        );

        $leave_summary = array(
            'staff_id'               => 'pll.staff_id',
            'status_leave'             => new Zend_Db_Expr("SUM(CASE WHEN pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'sick_leave'             => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 1 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'personal_leave'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 2 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'vacation_leave'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 3 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'day_off'                => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 4 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'switch_day_off'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 5 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'ordination_leave'       => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 6 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
//            'maternity_leave'        => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 7 AND pll.status = 'W' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
        );

        $check_in_summary = array(
            'staff_id'        => 'pl.staff_id',
            'status_approve'  => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'Y' THEN 1 ELSE 0  END)"),
            'status_reject'   => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'N' THEN 1 ELSE 0  END)"),
            'status_wait'     => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'W' THEN 1 ELSE 0  END)"),
        );

        $AAA = $db
            ->select()
            ->from(array('pll' => 'pcm_leave_log'), $leave_summary)
            ->where("DATE(pll.created_at) >= ?", $start_date)
            ->where("DATE(pll.created_at) <= ?", $end_date)
            ->where("pll.new_check_in <> ?", 'Y')
            ->group('pll.staff_id');

        $BBB = $db
            ->select()
            ->from(array('pl' => 'pcm_check_in_log'), $check_in_summary)
            ->where("DATE(pl.created_at) >= ?", $start_date)
            ->where("DATE(pl.created_at) <= ?", $end_date)
            ->where("pl.new_check_in <> ?", 'Y')
            ->group('pl.staff_id');

        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
            ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
            ->join(array('rm' => 'regional_market'), 'rm.id = s.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
 
            ->where("rm.area_id IN (?)", $area_id)
            ->where("s.group_id IN (17,36)")
            ->where(new Zend_Db_Expr("s.off_date >= '$start_date' OR s.off_date IS NULL"))
            ->where(new Zend_Db_Expr("AAA.status_leave <> 0 OR BBB.status_wait <> 0"))
            ->where(new Zend_Db_Expr("MID(s.code,1,2) >= 50"))
            ->order(array('s.code ASC'));

        $result = $db->fetchAll($select);

        return $result;
    }
 }