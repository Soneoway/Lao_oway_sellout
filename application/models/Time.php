<?php
class Application_Model_Time extends Zend_Db_Table_Abstract
{
    protected $_name = 'time';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');
        if (isset($params['month']) and $params['month']) {

            $select = $db->select();

            $select_fields = array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'),
                'total_date' => 'COUNT(p.staff_id)',
                'staff_id',
                'status');

            if (isset($params['get_fields']) and is_array($params['get_fields']))
                foreach ($params['get_fields'] as $get_field)
                    array_push($select_fields, $get_field);
                else
                    array_push($select_fields, 'p.*');

            $select->from(array('p' => $this->_name), $select_fields)->group('p.staff_id');
        } else {
            if ($limit) {
                $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                        ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));
            } else {
                $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            }
        }

        $select->distinct();
        
        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array(
            's.email',
            's.firstname',
            's.lastname',
            's.department',
            's.team',
            's.regional_market',
            's.phone_number'));
        //join bang ngay phep
        $select->joinLeft(array('o' => 'off_date'), 'p.staff_id = o.id', array('o.date'));

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/' . EMAIL_SUFFIX . '/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'] . EMAIL_SUFFIX);
        }

        if (isset($params['month']) and $params['month']) {
            $select->where('MONTH(p.created_at) = ?', $params['month']);
        }

        if (isset($params['team']) and $params['team']) {
            $select->where('s.team in (?)', $params['team']);
        }

        if (isset($params['code']) and $params['code'])
            $select->where('s.code LIKE ?', '%' . $params['code'] . '%');

        if (isset($params['off']) and $params['off']) {
            if ($params['off'] == 1)
                $select->where('p.off = ?', $params['off']);
            else
                $select->where('p.off = ?', '0');
        }


        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%' . $params['name'] .
                '%');

        if (isset($params['staff_id']) and $params['staff_id']) {
            $select->where('s.id = ?', $params['staff_id']);

        } elseif (isset($params['sale']) and $params['sale']) {

            $select->where('s.id = ?', $params['sale']);

        } elseif (isset($params['leader']) and $params['leader']) {
            $select->joinLeft(array('lg' => 'store_leader_log'), 'st.id = lg.store_id',
                array())->joinLeft(array('ssl' => 'store_staff_log'), 'st.id = ssl.store_id',
                array());

            // quy?n leader
            $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader']) .
                " AND " . $this->getAdapter()->quoteInto('DATE(p.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')',
                1) . " AND (" . $this->getAdapter()->quoteInto('DATE(p.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')',
                1) . " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1) .
                " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1) . " ) 
                        ) ";
            // quy?n sales
            $log_where .= " OR " . " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?',
                $params['leader']) . " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?',
                1) . " AND " . $this->getAdapter()->quoteInto('DATE(p.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')',
                1) . " AND (" . $this->getAdapter()->quoteInto('DATE(p.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')',
                1) . " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1) .
                " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1) . " )
                        )";

            $select->where($log_where);

        } elseif (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_region_cache($params['asm']);
            
            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('p.regional_market IN (?)', $list_regions); // l?c staff thu?c regional_market tr�n
            else
                $select->where('1=0', 1);
            $select->where('s.team in (75,119)', '');

        } elseif (isset($params['asm_standby']) and $params['asm_standby']) {
            $QAsm = new Application_Model_AsmStandby();
            $list_regions = $QAsm->get_region_cache($params['asm_standby']);

            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('p.regional_market IN (?)', $list_regions); // l?c staff thu?c regional_market tr�n
            else
                $select->where('1=0', 1);
            $select->where('s.team in (75,119)', '');
        } elseif (isset($params['other']) and $params['other']) {
            $QTimePermission = new Application_Model_TimePermission();
            $list_regions = $QTimePermission->get_region_cache($params['other']);

            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('p.regional_market IN (?)', $list_regions); // l?c staff thu?c regional_market tr�n
            else
                $select->where('1=0', 1);

            ///////////////////////////////////////////////////////////////
            $list_teams = $QTimePermission->get_team_cache($params['other']);

            if ($list_teams && is_array($list_teams) && count($list_teams) > 0)
                $select->where('s.team IN (?)', $list_teams); // l?c staff thu?c regional_market tr�n
            else
                $select->where('1=0', 1);

        }


        if (isset($params['from_date']) and $params['from_date']) {
            $date = explode('/', $params['from_date']);
            $select->where('date(p.created_at) >= ?', $date[2] . '-' . $date[1] . '-' . $date[0]);
        }

        if (isset($params['to_date']) and $params['to_date']) {
            $date = explode('/', $params['to_date']);
            $select->where('date(p.created_at) <= ?', $date[2] . '-' . $date[1] . '-' . $date[0]);
        }

        if (isset($params['regional_market']) and $params['regional_market'])
            $select->where('st.regional_market = ?', $params['regional_market']);

        if (isset($params['district']) and $params['district'])
            $select->where('st.district = ?', $params['district']);


        if (isset($params['area_id']) and $params['area_id']) {


            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id in (?)', $params['area_id']);

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();
            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            $select->where('s.regional_market IN (?)', $tem);
        }


        if (isset($params['sort']) and $params['sort']) {
            $order_str = '';

            switch ($params['sort']) {
                case 'st.id':
                    $order_str = 'st.`name`';
                    break;
                case 'st.regional_market':
                    $select->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id',
                        array());
                    $order_str = 'rm.`name`';
                    break;
                default:
                    break;
            }

            $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name') {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(s.firstname, " ",s.lastname) ' . $collate . $desc;
            } elseif (in_array($params['sort'], array('st.regional_market', 'st.id'))) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $collate . $desc;
            } else {
                $order_str = $params['sort'] . ' ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit)
            $select->limitPage($page, $limit);


        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function getDay($staff_id, $month)
    {
        $db = Zend_Registry::get('db');
        if (isset($staff_id) and isset($month)) {
            $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('MONTH(created_at) = ?', $month);
            $select->where('status = ?', '1');
            $result = count($db->fetchAll($select));
            return $result;
        } else
            return - 1;
    }

    function checkInStatus($staff_id)
    {
        $QStaff = new Application_Model_Staff();
        $staff_rowset = $QStaff->find($staff_id);
        $staff = $staff_rowset->current();

       // if($staff['title'] and $staff['title'] == PGPB_TITLE)
       // {
      //      return 1;
       // }



        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
        $select->where('staff_id = ? ', $staff_id);
        $select->where('DATE(created_at) = DATE(CURDATE())', null);

        $result = $db->fetchRow($select);



        if(empty($result))
        {
            return 0;
        }
        else
            return 1;

    }
    
    function getDayDashboard($params)
    {
        $db = Zend_Registry::get('db');
        $staff_id = $params['user_id'];
        $month = $params['month'];
        
        
        if (isset($staff_id) and isset($month)) {
            $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('MONTH(created_at) = ?', $month);
            
            $result = $db->fetchAll($select);
            $day_approve = $day_not_approve = 0;
            
            foreach ($result as $k => $v)
            {
                if($v['status'] == 0)
                {
                    $day_not_approve++;
                }
                else
                {
                    $day_approve++;
                }
            }
            
            $result = array(
            'day_approve' => $day_approve,
            'day_not_approve' => $day_not_approve,
            'total' => count($result),            
            );
            
            return $result;
        } else
            return - 1;
    }

    function fetchChildbearing($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p'=>'off_childbearing'),array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'),
                'p.*',
                'staff_name'=>'CONCAT(s.firstname," ",s.lastname)',
                'title'=>'t1.name',
                'team'=>'t2.name',
                's.code',
                'p.off_type',
                ))
            ->join(array('s'=>'staff'),'p.staff_id = s.id',array())
            ->join(array('t1'=>'team'),'t1.id = s.title',array())
            ->join(array('t2'=>'team'),'t2.id = t1.parent_id',array())
        ;

        if(isset($params['from_date']) AND $params['from_date']){
            $from = explode('/',$params['from_date']);
            $from = date('Y-m-d',strtotime($from[2].'-'.$from[1].'-'.$from[0]));
            $select->where('p.from_date >= ?',$from);
        }

        if(isset($params['to_date']) AND $params['to_date']){
            $to = explode('/',$params['to_date']);
            $to = date('Y-m-d',strtotime($to[2].'-'.$to[1].'-'.$to[0]));
            $select->where('p.from_date <= ?',$to);
        }

        if(isset($params['code']) AND $params['code']){
            $select->where('s.code = ?',$params['code']);
        }

        if(isset($params['email']) AND $params['email']){
            $select->where('s.email LIKE ?','%'.$params['email'].'%');
        }

        if(isset($params['name']) AND $params['name']){
            $select->where('CONCAT(s.firstname," ",s.lastname) LIKE ?','%'.$params['name'].'%');
        }

        if(isset($params['off_type']) AND $params['off_type']){
            $select->where('p.off_type = ?',$params['off_type']);
        }

        $select->order('p.created_at DESC');
        if ($limit)
            $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    public function getTimeNotCheckIn($staff_id)
    {

        $month = date('m');
        $last_day_of_month = date('d');
        $QStaff = new Application_Model_Staff();
        $QTime  = new Application_Model_Time();
        $staff_current_set = $QStaff->find($staff_id);
        $staff = $staff_current_set->current();
        $QDateSpecial = new Application_Model_DateSpecial();
        $list_date_special = $QDateSpecial->get_date($month);
        $time_not_check_in = array();
        for($i= 1 ; $i <= $last_day_of_month ; $i++)
        {
            $date_special = 0;
            if(isset($list_date_special) and $list_date_special)
            {
                foreach($list_date_special as $k => $v) {
                    if ($v['date'] == $i and $v['team'] == $staff['id'])
                    {
                        $date_special = 1;
                    }
                }

                if(isset($date_special) and $date_special)
                {
                    continue;
                }
            }


            $date_time =  $i <= 9 ? '0'.$i :  $i;

            $check = date('Y-m-'.$date_time);

            if(isset($check) and $check)
            {
                $weekday = date("l", strtotime($check));

                if(isset($weekday) and $weekday == "Sunday")
                {
                    continue;
                }

                if(isset($weekday) and $weekday == "Saturday")
                {
                    continue;
                }
            }

            $where = array();


            $where[] = $QTime->getAdapter()->quoteInto('DATE(created_at) = ?' , date('Y-m-d' , strtotime($check)));
            $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?' , $staff_id);
            $result  = $QTime->fetchRow($where);

            if(empty($result))
            {
                $time_not_check_in[] = date('d' , strtotime($check));
            }


        }

        if(!empty($time_not_check_in))
        {
            return implode("," , $time_not_check_in);
        }
        else
        {
            return null;
        }


    }

    public function getLimitedTime($staff_id)
    {
        $date = date('d');
        $QStaff = new Application_Model_Staff();
        $staff_current_set = $QStaff->find($staff_id);
        $staff = $staff_current_set->current();

        $team_of_staff = $staff['team'];
        $joined_at = $staff['joined_at'];

        $QDateSpecial = new Application_Model_DateSpecial();
        $list_date_special = $QDateSpecial->get_date(date('m'));
        $total_timing = 0;


        for($i=1; $i<= $date; $i++)
        {
            $date_special = 0;
            foreach($list_date_special as $k => $v) {
                if ($v['date'] == $i and $v['team'] == $team_of_staff)
                {
                    $date_special = 1;
                }
            }

            if(isset($date_special) and $date_special)
            {
                continue;
            }

            if($i <= 9)
            {
                $date_time = '0'.$i;
            }
            else{
                $date_time=$i;
            }

            $check = date('Y-m-'.$date_time);

            if(isset($check) and $check)
            {
                $weekday = date("l", strtotime($check));

                if(isset($weekday) and $weekday == "Sunday")
                {
                    continue;
                }

                if(isset($weekday) and $weekday == "Saturday")
                {
                    continue;
                }
            }

            //set xem nhan vien do co phai nhan vien moi di lam trong thang k
            if($joined_at <= $date_time)
            {
                continue;
            }


            $total_timing ++;
        }
        return $total_timing;

    }

}
