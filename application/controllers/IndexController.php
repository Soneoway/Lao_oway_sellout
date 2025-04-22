<?php

class IndexController extends My_Controller_Action
{

    public function init()
    {

    }

    /**
     * Dashboard
     */
    public function indexAction()
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');
        
        // lấy thông tin user đăng nhập
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QDashboard = new Application_Model_Dashboard();
        $group_id = $userStorage->group_id;
        $user_id = $userStorage->id;

        // if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
        if (in_array($group_id, array(
            ADMINISTRATOR_ID,
            CHECKTIMING_ID,
            46,
            HR_ID,
            HR_EXT_ID,
            HR_RECRUITMENT,
            BOARD_ID,
            SALES_ADMIN_ID)))
            
        {

            // lọc dashboard theo user id
            $where_1 = $QDashboard->getAdapter()->quoteInto('FIND_IN_SET(?, staff_ids)', $userStorage->
                id);

            // trường hợp user này đc thêm vào nhóm rồi thì lọc thêm theo group
            if (isset($userStorage->group_id))
            {
                $where_2 = $QDashboard->getAdapter()->quoteInto('FIND_IN_SET(?, group_ids)', $userStorage->
                    group_id);
                $dashboards = $QDashboard->fetchAll(' ( ' . $where_1 . ' OR ' . $where_2 .
                    ' ) AND status = 1');

            } else
            {
                $dashboards = $QDashboard->fetchAll($where_1 . '  AND status = 1');
            }

            /**
             * các ô dashboard
             * @var array
             */
            $regions = array();

            $QStaff = new Application_Model_Staff();
            $QReligion = new Application_Model_Religion();
            $QTeam = new Application_Model_Team();
            $QNationality = new Application_Model_Nationality();
            $QArea = new Application_Model_Area();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $QGroup = new Application_Model_Group();
            $QContractTerm = new Application_Model_ContractTerm();
            $QContractType = new Application_Model_ContractType();
            $QStaffLog = new Application_Model_StaffLog();
            $QStaff = new Application_Model_Staff();

            $QTiming = new Application_Model_Timing();
            $chartTimingMonth = $QTiming->chart_timing_month();
            $chartTimingWeek = $QTiming->chart_timing_week();
            $chartTimingDay = $QTiming->chart_timing_day();
            $this->view->chartTimingMonth = $chartTimingMonth;
            $this->view->chartTimingWeek = $chartTimingWeek;
            $this->view->chartTimingDay = $chartTimingDay;

            $this->view->staffs = $QStaff->get_cache();
            $this->view->religion = $QReligion->get_cache();
            $this->view->team = $QTeam->get_cache();
            $this->view->nationality = $QNationality->get_cache();
            $this->view->area = $QArea->get_cache();
            $this->view->regionalMarket = $QRegionalMarket->get_cache_all();
            $this->view->group = $QGroup->get_cache();
            $this->view->contractTerm = $QContractTerm->get_cache();
            $this->view->contractType = $QContractType->get_cache();
            

            //  Lấy danh sách các staff theo điều kiện riêng ở mỗi dasboard
            foreach ($dashboards as $key => $value)
            {
                $cols_existed = explode(',', $value['cols_existed']);
                $cols_needed = explode(',', $value['cols_needed']);

                if ($value['table'] == 'recent')
                {
                    $page = $this->getRequest()->getParam('page', 1);
                    $limit = 10;
                    $total = 0;
                    $params = array();
                    $log_res = $QStaffLog->fetchPagination($page, $limit, $total, $params);
                    $logs = array();

                    foreach ($log_res as $k => $log)
                    {
                        $logs[] = array(
                            'time' => $log['time'],
                            'user_id' => $log['user_id'],
                            'ip' => $log['ip_address'],
                            'before' => unserialize($log['before']),
                            'after' => unserialize($log['after']),
                            'diffs' => array(),
                            'type' => $log['type'],
                            'object' => $log['object'],
                        );
                    }

                    $n = count($logs);

                    for ($i = 0; $i < $n; $i++)
                    {
                        $logs[$i]['diffs'] = array_diff_assoc($logs[$i]['after'], $logs[$i]['before']);
                    }

                    $regions[$value['id']] = $logs;
                } elseif ($value['table'] == 'off')
                {
                    $where = array();
                    $where[] = $QStaff->getAdapter()->quoteInto('off_date IS NOT NULL AND off_date <> 0 AND off_date <> \'\'',
                        1);
                    $where[] = $QStaff->getAdapter()->quoteInto('old_email IS NOT NULL', 1);
                    $regions[$value['id']] = $QStaff->fetchAll($where);
                }
                elseif ($value['table'] == 'staff_transfer')
                {
                    $this->view->staff_print_contract = 1;
                }

                else
                {
                    $regions[$value['id']] = $QStaff->get_by_step($cols_existed, $cols_needed, $value['table']);
                }

            }

            // bỏ các staff thuộc danh sách dismiss
            $QDismiss = new Application_Model_DashboardDismiss();
            // $where = $QDismiss->getAdapter()->quoteInto('user_id = ?', $userStorage->id);
            $dismisses = $QDismiss->fetchAll();

            /**
             * Danh sách staff dismiss theo dashboard
             * @var array
             */
            $dismiss = array();

            foreach ($dismisses as $key => $value)
            {
                if (!isset($dismiss[$value['dashboard_id']]))
                {
                    $dismiss[$value['dashboard_id']] = array();
                }

                $dismiss[$value['dashboard_id']][] = $value['staff_id'];
            }

            $this->view->dashboards = $dashboards;
            $this->view->dismiss = $dismiss;
            $this->view->regions = $regions;
            $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER') ? $this->
            getRequest()->getServer('HTTP_REFERER') : HOST;
        } else
        {
            //load dashboard cho nhân viên
            $QModel = new Application_Model_Inform();
            $QStaff = new Application_Model_Staff();
            $QTime = new Application_Model_Time();
            $QTiming = new Application_Model_Timing();
            $QTimeOffAdd = new Application_Model_OffDateAdd();
            $QDepartment = new Application_Model_Department();
            $QContractTerm = new Application_Model_ContractTerm();
            $QNofitfication = new Application_Model_Notification();
            $QOffHistory = new Application_Model_OffHistory();

            $StaffRowSet = $QStaff->find($user_id);
            $staff = $StaffRowSet->current();


            $QArea = new Application_Model_Area();
            $this->view->areas = $QArea->get_cache();

            $month = date('m');

            $QRegionalMarket = new Application_Model_RegionalMarket();
            $this->view->all_province_cache = $QRegionalMarket->get_cache();

            //get area & province
            $rowset = $QRegionalMarket->find($staff->regional_market);

            if ($rowset)
            {
                $this->view->regional_market = $regional_market = $rowset->current();
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $regional_market['area_id']);

                $this->view->regional_markets = $QRegionalMarket->fetchAll($where);

                $rowset = $QArea->find($regional_market['area_id']);
                $this->view->area = $rowset->current();
            }

            $day = 0;
            $params = array('user_id' => $user_id, 'month' => $month);

            if ($staff['title'] == PGPB_TITLE)
            {
                $day = $QTiming->getDay($params);
            } else
            {
                $day = $QTime->getDayDashboard($params);
            }

            $sabbatical = $QTimeOffAdd->getTotalOff($user_id);

            // Range Date [Current]
            $first_day_this_month = date('Y-m-01');
            $last_day_this_month = date('Y-m-t');

            // Range Date [Last 1 Month]
            $tmp_start_01 = new DateTime( $first_day_this_month );
            $tmp_start_01->modify( 'first day of previous month' );
            $first_day_last_month = $tmp_start_01->format( 'Y-m-d' );

            $tmp_end_01 = new DateTime( $first_day_this_month );
            $tmp_end_01->modify( 'last day of previous month' );
            $last_day_last_month = $tmp_end_01->format( 'Y-m-d' );

            $QGood = new Application_Model_Good();
            $this->view->goods = $QGood->get_cache();
            $QGoodColor = new Application_Model_GoodColor();
            $this->view->good_colors = $QGoodColor->get_cache();

            $this->view->contract_term = $QContractTerm->get_cache();
            $department = $QDepartment->get_cache();
            $params = array(
                'staff_id' => $userStorage->id,
                'filter' => true,
                'status' => 1,
            );

            $store_list = My_Staff::getOwnStores($userStorage->id, $userStorage->group_id);
            $list_imei_expired = My_Staff::getImeiExpired($userStorage->id);
            $store_list_array = array();
            $pc_list_array = array();
            $pcdb_list_array = array();

            if ($userStorage->group_id == PGPB_ID) {

                foreach ($store_list as $k => $v)
                {
                    $store_list_array[$k]['store_id']   = $v['store_id'];
                    $store_list_array[$k]['store_code'] = $v['store_code'];
                    $store_list_array[$k]['store_name'] = $v['store_name'];
                    $store_list_array[$k]['d_id']       = $v['d_id'];
                    $store_list_array[$k]['d_name']     = $v['d_name'];
                    $store_list_array[$k]['area_id']    = $v['area_id'];
                    
                    $store_list_array[$k]['pc_target']          = $v['pc_target'];
                    $store_list_array[$k]['pc_target_hero']     = $v['pc_target_hero'];

                    $store_list_array[$k]['Target_Store_PC']          = $v['Target_Store_PC'];
                    $store_list_array[$k]['Hero_Store_PC']     = $v['Hero_Store_PC'];
                    $store_list_array[$k]['Target_Store_PC2']          = $v['Target_Store_PC2'];
                    $store_list_array[$k]['Hero_Store_PC2']     = $v['Hero_Store_PC2'];


                    $store_list_array[$k]['pc']     = $v['pc'];
                    
                    $store_list_array[$k]['sale_target']        = $v['sale_target'];
                    $store_list_array[$k]['sale_target_hero']   = $v['sale_target_hero'];
                    
                }

            } else {

                foreach ($store_list as $k => $v)
                {
                    $store_list_array[$k]['store_id']   = $v['store_id'];
                    $store_list_array[$k]['store_code'] = $v['store_code'];
                    $store_list_array[$k]['store_name'] = $v['store_name'];
                    $store_list_array[$k]['d_code']       = $v['d_code'];
                    $store_list_array[$k]['d_id']       = $v['d_id'];
                    $store_list_array[$k]['d_name']     = $v['d_name'];
                    $store_list_array[$k]['area_id']    = $v['area_id'];
                    
                    $store_list_array[$k]['pcdb_target']          = $v['pcdb_target'];
                    $store_list_array[$k]['pcdb_target_hero']     = $v['pcdb_target_hero'];
                    $store_list_array[$k]['pcdb_target_last']          = $v['pcdb_target_last'];
                    $store_list_array[$k]['pcdb_target_hero_last']     = $v['pcdb_target_hero_last'];

                    $store_list_array[$k]['sale_target']        = $v['sale_target'];
                    $store_list_array[$k]['sale_target_hero']   = $v['sale_target_hero'];
                    
                    $store_list_array[$k]['pc_target_next']         = $v['pc_target_next'];
                    $store_list_array[$k]['pc_target_hero_next']    = $v['pc_target_hero_next'];
                    $store_list_array[$k]['sale_target_next']       = $v['sale_target_next'];
                    $store_list_array[$k]['sale_target_hero_next']  = $v['sale_target_hero_next'];
                }

                $pc_list = My_Staff::getOwnPC($userStorage->id, $userStorage->group_id);

                foreach ($pc_list as $k => $v)
                {
                    $pc_list_array[$k]['staff_id']      = $v['staff_id'];
                    $pc_list_array[$k]['staff_code']    = $v['staff_code'];
                    $pc_list_array[$k]['staff_name']    = $v['staff_name'];
                    $pc_list_array[$k]['pc_stand_by']   = $v['pc_stand_by'];
                    $pc_list_array[$k]['sellout']       = $v['sellout'];
                    
                    $pc_list_array[$k]['pc_target']          = $v['pc_target'];
                    $pc_list_array[$k]['pc_target_hero']     = $v['pc_target_hero'];
                }

                $pcdb_list = My_Staff::getOwnPCDB($userStorage->id, $userStorage->group_id);

                foreach($pcdb_list as $k => $v) 
                {
                    $pcdb_list_array[$k]['staff_id']      = $v['staff_id'];
                    $pcdb_list_array[$k]['staff_code']    = $v['staff_code'];
                    $pcdb_list_array[$k]['staff_name']    = $v['staff_name'];
                    $pcdb_list_array[$k]['sellout']       = $v['sellout'];
                    
                    $pcdb_list_array[$k]['pcdb_target']          = $v['pcdb_target'];
                    $pcdb_list_array[$k]['pcdb_target_hero']     = $v['pcdb_target_hero'];
                }

            }
            

            // Sellot By PC : MTD + Last Month 
            if(in_array($userStorage->group_id,array(PGPB_ID,PCDB_ID)))  {
                $sell_out = My_Kpi::fetchGrid($userStorage->id, $first_day_this_month, $last_day_this_month);
                $sell_out_last1 = My_Kpi::fetchGrid($userStorage->id, $first_day_last_month, $last_day_last_month);
            }

            // Sellout By Shop for AM
            $sellout_am_array = array();
            if ($userStorage->group_id == AM_ID && !in_array($userStorage->id, array(16545,15570,28394,2131,7684,2107))) {
                $sellout_am_array = $QTiming->getSelloutByShopAM($userStorage->id);
                $this->view->store_am = $sellout_am_array;
            }

            if ($userStorage->group_id == SALES_ID) {

                // Sale Order Info
                // $QMarket = new Application_Model_Market();
                
                $params2 = array(
                    'staff_id'  => $userStorage->id,
                    'staff_code'=> $userStorage->code,
                    'from'      => $first_day_this_month,
                    'to'        => $last_day_this_month
                );
/*
                //print_r($params2); die;
                $this->view->so_list = $QMarket->getSOListBySale($params2);
                
                $params3 = array(
                    'staff_id'  => $userStorage->id,
                    'staff_code'=> $userStorage->code,
                    'from'      => $first_day_last_month,
                    'to'        => $last_day_last_month
                );

                //print_r($params3); die;
                $this->view->so_list_last = $QMarket->getSOListBySale($params3);

                // Sellin Commission for Sale 
                $QGoodKpiLog = new Application_Model_GoodKpiLog();
                
                $params2['dash_sale'] = 1;
                $params3['dash_sale'] = 1;

                $this->view->sellin = $QGoodKpiLog->sellin_com_sale($params2);
                $this->view->sellin_last = $QGoodKpiLog->sellin_com_sale($params3);

                unset($params2['dash_sale']);
                unset($params3['dash_sale']);
*/
            }

            $params['group_cat'] = 1;
            $params['filter_display'] = 1;
            $page = 1;
            $limit = 5;
            $total = 0;
            $inform = $QModel->fetchPagination($page, $limit, $total, $params);

            $notifications = $QNofitfication->fetchPagination($page, $limit, $total, $params);

            $params = array('name' => $user_id);

            $off = $QOffHistory->fetchPagination($page, $limit, $total, $params);
            $QOppoAreaTarget = new Application_Model_OppoAreaTarget();
            $hero = $QOppoAreaTarget->getheroproduct();

            if (isset($off) and $off)
            {
                $this->view->off = $off;
            }
            
            $this->view->params2 = $params2;
            $this->view->list_expired = $list_imei_expired;
            $this->view->store_list = $store_list_array;
            $this->view->pc_list = $pc_list_array;
            $this->view->pcdb_list = $pcdb_list_array;
            $this->view->hero = $hero;

            $this->view->notifications = $notifications;
            $this->view->sellout = $sell_out;
            $this->view->sellout_last1 = $sell_out_last1;
            $this->view->sabbatical = $sabbatical;
            $this->view->day = $day;
            $this->view->department = $department;
            $this->view->staff = $staff;
            $this->view->informs = $inform;
            $this->_helper->viewRenderer('index/dashboard', null, true);
            $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER') ? $this->
            getRequest()->getServer('HTTP_REFERER') : HOST;
        }

    }

    public function mobileNotificationJsonAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $staff_id = $this->getRequest()->getParam('staff_id');
        $staff_code = $this->getRequest()->getParam('staff_code');

        $params = array(
            'staff_id' => $staff_id,
            'staff_code' => $staff_code,
            'filter' => true,
            'status' => 1,
        );

        if ($staff_id || $staff_code) {
            $QNofitfication = new Application_Model_Notification();
            $result = $QNofitfication->getNotificationMobile($params);

//            echo "<pre>"; print_r($result); echo "</pre>";
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }
    }
}
