<?php

class Application_Model_BmCheckInLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'bm_check_in_log';

    function fetchPagination($page, $limit, &$totalb, $params)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sf.id'),
            'staff_code' => 'sf.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(sf.firstname, ' ', sf.lastname)"),
            'area_name' => 'ar.name',
            'group_name' => 'g.name',
            'type_report' => new Zend_Db_Expr("'BM'")      
              );
        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->join(array('bm'=>$this->_name),'bm.staff_id=sf.id',array())
            ->join(array('g'=>'group'),'g.id = sf.group_id')
            ->join(array('rm' => 'regional_market'), 'rm.id = sf.regional_market', array())
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where(new Zend_Db_Expr("MID(sf.code,1,2) >= 50"))
            ->group('staff_code')
            ->order('staff_code ASC');


        if (isset($params['training_id']) && $params['training_id']) {
            $select->join(array('a' => 'asm'), 'a.area_id = rm.area_id', array());
            $select->where('a.staff_id = ?', $params['training_id']);
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

        if ($limit)
            $select->limitPage($page, $limit);

//        echo '<pre>';
//        print_r($params);
//        echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $totalb = $db->fetchOne("select FOUND_ROWS()");

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

        $result = $db->fetchRow($select);

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

        $result = $db->fetchRow($select);

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
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(pcl.created_at) FROM bm_check_in_log AS pcl WHERE pcl.staff_id = sf.id ORDER BY pcl.created_at ASC LIMIT 1)'),
            'first_leave' => new Zend_Db_Expr('(SELECT DATE(bl.created_at) FROM bm_leave_log AS bl WHERE bl.staff_id = sf.id ORDER BY bl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('sf' => 'staff'), $get)
            ->where('sf.id = ?', $sf_id)
            ->order('staff_name ASC');

        $result = $db->fetchRow($select);

        return $result;
    }

    function getBmCheckIn($staff_id, $start_date, $end_date)
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
            'leave_remark' => new Zend_Db_Expr('CONCAT(null)'),
            'mode_code' => new Zend_Db_Expr('CONCAT("CHECKIN")'),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(pcl.created_at) FROM bm_check_in_log AS pcl WHERE pcl.staff_id = ci.staff_id ORDER BY pcl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('ci' => $this->_name), $get)
            ->join(array('sf' => 'staff'), 'sf.id = ci.staff_id', array())
            ->joinleft(array('st' => 'store'), 'st.id = ci.store_id', array())
            ->joinleft(array('rm' => 'regional_market'), 'rm.id = st.regional_market', array())
            ->joinleft(array('ar' => 'area'), 'ar.id = rm.area_id', array())
            ->where('ci.staff_id = ?', $staff_id)
            ->where('ci.action_id = ?', '1')
            ->where("DATE(ci.created_at) >= ?", $start_date)
            ->where("DATE(ci.created_at) <= ?", $end_date);

        $result = $db->fetchAll($select);

        return $result;
    }

    function getBmLeave($staff_id, $start_date, $end_date)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'day_month' => new Zend_Db_Expr('DATE_FORMAT(bl.created_at, "%d")'),
            'day_week' => new Zend_Db_Expr('DATE_FORMAT(bl.created_at, "%Y-%m-%d")'),
            'id_check_in' => 'bl.id',
            't_check_in' => new Zend_Db_Expr('CONCAT(null)'),
            't_check_out' => new Zend_Db_Expr('CONCAT(null)'),
            'action_id' => 'bl.action_id',
            'leave_remark' => 'bl.leave_remark',
            'certificate_file' => 'bl.certificate_file',
            'certificate_at' => 'bl.certificate_at',
            'mode_code' => new Zend_Db_Expr('CONCAT("LEAVE")'),
            'joined_at' => 'sf.joined_at',
            'first_check_in' => new Zend_Db_Expr('(SELECT DATE(bl.created_at) FROM bm_leave_log AS bl WHERE bl.staff_id = sf.id ORDER BY bl.created_at ASC LIMIT 1)'),
        );

        $select = $db->select()
            ->from(array('bl' => 'bm_leave_log'), $get)
            ->join(array('sf' => 'staff'), 'sf.id = bl.staff_id', array())
            ->where('bl.staff_id = ?', $staff_id)
            ->where("DATE(bl.created_at) >= ?", $start_date)
            ->where("DATE(bl.created_at) <= ?", $end_date);

        $result = $db->fetchAll($select);
        return $result;
    }

    function CheckLeave($staff_id, $date_current)
    {
        $current_date = "'" . $date_current . "'";
        $db = Zend_Registry::get('db');

        $get = array(
            'action_id' => 'bll.action_id',
            'status_ap' => 'bll.status',
            'mode_code' => new Zend_Db_Expr("CONCAT('LEAVE')"),
            'leave_remark' => 'bll.leave_remark',
            'approve_time' => 'bll.updated_at',
            'remark' => 'bll.remark',
            'certificate_file' => 'bll.certificate_file',
            'certificate_at' => 'bll.certificate_at',
        );

        $select = $db->select()
            ->from(array('bll' => 'pc_leave_log'), $get)
            ->where("bll.staff_id = ?", $staff_id)
            ->where("bll.action_id IN (?)", [1, 6, 7])
            ->where('bll.new_check_in <> ?', 'Y')
            ->where("DATE($current_date) >= ?", new Zend_Db_Expr("DATE(bll.from_date)"))
            ->where("DATE($current_date) <= ?", new Zend_Db_Expr("DATE(bll.to_date)"));

        $result = $db->fetchRow($select);

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
            'off_date'        => 's.off_date'
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
            'staff_id'        => 'cl.staff_id',
            'status_approve'  => new Zend_Db_Expr("SUM(CASE WHEN cl.status = 'Y' THEN 1 ELSE 0  END)"),
            'status_reject'   => new Zend_Db_Expr("SUM(CASE WHEN cl.status = 'N' THEN 1 ELSE 0  END)"),
            'status_wait'     => new Zend_Db_Expr("SUM(CASE WHEN cl.status = 'W' THEN 1 ELSE 0  END)"),
            'training'        => new Zend_Db_Expr("0"),
        );

        $AAA = $db
            ->select()
            ->from(array('ll' => 'bm_leave_log'), $leave_summary)
            ->where("DATE(ll.created_at) >= ?", $start_date)
            ->where("DATE(ll.created_at) <= ?", $end_date)
            ->group('ll.staff_id');

        $BBB = $db
            ->select()
            ->from(array('cl' => 'bm_check_in_log'), $check_in_summary)
            ->where("cl.action_id = 1")
            ->where("DATE(cl.created_at) >= ?", $start_date)
            ->where("DATE(cl.created_at) <= ?", $end_date)
            ->where("cl.action_id = 1")
            ->where("cl.new_check_in <> ?", 'Y')
            ->group('cl.staff_id');
        // $EEE=$db
        //     ->select()
        //     ->from(array('cl'=>'bm_check_in_log'),array())
        //     ->join(array('s'=>'staff'))


        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->joinRight(array('cl'=>'bm_check_in_log'),'cl.staff_id=s.id')
            ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
            ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
            ->join(array('rm'   => 'regional_market'), 'rm.id = s.regional_market', array())
            ->where("rm.area_id IN (?)", $area_id)
            ->where("s.group_id = (?)", 30)
            ->where(new Zend_Db_Expr("( s.off_date >= '$start_date' OR s.off_date IS NULL )"))
            ->where(new Zend_Db_Expr("MID(s.code,1,2) >= 50"))
            ->group('s.id')
            ->order(array('s.code ASC'));
// echo $select;
// die;
        $result = $db->fetchAll($select);


        return $result;
    }
}