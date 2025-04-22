<?php
class ToolController extends My_Application_Controller_Cli
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    public function getSalesManAction()
    {
        $db = Zend_Registry::get('db');

        $sql = "SELECT COUNT(t.id)
                FROM timing t
                    INNER JOIN staff s
                    ON t.approved_by=s.id
                    AND t.approved_at <> 0
                    AND t.approved_at <> ''
                    AND t.approved_at IS NOT NULL
                    AND t.`from` >= '2015-01-01 00:00:00'
                    AND t.`from` <= '2015-01-31 23:59:59'
                AND t.sales_id IS NULL";

        $stmt = $db->query($sql);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        $total = $stmt->fetchAll();
        $total = $total[0][0];
        
        $sql = "SELECT t.id, t.`from`, t.approved_by, t.store, t.approved_at
                FROM timing t
                    INNER JOIN staff s
                    ON t.approved_by=s.id
                    AND t.approved_at <> 0
                    AND t.approved_at <> ''
                    AND t.approved_at IS NOT NULL
                    AND t.`from` >= '2015-01-01 00:00:00'
                    AND t.`from` <= '2015-01-31 23:59:59'
                AND t.sales_id IS NULL";
        
        $timings = $db->query($sql);
        $i = 1;
        foreach ($timings as $key => $timing) {
            My_Cli_Util::show_status($i++, $total);

            $QStoreStaffLog = new Application_Model_StoreStaffLog();
            
            $staff_id = $QStoreStaffLog->get_sales_man($timing['store'], $timing['from']);
            
            if ( ! $staff_id )
                $staff_id = $QStoreStaffLog->get_sales_man($timing['store'], $timing['approved_at']);

            if ( ! $staff_id )
                $staff_id = $QStoreStaffLog->get_sales_man($timing['store'], date('Y-m-d H:i:s'));
            
            if ($staff_id) {
                $sql = "UPDATE timing SET timing.sales_id=? WHERE timing.id=?";
                $db->query($sql, array($staff_id, $timing['id']));
            }
        }

        exit;
    }

    public function getLeaderAction()
    {
        $db = Zend_Registry::get('db');

        $sql = "SELECT COUNT(t.id)
                FROM timing t
                WHERE
                     t.approved_at <> 0
                    AND t.approved_at <> ''
                    AND t.approved_at IS NOT NULL
                    AND t.`from` >= '2015-01-01 00:00:00'
                    AND t.`from` <= '2015-01-31 23:59:59'";

        $stmt = $db->query($sql);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        $total = $stmt->fetchAll();
        $total = $total[0][0];
        
        $sql = "SELECT t.id, t.`from`, t.approved_by, t.store, t.approved_at
                FROM timing t
                WHERE
                     t.approved_at <> 0
                    AND t.approved_at <> ''
                    AND t.approved_at IS NOT NULL
                    AND t.`from` >= '2015-01-01 00:00:00'
                    AND t.`from` <= '2015-01-31 23:59:59'
                    
                    ";
        
        $timings = $db->query($sql);
        $i = 1;
        foreach ($timings as $key => $timing) {
            My_Cli_Util::show_status($i++, $total);

            $QStoreStaffLog = new Application_Model_StoreLeaderLog();
            
            $staff_id = $QStoreStaffLog->get_leader($timing['store'], $timing['from']);
            
            if ($staff_id) {
                $sql = "UPDATE timing SET timing.leader_id=? WHERE timing.id=?";
                $db->query($sql, array($staff_id, $timing['id']));
            } else {
                $sql = "UPDATE timing SET timing.leader_id=NULL WHERE timing.id=?";
                $db->query($sql, array($timing['id']));
            }
        }

        exit;
    }

    ///////////////////////////////////////////////
    public function salesdupAction()
    {
        $db = Zend_Registry::get('db');
        
        $sql = "SELECT t.id, t.staff_id
                FROM imei_dup t
                    INNER JOIN staff s
                    ON t.staff_id=s.id";
        
        $timings = $db->query($sql);
        $total = count($timings);
        echo "Total: " . $total . "\n";
        $n = 1;
        foreach ($timings as $key => $timing) {
            echo "--: " . $n++ . "\n";
            $sql = "SELECT staff_id FROM sales_team_staff
                    WHERE is_leader=1 AND sales_team_id IN(
                        SELECT sales_team_id FROM sales_team_staff

                        WHERE staff_id = ?
                    )";
            $staff = $db->query($sql, array($timing['staff_id']));
            $staff_id = $staff->fetch();
            $staff_id = $staff_id['staff_id'];

            $sql = "UPDATE imei_dup SET imei_dup.sales_id=? WHERE imei_dup.id=?";
            $db->query($sql, array($staff_id, $timing['id']));
        }

        exit;
    }

    /**
     * init log info
     */
    public function logAction()
    {
        $QStaffLog = new Application_Model_StaffLog();
        $QStaff = new Application_Model_Staff();
        $staffs = $QStaff->fetchAll();

        echo count($staffs);
        $i = 1;

        foreach ($staffs as $k => $staff) {
            echo $i++."\n";
            $where = $QStaffLog->getAdapter()->quoteInto('object = ?', $staff['id']);
            $log = $QStaffLog->fetchRow($where);

            if (!$log) {
                Log::w(array(), $staff->toArray(), $staff['id'], LogGroup::Staff, LogType::Insert);
            }
        }

        exit;
    }

    public function noteAction()
    {
        $db = Zend_Registry::get('db');

        $sql = "SELECT i.note AS inote, s.note AS snote, s.id FROM staff_ignore i
            INNER JOIN staff s
            ON i.staff_id=s.id
            ";

        $staffs = $db->query($sql);

        echo count($staffs)."\n";
        $i = 1;

        foreach ($staffs as $key => $value) {
            $sql = "UPDATE staff SET note = ? WHERE staff.id= ?";

            $db->query($sql, array($value['snote'] ."\n". $value['inote'], $value['id']));
            echo $i++."\n";
        }


        exit;
    }

    public function insertUserAction()
    {
        $data = 'a:37:{s:4:"code";s:8:"14030152";s:13:"contract_type";s:0:"";s:18:"contract_signed_at";N;s:13:"contract_term";s:0:"";s:19:"contract_expired_at";N;s:10:"department";s:1:"5";s:4:"team";s:2:"16";s:9:"firstname";s:9:"LÊ THỊ";s:8:"lastname";s:4:"VÂN";s:5:"title";s:14:"NHÂN VIÊN PG";s:12:"phone_number";s:0:"";s:9:"joined_at";s:10:"2014-03-19";s:8:"off_date";N;s:6:"gender";s:1:"0";s:5:"level";s:0:"";s:11:"certificate";s:0:"";s:7:"address";s:0:"";s:17:"permanent_address";s:0:"";s:11:"birth_place";s:0:"";s:9:"ID_number";s:0:"";s:8:"ID_place";s:0:"";s:7:"ID_date";N;s:11:"nationality";s:0:"";s:8:"religion";s:0:"";s:4:"note";s:5:"- BHS";s:5:"email";s:20:"van.le@oppomobile.vn";s:15:"regional_market";s:2:"48";s:8:"group_id";s:1:"4";s:21:"social_insurance_time";s:0:"";s:23:"social_insurance_number";s:0:"";s:12:"personal_tax";s:0:"";s:28:"family_allowances_registered";s:0:"";s:12:"native_place";s:0:"";s:3:"dob";s:0:"";s:8:"password";s:32:"e10adc3949ba59abbe56e057f20f883e";s:10:"updated_at";s:19:"2014-03-20 17:06:07";s:10:"updated_by";s:3:"340";}';
        $data = unserialize($data);
        $data['id'] = 1651;
        $QStaff = new Application_Model_Staff();
        $QStaff->insert($data);

        exit;
    }

    public function removeActivatedImeiFromNotAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $sql = "DELETE FROM imei_not_activated 
                    WHERE imei_not_activated.imei IN 
                (SELECT wh_imei_activation.imei_sn 
                    FROM wh_imei_activation 
                    WHERE wh_imei_activation.activated_at IS NOT NULL)";

        $db = Zend_Registry::get('db');
        $db->query($sql);

        echo date('Y-m-d') ." Done: Remove activated IMEI from unactivated list\n";

        exit;
    }

    public function getStoreForDupImeiAction()
    {
        $sql = "SELECT d.id, d.staff_id, s.id AS store_id FROM duplicated_imei d
                    INNER JOIN timing t ON d.staff_id=t.staff_id
                    AND DATE(d.date)=DATE(t.`from`)
                    INNER JOIN store s ON t.store=s.id
                    INNER JOIN staff ss ON d.staff_id=ss.id
                    AND ss.group_id=4
                    WHERE  
                    d.date >= '2014-07-01 00:00:00'
                    AND d.date <= '2014-07-31 23:59:59'
                    AND d.asm_check=0
                AND d.store_id IS NULL";

        $db = Zend_Registry::get('db');

        $missing_list = $db->query($sql);

        $i = 0;
        foreach ($missing_list as $value) {
            $sql = "UPDATE duplicated_imei SET store_id = ? WHERE id = ?";
            $db->query($sql, array($value['store_id'], $value['id']));

            echo ++$i . "\n";
        }

        echo "Done\n";

        exit;
    }

    public function setInitLogAction()
    {
        $db = Zend_Registry::get('db');

        $list = array(
            '2' => 'regional_market',
            '3' => 'group_id',
            '4' => 'team',
            '5' => 'department',
        );

        for ($i=2; $i <= 5; $i++) { 
            if ($i == 3) continue;

            $sql = "SELECT id, team, regional_market, department, joined_at FROM staff";
            $staff_ids = $db->query($sql);
            
            foreach ($staff_ids as $key => $value) {
                echo $i . " - " . $key . "\n";

                $sql = "SELECT id FROM staff_log_detail WHERE info_type = ? AND object = ? ORDER BY from_date ASC";
                $res = $db->query($sql, array($i, $value['id']));
                $row = $res->fetch();
                
                if (!$row  || ( isset($row['from_date']) 
                            && strtotime( date('Y-m-d', $row['from_date']) ) > 
                                strtotime ( date( 'Y-m-d', strtotime($value['joined_at']) ) ) ) ) {
                    $sql = "INSERT INTO staff_log_detail(`object`, `info_type`, `current_value`, `from_date`, `to_date`) VALUES(?,?,?,?,?)";
                    $db->query($sql, array(
                                    $value['id'], 
                                    $i, 
                                    isset($row['old_value']) ? $row['old_value'] : $value[ $list[ $i ] ], 
                                    isset($value['joined_at']) ? strtotime($value['joined_at']) : strtotime('2013-01-01 00:00:00'), 
                                    ( isset($row['from_date']) 
                                        && strtotime( date('Y-m-d', $row['from_date']) ) > 
                                            strtotime ( date( 'Y-m-d', strtotime($value['joined_at']) ) ) ) ? $row['from_date'] : null
                                )
                        );
                }
            }
        }

        exit;
    }
    
    public function offAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $db = Zend_Registry::get('db');
        $sql = "update off_date as p
                    inner join staff s on p.id = s.id
                        set p.date = p.date + 1
                               where s.stauts <> 0
                    ";
        
        $rerult = $db->query($sql);
    }

    public function storeMainDealerAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $db = Zend_Registry::get('db');
        // get stores which dealer is sub-dealer
        $sql = "SELECT s.id, s.d_id FROM store s INNER JOIN wh_distributor d ON s.d_id=d.id AND d.parent <> 0";
        $stores = $db->query($sql);

        // get sub-dealer and their parents
        $sql = "SELECT d.id AS child, dd.id AS parent FROM wh_distributor d 
                    INNER JOIN wh_distributor dd ON d.parent=dd.id
                    AND d.parent <> 0";

        $subs = $db->query($sql);

        $sub_arr = array();

        foreach ($subs as $key => $value)
            $sub_arr[ $value['child'] ] = $value['parent'];

        $i = 1;
        foreach ($stores as $key => $value) {
            echo $i++ . "\n";

            $sql = "UPDATE store SET store.d_id = ? WHERE store.id = ?";
            $db->query($sql, array($sub_arr[ $value['d_id'] ], $value['id']));
        }

        exit;
    }

    public function getTimingPriceAction()
    {
        $QPrice = new Application_Model_GoodPriceLog();

        $db = Zend_Registry::get('db');
        $sql = "SELECT COUNT(ts.id) AS total
                FROM timing t
                INNER JOIN timing_sale ts ON t.id=ts.timing_id
                WHERE t.`from` >= '2014-09-01 00:00:00'";

        $stmt = $db->query($sql);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        $total = $stmt->fetchAll();
        $total = $total[0][0];

        $sql = "SELECT ts.id AS tsid, t.`from`, ts.product_id
                FROM timing t
                INNER JOIN timing_sale ts ON t.id=ts.timing_id
                WHERE t.`from` >= '2014-09-01 00:00:00'
                -- AND (ts.price IS NULL OR ts.price = 0)";

        $timing_without_price = $db->query($sql);

        $i = 1;

        if ($timing_without_price) {
            foreach ($timing_without_price as $key => $value) {
                My_Cli_Util::show_status($i++, $total);

                $price = $QPrice->get_price($value['product_id'], strtotime(date('Y-m-d', strtotime($value['from']))));

                // if ($price) {
                    $sql = "UPDATE timing_sale SET price = ? WHERE id = ?";
                    $db->query($sql, array(intval($price), $value['tsid']));
                // }
            }
        }

        exit;
    }

    public function updateTimingPriceAction()
    {
        echo "Start Update Timing Price...\r\n";
        
        $db = Zend_Registry::get('db');
        $sql = "UPDATE timing,
                 timing_sale
                SET timing_sale.price = fn_get_price(
                    timing_sale.product_id,
                    timing_sale.model_id,
                    DATE(timing.`from`)
                )
                WHERE
                    timing.id = timing_sale.timing_id
                AND timing.`from` >= '2015-02-01 00:00:00';";

        $db->query($sql);

        echo "Done.\r\n";

        exit;
    }

    public function getTimingPriceTestAction()
    {
        $product_id = $this->getRequest()->getParam('product_id');
        $from = $this->getRequest()->getParam('from');

        $QPrice = new Application_Model_GoodPriceLog();
        $price = $QPrice->get_price($product_id, strtotime(date('Y-m-d', strtotime($from))));
        echo $price."\r\n";
        exit;
    }

    public function refStoreLeaderLogAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $db = Zend_Registry::get('db');
        $sql = "SELECT COUNT(id) AS total
                FROM store_leader_log 
                WHERE (parent IS NULL OR parent = 0)
                AND released_at IS NULL";

        $stmt = $db->query($sql);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        $total = $stmt->fetchAll();
        $total = $total[0][0];

        $sql = "SELECT id, staff_id, store_id 
                FROM store_leader_log 
                WHERE (parent IS NULL OR parent = 0)
                AND released_at IS NULL";
        $null_parent_logs = $db->query($sql);

        $i = 1;
        foreach ($null_parent_logs as $key => $value) {
            My_Cli_Util::show_status($i++, $total);

            $sql = "SELECT id FROM store_leader 
                    WHERE staff_id = ? 
                    AND store_id = ?";

            $log = $db->query($sql, array($value['staff_id'], $value['store_id']));
            $log = $log->fetch();

            if ($log) {
                $sql = "UPDATE store_leader_log SET parent = ? WHERE id = ?";
                $db->query($sql, array($log['id'], $value['id']));
            }
        }

        exit;
    }
}