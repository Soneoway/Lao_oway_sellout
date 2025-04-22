<?php

class Application_Model_PcCheckInLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'pc_check_in_log';

    function fetchPagination($page, $limit, &$totalp, $params)
    {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sf.id'),
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'area_name' => 'ar.name',
            'group_name'=> 'g.name',
            'type_report' => new Zend_Db_Expr("'PC'")

        );

       //  echo "<pre>";

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->joinright(array('pc'=> $this->_name),'pc.staff_id=sf.id',array())
            ->join(array('g'=>'group'),'g.id = sf.group_id')
            ->join(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where(new Zend_Db_Expr("MID(sf.code,1,2) >= 50"))
            ->group('staff_id')
//            ->where(new Zend_Db_Expr("( sf.off_date >= '".$params['start_date']."' OR sf.off_date IS NULL )"))
            ->order('staff_code ASC');




        if (isset($params['training_id']) && $params['training_id']) {
            $select->join(array('a' => 'asm'), 'a.area_id = rm.area_id', array());
            $select->where('a.staff_id = ?', $params['training_id']);
        }


        if (isset($params['store_id']) && $params['store_id']) {
            $select->where('st.id LIKE ?', '%' . $params['store_id'] . '%');
        }
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
            $totalp = $db->fetchOne("select FOUND_ROWS()");

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
            ->from(array('ss' => 'store_staff'),$get)
            ->join(array('st' => 'store'), 'st.id = ss.store_id', array())
            ->where('ss.staff_id = ?', $staff_id);

// echo $select;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getStaffId($staff_code)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => 'sf.id',
            );

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->where('sf.code = ?', $staff_code);

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
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(pcl.created_at) FROM pc_check_in_log AS pcl WHERE pcl.staff_id = sf.id ORDER BY pcl.created_at ASC LIMIT 1)'),
            'first_leave' => new Zend_Db_Expr('(SELECT DATE(pl.created_at) FROM pc_leave_log AS pl WHERE pl.staff_id = sf.id ORDER BY pl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
//            ->where('sf.group_id = ?', '4')
//            ->where('sf.off_date IS NULL')
            ->where('sf.id = ?', $sf_id)
            ->order('staff_name ASC');

        $result = $db->fetchRow($select);

        return $result;
    }


    function getPcCheckIn($staff_id, $store_id, $start_date, $end_date)
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
            'status_ap' => 'ci.status',
            'approve_time' => 'ci.updated_at',
            'remark' => 'ci.remark',
            'leave_remark' => new Zend_Db_Expr("null"),
            'mode_code' => new Zend_Db_Expr("CASE WHEN st.id <> 0 THEN 'CHECKIN' ELSE 'TRAINING' END"),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(pcl.created_at) FROM pc_check_in_log AS pcl WHERE pcl.staff_id = ci.staff_id ORDER BY pcl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('ci' => $this->_name), $get)
            ->join(array('sf' => 'staff'), 'sf.id = ci.staff_id', array())
            ->joinleft(array('st' => 'store'), 'st.id = ci.store_id', array())
            ->joinleft(array('rm' => 'regional_market'), 'rm.id = st.regional_market', array())
            ->joinleft(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where('ci.staff_id = ?', $staff_id)
            // ->where('sf.group_id =?','4')
            ->where('ci.action_id = ?', '1')
            ->where('ci.new_check_in <> ?', 'Y')
//            ->where('ci.store_id = ?', $store_id)
            ->where("DATE(ci.created_at) >= ?", $start_date)
            ->where("DATE(ci.created_at) <= ?", $end_date);


// echo $select;

        $result = $db->fetchAll($select);

        return $result;
    }

    function getPcLeave($staff_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'day_month' => new Zend_Db_Expr('DATE_FORMAT(pl.created_at, "%d")'),
            'day_week' => new Zend_Db_Expr('DATE_FORMAT(pl.created_at, "%Y-%m-%d")'),
            'id_check_in' => 'pl.id',
            't_check_in' => new Zend_Db_Expr("null"),
            't_check_out' => new Zend_Db_Expr("null"),
            'action_id' => 'pl.action_id',
            'status_ap' => 'pl.status',
            'approve_time' => 'pl.updated_at',
            'remark' => 'pl.remark',
            'leave_remark' => 'pl.leave_remark',
            'certificate_file' => 'pl.certificate_file',
            'certificate_at' => 'pl.certificate_at',
            'mode_code' => new Zend_Db_Expr("'LEAVE'"),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(pl.created_at) FROM pc_leave_log AS pl WHERE pl.staff_id = sf.id ORDER BY pl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('pl' => 'pc_leave_log'), $get)
            ->join(array('sf' => 'staff'), 'sf.id = pl.staff_id', array())
            ->where('pl.staff_id = ?', $staff_id)
            ->where('pl.new_check_in <> ?', 'Y')
            ->where("DATE(pl.created_at) >= ?", $start_date)
            ->where("DATE(pl.created_at) <= ?", $end_date);
// echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function CheckLeave($staff_id, $date_current)
    {
        $current_date = "'" . $date_current . "'";
        $db = Zend_Registry::get('db');

        $get = array(
            'action_id' => 'pll.action_id',
            'status_ap' => 'pll.status',
            'mode_code' => new Zend_Db_Expr("CONCAT('LEAVE')"),
            'leave_remark' => 'pll.leave_remark',
            'approve_time' => 'pll.updated_at',
            'remark' => 'pll.remark',
            'certificate_file' => 'pll.certificate_file',
            'certificate_at' => 'pll.certificate_at',
        );

        $select = $db->select()
            ->from(array('pll' => 'pc_leave_log'), $get)
            ->where("pll.staff_id = ?", $staff_id)
            ->where("pll.action_id IN (?)", [1, 6, 7])
            ->where('pll.new_check_in <> ?', 'Y')
            ->where("DATE($current_date) >= ?", new Zend_Db_Expr("DATE(pll.from_date)"))
            ->where("DATE($current_date) <= ?", new Zend_Db_Expr("DATE(pll.to_date)"));
  // echo $select;

        $result = $db->fetchRow($select);
                // echo $result;
                // die;
        return $result;
    }

    function getRemarkGroup ($staff_id)
        {
             $db = Zend_Registry::get('db');

             $get=array(
            'change_group' => new Zend_Db_Expr("CONCAT('เปลี่ยนจาก ',g.name,' เป็น ', g2.name)"),
             'created' => new Zend_Db_Expr('DATE_FORMAT(sgl.created_at, "%Y-%m-%d")'),

            );

            $select=$db->select()
                    ->from(array('sgl'=>'staff_group_log'),$get)
                    ->join(array('g'=>'group'),'g.id=sgl.before_id',array())
                    ->join(array('g2'=>'group'),'g2.id=sgl.after_id',array())
                    ->where('sgl.staff_id =?',$staff_id)
                    ->order(array('sgl.created_at ASC'));

            $result=$db->fetchAll($select);

            return $result;

        }

    function loadReportExcelAllCheckIn($area_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'        => 's.id',
            'staff_code'      => 's.code',
            'staff_name'      => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'joined_at'       => 's.joined_at',
            'off_date'        => 's.off_date',
//            'status_approve'  => 'BBB.status_approve',
//            'status_reject'   => 'BBB.status_reject',
//            'status_wait'     => 'BBB.status_wait',
//            'sick_leave'      => 'AAA.sick_leave',
//            'personal_leave'  => 'AAA.personal_leave',
//            'vacation_leave'  => 'AAA.vacation_leave',
//            'day_off'         => 'AAA.day_off',
//            'switch_day_off'  => 'AAA.switch_day_off',
//            'ordination_leave'=> 'AAA.ordination_leave',
//            'maternity_leave' => 'AAA.maternity_leave',
        );

        $leave_summary = array(
            'staff_id'               => 'pll.staff_id',
            'sick_leave'             => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 1 AND pll.status <> 'N' AND pll.certificate_file IS NULL THEN 
                                        CASE
	                                      WHEN 
	                                        pll.from_date < '$start_date' AND pll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        pll.from_date < '$start_date' THEN DATEDIFF(pll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        pll.to_date > '$end_date' THEN DATEDIFF('$end_date', pll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(pll.to_date, pll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'sick_leave_cert'        => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 1 AND pll.status <> 'N' AND pll.certificate_file IS NOT NULL THEN 
                                        CASE
	                                      WHEN 
	                                        pll.from_date < '$start_date' AND pll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        pll.from_date < '$start_date' THEN DATEDIFF(pll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        pll.to_date > '$end_date' THEN DATEDIFF('$end_date', pll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(pll.to_date, pll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'personal_leave'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 2 AND pll.status <> 'N' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'vacation_leave'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 3 AND pll.status <> 'N' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'day_off_approve'        => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 4 AND pll.status <> 'N'  THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'day_off_reject'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 4 AND pll.status = 'N' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'switch_day_off'         => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 5 AND pll.status <> 'N' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'switch_day_off_reject'  => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 5 AND pll.status = 'N' THEN DATEDIFF(pll.to_date, pll.from_date)+1 ELSE 0 END)"),
            'ordination_leave'       => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 6 AND pll.status <> 'N' THEN 
                                        CASE
	                                      WHEN 
	                                        pll.from_date < '$start_date' AND pll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        pll.from_date < '$start_date' THEN DATEDIFF(pll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        pll.to_date > '$end_date' THEN DATEDIFF('$end_date', pll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(pll.to_date, pll.from_date) + 1 
	                                      END 
	                                      ELSE 0 END)"),
            'maternity_leave'        => new Zend_Db_Expr("SUM(CASE WHEN pll.action_id = 7 AND pll.status <> 'N' THEN 
                                        CASE
	                                      WHEN 
	                                        pll.from_date < '$start_date' AND pll.to_date > '$end_date' THEN DATEDIFF('$end_date', '$start_date') + 1
	                                      WHEN 
	                                        pll.from_date < '$start_date' THEN DATEDIFF(pll.to_date, '$start_date') + 1
	                                      WHEN 
	                                        pll.to_date > '$end_date' THEN DATEDIFF('$end_date', pll.from_date) + 1
	                                      ELSE 
	                                        DATEDIFF(pll.to_date, pll.from_date) + 1
	                                      END 
	                                      ELSE 0 END)"),
        );

        $check_in_summary = array(
            'staff_id'        => 'pl.staff_id',
            'status_approve'  => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'Y' AND pl.store_id <> 0 THEN 1 ELSE 0  END)"),
            'status_reject'   => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'N' AND pl.store_id <> 0 THEN 1 ELSE 0  END)"),
            'status_wait'     => new Zend_Db_Expr("SUM(CASE WHEN pl.status = 'W' AND pl.store_id <> 0 THEN 1 ELSE 0  END)"),
            'training'        => new Zend_Db_Expr("SUM(CASE WHEN pl.store_id = 0 THEN 1 ELSE 0  END)"),
        );

//        $training_summary = array(
//            'staff_id'        => 'pt.staff_id',
//            'training'  => new Zend_Db_Expr("COUNT(pt.check_in)"),
//        );

        $AAA = $db
            ->select()
            ->from(array('pll' => 'pc_leave_log'), $leave_summary)
            ->where("DATE(pll.to_date) >= ?", $start_date)
            ->where("DATE(pll.from_date) <= ?", $end_date)
            ->where("pll.status <> 'N'")
            ->where("pll.new_check_in <> ?", 'Y')
            ->group('pll.staff_id');

        $BBB = $db
            ->select()
            ->from(array('pl' => 'pc_check_in_log'), $check_in_summary)
            ->where("DATE(pl.created_at) >= ?", $start_date)
            ->where("DATE(pl.created_at) <= ?", $end_date)
            ->where("pl.action_id = 1")
            ->where("pl.status <> 'N'")
            ->where("pl.new_check_in <> ?", 'Y')
            ->group('pl.staff_id');

//        $CCC = $db
//            ->select()
//            ->from(array('pt' => 'pc_training'), $training_summary)
//            ->where("DATE(pt.created_at) >= ?", $start_date)
//            ->where("DATE(pt.created_at) <= ?", $end_date)
//            ->group('pt.staff_id');

        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
            ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
//            ->joinLeft(array('CCC' => $CCC), 'CCC.staff_id = s.id ')
            ->join(array('rm'   => 'regional_market'), 'rm.id = s.regional_market', array())
            ->where("rm.area_id IN (?)", $area_id)
            ->where("s.group_id = (?)", 4)
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
            'pcm_code'        => 's2.code',
            'pcm_name'      => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
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
            ->from(array('pll' => 'pc_leave_log'), $leave_summary)
            ->where("DATE(pll.created_at) >= ?", $start_date)
            ->where("DATE(pll.created_at) <= ?", $end_date)
            ->where("pll.new_check_in <> ?", 'Y')
            ->group('pll.staff_id');

        $BBB = $db
            ->select()
            ->from(array('pl' => 'pc_check_in_log'), $check_in_summary)
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
            ->join(array('a'  => 'asm'), 'a.area_id = ar.id', array())
            ->join(array('s2' => 'staff'), 's2.id = a.staff_id', array())
            ->where("rm.area_id IN (?)", $area_id)
            ->where("s.group_id = (?)", 4)
            ->where("s2.group_id = (?)", 17)
            ->where(new Zend_Db_Expr("s.off_date >= '$start_date' OR s.off_date IS NULL"))
            ->where(new Zend_Db_Expr("AAA.status_leave <> 0 OR BBB.status_wait <> 0"))
            ->where(new Zend_Db_Expr("MID(s.code,1,2) >= 50"))
            ->order(array('s.code ASC'));

        $result = $db->fetchAll($select);

        return $result;
    }

    function eventCheckListPagination($page, $limit, &$totalp, $params)
    {

        $db = Zend_Registry::get('db');

        $bkk_list = array(
            81,82,83,85,86,87,115,90,91,92,
            93,113,94,95,96,88,89,117,110,111,
            112,97,109,98,99,100,101,102,114,103,
            104,105,116,106,107,108);

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $get = array(
            'staff_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sf.id'),
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'area_name' => 'ar.name',
            'group_name'=> 'g.name',
            'event_name'=> 'e.name',
            'event_booth'=> 'esa.org',
            'id' => 'pc.id',
            'check_in'=> 'pc.check_in',
            'check_out'=> 'pc.check_out',
            'filepic'=> 'pc.filepic',
            'events_pay_img'=> 'pc.events_pay_img',
            'events_pay_at'=> 'pc.events_pay_at',
            'events_lunch_img_1'=> new Zend_Db_Expr("(SELECT p1.events_lunch_img FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in ASC LIMIT 1)"),
            'events_lunch_at_1'=> new Zend_Db_Expr("(SELECT p1.events_lunch_at FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in ASC LIMIT 1)"),
            'events_lunch_img_2'=> new Zend_Db_Expr("(SELECT p1.events_lunch_img FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in DESC LIMIT 1)"),
            'events_lunch_at_2'=> new Zend_Db_Expr("(SELECT p1.events_lunch_at FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in DESC LIMIT 1)"),
            'work_day'=> new Zend_Db_Expr("(SELECT COUNT(p2.staff_id) FROM pc_check_in_log AS p2 WHERE p2.staff_id = pc.staff_id AND p2.action_id = 1 AND p2.status = 'Y' AND p2.check_in BETWEEN e.from_date AND e.to_date)"),
//            'events_pay_img'=> 'pc.events_pay_img',
//            'events_pay_at'=> 'pc.events_pay_at',
//            'events_lunch_img'=> 'pc2.events_lunch_img',
//            'events_lunch_at'=> 'pc2.events_lunch_at',

        );

        $select = $db->select()
            ->from(array('esa' => 'events_staff_assign'), $get)
            ->join(array('pc' => 'pc_check_in_log'),'pc.staff_id = esa.staff_id AND pc.action_id = 1', array())
//            ->joinleft(array('pc2' => 'pc_check_in_log'),'pc2.staff_id = pc.staff_id AND pc2.action_id = 3 AND DATE(pc2.check_in) = DATE(pc.check_in)', array())
            ->join(array('e' => 'events'), 'e.id = esa.events_id AND pc.check_in BETWEEN e.from_date AND e.to_date', array())
            ->join(array('sf' => 'staff'), 'sf.id = esa.staff_id', array())
            ->join(array('g' => 'group'),'g.id = sf.group_id', array())
            ->join(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->order(array('pc.check_in DESC','staff_code ASC'));

        $select->where('pc.status = ?', 'Y');
        $select->where('pc.check_in >= ?', $from.' 00:00:00');
        $select->where('pc.check_in <= ?', $to.' 23:59:59');


        if (isset($params['event_id']) && $params['event_id']) {
            $select->where('e.id = ?', $params['event_id']);
        }

        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('sf.code = ?', $params['staff_code']);
        }

        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where('CONCAT(sf.firstname, " ",sf.lastname) LIKE ?', '%' . $params['staff_name'] . '%');
        }

        if (isset($params['get_money']) && $params['get_money']) {

            if ( in_array(0, $params['get_money']) && in_array(1, $params['get_money']) ) {  }
            elseif ( in_array(0, $params['get_money']) )
                $select->where('pc.events_pay_img IS NOT NULL');
            elseif ( in_array(1, $params['get_money']) )
                $select->where('pc.events_pay_img IS NULL');
        }

        if (isset($params['get_food']) && $params['get_food']) {

            if ( in_array(0, $params['get_food']) && in_array(1, $params['get_food']) ) {  }
            elseif ( in_array(0, $params['get_food']) )
                $select->where('pc.events_lunch_img IS NOT NULL');
            elseif ( in_array(1, $params['get_food']) )
                $select->where('pc.events_lunch_img IS NULL');
        }


        if (isset($params['chk_bkk']) && $params['chk_bkk']) {
            $select->where('rm.area_id IN (?)', $bkk_list);
        } else {

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
                    $select->where('s.regional_market IN (?)', $params['regional_market']);
                elseif (is_numeric($params['regional_market']))
                    $select->where('s.regional_market = ?', intval($params['regional_market']));
                else
                    $select->where('1=0', 1);
            }

        }

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $totalp = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function getEventCheckListDetails($id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sf.id'),
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'area_name' => 'ar.name',
            'group_name'=> 'g.name',
            'event_name'=> 'e.name',
            'id' => 'pc.id',
            'check_in'=> 'pc.check_in',
            'check_out'=> 'pc.check_out',
            'filepic'=> 'pc.filepic',
            'events_pay_img'=> 'pc.events_pay_img',
            'events_pay_at'=> 'pc.events_pay_at',
            'events_lunch_img_1'=> new Zend_Db_Expr("(SELECT p1.events_lunch_img FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in ASC LIMIT 1)"),
            'events_lunch_at_1'=> new Zend_Db_Expr("(SELECT p1.events_lunch_at FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in ASC LIMIT 1)"),
            'events_lunch_img_2'=> new Zend_Db_Expr("(SELECT p1.events_lunch_img FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in DESC LIMIT 1)"),
            'events_lunch_at_2'=> new Zend_Db_Expr("(SELECT p1.events_lunch_at FROM pc_check_in_log AS p1 WHERE p1.action_id = 3 AND pc.staff_id = p1.staff_id AND DATE(pc.check_in) = DATE(p1.check_in) ORDER BY p1.check_in DESC LIMIT 1)"),
            'work_day'=> new Zend_Db_Expr("(SELECT COUNT(p2.staff_id) FROM pc_check_in_log AS p2 WHERE p2.staff_id = pc.staff_id AND p2.action_id = 1 AND p2.status = 'Y' AND p2.check_in BETWEEN e.from_date AND e.to_date)"),
//            'events_pay_img'=> 'pc.events_pay_img',
//            'events_pay_at'=> 'pc.events_pay_at',
//            'events_lunch_img'=> 'pc2.events_lunch_img',
//            'events_lunch_at'=> 'pc2.events_lunch_at',

        );

        $select = $db->select()
            ->from(array('esa' => 'events_staff_assign'), $get)
            ->join(array('pc' => 'pc_check_in_log'),'pc.staff_id = esa.staff_id AND pc.action_id = 1', array())
//            ->joinleft(array('pc2' => 'pc_check_in_log'),'pc2.staff_id = pc.staff_id AND pc2.action_id = 3 AND DATE(pc2.check_in) = DATE(pc.check_in)', array())
            ->join(array('e' => 'events'), 'e.id = esa.events_id AND pc.check_in BETWEEN e.from_date AND e.to_date', array())
            ->join(array('sf' => 'staff'), 'sf.id = esa.staff_id', array())
            ->join(array('g' => 'group'),'g.id = sf.group_id', array())
            ->join(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->order(array('pc.check_in DESC','staff_code ASC'))
            ->where('pc.id = ?', $id);

        $result = $db->fetchRow($select);

        return $result;
    }
}