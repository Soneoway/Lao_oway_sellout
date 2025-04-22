<?php

class TimeController extends My_Controller_Action
{

    // Timing All Brand
    public function staffCheckInReportAction()
    {
        require_once 'time'.DIRECTORY_SEPARATOR.'staff-check-in-report'.DIRECTORY_SEPARATOR.'staff-check-in-report.php';
    }
    public function staffCheckInReportDetailAction()
    {
        require_once 'time'.DIRECTORY_SEPARATOR.'staff-check-in-report'.DIRECTORY_SEPARATOR.'staff-check-in-detail.php';
    }
    public function staffCheckInReportPrintAction()
    {
        require_once 'time'.DIRECTORY_SEPARATOR.'staff-check-in-report'.DIRECTORY_SEPARATOR.'staff-check-in-print.php';
    }
    public function staffCheckInReportMapAction()
    {
        require_once 'time'.DIRECTORY_SEPARATOR.'staff-check-in-report'.DIRECTORY_SEPARATOR.'staff-check-in-map.php';
    }


    public function init()
    {
         $userStorage = Zend_Auth::getInstance()->getStorage()->read();
         $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
        $where = array();
         $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ?' , $userStorage->id );
        $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
        $result  = $QTimeStaffExpired->fetchRow($where);
        if($result)
         {
             $this->redirect('user/lock');
         }
    }


    public function indexAction()
    {
        
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $code = $this->getRequest()->getParam('code');
        $department = $this->getRequest()->getParam('department');
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date = $this->getRequest()->getParam('to_date', date('t/m/Y'));
        $team = $this->getRequest()->getParam('team');
        $area_id = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district = $this->getRequest()->getParam('district');
        $sales_team = $this->getRequest()->getParam('sales_team');
        $team = $this->getRequest()->getParam('team');
        $store = $this->getRequest()->getParam('store');
        $imei = $this->getRequest()->getParam('imei');
        $email = $this->getRequest()->getParam('email');
        $off = $this->getRequest()->getParam('off');
        $sort = $this->getRequest()->getParam('sort', 'p.id');
        $desc = $this->getRequest()->getParam('desc', 1);
        $this->view->desc = $desc;
        $this->view->current_col = $sort;
        $limit = LIMITATION;
        $total = 0;

        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'from_date' => $from_date,
            'team' => $team,
            'to_date' => $to_date,
            'team' => $team,
            'area_id' => $area_id,
            'regional_market' => $regional_market,
            'district' => $district,
            'sales_team' => $sales_team,
            'store' => $store,
            'imei' => $imei,
            'email' => $email,
            'off' => $off,
            'code' => $code,
            ));

        $params['sort'] = $sort;
        $params['desc'] = $desc;

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;

        if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
            ADMINISTRATOR_ID,
            HR_ID,
            HR_EXT_ID,
            BOARD_ID)))
        {
        } elseif ($group_id == PGPB_ID)
            $params['staff_id'] = $user_id;

        elseif ($group_id == SALES_ID)
            $params['sale'] = $user_id;

        elseif ($group_id == ASM_ID)
            $params['asm'] = $user_id;

        elseif ($group_id == ASMSTANDBY_ID)
            $params['asm_standby'] = $user_id;

        elseif ($group_id == ACCESSORIES_ID)
            $params['other'] = $user_id;

        elseif ($group_id == TRAINING_TEAM_ID)
            $params['other'] = $user_id;

        elseif ($group_id == TRADE_MARKETING_GROUP_ID)
            $params['other'] = $user_id;

        elseif ($group_id == DIGITAL_ID)
            $params['other'] = $user_id;

        elseif ($group_id == SERVICE_ID)
            $params['other'] = $user_id;

        //set quyen cho hong nhung trainer
        elseif ($user_id == 2857 || $user_id == 240)
            $params['other'] = $user_id;
        //chi van ha noi
        elseif ($user_id == 95)
            $params['other'] = $user_id;

        elseif ($group_id == 22)
            $params['other'] = $user_id;
        elseif ($user_id == 3601 || $user_id == 2768)
            $params['asm'] = $user_id;

        else
            $params['sale'] = $user_id;


        $QTiming = new Application_Model_Time();

        $QTimes = $QTiming->fetchPagination($page, $limit, $total, $params);

        $month = date('m');

        $params_array = array(
            'user_id' => $user_id,
            'month' => $month,
        );

        $day = $QTiming->getDayDashboard($params_array);

        unset($params['sale']);
        unset($params['asm']);
        unset($params['staff_id']);

        $this->view->timings = $QTimes;
        $this->view->day = $day;


        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time' . ($params ? '?' . http_build_query($params) .
            '&' : '?');
        $this->view->offset = $limit * ($page - 1);

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $this->view->regional_market_all = $QRegionalMarket->get_cache();

        $QTeam = new Application_Model_Team();
        $this->view->team = $QTeam->get_cache_team();
        if ($area_id)
        {
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }

        if ($regional_market)
        {
            //get district
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }


        if ($district)
        {
            //get store
            $where = array();
            $where[] = $QStore->getAdapter()->quoteInto('district = ?', $district);
            $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);

            $this->view->stores = $QStore->fetchAll($where, 'name');
        }

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;

        //get product count
        $staff_ids = array();

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender('partials/index');
        }

    }

    public function testAction()
    {
        $db = Zend_Registry::get('db');
        $QTime = new Application_Model_Time();
        $QStaff = new Application_Model_Staff();
        $staff_cache = $QStaff->get_cache();
        $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
        $QLog = new Application_Model_Log();
        $category_id = 6;
        // tìm các chấm công chưa chấm
        $sql = "SELECT
                        p.staff_id,
                        CONCAT(
                            s.`firstname`,
                            ' ',
                            s.`lastname`

                        ),
                        s.title,
                        select_limited_timing(s.id)AS limited
                    FROM
                        time AS p
                    INNER JOIN staff AS s ON p.staff_id = s.id
                    WHERE
                        (
                            p.created_at BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01')
                            AND NOW()
                        )
                        AND s.title NOT IN (75,162,164,165,166,183,190)
                    group by p.staff_id
				";

        $data = $db->fetchAll($sql);


        foreach($data as $k=>$v)
        {
            $limited_timing = $QTime->getLimitedTime($v['staff_id']);
            if($v['limited'] < $limited_timing - 2)
            {
                $where = array();
                $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ? ' , $v['staff_id']);
                $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
                $result = $QTimeStaffExpired->fetchRow($where);

                if($result)
                {
                    break;
                }

                $title = sprintf("Tài khoản chấm công của bạn %s đã bị khóa", $staff_cache[ $v['staff_id'] ]);
                $content = sprintf('<p>Chào bạn %s,</p>
                    <p>Theo chính sách của Công ty, Bạn đã không chấm công ba ngày , hệ thống sẽ khóa tài khoản của bạn.</p>
                    <p>Để sử dụng lại tài khoản vui lòng liên hệ trưởng bộ phận: </p>
                    <p>List cụ thể được set quyền mở khóa, add time, bổ sung ngày nghỉ hàng ngày ở các bộ phận như sau:</p>
                    <table class="MsoNormalTable" border="0" cellspacing="0" cellpadding="0" width="456" style="width:4.75in;margin-left:79.1pt;border-collapse:collapse"><tbody><tr style="height:21.0pt"><td width="191" nowrap="" valign="bottom" style="width:143.0pt;border:solid windowtext 1.0pt;background:#92D050;padding:0in 5.4pt 0in 5.4pt;height:21.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:16.0pt;color:black">BỘ PHẬN</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border:solid windowtext 1.0pt;border-left:none;background:#92D050;padding:0in 5.4pt 0in 5.4pt;height:21.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="font-size:16.0pt;color:black">EMAIL</span></b></p></td></tr><tr style="height:15.0pt"><td width="191" rowspan="2" style="width:143.0pt;border-top:none;border-left:solid windowtext 1.0pt;border-bottom:solid black 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">SERVICE</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:trinh.hy@oppomobile.vn" target="_blank">trinh.hy@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:phuong.ly@oppomobile.vn" target="_blank">phuong.ly@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" rowspan="2" style="width:143.0pt;border-top:none;border-left:solid windowtext 1.0pt;border-bottom:solid black 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Kho</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:thanhhoa.huynh@oppomobile.vn" target="_blank">thanhhoa.huynh@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:duy.tran@oppomobile.vn" target="_blank">duy.tran@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" nowrap="" style="width:143.0pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Kế toán Hà Nội</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:van.vu@oppomobile.vn" target="_blank">van.vu@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" nowrap="" style="width:143.0pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">kế toán Đà Nẵng</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:bichha.dang@oppomobile.vn" target="_blank">bichha.dang@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" rowspan="2" style="width:143.0pt;border-top:none;border-left:solid windowtext 1.0pt;border-bottom:solid black 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Training</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:hongnhung.truong@oppomobile.vn" target="_blank">hongnhung.truong@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:minhtam.nguyen@oppomobile.vn" target="_blank">minhtam.nguyen@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" nowrap="" style="width:143.0pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Phụ Kiện</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:thucanh.tran@oppomobile.vn" target="_blank">thucanh.tran@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" nowrap="" style="width:143.0pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Digital</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:huynhngoc.nguyen@oppomobile.vn" target="_blank">huynhngoc.nguyen@oppomobile.vn </a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" nowrap="" style="width:143.0pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Trade Marketing</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:trong.nguyen@oppomobile.vn" target="_blank">trong.nguyen@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="191" rowspan="3" style="width:143.0pt;border-top:none;border-left:solid windowtext 1.0pt;border-bottom:solid black 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal" align="center" style="text-align:center"><b><span style="color:black">Tất cả bộ phận</span></b></p></td><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:huycuong.nguyen@oppomobile.vn" target="_blank">huycuong.nguyen@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:yen.dao@oppomobile.vn" target="_blank">yen.dao@oppomobile.vn</a></span></u></p></td></tr><tr style="height:15.0pt"><td width="265" nowrap="" valign="bottom" style="width:199.0pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:15.0pt"><p class="MsoNormal"><u><span style="color:blue"><a href="mailto:phuongngan.tran@oppomobile.vn" target="_blank">phuongngan.tran@oppomobile.vn</a></span></u></p></td></tr></tbody></table>

                    ' , $staff_cache[ $v['staff_id'] ] );

                My_Notification::add(
                    $title,
                    $content,
                    $category_id,
                    array(
                        'staff' => $v['staff_id'],
                        'from'  => date('d/m/Y H:i:s'),
                        'to'    => (new DateTime('now'))
                            ->add(new DateInterval('P10D'))
                            ->format('d/m/Y H:i:s'),
                    ),
                    1,
                    My_Notification_Type::SystemCreate
                );

                //them nhan vien do vo bang LOG
                $data = array(
                    'staff_id' => $v['staff_id'],
                    'locked_at' => date('Y-m-d h:i:s')
                );

                $QTimeStaffExpired->insert($data);
            }
        }
    }

    public function monthAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $code = $this->getRequest()->getParam('code');
        $department = $this->getRequest()->getParam('department');
        $month = $this->getRequest()->getParam('month', date('n'));
        $team = $this->getRequest()->getParam('team');
        $area_id = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district = $this->getRequest()->getParam('district');
        $sales_team = $this->getRequest()->getParam('sales_team');
        $from = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export_time = $this->getRequest()->getParam('export_time', 0);
        $store = $this->getRequest()->getParam('store');
        $imei = $this->getRequest()->getParam('imei');
        $email = $this->getRequest()->getParam('email');
        $sort = $this->getRequest()->getParam('sort', 'p.id');
        $desc = $this->getRequest()->getParam('desc', 1);
        $department = $this->getRequest()->getParam('department');
        $title = $this->getRequest()->getParam('title');
        $asm = $this->getRequest()->getParam('asm');
        $QStore = new Application_Model_Store();
        $this->view->desc = $desc;
        $this->view->current_col = $sort;
        $limit = LIMITATION;
        $total = 0;


        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'team' => $team,
            'area_id' => $area_id,
            'regional_market' => $regional_market,
            'district' => $district,
            'sales_team' => $sales_team,
            'store' => $store,
            'imei' => $imei,
            'email' => $email,
            'from' => $from,
            'to' => $to,
            'code' => $code,
            'title' => $title,
            'department' => $department,
            'asm' => $asm,
            ));

        $params['sort'] = $sort;
        $params['desc'] = $desc;
        $params['month'] = $month;

        if (isset($export_time) && $export_time == 1)
        {
            //  if (!in_array($group_id, array(
            //                HR_ID,
            //                ADMINISTRATOR_ID,
            //                BOARD_ID,
            //                SALES_EXT_ID)))
            //                $this->_redirect(HOST . 'time/month');

            $this->_exportExcel_time_by_month($params);
            exit;
        }


        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;

        if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
            ADMINISTRATOR_ID,
            HR_ID,
            HR_EXT_ID,
            BOARD_ID)))
        {
        } elseif ($group_id == PGPB_ID)
            $params['staff_id'] = $user_id;

        elseif ($group_id == SALES_ID)
            $params['sale'] = $user_id;


        elseif ($group_id == ASM_ID)
            $params['asm'] = $user_id;

        elseif ($group_id == ASMSTANDBY_ID)
            $params['asm_standby'] = $user_id;

        elseif ($group_id == ACCESSORIES_ID)
            $params['other'] = $user_id;

        elseif ($group_id == TRAINING_TEAM_ID)
            $params['other'] = $user_id;

        elseif ($group_id == DIGITAL_ID)
            $params['other'] = $user_id;

        elseif ($group_id == SERVICE_ID)
            $params['other'] = $user_id;

        elseif ($group_id == TRADE_MARKETING_GROUP_ID)
            $params['other'] = $user_id;


        //set quyen cho hong nhung trainer
        elseif ($user_id == 2857 || $user_id == 240)
            $params['other'] = $user_id;
        //chi van ha noi
        elseif ($user_id == 95)
            $params['other'] = $user_id;

        elseif ($group_id == 22)
            $params['other'] = $user_id;
        elseif ($user_id == 3601 || $user_id == 2768)
            $params['asm'] = $user_id;

        else
            $params['sale'] = $user_id;

        $QTiming = new Application_Model_Time();
        $QTimeNorm = new Application_Model_DateNorm();
        $date_norm = $QTimeNorm->get_cache();

        $QTimes = $QTiming->fetchPagination($page, $limit, $total, $params);

        unset($params['sale']);
        unset($params['asm']);
        unset($params['staff_id']);

        $data = array();
        $data = $QTimes;
        foreach ($QTimes as $k => $v)
        {
            $data[$k]['date_approved'] = $QTiming->getDay($v['staff_id'], $month);
        }
        $this->view->date_norm = $date_norm;
        $this->view->month = $month;
        $this->view->timings = $data;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time/month' . ($params ? '?' . http_build_query($params) .
            '&' : '?');
        $this->view->offset = $limit * ($page - 1);

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $this->view->regional_market_all = $QRegionalMarket->get_cache();

        $QTeam = new Application_Model_Team();
        $this->view->team = $QTeam->get_cache_team();

        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();

        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;


        if ($area_id)
        {
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id in (?)', $area_id);
            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }


        if ($regional_market)
        {
            //get district
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }


        if ($district)
        {
            //get store
            $where = array();
            $where[] = $QStore->getAdapter()->quoteInto('district = ?', $district);
            $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);

            $this->view->stores = $QStore->fetchAll($where, 'name');
        }


        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;

        //get product count
        $staff_ids = array();

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender('partials/index');
        }
    }

    public function createAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $flashMessenger = $this->_helper->flashMessenger;

        //get staff info
        $QStaff = new Application_Model_Staff();
        $where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
        $this->view->staff = $staff = $QStaff->fetchRow($where);

        $QDate = new Application_Model_DateNorm();
        $date = $QDate->get_cache();
        $this->view->date = $date;

        $QTime = new Application_Model_Time();
        $where = array();
        $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff['id']);
        $where[] = $QTime->getAdapter()->quoteInto('DATE(created_at) = CURDATE()', '');
        $time = $QTime->fetchRow($where);

        if (isset($time) and $time)
        {
            $this->view->time = 1;
        }

        $this->view->user_id = $userStorage->id;
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;
    }

    public function createLeaveAction()
    {

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $flashMessenger = $this->_helper->flashMessenger;

        //get staff info
        $QStaff = new Application_Model_Staff();
        $where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
        $this->view->staff = $staff = $QStaff->fetchRow($where);



        $this->view->user_id = $userStorage->id;
        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;
    }

    public function createMonthAction()
    {

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $flashMessenger = $this->_helper->flashMessenger;
        $id = $this->getRequest()->getParam('id');
        $month = $this->getRequest()->getParam('month', date('n'));

        $QTeam = new Application_Model_Team();
        $team = $QTeam->fetchAll();
        $team_data = array();
        foreach($team as $k=>$v)
        {
            $team_data[$v['id']] = $v['name'];
        }
        $this->view->team = $team_data;
        //get staff info
        $QStaff = new Application_Model_Staff();
        $where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
        $this->view->staff = $staff = $QStaff->fetchRow($where);

        $QDate = new Application_Model_DateNorm();
        $date = $QDate->get_cache();
        $this->view->date = $date;

        $QTime = new Application_Model_Time();
        $where = array();
        $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff['id']);
        $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $month);
        $time = count($QTime->fetchAll($where));

        if (isset($time) and $time)
        {
            $this->view->time = $time;
        }

        $this->view->month = $month;
        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        //office
        if(isset($staff['is_officer']) and $staff['is_officer'])
        {
            $this->_helper->viewRenderer('time/create-month-office', null, true);
        }

    }

    public function saveAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QTime = new Application_Model_Time();
        $QStaff = new Application_Model_Staff();
        $staffRowSet = $QStaff->find($userStorage->id);
        $staff = $staffRowSet->current();
        $yesterday = $this->getRequest()->getParam('yesterday');

        if ($this->getRequest()->getMethod() == 'POST')
        {

            set_time_limit(0);
            $id = $this->getRequest()->getParam('id');
            if (!$id)
            {
                echo '<script> alert("id is not valid");
                            </script>';
                exit;
            }
            $staffRowSet = $QStaff->find($id);
            $staff = $staffRowSet->current();

            if (!$staff['regional_market'])
            {
                echo '<script> alert("Not set Regional ! Please contact HR Group");
                            </script>';
                exit;
            }

            $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
            $where = array();
            $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ?' , $userStorage->id );
            $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
            $result = $QTimeStaffExpired->fetchRow($where);

            if($result) {
                echo '<script> alert("You have banned ! Please contact HR Group");
                            </script>';
                exit;
            }

            $date = date('Y-m-d H:i:s');
            if (isset($yesterday) and $yesterday)
                $date = date("Y-m-d H:i:s", strtotime($date . "- 1 days"));

            // kiem tra ngay cham cong
            if ($date)
            {
                $where = array();
                $datetime = new DateTime($date);
                $day = $datetime->format('d');
                $month = $datetime->format('m');
                $year = $datetime->format('Y');

                $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $id);
                $where[] = $QTime->getAdapter()->quoteInto('DAY(created_at) = ?', $day);
                $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $month);
                $where[] = $QTime->getAdapter()->quoteInto('YEAR(created_at) = ?', $year);
                $result = $QTime->fetchRow($where);
                if (isset($result) and $result)
                {
                    echo '<script> alert("You checked in this days! See you tomorrow.");
                            </script>';
                    echo '<script>parent.location.href="/time"</script>';
                    exit;
                }
            }


            $data = array(
                'staff_id' => $id,
                'created_at' => $date,
                'created_by' => $id,
                'regional_market' => $staff['regional_market'],
                );
            if (isset($yesterday) and $yesterday)
            {
                $data['yesterday'] = 1;
            }
            if (isset($staff) and $staff['team'] == TRAINING_TEAM)
            {

                $work = $this->getRequest()->getParam('work');
                $locale = $this->getRequest()->getParam('locale');
                $number = $this->getRequest()->getParam('number');
                $data['work'] = $work;
                $data['locale'] = $locale;
                $data['number'] = $number;

            }

            $QTime->insert($data);

            if ($id)
            {
                //update log
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $userStorage->id;
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "TIME - Check in";
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));
            }


            $back_url = $this->getRequest()->getParam('back_url');

            echo '<script>parent.location.href="/time"</script>';
            exit;

        }

    }

    public function saveLeaveAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QTime = new Application_Model_Time();
        $QStaff = new Application_Model_Staff();
        $QOHistory = new Application_Model_OffHistory();
        $QOff = new Application_Model_OffDate();
        $staffRowSet = $QStaff->find($userStorage->id);
        $staff = $staffRowSet->current();

        if ($this->getRequest()->getMethod() == 'POST')
        {

            set_time_limit(0);
            $id = $this->getRequest()->getParam('id');
            $from_date = $this->getRequest()->getParam('from_date');
            $type = $this->getRequest()->getParam('type');
            $to_date = $this->getRequest()->getParam('to_date');
            $reason = $this->getRequest()->getParam('reason');
            $shift = $this->getRequest()->getParam('shift', 0);

            $from_date = explode('/', $from_date);
            $from_date = $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0];

            $to_date = explode('/', $to_date);
            $to_date = $to_date[2] . '-' . $to_date[1] . '-' . $to_date[0];

            $startTime = strtotime($from_date);
            $endTime = strtotime($to_date);

            //        bá» chan 3 ngay
            //            if ($endTime - $startTime >= 86400 * 3) {
            //                echo '<script>alert("Maximum day is 3")</script>';
            //                echo '<script>parent.location.href="/time/create-leave"</script>';
            //                exit;
            //            }

            // Loop between date
            for ($i = $startTime; $i <= $endTime; $i = $i + 86400)
            {

                $date = date('Y-m-d', $i);

                $d = date('d', strtotime($date));
                $m = intval(date('m', strtotime($date)));
                $y = date('Y', strtotime($date));

                $data = array(
                    'staff_id' => $id,
                    'created_at' => $date,
                    'created_by' => $id,
                    'off' => 1,
                    'reason' => $reason,
                    'off_type' => $type,
                    'shift' => $shift,
                    'regional_market' => $staff['regional_market'],
                    );
                $QTime->insert($data);

                ///////////////////////
                //tru ngay phep///////
                //////////////////////

                //  $off = $QOff->checkOff($staff['code'], $m, $y);
                //
                //
                //                if (!isset($shift) and $shift != 0) {
                //                    $off = $off - 1;
                //                } else {
                //                    $off = $off - 0.5;
                //                }
                //
                //                if ($off < -1) {
                //                    echo '<script>alert("báº¡n Ä‘Ã£ xin nghá»‰ vÆ°á»£t quÃ¡ thá»i gian cho phÃ©p")</script>';
                //                    echo '<script>parent.location.href="/time/create-leave"</script>';
                //                    exit;
                //                }
                //
                //                $data = array('date' => $off, );
                //
                //                $where = array();
                //
                //                $where[] = $QOff->getAdapter()->quoteInto('staff_id = ? ', $staff['code']);
                //
                //                $QOff->update($data, $where);
                //
                //                ///////////////////////////
                //                //Them vao date history///
                //                /////////////////////////
                //                //$data = array(
                //                //  'staff_id' => $id,
                //
                //                $data = array(
                //                    'staff_id' => $staff['id'],
                //                    'month' => $m,
                //                    'date' => $date,
                //                    'reason' => $reason,
                //                    'shift' => $shift,
                //                    );
                //
                //
                //                $QOff->insert($data);


            }


            if ($id)
            {
                //update log
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $userStorage->id;
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "TIME SYSTEM - Request for leave : from " . $from_date . " ---> to : " .
                    $to_date;
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));
            }


            $back_url = $this->getRequest()->getParam('back_url');

            echo '<script>parent.location.href="' . ($back_url ? $back_url : '/time') .
                '"</script>';
            exit;

        }

    }

    public function updateNoteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $note = $this->getRequest()->getParam('note');
        $new = $note;

        if ($id)
        {
            $QTiming = new Application_Model_Time();
            $QTimeRowset = $QTiming->find($id);
            $QTime = $QTimeRowset->current();

            if ($QTime)
            {
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $QStoreStaffLog = new Application_Model_StoreStaffLog();


                if ($note)
                {
                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

                    $note = $QTime['note'] . "\n" . "* " . $userStorage->lastname . " [" . date('Y-m-d H:i:s') .
                        "] " . $note;

                    $data = array(
                        'note' => $note,
                        'note_updated' => 1,
                        'note_read' => 0,
                        );

                    $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);

                    try
                    {
                        $QTiming->update($data, $where);

                        $QLog = new Application_Model_Log();
                        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                        $info = "TIME ATTENDANCE NOTE - Update (" . $id . ") - Add Content (" . $new .
                            ")";
                        //todo log
                        $QLog->insert(array(
                            'info' => $info,
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));

                        echo '1';
                        exit;
                    }
                    catch (exception $e)
                    {
                        echo '0';
                        exit;
                    }
                } else
                {
                    $data = array(
                        'note_updated' => 0,
                        'note_read' => 1,
                        );

                    $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);

                    try
                    {
                        $QTiming->update($data, $where);
                        echo '1';
                        exit;
                    }
                    catch (exception $e)
                    {
                        echo '0';
                        exit;
                    }
                }
            }
        }

        echo '0';
        exit;
    }

    public function updateNoteOffAction()
    {
        $id = $this->getRequest()->getParam('id');
        $note = $this->getRequest()->getParam('note');
        $new = $note;

        if ($id)
        {
            $QOff = new Application_Model_OffDate();
            $QTimeRowset = $QOff->find($id);
            $QOffTime = $QTimeRowset->current();

            if ($QOffTime)
            {
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $QStoreStaffLog = new Application_Model_StoreStaffLog();


                if ($note)
                {
                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

                    $note = $QOffTime['note'] . "\n" . "* " . $userStorage->lastname . " [" . date('Y-m-d H:i:s') .
                        "] " . $note;

                    $data = array('note' => $note);

                    $where = $QOff->getAdapter()->quoteInto('id = ?', $id);

                    try
                    {
                        $QOff->update($data, $where);
                        $QLog = new Application_Model_Log();
                        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                        $info = "TIME ATTENDANCE NOTE - Update (" . $id . ") - Add Content (" . $new .
                            ")";
                        //todo log
                        $QLog->insert(array(
                            'info' => $info,
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));

                        echo '1';
                        exit;
                    }
                    catch (exception $e)
                    {
                        echo '0';
                        exit;
                    }
                }
            }
        }

        echo '0';
        exit;
    }

    public function approveMonthAction()
    {
        $id = $this->getRequest()->getParam('id');
        $month = $this->getRequest()->getParam('month', date('n'));
        try
        {
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $flashMessenger = $this->_helper->flashMessenger;
            $QTime = new Application_Model_Time();
            $QStaff = new Application_Model_Staff();
            $staff = $QStaff->get_cache();

            if (!isset($id))
            {
                $flashMessenger->setNamespace('error')->addMessage('Staff is not exist');

                $this->_redirect('/time/month');
            }

            //if (!in_array($userStorage->group_id, array(
            //                SALES_ID,
            //                LEADER_ID,
            //                ASM_ID)) && $userStorage->group_id != ADMINISTRATOR_ID && $userStorage->
            //                group_id != SUPERADMIN_ID) {
            //
            //                $flashMessenger->setNamespace('error')->addMessage('Permission Deny, please try again!');
            //
            //                $this->_redirect('/time/month');
            //            }

            $where = array();
            $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $month);
            $data = array(
                'status' => 1,
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $userStorage->id,
                );

            $QTime->update($data, $where);


            //todo log
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');

            $info = 'TIME SYSTEM - Approve Timing Month for : ' . $staff[$id] . ' - by - ' .
                $staff[$userStorage->id];

            $QLog = new Application_Model_Log();

            $QLog->insert(array(
                'info' => $info,
                'user_id' => $userStorage->id,
                'ip_address' => $ip,
                'time' => date('Y-m-d H:i:s'),
                ));

            $flashMessenger->setNamespace('success')->addMessage('Done!');

            $this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));

        }
        catch (exception $e)
        {

            $flashMessenger->setNamespace('error')->addMessage('Cannot update, please try again!');

            $this->_redirect('/time/month');
        }
    }

    public function approveAction()
    {
        $id = $this->getRequest()->getParam('id');
        try
        {
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $QPermission = new Application_Model_TimePermission();


            if (!in_array($userStorage->group_id, array(
                ASMSTANDBY_ID,
                ASM_ID,
                SALES_ADMIN_ID,
                ACCESSORIES_ID,
                SERVICE_ID)) && $userStorage->group_id != ADMINISTRATOR_ID && $userStorage->
                group_id != SUPERADMIN_ID && !$QPermission->getPermission($userStorage->id))
            {
                echo '-1';
                exit;
            }

            $QTime = new Application_Model_Time();
            $QOff = new Application_Model_OffDate();
            $QOffHistory = new Application_Model_OffHistory();
            $QStaff = new Application_Model_Staff();
            $where = $QTime->getAdapter()->quoteInto('id = ?', $id);
            $item = $QTime->fetchRow($where);
            $staffRowSet = $QStaff->find($item['staff_id']);
            $staff = $staffRowSet->current();
            $shift = $item['shift'];

            //////////////////////////
            ////giai quyet don off////
            /////////////////////////
            if ($item['off'] == 1)
            {
                $date = date('Y-m-d', strtotime($item['created_at']));
                $d = date('d', strtotime($date));
                $m = intval(date('m', strtotime($date)));
                $y = date('Y', strtotime($date));

                $off = $QOff->checkOff($staff['code'], $m, $y);
                // $m = date('m', strtotime($date));
                if (!isset($shift) and $shift != 0)
                {
                    $off = $off - 1;
                } else
                {
                    $off = $off - 0.5;
                }

                if ($off < -1)
                {
                    //neu da tru qua ngay phep cua thang do thi k tru nua
                    $off = -1;
                }

                $data = array('date' => $off, );

                $where = array();

                $where[] = $QOff->getAdapter()->quoteInto('staff_id = ? ', $staff['code']);

                $QOff->update($data, $where);
                ///luu log va ngay phep con lai cua nhan vien///


                $data = array(
                    'staff_id' => $item['staff_id'],
                    'month_id' => $m,
                    'date' => $date,
                    'type' => $item['off_type'],
                    'reason' => $item['reason'],
                    'shift' => $shift,
                    'off_date' => $off,
                    'time_id' => $id,
                    );


                $QOffHistory->insert($data);

            }


            $data = array(
                'status' => 1,
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $userStorage->id,
                );
            $where = $QTime->getAdapter()->quoteInto('id = ?', $id);
            $QTime->update($data, $where);

            $QLog = new Application_Model_Log();
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
            $info = "TIME SYSTEM - Approve (" . $id . ")";
            //todo log
            $QLog->insert(array(
                'info' => $info,
                'user_id' => $userStorage->id,
                'ip_address' => $ip,
                'time' => date('Y-m-d H:i:s'),
                ));


            echo 99;
            exit;
        }
        catch (exception $e)
        {
            echo - 1;
            exit;
        }
    }

    public function offHistoryAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QOffHistory = new Application_Model_OffHistory();
        $QOff = new Application_Model_OffDate();
        $QStaff = new Application_Model_Staff();
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date = $this->getRequest()->getParam('to_date', date('t/m/Y'));
        $email = $this->getRequest()->getParam('email');
        $off = $this->getRequest()->getParam('off');
        $sort = $this->getRequest()->getParam('sort', 'p.id');
        $desc = $this->getRequest()->getParam('desc', 1);
        $user_id = $this->getRequest()->getParam('id');
        $this->view->desc = $desc;
        $this->view->current_col = $sort;
        $limit = LIMITATION;
        $total = 0;
        $staffRowSet = $QStaff->find($user_id);
        $staff = $staffRowSet->current();
        $staff_name = $staff['firstname'] . ' ' . $staff['lastname'];
        $QOff_Decrease = new Application_Model_OffHistory();
        $QOff_Increase = new Application_Model_OffDateAdd();


        $params = array('name' => $user_id);

        $off = $QOffHistory->fetchPagination($page, $limit, $total, $params);

        $where = $QOff->getAdapter()->quoteInto('id = ? ', $user_id);
        $offday = $QOff->fetchRow($where, 'date');

        $offday = ($QOff_Increase->getDate(DAY_OFF_BEGIN, $user_id) - $QOff_Decrease->
            getDate(DAY_OFF_BEGIN, $user_id));


        $this->view->off = $off;
        $this->view->offday = $offday;
        $this->view->staff_name = $staff_name;

        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time/off-history/' . ($params ? '?' .
            http_build_query($params) . '&' : '?');
        $this->view->offset = $limit * ($page - 1);
    }

    public function dateAction()
    {
        $QDate = new Application_Model_DateNorm();
        $date = $QDate->fetchAll();
        $this->view->date = $date;
    }

    public function dateUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $date = $this->getRequest()->getParam('date');
        $id = $this->getRequest()->getParam('id');
        try
        {
            if (!$id)
                exit(json_encode(array('result' => -1, )));
            $data = array('date' => $date, );
            $QDate = new Application_Model_DateNorm();
            $where = $QDate->getAdapter()->quoteInto('id = ? ', $id);
            $QDate->update($data, $where);
            exit(json_encode(array('result' => 1, )));
        }

        catch (exception $e)
        {
            exit(json_encode(array('error' => $e->getMessage(), )));
        }
    }
    /*  - Cham cong bo sung
    - ch? dÃ nh cho ASM ASM STAND BY
    */
    public function addAction()
    {

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $QTiming = new Application_Model_Time();
        $time = $this->getRequest()->getParam('time');
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $flashMessenger = $this->_helper->flashMessenger;


        try
        {

            $QPermission = new Application_Model_TimePermission();

            if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
                ADMINISTRATOR_ID,
                HR_ID,
                HR_EXT_ID,
                BOARD_ID,
                ASM_ID,
                ASMSTANDBY_ID)))
            {
                if (in_array($userStorage->group_id, array(ASM_ID, ASMSTANDBY_ID)))
                {

                    if ($userStorage->group_id == ASM_ID)
                    {
                        $QAsm = new Application_Model_Asm();
                    } elseif ($userStorage->group_id == ASMSTANDBY_ID)
                    {
                        $QAsm = new Application_Model_Asm();
                    }

                    if (HAPPY_TIME_CHECKIN != 1)
                    {
                        $this->redirect(HOTS . 'user/noauth');
                    }

                    $region_list = $QAsm->get_cache($userStorage->id);

                    $region_list = $region_list['area'];

                    $QArea = new Application_Model_Area();

                    $QRegional_Market = new Application_Model_RegionalMarket();

                    $region_market = $QRegional_Market->get_cache();

                    $area = $QArea->get_cache();

                    $areas = array();

                    foreach ($region_list as $k => $v)
                    {
                        if (isset($area[$v]) and $area[$v])
                            $areas[$v] = $area[$v];
                    }


                    $this->view->date = date('d/m/Y');

                    $this->view->areas_all = $areas;

                    $this->_helper->viewRenderer->setRender('partials/add_asm');

                    $QTeam = new Application_Model_Team();

                    $this->view->team_all = $QTeam->get_cache();

                } else
                {

                    $QTeam = new Application_Model_Team();

                    $this->view->team_all = $QTeam->get_cache_team();

                    $this->view->date = date('d/m/Y');

                    $QArea = new Application_Model_Area();

                    $this->view->areas_all = $QArea->get_cache();
                }

            } else
            {
                $team_choose = $QPermission->getTeam($user_id);
                $this->view->team = $team_choose;
                $this->view->date = date('d/m/Y');
                $areas_choose = $QPermission->getArea($user_id);
                $this->view->areas = $areas_choose;
            }


        }
        catch (exception $ex)
        {
            $flashMessenger->setNamespace('error')->addMessage($ex->getMessage());
            $this->_redirect(HOST . 'time/');
        }
    }

    /* ch?m c?g b? sung d?h cho PG
    */
    public function addPgAction()
    {

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $time = $this->getRequest()->getParam('time');
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $flashMessenger = $this->_helper->flashMessenger;

        $QTiming = new Application_Model_Timing();
        $QTime = new Application_Model_Time();
        $QArea = new Application_Model_Area();
        $QShift = new Application_Model_Shift();
        $QStaff = new Application_Model_Staff();
        $QStoreStaff = new Application_Model_StoreStaff();
        $QPermission = new Application_Model_TimePermission();
        if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
            ADMINISTRATOR_ID,
            HR_ID,
            HR_EXT_ID,
            BOARD_ID,
            ASM_ID,
            ASMSTANDBY_ID)))
        {
            if (in_array($userStorage->group_id, array(ASM_ID, ASMSTANDBY_ID)))
            {

                if ($userStorage->group_id == ASM_ID)
                {
                    $QAsm = new Application_Model_Asm();
                } elseif ($userStorage->group_id == ASMSTANDBY_ID)
                {
                    $QAsm = new Application_Model_Asm();
                }


                $region_list = $QAsm->get_cache($userStorage->id);

                $region_list = $region_list['area'];

                $QArea = new Application_Model_Area();

                $QRegional_Market = new Application_Model_RegionalMarket();

                $region_market = $QRegional_Market->get_cache();

                $area = $QArea->get_cache();

                $areas = array();

                foreach ($region_list as $k => $v)
                {
                    if (isset($area[$v]) and $area[$v])
                        $areas[$v] = $area[$v];
                }
                $this->view->date = date('d/m/Y');

                $this->view->areas_all = $areas;

                $QTeam = new Application_Model_Team();

                $this->view->team_all = $QTeam->get_cache_team();

            } else
            {
                $team_choose = $QPermission->getTeam($user_id);
                $this->view->team = $team_choose;
                $this->view->date = date('d/m/Y');
                $areas_choose = $QPermission->getArea($user_id);
                $this->view->areas = $areas_choose;
            }}

        else
            {
                $team_choose = $QPermission->getTeam($user_id);
                $this->view->team = $team_choose;
                $this->view->date = date('d/m/Y');
                $areas_choose = $QPermission->getArea($user_id);
                $this->view->areas = $areas_choose;
            }

            if ($this->getRequest()->isPost())
            {


                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                echo '<link href="' . HOST . '/css/bootstrap.min.css" rel="stylesheet">';

                $from_date = $this->getRequest()->getParam('from_date');
                $start = $this->getRequest()->getParam('timepicker_start');
                $end = $this->getRequest()->getParam('timepicker_end');
                $staffs = $this->getRequest()->getParam('a_staffresult');
                $areas = $this->getRequest()->getParam('a_area');
                $teams = $this->getRequest()->getParam('team');
                $shift = $this->getRequest()->getParam('shift');

                if (!$shift)
                {
                    throw new Exception("Failed - Please select shift !");
                }


                $temp = explode('/', $from_date);
                $temp2 = explode(':', $start);
                $temp3 = explode(':', $end);
                $formatedFrom = $start . ':00';
                $formatedTo = $end . ':00';
                $formatedDate = (isset($temp[2]) ? $temp[2] : '1970') . '-' . (isset($temp[1]) ?
                    $temp[1] : '01') . '-' . (isset($temp[0]) ? $temp[0] : '01');

                if (!(isset($temp[2]) and intval($temp[2]) >= 2013 and intval($temp[2]) <= 2020 and
                    isset($temp[1]) and intval($temp[1]) <= 12 and intval($temp[1]) >= 1 and isset($temp[0]) and
                    intval($temp[0]) <= 31 and intval($temp[0]) >= 1 and isset($temp2[0]) and intval
                    ($temp2[0]) <= 23 and intval($temp2[0]) >= 0 and isset($temp2[1]) and intval($temp2[1]) <=
                    59 and intval($temp2[1]) >= 0 and isset($temp3[0]) and intval($temp3[0]) <= 23 and
                    intval($temp3[0]) >= 0 and isset($temp3[1]) and intval($temp3[1]) <= 59 and
                    intval($temp3[1]) >= 0))
                {

                    throw new Exception("Date format is error");
                }

                if (!$formatedDate)
                {
                    throw new Exception("Failed - Please input Date for checkin!");
                }

                if (!$staffs)
                {
                    throw new Exception("Failed - Please select at leaset !");
                }



                try
                {
                    $db = Zend_Registry::get('db');
                    $db->beginTransaction();

                    foreach ($staffs as $k => $staff_id)
                    {
                        $staff_rowset = $QStaff->find($staff_id);
                        $staff = $staff_rowset->current();

                        if (!$staff)
                        {
                            throw new Exception("Invalid staff, Please try again");
                        }

                        $datetime = new DateTime($formatedDate);
                        $day = $datetime->format('d');
                        $month = $datetime->format('m');
                        $year = $datetime->format('Y');
                        $date_timing = $formatedDate . ' ' . $formatedFrom;

                        if($staff['title'] == PGPB_TITLE)
                        {
                            $where = array();
                            $where[] = $QTiming->getAdapter()->quoteInto('shift = ?', $shift);
                            $where[] = $QTiming->getAdapter()->quoteInto('date(`from`) = ?', $formatedDate);
                            $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $staff_id);

                            //get store
                            $result = $QTiming->fetchRow($where);
                            if (isset($result) and $result)
                            {
                                throw new Exception("Checking List is exist");
                            }
                            /*
                            if(strtotime($date_timing) >= strtotime("+4 day", strtotime($staff['joined_at'])))
                            {
                                throw new Exception("Date is not correct");
                            }*/

                            $store = $QStoreStaff->getStore($staff_id);

                            if (empty($store))
                            {
                                throw new Exception("Checking shop is error");
                            }

                            $system_log = 'Add time for PG by System at ' . date('Y-m-d H:i:s');

                            $data = array(
                                'shift' => $shift,
                                'from' => $formatedDate . ' ' . $formatedFrom,
                                'to' => $formatedDate . ' ' . $formatedTo,
                                'store' => $store,
                                'status' => 1,
                                'note' => $system_log,
                                'store' => $store,
                                'approved_at' => date('Y-m-d H:i:s'),
                            );

                            $data['created_at'] = date('Y-m-d H:i:s');
                            $data['created_by'] = $userStorage->id;
                            $data['staff_id'] = $staffs[0];
                            $id = $QTiming->insert($data);
                        }

                        else
                        {

                            $where = array();
                           // $where[] = $QTime->getAdapter()->quoteInto('shift = ?', $shift);
                            $where[] = $QTime->getAdapter()->quoteInto('date(`created_at`) = ?', $formatedDate);
                            $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff_id);

                            //get store
                            $result = $QTime->fetchRow($where);
                            if (isset($result) and $result)
                            {
                                throw new Exception("Checking List is exist");
                            }

                            if(strtotime($date_timing) >= strtotime("+4 day", strtotime($staff['joined_at'])))
                            {
                                throw new Exception("Date is not correct");
                            }


                            $system_log = 'Add time for STAFF by System at ' . date('Y-m-d H:i:s');

                            $data = array(
                                'created_at' => $formatedDate ,
                                'status' => 1,
                                'note' => $system_log,
                                'approved_at' => date('Y-m-d H:i:s'),
                            );

                            $data['add_at'] = date('Y-m-d H:i:s');
                            $data['add_by'] = $userStorage->id;
                            $data['staff_id'] = $staffs[0];
                            $id = $QTime->insert($data);
                        }

                    }


                    $QLog = new Application_Model_Log();
                    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                    $info = "TIME ADD (PG) - CHECK for (" . $staff_id . ")";
                    //todo log
                    $QLog->insert(array(
                        'info' => $info,
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));

                    $db->commit();
                    echo '<script>

                     parent.alert("Add Time Successful!");
                         </script>';
                    exit;
                }
                catch (exception $e)
                {
                    $db->rollback();
                    echo '<script>
                     parent.palert("' . $e->getMessage() . '");
                     parent.alert("' . $e->getMessage() . '");
                         </script>';
                    exit;
                }
            }


            $_shifts = $QShift->get_cache();
            $this->view->shifts = $_shifts;
        }

    // Hi?n th? ng? ph? c?a nh? vi? //
    public function offListAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $code = $this->getRequest()->getParam('code');
        $department = $this->getRequest()->getParam('department');
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date = $this->getRequest()->getParam('to_date', date('t/m/Y'));
        $team = $this->getRequest()->getParam('team');
        $area_id = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district = $this->getRequest()->getParam('district');
        $sales_team = $this->getRequest()->getParam('sales_team');
        $team = $this->getRequest()->getParam('team');
        $store = $this->getRequest()->getParam('store');
        $imei = $this->getRequest()->getParam('imei');
        $email = $this->getRequest()->getParam('email');
        $off = $this->getRequest()->getParam('off');
        $sort = $this->getRequest()->getParam('sort', 'p.id');
        $desc = $this->getRequest()->getParam('desc', 1);
        $export = $this->getRequest()->getParam('export');
        $this->view->desc = $desc;
        $this->view->current_col = $sort;
        $limit = LIMITATION;
        $total = 0;
        $QOff = new Application_Model_OffDate();
        $QOff_Decrease = new Application_Model_OffHistory();
        $QOff_Increase = new Application_Model_OffDateAdd();


        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'from_date' => $from_date,
            'team' => $team,
            'to_date' => $to_date,
            'team' => $team,
            'area_id' => $area_id,
            'regional_market' => $regional_market,
            'district' => $district,
            'sales_team' => $sales_team,
            'store' => $store,
            'imei' => $imei,
            'email' => $email,
            'off' => $off,
            'code' => $code,
            ));

        $params['sort'] = $sort;
        $params['desc'] = $desc;

        $off_date = $QOff->fetchPagination($page, $limit, $total, $params);
        $off_list = array();
        foreach ($off_date as $k => $v)
        {

            $off_list[] = $v;
            $off_list[$k]['date'] = intval($QOff_Increase->getDate(DAY_OFF_BEGIN, $v['staff_id']) -
                $QOff_Decrease->getDate(DAY_OFF_BEGIN, $v['staff_id']));
        }


        if (isset($export) and $export == 2)
        {
            $this->_exportTimeOffOffice($params);
        }

        if (isset($export) and $export == 1)
        {
            $this->_exportTimeOff($off_date);
        }

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time/off-list' . ($params ? '?' . http_build_query($params) .
            '&' : '?');
        $this->view->offset = $limit * ($page - 1);

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $this->view->regional_market_all = $QRegionalMarket->get_cache();

        $QTeam = new Application_Model_Team();
        $this->view->team = $QTeam->get_cache();
        if ($area_id)
        {
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }


        if ($regional_market)
        {
            //get district
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }


        if ($district)
        {
            //get store
            $where = array();
            $where[] = $QStore->getAdapter()->quoteInto('district = ?', $district);
            $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);

            $this->view->stores = $QStore->fetchAll($where, 'name');
        }
        $this->view->off = $off_list;
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;

    }

    public function offAction()
    {
        //$QOff = new Application_Model_OffDate();
        //$QOff->backupDatabase();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // step 1 , calculate off date for Office
        try
        {
            $result = $this->calculateOffice();

            if ($result != 1)
            {
                echo - 1;
                exit;
            }
            flush();
            sleep(2);
            // step 2 , calculate off date for All staff
            $result = $this->calculateSale();
            //successful
            if ($result != 1)
            {
                echo - 2;
                exit;
            }
            flush();
            sleep(2);
            // step 2 , calculate off date for PG
            $result = $this->calculatePG();
            //successful
            if ($result != 1)
            {
                echo - 3;
                exit;
            }

            echo 1;
            exit;
        }
        catch (exception $e)
        {

            echo - 1;
            exit;
        }


    }


    public function addSaveAction()
    {
        $from_date = $this->getRequest()->getParam('from_date');
        $staffs    = $this->getRequest()->getParam('a_staffresult');
        $areas     = $this->getRequest()->getParam('a_area');
        $teams     = $this->getRequest()->getParam('team');
        $shilf     = $this->getRequest()->getParam('shilf', 0);
        $asm       = $this->getRequest()->getParam('asm');
        $off       = $this->getRequest()->getParam('off', '');
        $yesterday = $this->getRequest()->getParam('yesterday');
        $type      = $this->getRequest()->getParam('type');
        $to_date   = $this->getRequest()->getParam('to');
        $reason    = $this->getRequest()->getParam('reason', '');

        $flashMessenger = $this->_helper->flashMessenger;
        $QTime = new Application_Model_Time();
        $QStaff = new Application_Model_Staff();
        $QTimePermission = new Application_Model_TimePermission();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;

        try
        {


            $db = Zend_Registry::get('db');
            $db->beginTransaction();

            if (!isset($asm))
            {
                if (!$areas && $teams != ACCESSORIES_TEAM)
                {
                    throw new Exception("Please choose Areas");
                }

                if (!$teams)
                {
                    throw new Exception("Please choose your team");
                }

                if (!$staffs)
                {
                    throw new Exception("Please choose staff for checkin");
                }
                if (HAPPY_TIME_CHECKIN == 1)
                {
                    if (!$from_date)
                    {
                        throw new Exception("Please Choose Date for Checkin");
                    }
                }
            }


            $temp = explode('/', $from_date);

            if (!(isset($temp[2]) and intval($temp[2]) >= 2013 and intval($temp[2]) <= 2020 and
                isset($temp[1]) and intval($temp[1]) <= 12 and intval($temp[1]) >= 1 and isset($temp[0]) and
                intval($temp[0]) <= 31 and intval($temp[0]) >= 1))
            {
                throw new Exception("Date format is error");
            }

            if (!in_array($group_id, array(
                    ADMINISTRATOR_ID,
                    HR_ID,
                    HR_EXT_ID)))
            {
                if (HAPPY_TIME_CHECKIN != 1)
                {
                    if ($temp[0] != date('d'))
                    {
                        throw new Exception("Hacking is attemp! Information will report");
                    }
                }
            }


            // xin nghi off
            if (isset($off) and $off)
            {
                if (count($staffs) != 1)
                {
                    throw new Exception("Please choose only one staff for request off");
                }
                $from_date = $this->getRequest()->getParam('from');

                $from_date = explode('/', $from_date);
                $from_date = $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0];

                $to_date = explode('/', $to_date);
                $to_date = $to_date[2] . '-' . $to_date[1] . '-' . $to_date[0];

                $startTime = strtotime($from_date);
                $endTime = strtotime($to_date);

                // Loop between date
                for ($i = $startTime; $i <= $endTime; $i = $i + 86400)
                {

                    $date = date('Y-m-d', $i);
                    $d = date('d', strtotime($date));
                    $m = intval(date('m', strtotime($date)));
                    $y = date('Y', strtotime($date));

                    $where = array();
                    $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staffs[0]);
                    $where[] = $QTime->getAdapter()->quoteInto('DAY(created_at) = ?', $d);
                    $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $m);
                    $where[] = $QTime->getAdapter()->quoteInto('YEAR(created_at) = ?', $y);

                    $result = $QTime->fetchRow($where);
                    if (isset($result) and $result)
                    {
                        throw new Exception("Checking List is exist");
                    }

                    $data = array(
                        'staff_id' => $staffs[0],
                        'created_at' => $date,
                        'off' => 1,
                        'reason' => $reason,
                        'off_type' => $type,
                        'shift' => $shilf,
                        'regional_market' => $staff['regional_market'],
                        );
                    $QTime->insert($data);

                    $QLog = new Application_Model_Log();
                    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                    $info = "TIME ADD - OFF for (" . $staffs[0] . ")";
                    //todo log
                    $QLog->insert(array(
                        'info' => $info,
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));
                }
            } else
            {
                $from_date = explode('/', $from_date);
                $from_date = $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0];

                if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
                    ADMINISTRATOR_ID,
                    HR_ID,
                    HR_EXT_ID,
                    BOARD_ID)))
                {
                } else
                {
                    $where = $QTimePermission->getAdapter()->quoteInto('staff_id = ? ', $user_id);
                    $result = $QTimePermission->fetchAll($where);
                    if (!$result)
                    {
                        throw new Exception("Permission denied");
                    }
                }


                if (isset($yesterday) and $yesterday)
                    $from_date = date("Y-m-d", strtotime($from_date . "- 1 days"));


                foreach ($staffs as $k => $staff_id)
                {
                    $staff_rowset = $QStaff->find($staff_id);
                    $staff = $staff_rowset->current();

                    if (!$staff)
                    {
                        throw new Exception("Invalid staff, Please try again");
                    }

                    $datetime = new DateTime($from_date);
                    $day = $datetime->format('d');
                    $month = $datetime->format('m');
                    $year = $datetime->format('Y');

                    $where = array();

                    $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff->id);
                    $where[] = $QTime->getAdapter()->quoteInto('DAY(created_at) = ?', $day);
                    $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $month);
                    $where[] = $QTime->getAdapter()->quoteInto('YEAR(created_at) = ?', $year);

                    $result = $QTime->fetchRow($where);
                    if (isset($result) and $result)
                    {
                        throw new Exception("Checking List is exist");
                    }

                    $data = array(
                        'staff_id' => $staff_id,
                        'created_at' => $from_date,
                        'reason' => $reason,
                        'add_at' => date('Y-m-d H:i:s'),
                        'add_by' => $user_id,
                        'regional_market' => $staff['regional_market'],
                        );

                    if (isset($shilf) and $shilf)
                        $data['shift'] = 1;

                    $QTime->insert($data);

                    $QLog = new Application_Model_Log();
                    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                    $info = "TIME ADD - CHECK for (" . $staff_id . ")";
                    //todo log
                    $QLog->insert(array(
                        'info' => $info,
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));

                }


            }

            $db->commit();
            echo '<script>parent.location.href="/time"</script>';

        }
        catch (exception $ex)
        {
            $db->rollback();
            echo '<script>
            parent.palert("' . $ex->getMessage() . '");
            parent.alert("' . $ex->getMessage() . '");
                 </script>';
            exit;
        }

        exit;
    }
    /*
    - permission action
    - set quyen cho doi tuong duoc cham cong dum
    */
    public function permissionAction()
    {

        if ($this->getRequest()->getMethod() == 'POST')
        {
            try
            {
                $id = $this->getRequest()->getParam('id');
                $team_objects = $this->getRequest()->getParam('teams', array());
                $area_objects = $this->getRequest()->getParam('areas', array());
                if (!$id && !$team_objects && !$areas)
                {
                    throw new Exception("Invalid information");
                }
                $QPermission = new Application_Model_TimePermission();
                $where = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
                $permission = $QPermission->fetchAll($where);

                if (!$permission)
                {
                    throw new Exception("Invalid user");
                }

                // luu n?i dung
                $data = array('staff_id' => $id, );

                if ($id)
                {
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $where = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
                    $QPermission->update($data, $where);
                } else
                {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $id = $QPermission->insert($data);
                }

                $where = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
                $old_objects = $QPermission->fetchAll($where);

                $old_team_objects = array();
                $old_area_objects = array();

                foreach ($old_objects as $_key => $_value)
                {
                    $old_team_objects[] = $_value['team'];
                    $old_area_objects[] = $_value['area_id'];
                }
                ////////////////////////////////////////////////////////////////
                $add_team_objects = array_diff($team_objects, $old_team_objects);
                $remove_team_objects = array_diff($old_team_objects, $team_objects);

                foreach ($add_team_objects as $_key => $_value)
                {
                    $data = array(
                        'staff_id' => $id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'team' => $_value,
                        'type' => 1,
                        );

                    $QPermission->insert($data);
                }

                foreach ($remove_team_objects as $_key => $_value)
                {
                    $where = array();
                    $where[] = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QPermission->getAdapter()->quoteInto('team = ?', $_value);
                    $where[] = $QPermission->getAdapter()->quoteInto('type  = ?', 1);
                    $QPermission->delete($where);
                }

                ////////////////////////////////////////////////////////////////
                $add_area_objects = array_diff($area_objects, $old_area_objects);
                $remove_area_objects = array_diff($old_area_objects, $area_objects);

                foreach ($add_area_objects as $_key => $_value)
                {
                    $data = array(
                        'staff_id' => $id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'area_id' => $_value,
                        'type' => 2,
                        );

                    $QPermission->insert($data);
                }

                foreach ($remove_area_objects as $_key => $_value)
                {
                    $where = array();
                    $where[] = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QPermission->getAdapter()->quoteInto('area_id = ?', $_value);
                    $where[] = $QPermission->getAdapter()->quoteInto('type  = ?', 2);
                    $QPermission->delete($where);
                }
                ////////////////////////////////////////////////////////////////

                $this->_redirect(HOST . 'time/list-permiss');
            }
            catch (exception $e)
            {
                My_Controller::palert($e->getMessage());
            }

        } else
        {
            $QTeam = new Application_Model_Team();
            $QArea = new Application_Model_Area();
            $QPermission = new Application_Model_TimePermission();
            $QGroup = new Application_Model_Group();

            $QModel = new Application_Model_Department();
            $this->view->departments = $QModel->fetchAll();
            $this->view->group = $QGroup->get_cache_2();

            $this->view->team_cache = $QTeam->get_cache_team();

            $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();
            $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;


            $this->view->area_cache = $QArea->get_cache();
            $id = $this->getRequest()->getParam('id');
            $flashMessenger = $this->_helper->flashMessenger;

            if (!$id)
            {
                $this->_redirect(HOST . 'time/list-permiss');
            }

            $where = $QPermission->getAdapter()->quoteInto('staff_id = ?', $id);
            $old_objects = $QPermission->fetchAll($where);

            $old_team_objects = array();
            $old_area_objects = array();


            foreach ($old_objects as $_key => $_value)
            {
                $old_team_objects[] = $_value['team'];
                $old_area_objects[] = $_value['area_id'];
            }
            $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->view->old_team_objects = $old_team_objects;
            $this->view->old_area_objects = $old_area_objects;
            $QStaff = new Application_Model_Staff();
            $staff_rowset = $QStaff->find($id);
            $staff = $staff_rowset->current();
            $this->view->staff = $staff;
            $this->view->id = $id;
        }
    }
    //*Delete ngay cong*//
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        try
        {
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $user_id = $userStorage->id;
            $group_id = $userStorage->group_id;
            $QPermission = new Application_Model_TimePermission();




            $QTime = new Application_Model_Time();
            $QStaff = new Application_Model_Staff();
            $QTimePermission = new Application_Model_TimePermission();
            $QOffHistory = new Application_Model_OffHistory();
            $result = $QTimePermission->getPermission($userStorage->user_id);

                $where = $QTime->getAdapter()->quoteInto('id = ?', $id);
                $item = $QTime->fetchRow($where);
                $staffRowSet = $QStaff->find($item['staff_id']);
                $staff = $staffRowSet->current();
                $shift = $item['shift'];

                $where = array();
                //$where[] = $QTime->getAdapter()->quoteInto('status = 0', null);
                $where[] = $QTime->getAdapter()->quoteInto('id = ?', $id);
                $QTime->delete($where);


                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "TIME SYSTEM - DELETE (" . $id . ")";
                //todo log
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));

                $this->_redirect(HOST . 'time');


        }
        catch (exception $e)
        {
            echo '<script> alert("Can not Delete TIMING"); </script>';
            $this->_redirect(HOST . 'time');
        }
    }

    public function staffOffStepAction()
    {
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;
    }


    public function staffWorkingDayAction()
    {
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;
    }

    public function staffWorkingDayOfficeAction()
    {
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;
    }

    public function offCalculate()
    {
        $month = $this->getRequest()->getParam('month');
        $month = (isset($_SESSION['month']) and $_SESSION['month']) ? $_SESSION['month'] :
            $month;

        $moduleName = $this->getRequest()->getModuleName();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();

        if (!$month and !($moduleName == 'default' and $controllerName == 'time' and $actionName ==
            'choose-month'))
        {
            $this->_redirect(HOST . 'time/choose-month');
        }
    }

    public function chooseMonthAction()
    {
        if ($this->getRequest()->getMethod() == 'POST')
        {

            $month = $this->getRequest()->getParam('month');

            $tm = explode('/', $month);

            $flashMessenger = $this->_helper->flashMessenger;

            if (!isset($tm[0]) or !(intval($tm[0]) > 0 and intval($tm[0]) <= 12) or !isset($tm[1]) or
                !(intval($tm[1]) >= 2014 and intval($tm[0]) <= 2020))
            {

                $flashMessenger->setNamespace('error')->addMessage('Please choose correct month!');
                $this->_redirect(HOST . 'time/choose-month');
                exit;
            }

            $month = $tm[1] . $tm[0];

            $_SESSION['month'] = $month;

            $this->_redirect(HOST . 'time/staff-working-day');
        }

    }

    public function staffOfficeWorkingDaySaveAction()
    {
        $this->_helper->layout->disableLayout();

        if ($this->getRequest()->getMethod() == 'POST') {
            $month = $this->getRequest()->getParam('month');
            $upload = new Zend_File_Transfer();

            if (function_exists('finfo_file'))
                $upload->addValidator('MimeType', false, array(
                    'application/vnd.ms-excel',
                    'application/excel',
                    'application/x-excel',
                    'application/x-msexcel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));

            $upload->addValidator('Extension', false, 'xls,xlsx');

            $upload->addValidator('FilesSize', false, array('max' => '20MB'));

            $upload->addValidator('ExcludeExtension', false, 'php,sh');

            $upload->addValidator('Count', false, 1);

            $uniqid = uniqid();

            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' .
                DIRECTORY_SEPARATOR . 'workingdayoffice' . DIRECTORY_SEPARATOR . $uniqid;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            if (!$upload->isValid())
            {
                $errors = $upload->getErrors();

                $sError = null;

                if ($errors and isset($errors[0]))
                    switch ($errors[0])
                    {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                        case 'fileExtensionFalse':
                            $sError = 'The file(s) you selected weren\'t the type we were expecting';
                            break;
                    }

                $result = array('error' => $sError);

            }

            else
            {
                try
                {
                    $upload->receive();
                    $result = array('success' => true);

                }
                catch (Zend_File_Transfer_Exception $e)
                {
                    $result = array('error' => $e->message());
                }

                if (!isset($result['success']) or !$result['success'])
                {
                echo '<script>
                        parent.palert("' . $result['error'] . '");
                    </script>';
                exit;
            }

            //read file
            include 'PHPExcel/IOFactory.php';

            $inputFileName = $uploaded_dir . DIRECTORY_SEPARATOR . $upload->getFileInfo()['file']['name'];


            //  Read your Excel workbook
            try
            {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            }
            catch (exception $e)
            {
                echo '<script>
                        parent.palert("Error loading file \'' . pathinfo($inputFileName,
                        PATHINFO_BASENAME) . '\': ' . $e->getMessage() . '");
                    </script>';
                exit;
            }

            //  Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($sheet->
            getHighestColumn());

            $row = 1;
            $year = substr($month, 0, 4);
            $month_cal = substr($month, -2, 2);
            $month = intval($month_cal);
            $table_name = 'staff_office_time' . $month;
            $db = Zend_Registry::get('db');

            try
            {
                $result = $db->describeTable($table_name);
                if (empty($result))
                    $new = true;
            }
            catch (exception $e)
            {
                $new = true;
            }

            //create table
            if (isset($new) and $new)
            {

                $sql = ' CREATE TABLE `' . $table_name . '` (';
                $sql .= ' `staff_id` int(11) UNSIGNED ';
                $sql .= ', `date` datetime(0) NULL ';
                $sql .= ', `check_in` varchar(100) NULL ';
                $sql .= ', `check_out` varchar(100) NULL ';
                $sql .= '
            , INDEX `staff_id` (`staff_id`)
            ) ENGINE=MyISAM;';

                $db->query($sql);
            }

                try
                {
                    $QStaff = new Application_Model_Staff();
                    $array_invalid = array();

                    $headData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1, null, true, false);
                    $headData = $headData[0];
                    $array_insert = array();

                    // kiem tra so ngay lam viec quy dinh
                    $QWorkingDay = new Application_Model_DateNorm();
                    $QTime = new Application_Model_Time();
                    $QTimeCheckIn = new Application_Model_TimeCheckIn();
                    $where = $QWorkingDay->getAdapter()->quoteInto('id = ?', $month);
                    $dw = $QWorkingDay->fetchRow($where);
                    $working_day = $dw['date'];
                    $first_day_start_at = 1;
                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                    $user_id = $userStorage->id;

                    //  Loop through each row of the worksheet in turn
                    for ($row = 2; $row <= $highestRow; $row++) {

                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                        $staff_code = $date = $dateDay = $timeCheckIn = $timeCheckOut = '';
                        $staff_code = $rowData[0][0];
                        $date = $rowData[0][1];
                        $dateDay = $rowData[0][2];
                        $timeCheckIn = $rowData[0][3];
                        $timeCheckOut = $rowData[0][4];



                        $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
                        $staff = $QStaff->fetchRow($where);

                        if ($staff)
                        {
                            $where = array();
                            $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff['id']);
                            $where[] = $QTime->getAdapter()->quoteInto('DATE(created_at) = ?', $date);
                            $result  = $QTime->fetchRow($where);


                            if(empty($result))
                            {

                                $stmt = $db->query(
                                    'SELECT * FROM `'.$table_name . '` WHERE
                                `staff_id` = ? '. ' AND `date` = ? '
                                ,
                                    array($staff['id'] , $date)
                                );

                                $rows = $stmt->fetchAll();

                                $is_new_record = true;

                                if (isset($rows[0]))
                                    $is_new_record = false;

                                $data = array(
                                    'staff_id' => $staff['id'],
                                    'date' => $date,
                                    'check_in' => $timeCheckIn,
                                    'check_out' => $timeCheckOut
                                );

                                if ($is_new_record){

                                     $db->insert($table_name, $data);

                                    $data_timing = array(
                                        'staff_id' => $staff['id'],
                                        'created_at' => $date,
                                        'add_at' => date('Y-m-d H:i:s'),
                                        'add_by' => $user_id,
                                        'regional_market' => $staff['regional_market'],
                                        'status' => 1,
                                        'approved_at' => date('Y-m-d H:i:s'),
                                        'approved_by' => $user_id,
                                    );

                                    $id_timing = $QTime->insert($data_timing);

                                    $data = array(
                                        'staff_id' => $staff['id'],
                                        'date' => $date,
                                        'check_in' => $date . ' ' . $timeCheckIn,
                                        'check_out' => $date . ' ' . $timeCheckOut,
                                        'timing_id' => $id_timing
                                    );

                                    $QTimeCheckIn->insert($data);

                                }
                                else
                                {
                                    $where = array();
                                    $where['`staff_id` = ?'] = $staff['id'];
                                    $where['`date` = ?'] = $staff['id'];
                                    $db->update($table_name, $data, $where);
                                }
                            }
                        }
                        else{
                            $array_invalid[] = $staff_code;
                        }
                    }
                }
                catch (exception $e)
                {
                    $this->view->errors = $e->getMessage();
                    return;
                }


                $this->view->array_invalid = $array_invalid;
                $this->view->result = "Done.";


            }


        }

    }

    public function staffWorkingDaySaveAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->getMethod() == 'POST')
        {


            $upload = new Zend_File_Transfer();

            if (function_exists('finfo_file'))
                $upload->addValidator('MimeType', false, array(
                    'application/vnd.ms-excel',
                    'application/excel',
                    'application/x-excel',
                    'application/x-msexcel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));

            $upload->addValidator('Extension', false, 'xls,xlsx');

            $upload->addValidator('FilesSize', false, array('max' => '20MB'));

            $upload->addValidator('ExcludeExtension', false, 'php,sh');

            $upload->addValidator('Count', false, 1);

            $uniqid = uniqid();

            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' .
                DIRECTORY_SEPARATOR . 'workingday' . DIRECTORY_SEPARATOR . $uniqid;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            if (!$upload->isValid())
            {
                $errors = $upload->getErrors();

                $sError = null;

                if ($errors and isset($errors[0]))
                    switch ($errors[0])
                    {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                        case 'fileExtensionFalse':
                            $sError = 'The file(s) you selected weren\'t the type we were expecting';
                            break;
                    }

                $result = array('error' => $sError);

            } else
            {

                try
                {
                    $upload->receive();
                    $result = array('success' => true);

                }
                catch (Zend_File_Transfer_Exception $e)
                {
                    $result = array('error' => $e->message());
                }

            }

            if (!isset($result['success']) or !$result['success'])
            {
                echo '<script>
                        parent.palert("' . $result['error'] . '");
                    </script>';
                exit;
            }

            //read file
            include 'PHPExcel/IOFactory.php';

            $inputFileName = $uploaded_dir . DIRECTORY_SEPARATOR . $upload->getFileInfo()['file']['name'];


            //  Read your Excel workbook
            try
            {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            }
            catch (exception $e)
            {
                echo '<script>
                        parent.palert("Error loading file \'' . pathinfo($inputFileName,
                    PATHINFO_BASENAME) . '\': ' . $e->getMessage() . '");
                    </script>';
                exit;
            }

            //  Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($sheet->
                getHighestColumn());

            $row = 1;
            $month = $_SESSION['month'];
            $year = substr($month, 0, 4);
            $month_cal = substr($month, -2, 2);
            $month = intval($month_cal);
            $table_name = 'staff_office_' . $month;
            $db = Zend_Registry::get('db');
            $new = false;

            try
            {
                $result = $db->describeTable($table_name);
                if (empty($result))
                    $new = true;
            }
            catch (exception $e)
            {
                $new = true;
            }

            //create table
            if ($new)
            {

                $sql = ' CREATE TABLE `' . $table_name . '` (';
                $sql .= ' `staff_id` int(11) UNSIGNED ';
                $sql .= ', `amount` varchar(5000) NULL ';
                $sql .= '
                , INDEX `staff_id` (`staff_id`)
                ) ENGINE=MyISAM;';

                $db->query($sql);
            }
            try
            {

                $QStaff = new Application_Model_Staff();
                $array_invalid = array();

                $headData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1, null, true, false);

                $headData = $headData[0];

                $array_insert = array();

                // kiem tra so ngay lam viec quy dinh
                $QWorkingDay = new Application_Model_DateNorm();
                $QTime = new Application_Model_Time();
                $where = $QWorkingDay->getAdapter()->quoteInto('id = ?', $month);
                $dw = $QWorkingDay->fetchRow($where);
                $working_day = $dw['date'];
                $first_day_start_at = 1;
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $user_id = $userStorage->id;

                //  Loop through each row of the worksheet in turn
                for ($row = 2; $row <= $highestRow; $row++)
                {
                    //  Read a row of data into an array
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                    $staff_code = $rowData[0][0];

                    $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
                    $staff = $QStaff->fetchRow($where);

                    if ($staff)
                    {

                        $day = 0;

                        for ($cell = $first_day_start_at; $cell < $highestColumnIndex; $cell++)
                        {

                            // them cham cong vao bang TIME
                            $where = array();
                            $where[] = $QTime->getAdapter()->quoteInto('staff_id = ?', $staff['id']);
                            $where[] = $QTime->getAdapter()->quoteInto('DAY(created_at) = ?', $cell);
                            $where[] = $QTime->getAdapter()->quoteInto('MONTH(created_at) = ?', $month);
                            $result  = $QTime->fetchRow($where);

                            if (empty($result) and 'x' == strtolower($rowData[0][$cell]))
                            {

                                $date_col = (intval($cell) <= 9) ? '0' . intval($cell) : intval($cell);
                                $from_date = date('Y') . '-' . $month_cal . '-' . $date_col;

                                $data = array(
                                    'staff_id' => $staff['id'],
                                    'created_at' => $from_date,
                                    'add_at' => date('Y-m-d H:i:s'),
                                    'add_by' => $user_id,
                                    'regional_market' => $staff['regional_market'],
                                    'status' => 1,
                                    'approved_at' => date('Y-m-d H:i:s'),
                                    'approved_by' => $user_id,
                                    );

                                if ('0.5' == strtolower($rowData[0][$cell]))
                                {
                                    $data['shift'] = 1;
                                }

                                $QTime->insert($data);

                            }

                            if ('x' == strtolower($rowData[0][$cell]))
                            {
                                $day++;
                            }

                            if ('0.5' == strtolower($rowData[0][$cell]))
                            {
                                $day = $day + 0.5;
                            }

                            if ($day == $working_day)
                                break;
                        }


                        $stmt = $db->query('SELECT * FROM `' . $table_name . '` WHERE
                                `staff_id` = ?
                                ', array($staff['id']));

                        $rows = $stmt->fetchAll();

                        $is_new_record = true;

                        if (isset($rows[0]))
                            $is_new_record = false;

                        $data = array(
                            'staff_id' => $staff['id'],
                            'amount' => $day,
                            );

                        $array_insert[] = $data;

                        if ($is_new_record)
                        {
                            $db->insert($table_name, $data);

                        } else
                        {
                            $where = array();
                            $where['`staff_id` = ?'] = $staff['id'];
                            $db->update($table_name, $data, $where);
                        }
                    } else
                    {
                        $array_invalid[] = $staff_code;
                    }
                }

            }
            catch (exception $e)
            {
                echo '<script>
                        parent.palert("Error: ' . $e->getMessage() . '");
                    </script>';
                exit;
            }


            $back_url = $this->getRequest()->getParam('back_url');

            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('success')->addMessage('Done!');

            echo '<script>parent.location.href="' . ($back_url ? $back_url : HOST .
                'time/staff-working-day') . '"</script>';
            exit;


        } else
        {
            exit;
        }

    }

    public function calculateOfficeAction()
    {
        $result = $this->calculateOffice();
        //successful
        if ($result == 1)
        {
            echo 1;
            exit;
        } else
        {
            echo - 1;
            exit;
        }

    }


    public function addOffAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->getRequest()->getParam('id');
        $off_number = $this->getRequest()->getParam('off_number');
        $reason = $this->getRequest()->getParam('reason');
        $type = $this->getRequest()->getParam('type');
        $month = $this->getRequest()->getParam('month');
        $QOffHistory = new Application_Model_OffHistory();
        $QOffAdd = new Application_Model_OffDateAdd();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QLog = new Application_Model_Log();
        $user_id = $userStorage->id;
        $system_note = 'System auto insert at ' . date('Y-m-d h:i:s');
        $year = substr($month, 0, 4);
        $month_cal = substr($month, -2, 2);
        $sn = date('YmdHis') . substr(microtime(), 2, 4);
        $date = date('Y-m-d', strtotime($year . '-' . $month_cal . '01'));

        if (empty($off_number) || empty($reason))
        {
            echo '0';
            exit;
        }


        if ($id)
        {
            if (isset($type) and $type)
            {
                //Thêm ngày phép
                if ($type == 1)
                {
                    try
                    {


                        for ($i = 0; $i < $off_number; $i++)
                        {
                            $where = array();
                            $where[] = $QOffAdd->getAdapter()->quoteInto('staff_id = ?', $id);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                            $result = $QOffAdd->fetchAll($where);
                            $total = count($result);

                            if (isset($total) and $total > 12)
                            {
                                echo 0;
                                exit;
                            }

                            $data = array(
                                'staff_id' => $id,
                                'date' => $date,
                                'add_id' => $sn,
                                'note' => $system_note,
                                );

                            $QOffAdd->insert($data);

                            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                            $info = 'TIME SYSTEM AUTO ADD' . $system_note . ' Add by : ' . $userStorage->id .
                                ' at : ' . date('Y-m-d h:i:s') . 'for :' . $id;
                            $QLog->insert(array(
                                'info' => $info,
                                'user_id' => $user_id,
                                'ip_address' => $ip,
                                'time' => date('Y-m-d H:i:s'),
                                ));

                            echo 1;
                            exit;
                        }
                    }
                    catch (exception $e)
                    {
                        echo 0;
                        exit;
                    }

                }

                if ($type == 2)
                {
                    try
                    {
                        for ($i = 0; $i < $off_number; $i++)
                        {
                            $data = array(
                                'month_id' => intval($month),
                                'date' => $date,
                                'type' => 1,
                                'shift' => 1,
                                'staff_id' => $id,
                                'status' => 1,
                                'reason' => $reason);

                            $QOffHistory->insert($data);

                            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                            $info = 'TIME SYSTEM AUTO ADD' . $system_note . ' Add by : ' . $userStorage->id .
                                ' at : ' . date('Y-m-d h:i:s') . 'for :' . $id;
                            $QLog->insert(array(
                                'info' => $info,
                                'user_id' => $user_id,
                                'ip_address' => $ip,
                                'time' => date('Y-m-d H:i:s'),
                                ));

                            echo 1;
                            exit;
                        }
                    }
                    catch (exception $e)
                    {
                        echo 0;
                        exit;
                    }
                }
            }
        } else
        {
            echo 0;
            exit;
        }
    }

    private function calculateOffice()
    {
        $db = Zend_Registry::get('db');
        $QOffAdd = new Application_Model_OffDateAdd();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $month = $_SESSION['month'];

        if (isset($month) and $month)
        {
            try
            {
                $year = substr($month, 0, 4);
                $month_cal = substr($month, -2, 2);
                $first = $year . '-' . $month_cal . '-01';
                $last = date('Y-m-t', strtotime($first));
                // lay so ngay lam viec trong thang
                $working_day_office_table_name = 'staff_office' . '_' . intval($month_cal);

                $sql = 'SELECT `p`.staff_id, `p`.amount, s.contract_term , s.contract_signed_at , s.contract_expired_at , s.group_id   FROM `' .
                    $working_day_office_table_name . '` as p';
                $sql .= ' INNER JOIN staff as s on `p`.staff_id = s.id';


                $stmt = $db->query($sql);
                $rows = $stmt->fetchAll();
                $count_working_day = 0;
                $date = $first;
                $sn = date('YmdHis') . substr(microtime(), 2, 4);
                $info = 'SYSTEM OFF CALCULATE :';
                $note = '';
                $note .= $info . ' - ' . $sn . ' - ' . ' at : ' . $date;
                foreach ($rows as $k => $v)
                {
                    if ($v['amount'] and $v['amount'] and $v['amount'] >= LIMIT_STAFF_WORKING)
                    {


                        if ($v['contract_term'] == LABOUR_CONTRACT)
                        {
                            break;
                        } else
                        {

                            $where = array();
                            $where[] = $QOffAdd->getAdapter()->quoteInto('staff_id = ?', $v['staff_id']);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('MONTH(date) = ?', $month_cal);
                            $result = $QOffAdd->fetchRow($where);

                            if (isset($result) and $result)
                            {
                                break;
                            }

                            $data = array(
                                'staff_id' => $v['staff_id'],
                                'date' => $date,
                                'add_id' => $sn,
                                'note' => $note,
                                );

                            $QOffAdd->insert($data);
                            $info .= ' , ' . $v['staff_id'];

                        }
                    }
                }

                $info .= 'SN = ' . $sn;
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $user_id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));

                return 1;
            }
            catch (exception $e)
            {
                return - 1;
            }

        } else
        {
            return - 1;
        }
    }

    //step 2 , calculate for all user time checking in SYSTEM
    public function calculateSalesAction()
    {
        $result = $this->calculateSale();
        if ($result == 1)
        {
            echo 'ok';
            exit;
        } else
        {
            $this->_redirect(HOST . 'time/choose-month');
        }
    }

    //step 3 , calculate for PG
    public function calculatePgAction()
    {
        $result = $this->calculatePG();
        if ($result == 1)
        {
            echo 'ok';
            exit;
        } else
        {
            $this->_redirect(HOST . 'time/choose-month');
        }
    }

    private function calculateSale()
    {
        $db = Zend_Registry::get('db');
        $QOffAdd = new Application_Model_OffDateAdd();
        $QDateNorm = new Application_Model_DateNorm();
        $QOffHistory = new Application_Model_OffHistory();
        $date_norm = $QDateNorm->get_cache();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $month = $_SESSION['month'];

        if (isset($month) and $month)
        {
            try
            {

                $db->beginTransaction();
                $year = substr($month, 0, 4);
                $month_cal = substr($month, -2, 2);
                $limit_date = $date_norm[intval($month_cal)];
                $first = $year . '-' . $month_cal . '-01' . ' 00:00:00';
                $last = date('Y-m-t', strtotime($first)) . ' 23:59:59';

                $timing_table_name = 'time';

                if (isset($last) and $last and isset($first) and $first)
                {
                    $sql = 'SELECT  `p`.staff_id, `p`.created_at , `p`.approved_at,  s.`group_id` ,  s.contract_term , s.contract_signed_at , s.contract_expired_at , s.group_id , s.is_officer , count(`p`.staff_id) as total_timing   FROM `' .
                        $timing_table_name . '` as p';
                    $sql .= ' INNER JOIN staff as s on `p`.staff_id = s.id';
                    $sql .= " AND p.approved_at IS NOT NULL
					AND p.approved_at <> ''
					AND p.approved_at <> 0";
                    $sql .= " WHERE p.`created_at` >= '$first'";
                    $sql .= " AND p.`created_at` <= '$last'";
                    $sql .= " GROUP BY `p`.staff_id";


                    $stmt = $db->query($sql);
                    $rows = $stmt->fetchAll();
                    $count_working_day = 0;
                    $date = $first;
                    $sn = date('YmdHis') . substr(microtime(), 2, 4);

                    foreach ($rows as $k => $v)
                    {

                        if (in_array($group_id, array(
                            ASMSTANDBY_ID,
                            BOARD_ID,
                            ASM_ID)))
                        {
                            break;
                        }

                        if (in_array($v['contract_term'], array(
                            CONTRACT_TERM_LABOUR,
                            CONTRACT_TERM_NOT_YET,
                            CONTRACT_TERM_SEASONAL,
                            CONTRACT_TERM_SEASONAL_CHALLENGE)))
                        {
                            break;
                        }

                        if ($v['is_officer'] == 1)
                        {
                            $limit_date = LIMIT_STAFF_WORKING;
                        }

                        if ($v['total_timing'] >= $limit_date)
                        {


                            $where = array();
                            $where[] = $QOffAdd->getAdapter()->quoteInto('staff_id = ?', $v['staff_id']);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('MONTH(date) = ?', $month_cal);
                            $result = $QOffAdd->fetchRow($where);

                            if (isset($result) and $result)
                            {
                                break;
                            }

                            $data = array(
                                'staff_id' => $v['staff_id'],
                                'date' => $date,
                                'add_id' => $sn,
                                );

                            $QOffAdd->insert($data);
                            $where = array();
                            $where[] = $QOffHistory->getAdapter()->quoteInto('staff_id = ?', $v['staff_id']);
                            $where[] = $QOffHistory->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                            $where[] = $QOffHistory->getAdapter()->quoteInto('MONTH(date) = ?', $month_cal);
                            $result = $QOffHistory->fetchRow($where);

                            if ($result)
                            {
                                $allow_off = intval($QOffAdd->getDate(DAY_OFF_BEGIN, $v['staff_id']) - $QOffHistory->
                                    getDate(DAY_OFF_BEGIN, $v['staff_id']));
                                $QOffHistory->checkOff($v['staff_id'], $month_cal, $allow_off);
                            }


                        }
                    }
                }


                $db->commit();
                return 1;

            }

            catch (exception $e)
            {
                $db->rollback();
                return - 1;
            }

        } else
        {
            return - 1;
        }
    }


    private function calculatePG()
    {
        $db = Zend_Registry::get('db');
        $QOffAdd = new Application_Model_OffDateAdd();
        $QDateNorm = new Application_Model_DateNorm();
        $QOffHistory = new Application_Model_OffHistory();
        $date_norm = $QDateNorm->get_cache();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $month = $_SESSION['month'];
        $sn = date('YmdHis') . substr(microtime(), 2, 4);
        $info = '';

        if (isset($month) and $month)
        {
            try
            {
                $db = Zend_Registry::get('db');
                $db->beginTransaction();
                $year = substr($month, 0, 4);
                $month_cal = substr($month, -2, 2);
                $limit_date = $date_norm[intval($month_cal)];
                $first = $year . '-' . $month_cal . '-01' . ' 00:00:00';
                $last = date('Y-m-t', strtotime($first)) . ' 23:59:59';
                $timing_table_name = 'timing';

                if (isset($last) and $last and isset($first) and $first)
                {
                    $sql = 'SELECT  COUNT(
		DISTINCT DATE(p.`from`)
	)
 , s.`title` , p.`staff_id`, p.`from`, p.`approved_at` , s.`group_id` ,  s.contract_term , s.`contract_signed_at` , s.`contract_expired_at` , s.`group_id`, s.`is_officer` , count(`p`.staff_id) as total_timing   FROM `' .
                        $timing_table_name . '` as p';
                    $sql .= ' INNER JOIN staff as s on `p`.staff_id = s.id';
                    $sql .= " AND p.approved_at IS NOT NULL
					AND p.approved_at <> ''
					AND p.approved_at <> 0";
                    $sql .= " WHERE p.`from` >= '$first'";
                    ;
                    $sql .= " AND p.`from` <= '$last'";
                    ;
                    $sql .= " AND s.`title` = " . PGPB_TITLE;
                    $sql .= " GROUP BY `p`.staff_id ";

                    $stmt = $db->query($sql);
                    $rows = $stmt->fetchAll();
                    $count_working_day = 0;
                    $date = $first;

                    foreach ($rows as $k => $v)
                    {
                        if (in_array($v['contract_term'], array(
                            CONTRACT_TERM_LABOUR,
                            CONTRACT_TERM_NOT_YET,
                            CONTRACT_TERM_SEASONAL,
                            CONTRACT_TERM_SEASONAL_CHALLENGE)))
                        {
                            break;
                        }

                        if ($v['total_timing'] < $limit_date)
                        {

                            $off_date_month = $limit_date - $v['total_timing'];


                            $allow_off = intval($QOffAdd->getDate(DAY_OFF_BEGIN, $v['staff_id']) - $QOffHistory->
                                getDate(DAY_OFF_BEGIN, $v['staff_id']));


                            $system_note = 'System auto create';

                            if ($allow_off >= 0)
                            {
                                for ($i = 0; $i < $allow_off; $i++)
                                {

                                    $data = array(
                                        'month_id' => intval($month_cal),
                                        'date' => date('Y-m-d ', strtotime($first . "+ $i days")),
                                        'type' => 1,
                                        'shift' => 1,
                                        'staff_id' => $v['staff_id'],
                                        'status' => 1,
                                        'reason' => $system_note);

                                    $where = array();
                                    $where[] = $QOffHistory->getAdapter()->quoteInto('staff_id = ?', $v['staff_id']);
                                    $where[] = $QOffHistory->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                                    $where[] = $QOffHistory->getAdapter()->quoteInto('MONTH(date) = ?', $month_cal);
                                    $result = $QOffHistory->fetchRow($where);

                                    if (empty($result))
                                    {
                                        $QOffHistory->insert($data);
                                    }

                                    $off_date_month = $off_date_month - 1;


                                }
                            }


                        } else
                        {


                            $where = array();
                            $where[] = $QOffAdd->getAdapter()->quoteInto('staff_id = ?', $v['staff_id']);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('YEAR(date) = ?', $year);
                            $where[] = $QOffAdd->getAdapter()->quoteInto('MONTH(date) = ?', $month_cal);
                            $result = $QOffAdd->fetchRow($where);

                            if (isset($result) and $result)
                            {
                                break;
                            }

                            // tinh ngay pg nghi phep

                            $data = array(
                                'staff_id' => $v['staff_id'],
                                'date' => $date,
                                'add_id' => $sn,
                                );

                            $QOffAdd->insert($data);


                        }


                    }


                } else
                {
                    throw new Exception('Update failed');
                }

                $info .= 'SN = ' . $sn;
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $user_id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));
                $db->commit();
                return 1;
            }
            catch (exception $e)
            {
                $db->rollback();
                return - 1;
            }

        } else
        {
            return - 1;
        }
    }

    public function listPermissAction()
    {
        $QPermission = new Application_Model_TimePermission();
        $page = $this->getRequest()->getParam('page', 1);
        $limit = 100;
        $total = 0;
        $params = array('group_staff' => 1, );
        $QStaff = new Application_Model_Staff();
        $QArea = new Application_Model_Area();
        $QGroup = new Application_Model_Group();
        $area = $QArea->get_cache();
        $group = $QGroup->get_cache_2();

        $QTeam = new Application_Model_Team();
        $team = $QTeam->get_cache_team();

        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();
        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time/list-permiss' . ($params ? '?' .
            http_build_query($params) . '&' : '?');
        $staff = $QStaff->get_cache();
        $permiss = $QPermission->fetchPagination($page, $limit, $total, $params);
        unset($params['group_staff']);
        $permiss_list = array();
        if ($permiss)
        {
            foreach ($permiss as $k => $v)
            {
                $params['staff_id'] = $v['staff_id'];
                $permiss_list[$v['staff_id']] = $QPermission->fetchPagination(1, null, $total, $params);
            }
        }
        $this->view->group = $group;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'time/list-permiss' . ($params ? '?' .
            http_build_query($params) . '&' : '?');
        $this->view->offset = $limit * ($page - 1);
        $this->view->team = $team;
        $this->view->area = $area;
        $this->view->permiss_list = $permiss_list;
        $this->view->permission = $permiss;
        $this->view->staff = $staff;
    }

    public function dateSpecialAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $month = $this->getRequest()->getParam('month');
        $QTeam = new Application_Model_Team();
        $team = $QTeam->get_cache_team();
        $this->view->team = $team;
        if ($user_id == SUPERADMIN_ID || in_array($group_id, array(
            ADMINISTRATOR_ID,
            HR_ID,
            HR_EXT_ID,
            BOARD_ID,
            LEADER_ID)))
        {
            if ($this->getRequest()->getMethod() == 'POST')
            {

                $date = $this->getRequest()->getParam('date');
                $type = $this->getRequest()->getParam('type');
                $note = trim($this->getRequest()->getParam('note'));
                $team = $this->getRequest()->getParam('team', 0);
                $temp = explode('/', $date);
                if (!(isset($temp[2]) and intval($temp[2]) >= 2013 and intval($temp[2]) <= 2020 and
                    isset($temp[1]) and intval($temp[1]) <= 12 and intval($temp[1]) >= 1 and isset($temp[0]) and
                    intval($temp[0]) <= 31 and intval($temp[0]) >= 1))
                {

                    echo '<script>
                            parent.palert("Vui lÃ²ng ch?n dÃºng d?nh d?ng ngÃ y.");
                        </script>';
                    exit;
                }

                try
                {

                    $formatedDate = (isset($temp[2]) ? $temp[2] : '1970') . '-' . (isset($temp[1]) ?
                        $temp[1] : '01') . '-' . (isset($temp[0]) ? $temp[0] : '01');
                    if (isset($team) and $team)
                    {
                        $QDateSpec = new Application_Model_DateSpecial();
                        $where = array();
                        $where[] = $QDateSpec->getAdapter()->quoteInto('date = ?', $formatedDate);
                        $where[] = $QDateSpec->getAdapter()->quoteInto('team = ?', $team);
                        $result = $QDateSpec->fetchRow($where);
                        if ($result)
                        {
                            echo '<script>
                                     parent.palert("Date is exist");
                              </script>';
                            exit;
                        }
                    }


                    $data = array(
                        'date' => $formatedDate,
                        'note' => $note,
                        'type' => intval($type),
                        'add_time' => date('Y/m/d h:i:s'),
                        'team' => $team,
                        );
                    $QDateSpec->insert($data);
                    echo '<script> alert("Successful!");
                            </script>';
                    echo '<script>parent.location.href="/time/date"</script>';
                    exit;
                }
                catch (exception $e)
                {
                    echo '<script>
                            parent.palert("Error 101! Please contact technology");
                        </script>';
                    exit;
                }

            } else
            {
                $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
                $this->view->month = $month;
            }
        } else
        {
            $this->_redirect('/time');
        }
    }

    public function lockListAction()
    {
        $page = $this->getRequest()->getParam('page', 1);
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QTimePermission = new Application_Model_TimePermission();
        $QTime = new Application_Model_Time();
        $user_id = $userStorage->id;
        $group_id = $userStorage->group_id;
        $limit = LIMITATION;
        $total = 0;
        $params = array();

        if (!in_array($group_id, array(
            ADMINISTRATOR_ID,
            HR_ID,
            HR_EXT_ID,
            BOARD_ID)))
        {
           if ($group_id == ASM_ID)
               $params['asm'] = $user_id;
           elseif ($group_id == ASMSTANDBY_ID)
               $params['asm_standby'] = $user_id;

            $timePermission = $QTimePermission->find($user_id);
            $result         = $timePermission->current();
            if(!empty($result))
            {
                $params['other'] = $user_id;
            }
        }

        $QTeam = new Application_Model_Team();
        $team = $QTeam->fetchAll();
        $team_data = array();
        foreach($team as $k=>$v)
        {
            $team_data[$v['id']] = $v['name'];
        }

        $this->view->team = $team_data;
        $QTimeLock = new Application_Model_TimeStaffExpired();
        $data = $QTimeLock->fetchPagination($page, $limit, $total, $params);

        $data_lock_list = array();
        foreach($data as $k=> $v)
        {
            $data_lock_list[$k] = $v;
            $data_lock_list[$k]['list_not_check_in'] = $QTime->getTimeNotCheckIn($v['staff_id']);
        }



        $this->view->data  = $data_lock_list;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST.'time/lock-list'.( $params ? '?'.http_build_query($params).'&' : '?' );
        $this->view->offset = $limit*($page-1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;
    }

    public function approveLockAction()
    {
        $id = $this->getRequest()->getParam('id');
        $userStorage = Zend_Auth::getInstance()->getStorage()
            ->read();
        $user_id = $userStorage->id;
        $QTimeStaffExpired = new Application_Model_TimeStaffExpired();

        $where = $QTimeStaffExpired->getAdapter()->quoteInto('id = ?', $id);
        $data = array(
            'approved_at' => date('Y-m-d h:i:s'),
            'approved_by' => $user_id
        );
        $QTimeStaffExpired->update($data , $where);
        $this->_redirect('/time/lock-list');
    }

    public function eventSpecialAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $start = $this->getRequest()->getParam('start');
        $end = $this->getRequest()->getParam('end');
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $QTiming = new Application_Model_DateSpecial();
        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();
        $where = array();
        $where[] = $QTiming->getAdapter()->quoteInto('date(`date`) >= ?', date('Y-m-d',
            $start));
        $result = $QTiming->fetchAll($where);
        $data = null;
        if ($result->count())
        {
            $db = Zend_Registry::get('db');
            foreach ($result as $item)
            {
                $select = $db->select()->from(array('p' => 'time'), array('p.*'));
                $select->where('p.id = ?', $item->id);
                $infos = $db->fetchAll($select);
                $title = 'SD : ' . $item->date . ' Reason :' . $item->note;
                $type = array(
                    '1' => 'Normal',
                    '2' => 'x2',
                    '3' => 'x3',
                    );
                $title .= ' Salary : ' . $type[$item->type];
                $title .= ' Team : ' . $teams[$item->team];
                $data[] = array(
                    'id' => $item->id,
                    'title' => $title,
                    'start' => $item->date,
                    'allDay' => false,
                    'infos' => $infos,
                    'className' => 'customOffline',
                    );
            }
        }
        echo json_encode($data);
        exit;
    }

    /*
     * Timing for Office
     * Mot ngay bun
     * */

    public function eventOfficeAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $start = $this->getRequest()->getParam('start');
        $end = $this->getRequest()->getParam('end');
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $QTiming = new Application_Model_Time();
        $where = array();
        $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QTiming->getAdapter()->quoteInto('date(`created_at`) >= ?', date('Y-m-d',
            $start));
        $result = $QTiming->fetchAll($where);

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $start = $this->getRequest()->getParam('start');
        $end = $this->getRequest()->getParam('end');
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $QTiming = new Application_Model_Time();
        $where = array();
        $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QTiming->getAdapter()->quoteInto('date(`created_at`) >= ?', date('Y-m-d',
            $start));
        $result = $QTiming->fetchAll($where);

        $data = null;
        if ($result->count())
        {
            $db = Zend_Registry::get('db');
            foreach ($result as $item)
            {
                $select = $db->select()->from(array('p' => 'time'), array('p.*'));
                $select->joinLeft(array('t' => 'time_check_in'), 'p.id = t.timing_id', array(
                    't.date',
                    't.check_in',
                    't.check_out'
                   ));
                    $select->where('p.id = ?', $item->id);
                    $select->where('status = 1 ', '');

                    $infos = $db->fetchRow($select); //kiem tra co fai ca gay hay k
                    $check_in = $infos['check_in'] ? $infos['check_in'] : '';
                    $check_out =  $infos['check_out'] ? $infos['check_out'] : '';

                    $t1 = strtotime($check_in);
                    $t2 = strtotime($check_out);


                    list($date_check_in, $time_check_in) = explode(' ', $infos['check_in'] , 2);
                    list($date_check_out, $time_check_out) = explode(' ', $infos['check_out'] , 2);


                    $hours = '';
                    if(isset($t1) and isset($t2) and $t1 and $t2)
                    {
                        $diff = $t2 - $t1;
                            $hours = ROUND($diff / ( 60 * 60 ) - 1.5  , 2);
                        if($hours <= 0)
                            $hours = '';
                    }

                    $class_name = 'customCalendar';

                    if($time_check_in == TIME_NOT_CHECKIN and $time_check_out == TIME_NOT_CHECKIN)
                    {
                        continue;
                    }

                    if(isset($time_check_in) and $time_check_in != TIME_NOT_CHECKIN )
                    {
                        if(strtotime($time_check_in) > strtotime(TIME_CHECKIN_LIMITED))
                        {
                            $class_name = 'late';
                        }
                    }

                    if(isset($time_check_out) and $time_check_out and $time_check_out != TIME_NOT_CHECKIN)
                    {
                        if(strtotime($time_check_out) < strtotime(TIME_CHECKOUT_LIMITED))
                        {
                            $class_name = 'early';
                        }
                    }

                    $date = date('d-m-Y' , strtotime($item->created_at));

                    $select->where('shift = ?', 1);

                    $shift = $db->fetchAll($select);
                    if(isset($item['off']) and $item['off'])
                    {
                        $title = 'Offline ' . $date . ' Reason :' . $item->reason;
                    }
                    else
                        $title = '- Time check at ' . $date  . ' /- Check in : ' . $time_check_in . ' /- Check out : ' .  $time_check_out . ' /- Total work :' . $hours ;


                    $data[] = array(
                        'id' => $item->id,
                        'title' => $title,
                        'start' => $item->created_at,
                        'allDay' => false,
                        'infos' => $infos,
                        'className' => ($infos ? $class_name : ''));
                }
        }
        echo json_encode($data);
        exit;
    }

    public function viewTimeLost()
    {

    }


    public function eventAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $start = $this->getRequest()->getParam('start');
        $end = $this->getRequest()->getParam('end');
        $id = $this->getRequest()->getParam('id', $userStorage->id);
        $QTiming = new Application_Model_Time();
        $where = array();
        $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QTiming->getAdapter()->quoteInto('date(`created_at`) >= ?', date('Y-m-d',
            $start));
        $result = $QTiming->fetchAll($where);
        $data = null;
        if ($result->count())
        {
            $db = Zend_Registry::get('db');
            foreach ($result as $item)
            {
                $select = $db->select()->from(array('p' => 'time'), array('p.*'));
                if ($item->off != 1)
                {
                    $select->where('p.id = ?', $item->id);
                    $select->where('status = 0 ', '');
                    $infos = $db->fetchAll($select); //kiem tra co fai ca gay hay k
                    $select->where('shift = ?', 1);
                    $shift = $db->fetchAll($select);
                    $title = 'Time check at ' . $item->created_at;
                    if ($infos)
                    {
                        $title .= " ( Not Approved) ";
                    }

                    if ($shift)
                    {
                        $title .= " ( SHIFT 1/2) ";
                    }

                    $data[] = array(
                        'id' => $item->id,
                        'title' => $title,
                        'start' => $item->created_at,
                        'allDay' => false,
                        'infos' => $infos,
                        'className' => ($infos ? 'customCalendar' : ''));
                } else
                {
                    $select = $db->select()->from(array('p' => 'time'), array('p.*'));
                    $select->where('p.id = ?', $item->id);
                    $select->where('status = 0 ', '');
                    $infos = $db->fetchAll($select);
                    $title = 'Offline ' . $item->created_at . ' Reason :' . $item->reason;
                    if ($infos)
                    {
                        $title .= " ( Not Approved ) ";
                    }

                    $data[] = array(
                        'id' => $item->id,
                        'title' => $title,
                        'start' => $item->created_at,
                        'allDay' => false,
                        'infos' => $infos,
                        'className' => 'customOffline',
                        );
                }


            }
        }
        echo json_encode($data);
        exit;
    }


    private function _exportExcel_time_by_month($params)
    {
        set_time_limit(0);
        error_reporting(0);
        define('SIGN_SUNDAY_FULLDAY_CHECKIN' , 'C');

        ini_set('display_error', 0);
        $filename = 'Time By Month - ' . date('d/m/Y H:i:s');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $output = fopen('php://output', 'w');
        $QDateSpecial = new Application_Model_DateSpecial();
        $QDateNorm = new Application_Model_DateNorm();
        $QTeam = new Application_Model_Team();
        $teams_data = $QTeam->get_cache();
        if (isset($params['from']) && $params['from'] && isset($params['to']) && $params['to'])
        {
            $from = explode('/', $params['from']);
            $from = $from[2] . '-' . $from[1] . '-' . $from[0] . ' 00:00:00';
            $to = explode('/', $params['to']);
            $to = $to[2] . '-' . $to[1] . '-' . $to[0] . ' 23:59:59';
        } elseif (isset($params['from']) && $params['from'])
        {
            $from = explode('/', $params['from']);
            $from = $from[2] . '-' . $from[1] . '-' . $from[0] . ' 00:00:00';
            $to = date('Y-m-d 23:59:59');
        } elseif (isset($params['to']) && $params['to'])
        {
            $to = explode('/', $params['to']);
            $to = $to[2] . '-' . $to[1] . '-' . $to[0] . ' 23:59:59';
            $from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
        } else
        {
            $from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
            $to = date('Y-m-d 23:59:59');
        }


        $heads = array(
            'Area',
            'Team',
            'Title',
            'Code',
            'Name',
            'Join Date',
            'Off Date',
            );
        for ($i = 1; $i <= 31; $i++)
        {
            $heads[] = $i;
        }


        $heads[] = 'Tổng ngày công';
        $heads[] = 'Tổng ngày phép';
        $heads[] = 'Ca gãy';
        $heads[] = 'Thứ 7';
        $heads[] = 'Chủ Nhật';
        $heads[] = 'Ngày công đặc biệt x2';
        $heads[] = 'Ngày công đặc biệt x3';
        fputcsv($output, $heads);
        $db = Zend_Registry::get('db');
        $sql = "SELECT s.id, s.code, t.`created_at`, t.`off` , t.`shift` FROM time t
					INNER JOIN staff s
					ON t.staff_id=s.id

					AND t.approved_at IS NOT NULL
					AND t.approved_at <> ''
					AND t.approved_at <> 0

					 WHERE 	t.`created_at` >= '$from'
							AND t.`created_at` <= '$to'

				ORDER BY s.id";

        $timings = $db->query($sql);
        $timing_staffs = array();
        $date_staffs = array();
        $date_off = array();

        if (isset($params['team']) and $params['team'])
        {
            $teams = join(',', $params['team']);
            if (substr($teams, 0, 1) == ',')
                $teams = substr($teams, 1);
            foreach ($teams as $k => $v)
            {
                if ($v == SALES_TEAM)
                    $asm_id = 1;
            }
        }
        if (isset($params['area_id']) and $params['area_id'])
        {
            $ads = join(',', $params['area_id']);
            if (substr($ads, 0, 1) == ',')
                $ads = substr($ads, 1);
        }

        $db = Zend_Registry::get('db');
        $select = $db->select();
        $select->from(array('t' => 'time'), array('staff_id'));
        $select->join(array('s' => 'staff'), 't.staff_id = s.id', array(
            't.created_at',
            's.id',
            's.code',
            's.title',
            's.firstname',
            's.lastname',
            's.joined_at',
            's.off_date'));
        $select->join(array('tm' => 'team'), 's.team = tm.id', array('team' => 'tm.id',
                'team_name' => 'tm.name'));
        $select->join(array('r' => 'regional_market'), 'r.id = s.regional_market', array
            ());
        $select->join(array('a' => 'area'), 'a.id = r.area_id', array('name' => 'a.name'));
        $select->where('t.created_at >= ?', $from);
        $select->where('t.created_at <= ?', $to);
        $select->group('id');
        $select->order('s.id');


        if (isset($teams) and $teams)
        {
            $select->where('s.team in (?)', $teams);
        }

        if (isset($ads) and $ads)
        {
            $select->where('a.id in (?)', $ads);
        }
        //
        //        $sql = "SELECT
        //					s.id,
        //					s.`code`,
        //					s.firstname,
        //					s.lastname,
        //					a.`name`,
        //                    tm.`name` as team,
        //                    s.`joined_at`,
        //                    s.`off_date`
        //					FROM
        //						time t
        //					INNER JOIN staff s ON t.staff_id = s.id
        //                    INNER JOIN team tm ON s.team = tm.id
        //					INNER JOIN regional_market r ON r.id=s.regional_market
        //					INNER JOIN area a ON a.id=r.area_id";
        //
        //        if (!isset($teams) || !isset($ads))
        //        {
        //            $sql .= "  WHERE 	t.`created_at` >= '$from'
        //							AND t.`created_at` <= '$to'
        //					GROUP BY s.`id`
        //				ORDER BY s.id";
        //        } else
        //        {
        //
        //            $sql .= "   WHERE a.id in ($ads)
        //                    and tm.id in ($teams)
        //					t.`created_at` >= '$from'
        //							AND t.`created_at` <= '$to'
        //					GROUP BY s.`id`
        //				ORDER BY s.id";
        //        }


        $staffs = $db->fetchAll($select);

        $staffs_all = array();
        foreach ($staffs as $key => $staff)
        {
            $staffs_all[$staff['id']] = array(
                "code"      => $staff['code'],
                "name"      => $staff['firstname'] . " " . $staff['lastname'],
                "area"      => $staff['name'],
                "team"      => $staff['team'],
                "title"     => $teams_data[$staff['title']],
                "team_name" => $staff['team_name'],
                "joined_at" => $staff['joined_at'],
                "off_date"  => $staff['off_date'],
                );
        }

        // l?y ng? ngh?

        $saturdays = array();
        $sundays = array();
        $date_specials_double = $date_specials_triple = array();
        $month = '';
        if ($timings)
        {

            foreach ($timings as $key => $timing)
            {
                if (!isset($timing_staffs[$timing['id']]))
                {
                    $timing_staffs[$timing['id']] = array();
                }

                $d = date('d', strtotime($timing['created_at']));
                $month = date('m', strtotime($timing['created_at']));
                $id = $timing['id'];
                $date_staffs[$id][$d] = $timing['created_at'];
                if (isset($timing['off']) and $timing['off'] == 1)
                    $date_off[$id][$d] = $timing['off'];
                $timing_staffs[$id][$d] = $timing['shift'];
            }


            //load ngay cong dac biet trong thang

            $date_special = $QDateSpecial->get_date($month);
            $date_special_array = array();

            foreach ($date_special as $k => $v)
            {
                $date_special_array[$k]['date'] = $v['date'];
                $date_special_array[$k]['team'] = $v['team'];
                $date_special_array[$k]['type'] = $v['type'];
            }


            //ngay cong dinh muc trong thang
            $date_norm = $QDateNorm->get_cache();
            $date_norm_month = $date_norm[intval(preg_replace("/([0-9]+)\./", "\\1", $month))];
            foreach ($staffs_all as $id => $staff)
            {
        $off_month = false; // ki? tra th?g hi?n ngh? c?tr?ng th?g l?y d? li?u
        if ($staff['off_date'] == null or $staff['off_date'] == 0)
        {
            $off_day = 0;
        } else
        {
            $off_day = date('d', strtotime($staff['off_date']));
            $tmp = explode('/', $params['from']);
             if (isset($staff['off_date'])
                            and ( strtotime($staff['off_date']) >= strtotime($from) and  strtotime($staff['off_date']) <= strtotime($to) ))
            {
                $off_month = true;
            }
        }       $off_day = intval($off_day);


                $row = array();
                $row[0] = $staff['area'];
                $row[1] = $staff['team_name'];
                $row[2] = $staff['title'];
                $row[3] = $staff['code'];
                $row[4] = $staff['name'];
                $row[5] = $staff['joined_at'];
                $row[6] = $staff['off_date'];
                $saturday = 0;
                $sunday = 0;
                $d = count($row);
                $ca_gay = 0;
                $num = 0;
                $off = 0;
                $date_special_time_double = $date_special_time_triple = 0;
                for ($i = 1; $i <= 31; $i++)
                {
                    if ($i < 10)
                    {
                        $a = "0" . $i;
                    } else
                    {
                        $a = $i . "";
                    }

                    $date = date('Y-m-d', strtotime($date_staffs[$id][$a]));
                    $week = date('l', strtotime($date));
                    $sign = SIGN_TIME_CHECKIN_WEEKDAY_FULLDAY;
                    if ($week == 'Saturday')
                    {

                        if ($timing_staffs[$id][$a] == 2)
                        {
                            $saturday = $saturday + 0.5;

                        } else
                        {
                            $saturday++;
                        }

                        $saturdays[$id] = $saturday;
                    } elseif ($week == 'Sunday')
                    {
                        if ($timing_staffs[$id][$a] == 2)
                        {
                            $sunday = $sunday + 0.5;
                            $sign = SIGN_TIME_CHECKIN_SUNDAY_HALFDAY;
                        } else
                        {
                            $sunday++;
                            $sign = SIGN_TIME_CHECKIN_SUNDAY_FULLDAY;
                        }
                        $sundays[$id] = $sunday;
                    }

                    foreach ($date_special_array as $k => $v)
                    {
                        if ($staff['team'] == $v['team'] and $v['type'] and date('d', strtotime($date_staffs[$id][$a])) ==
                            $v['date'])
                        {
                            if ($v['type'] == 2)
                            {
                                if ($timing_staffs[$id][$a] == 2)
                                {
                                    $date_special_time_double = $date_special_time_double + 0.5;
                                } else
                                {
                                    $date_special_time_double++;
                                }

                                $date_specials_double[$id] = $date_special_time_double;
                            } elseif ($v['type'] == 3)
                            {
                                if ($timing_staffs[$id][$a] == 2)
                                {
                                    $date_special_time_triple = $date_special_time_triple + 0.5;
                                } else
                                {
                                    $date_special_time_triple++;
                                }
                                $date_specials_triple[$id] = $date_special_time_triple;
                            }

                            $sign = SIGN_TIME_CHECKIN_SPECIAL_DAY;
                        }
                    }


                    if (isset($timing_staffs[$id][$a]) and $off_day > 0 and $off_month == true)
                    {
                        if (intval($i) < $off_day)
                        {
                            $row[$d + $i - 1] = 'X';
                            $num++;
                            if ($timing_staffs[$id][$a] == 2)
                            {
                                $ca_gay++;
                            }
                        }
                    } else
                    {


                        if (isset($timing_staffs[$id][$a]) and !$date_off[$id][$a])
                        {


                            if (isset($timing_staffs[$id][$a]) and $timing_staffs[$id][$a] == 0)
                            {
                                $row[$d + $i - 1] = $sign;
                                $num++;
                            } else
                            {
                                $row[$d + $i - 1] = SIGN_TIME_CHECKIN_WEEKDAY_HALFDAY;
                                $num++;
                            }
                            // cham cong nua ngay
                            if ($timing_staffs[$id][$a] == 1)
                            {
                                $ca_gay++;
                            }


                        }
                        if ($date_off[$id][$a] == 1)
                        {
                            $row[$d + $i - 1] = SIGN_TIME_CHECKIN_OFF_DAY;
                            $off++;
                        }
                    }

                }

                for ($i = 1; $i <= 31; $i++)
                {
                    if (!isset($row[$d + $i - 1]))
                    {
                        $row[$d + $i - 1] = '-';
                    }
                }

                $row[] = $num;
                $row[] = $off;
                $row[] = $ca_gay;
                $row[] = $saturdays[$id];
                $row[] = $sundays[$id];
                $row[] = $date_specials_double[$id];
                $row[] = $date_specials_triple[$id];
                ksort($row);
                fputcsv($output, $row);
            }
            if (isset($params['asm']) and $params['asm'])
            {
                //export ASM timing
                $sql = "SELECT
					s.id,
					s.`code`,
					s.firstname,
					s.lastname,
					a.`name`,
                    tm.`name` as team,
                    s.`joined_at`,
                    s.`off_date`,
                    s.`title`,
					FROM
						staff s
                    INNER JOIN team tm ON s.team = tm.id

					INNER JOIN regional_market r ON r.id=s.regional_market
					INNER JOIN area a ON a.id=r.area_id
					WHERE s.group_id IN (5,16)
                    GROUP BY s.`id`
				ORDER BY s.id";
                $staffs = $db->query($sql, array(ASM_ID, ASMSTANDBY_ID));
                $asm_all = array();
                foreach ($staffs as $key => $asm)
                {
                    $asm_all[$asm['id']] = array(
                        "code" => $asm['code'],
                        "name" => $asm['firstname'] . " " . $asm['lastname'],
                        "area" => $asm['name'],
                        "team" => $asm['team'],
                        "title" => $asm['title'],
                        "joined_at" => $asm['joined_at'],
                        "off_date" => $asm['off_date'],
                        );
                }


                foreach ($asm_all as $id => $asm)
                {
                    $row = array();
                    $row[0] = $asm['area'];
                    $row[1] = $asm['team'];
                    $row[2] = $asm['title'];
                    $row[3] = $asm['code'];
                    $row[4] = $asm['name'];
                    $row[5] = $asm['joined_at'];
                    $row[6] = $asm['off_date'];
                    for ($i = 7; $i <= 37; $i++)
                    {

                        $date = date('Y') . '/' . $month . '/' . intval($i - 5);
                        $week = date('l', strtotime($date));
                        if ($week == 'Sunday')
                            $row[$i] = '-';
                        else
                            $row[$i] = 'X';
                    }

                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = $date_norm_month;
                    ksort($row);
                    fputcsv($output, $row);
                }
            }
            exit;
        }
    }

    /*
     * Export off list of Offifce
     * */

    private function _exportTimeOffOffice($params)
    {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Name',
            'Email',
            'Team',
            'Area',
            'Total'
        );

        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();
        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache_team();
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache();
        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        if (isset($params['from_date']) && $params['from_date'] && isset($params['to_date']) && $params['to_date'])
        {
            $from = explode('/', $params['from_date']);
            $from = $from[2] . '-' . $from[1] . '-' . $from[0] . ' 00:00:00';
            $to = explode('/', $params['to_date']);
            $to = $to[2] . '-' . $to[1] . '-' . $to[0] . ' 23:59:59';
        } elseif (isset($params['from_date']) && $params['from_date'])
        {
            $from = explode('/', $params['from_date']);
            $from = $from[2] . '-' . $from[1] . '-' . $from[0] . ' 00:00:00';
            $to = date('Y-m-d 23:59:59');
        } elseif (isset($params['to_date']) && $params['to_date'])
        {
            $to = explode('/', $params['to_date']);
            $to = $to[2] . '-' . $to[1] . '-' . $to[0] . ' 23:59:59';
            $from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
        } else
        {
            $from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
            $to = date('Y-m-d 23:59:59');
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $db = Zend_Registry::get('db');

        $warranty_department = DEPARTMENT_WARRANTY_CENTER;

        $sql = "SELECT  s.id,
                        s.`code`,
                        s.firstname,
                        s.email,
                        s.lastname,
                        s.department,
                        a.`name` as area_name,
                        tm.`name` AS team,
                        s.`joined_at`,
                        p.date,
                        COUNT(DISTINCT CASE WHEN p.`shift` = 0 THEN p.id END) as fulltime,
                        COUNT(DISTINCT CASE WHEN p.`shift` = 1 THEN p.id END) as parttime

                    FROM
                        off_history p
                    INNER JOIN staff s ON p.staff_id = s.id
                    INNER JOIN team tm ON s.team = tm.id
                    INNER JOIN regional_market r ON r.id = s.regional_market
                    INNER JOIN area a ON a.id = r.area_id
                    INNER JOIN time t ON p.time_id = t.id
                    WHERE
                        s.is_officer = 1
                        or s.department = $warranty_department
                        and p.date >= '$from'
                        and p.date <= '$to'
                    GROUP BY
                        p.staff_id
                    ORDER BY
                        s.id";



        $all_staff = $db->query($sql);

        $index = 2;
        $intCount = 1;

        if ($all_staff)
            foreach ($all_staff as $item)
            {
                $alpha = 'A';
                $sheet->setCellValue($alpha++ . $index, $intCount++);
                $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                    PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue($alpha++ . $index, $item['firstname']. ' ' . $item['lastname']);
                $sheet->setCellValue($alpha++ . $index, $item['email']);

                $sheet->setCellValue($alpha++ . $index, isset($item['team']) ? $item['team'] :
                    '');
                $sheet->setCellValue($alpha++ . $index, isset($item['area_name']) ? $item['area_name'] :
                    '');

                $sheet->setCellValue($alpha++ . $index, $item['fulltime'] + $item['parttime'] * 0.5 );
                $index++;
            }

        $filename = 'EXPORT_OFF_DATE_OFFICE ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;

    }

    private function _exportTimeOff($data)
    {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Firstname',
            'Lastname',
            'Email',
            'Department',
            'Team',
            'Title',
            'Province',
            'Contract Term',
            'Off Date');
        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;

        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;
        $intCount = 1;
        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();
        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache();
        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();
        $QOff_Increase = new Application_Model_OffDateAdd();
        $QOff_Decrease = new Application_Model_OffHistory();


        try
        {
            if ($data)
                foreach ($data as $item)
                {

                    $date = intval($QOff_Increase->getDate(DAY_OFF_BEGIN, $item['staff_id']) - $QOff_Decrease->
                        getDate(DAY_OFF_BEGIN, $item['staff_id']));
                    $alpha = 'A';
                    $sheet->setCellValue($alpha++ . $index, $intCount++);
                    $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                        PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValue($alpha++ . $index, $item['firstname']);
                    $sheet->setCellValue($alpha++ . $index, $item['lastname']);
                    $sheet->setCellValue($alpha++ . $index, $item['email']);
                    $sheet->setCellValue($alpha++ . $index, isset($departments[$item['department']]) ?
                        $departments[$item['department']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($teams[$item['team']]) ? $teams[$item['team']] :
                        '');
                    $sheet->setCellValue($alpha++ . $index, $item['title']);
                    $sheet->setCellValue($alpha++ . $index, isset($regional_markets[$item['regional_market']]) ?
                        $regional_markets[$item['regional_market']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($contract_terms[$item['contract_term']]) ?
                        $contract_terms[$item['contract_term']] : '');
                    $sheet->setCellValue($alpha++ . $index, $date);
                    $index++;
                }
        }
        catch (exception $e)
        {
            var_dump($e->getMessages());
            exit;
        }

        $filename = 'Time_OFFReport ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    public function createTimeOffAction()
    {
        $flashMessenger = $this->_helper->flashMessenger;
        $from_date = $this->getRequest()->getParam('from_date');
        $to_date   = $this->getRequest()->getParam('to_date');
        $staff_id  = $this->getRequest()->getParam('staff_id');
        $reason    = trim($this->getRequest()->getParam('reason'));
        $shift     = $this->getRequest()->getParam('shift');
        $type      = $this->getRequest()->getParam('type');
        $id        = $this->getRequest()->getParam('id');
        $QStaff    = new Application_Model_Staff();
        $db        = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => 'staff'), array('id', 'staff_name' =>'CONCAT(s.firstname," ",s.lastname," - ",s.code," - ",t.name)'))
                ->join(array('t' =>'team'), 't.id = s.team', array())
                -> order('s.firstname');
        $staffs = $db->fetchAll($select);

        if ($this->getRequest()->isPost())
        {
            if (!$type)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select type off');
                $this->_redirect('/time/create-time-off');
            }

            if (!$from_date)
            {
                $flashMessenger->setNamespace('error')->addMessage('please select "from date"');
                $this->_redirect('/time/create-time-off');
            }

            if (!$to_date and $type != CHILDBEARING)
            { //Nếu thai sản có thể cập nhật sau
                $flashMessenger->setNamespace('error')->addMessage('Please select "to date"');
                $this->_redirect('/time/create-time-off');
            }

            if (!$staff_id)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select a staff');
                $this->_redirect('/time/create-time-off');
            }

            $from_date = explode('/', $from_date);
            $from_date = $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0];

            if (trim($to_date))
            {
                $to_date = trim($to_date);
                $to_date = explode('/', $to_date);
                $to_date = $to_date[2] . '-' . $to_date[1] . '-' . $to_date[0];
            }else{
                $to_date = NULL;
            }

            $startTime   = strtotime($from_date);
            $endTime     = strtotime($to_date);

            if($endTime < $startTime):
                $flashMessenger->setNamespace('error')->addMessage('"From time" must be less "to time"');
                $this->_redirect('/time/create-time-off');
            endif;

            $QTime       = new Application_Model_Time();
            $QOffHistory = new Application_Model_OffHistory();
            $staff       = $QStaff->find($staff_id)->current();
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $created_at  = date('Y-m-d H:i:s');
            $db->beginTransaction();
            try
            {
                $idChildBearing = NULL;
                $arrOffLongTime = array(
                        CHILDBEARING,
                        CHILDBEARING_KL,
                        OFF_KL
                );


                if($type != CHILDBEARING)
                {
                    for ($i = $startTime; $i <= $endTime; $i = $i + 86400)
                    {
                        $date = date('Y-m-d', $i);
                        $d = date('d', strtotime($date));
                        $m = intval(date('m', strtotime($date)));
                        $y = date('Y', strtotime($date));
                        $day_name = date('N', strtotime($date));
                        if ($day_name == 7)
                        {
                            continue;
                        }

                        //check duplicate date
                        $selectDuplicate = $db->select()
                                ->from(array('p' => 'off_history'), array('p.*'))
                                ->where('p.staff_id = ?', $staff_id)
                                ->where('p.`date` = ?', $date);
                        ;
                        $checkDuplicate = $db->fetchRow($selectDuplicate);

                        if ($checkDuplicate)
                        {
                            $flashMessenger->setNamespace('error')->addMessage($staff->firstname . ' ' . $staff->
                                lastname . ' has already registered on :' . $date);
                            $this->_redirect('/time/create-time-off');
                        }

                        $data = array(
                            'staff_id'        => $staff_id,
                            'off'             => 1,
                            'reason'          => $reason,
                            'off_type'        => $type,
                            'shift'           => $shift,
                            'regional_market' => $staff->regional_market,
                            'created_by'      => $userStorage->id,
                            'created_at'      => $date,
                            'approved_at'     => $created_at,
                            'approved_by'     => $userStorage->id,
                            'status'          => 1
                        );
                        $time_id = $QTime->insert($data);

                        //update history
                        $data_history = array(
                            'staff_id'        => $staff_id,
                            'month_id'        => $m,
                            'date'            => $date,
                            'type'            => $type,
                            'reason'          => $reason,
                            'shift'           => $shift,
                            'off_date'        => 0,
                            'time_id'         => $time_id,
                            );
                        $QOffHistory->insert($data_history);
                    } // End for
                }

                $db->commit();
                $flashMessenger->setNamespace('success')->addMessage('Success');
                if ($type == CHILDBEARING OR $type == CHILDBEARING_KL)
                {
                    $this->_redirect('/time/list-childbearing');
                }
                $this->_redirect('/time/');
            }
            catch (exception $e)
            {
                $db->rollBack();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                $this->_redirect('/time/create-time-off');
            }
        } // End if post

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->staffs = $staffs;
    }

    public function listChildbearingAction()
    {
        $flashMessenger = $this->_helper->flashMessenger;
        $from_date = $this->getRequest()->getParam('from_date');
        $to_date   = $this->getRequest()->getParam('to_date');
        $code      = $this->getRequest()->getParam('code');
        $email     = $this->getRequest()->getParam('email');
        $name      = $this->getRequest()->getParam('name');
        $off_type  = $this->getRequest()->getParam('off_type');
        $export    = $this->getRequest()->getParam('export');
        $total     = 0;
        $page      = $this->getRequest()->getParam('page');
        $limit     = 20;
        $params = array(
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'code'      => $code,
            'email'     => $email,
            'name'      => $name,
            'off_type'  => $off_type
            );
        $QTime = new Application_Model_Time();
        if($export){
            $list = $QTime->fetchChildbearing($page, 0, $total, $params);
            $this->_exportChildBearing($list);
        }

        $list = $QTime->fetchChildbearing($page, $limit, $total, $params);
        $this->view->list   = $list;
        $this->view->params = $params;
        $this->view->limit  = $limit;
        $this->view->total  = $total;
        $this->view->url = HOST . 'time/list-childbearing/' . ($params ? '?' .
            http_build_query($params) . '&' : '?');
        $this->view->offset         = $limit * ($page - 1);
        $messages                   = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error             = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages       = $messages;
        $this->view->messages_error = $messages_error;

    }

    public function deleteChildbearingAction()
    {
        $id = $this->getRequest()->getParam('id');
        $db = Zend_Registry::get('db');
        $QOffChildbearing = new Application_Model_OffChildbearing();
        $flashMessenger = $this->_helper->flashMessenger;
        $db->beginTransaction();
        try
        {
            $where = $QOffChildbearing->getAdapter()->quoteInto('id = ?', $id);
            $QOffChildbearing->delete($where);
            $db->commit();
            $flashMessenger->setNamespace('success')->addMessage('Done');
            $this->_redirect('/time/list-childbearing');
        }
        catch (exception $e)
        {
            $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            $db->rollBack();
            $this->_redirect('/time/list-childbearing');
        }
    }

    private function _exportChildBearing($list){
        set_time_limit(0);
        error_reporting(0);
        ini_set('memory_limit', -1);
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Code',
            'Name',
            'Tilte',
            'From',
            'To',
            'Reason',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $index = 2;

        $offType  = unserialize(OFF_TYPE);
        foreach($list as $key => $value):
            $alpha = 'A';
            $sheet->getCell($alpha++.$index)->setValueExplicit( trim($value['code']), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++ . $index, $value['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $value['title']);
            $sheet->setCellValue($alpha++ . $index, date('d/m/Y',strtotime($value['from_date'])));
            $sheet->setCellValue($alpha++ . $index, date('d/m/Y',strtotime($value['to_date'])));
            $sheet->setCellValue($alpha++ . $index, $value['reason']);
            $index++;
        endforeach;

        $filename = 'off_childbearing_' . date('d-m-Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    public function createOffLongTimeAction(){

        $flashMessenger = $this->_helper->flashMessenger;
        $QStaff         = new Application_Model_Staff();
        $QPreTimeOff    = new Application_Model_PreTimeOff();
        $id             = $this->getRequest()->getParam('id');
        $db             = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => 'staff'), array('id','staff_name' => 'CONCAT(s.firstname," ",s.lastname," - ",s.code," - ",t.name)'))               
                ->join(array('t' =>'team'), 't.id = s.team', array())
                ->order('s.firstname');
        $staffs = $db->fetchAll($select);

          if($id){
                $row = $QPreTimeOff->find($id)->current();
                $this->view->row = $row;
            }

        $messages                     = $flashMessenger->setNamespace('error')->getMessages();
        $messages_success             = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages         = $messages;
        $this->view->messages_success = $messages_success;
        $this->view->staffs           = $staffs;
    }

    public function saveOffLongTimeAction(){
        $flashMessenger = $this->_helper->flashMessenger;
        $from_date      = $this->getRequest()->getParam('from_date');
        $to_date        = $this->getRequest()->getParam('to_date');
        $staff_id       = $this->getRequest()->getParam('staff_id');
        $reason         = $this->getRequest()->getParam('reason');
        $shift          = $this->getRequest()->getParam('shift');
        $type           = $this->getRequest()->getParam('type');
        $id             = $this->getRequest()->getParam('id');
        $QStaff         = new Application_Model_Staff();
        $QPreTimeOff    = new Application_Model_PreTimeOff();
        $db             = Zend_Registry::get('db');
        $back_url = '/time/create-off-long-time';
        if($id){
            $back_url .='?id='.$id;
        }
        
        if ($this->getRequest()->isPost())
        {
            if (!$type)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select type off');
                $this->_redirect($back_url);
            }

            if (!$from_date)
            {
                $flashMessenger->setNamespace('error')->addMessage('please select "from date"');
                $this->_redirect($back_url);
            }

            if (!$to_date)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select "to date"');
                $this->_redirect($back_url);
            }

            if (!$staff_id)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select a staff');
                $this->_redirect($back_url);
            }

            $from_date = explode('/', $from_date);
            $from_date = $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0];

            if (trim($to_date))
            {
                $to_date = trim($to_date);
                $to_date = explode('/', $to_date);
                $to_date = $to_date[2] . '-' . $to_date[1] . '-' . $to_date[0];
            }else{
                $to_date = NULL;
            }

            $startTime   = strtotime($from_date);
            $endTime     = strtotime($to_date);

            if($endTime < $startTime):
                $flashMessenger->setNamespace('error')->addMessage('Thời gian bắt đầu phải nhỏ hơn thời gian đến"');
                $this->_redirect($back_url);
            endif;

            $QTime       = new Application_Model_Time();
            $QOffHistory = new Application_Model_OffHistory();
            $staff       = $QStaff->find($staff_id)->current();
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $created_at  = date('Y-m-d H:i:s');
            $db->beginTransaction();
            try
            {
                $data = array(
                    'staff_id'   => $staff_id,
                    'from_date'  => $from_date,
                    'to_date'    => $to_date,
                    'off_type'   => $type,
                    'reason'     => $reason,
                    'created_at' => date('Y-m-d h:i:s'),
                    'created_by' => $userStorage->id
                );

                if(!$id){
                    //check date
                    $selectDate = $db->select()
                        ->from(array('p'=>'pre_time_off'))
                        ->where('to_date >= ?',$from_date)
                        ->where('staff_id = ?',$staff_id);
                    $resultCheck = $db->fetchRow($selectDate);
                    if($resultCheck){
                        $flashMessenger->setNamespace('error')->addMessage('Nhân viên này đã đăng kí nghỉ vào thời gian này!');
                        $this->_redirect($back_url);
                    }

                    //INSERT VÀO BẢNG PRE TIME OFF
                    $id = $QPreTimeOff->insert($data);
                }else{
                    $where = $QPreTimeOff->getAdapter()->quoteInto('id = ?',$id);
                    $QPreTimeOff->update($data,$where);
                    $stmt  = $db->query('DELETE `off_history`, `time`
                        FROM `off_history`
                        INNER JOIN `time` ON `off_history`.time_id = `time`.id
                        WHERE off_history.pre_time_off_id = ?',array($id));
                    $stmt->execute();
                }

                //INSERT VÀO OFF HISTORY AND TIME
                for ($i = $startTime; $i <= $endTime; $i = $i + 86400)
                {
                    $date     = date('Y-m-d', $i);
                    $d        = date('d', strtotime($date));
                    $m        = intval(date('m', strtotime($date)));
                    $y        = date('Y', strtotime($date));
                    $day_name = date('N', strtotime($date));//chủ nhật

                    if ($day_name == 7){
                        continue;
                    }

                    //check duplicate date
                    $selectDuplicate = $db->select()
                        ->from(array('p' => 'off_history'), array('p.*'))
                        ->where('p.staff_id = ?', $staff_id)
                        ->where('p.`date` = ?', $date);
                    ;

                    $checkDuplicate = $db->fetchRow($selectDuplicate);
                    if ($checkDuplicate)
                    {
                        $flashMessenger->setNamespace('error')->addMessage($staff->firstname .' '. $staff->lastname. ' has already registered on :' . $date);
                        $this->_redirect($back_url);
                    }

                    $data_time = array(
                        'staff_id'        => $staff_id,
                        'off'             => 1,
                        'reason'          => $reason,
                        'off_type'        => $type,
                        'shift'           => 0,
                        'regional_market' => $staff->regional_market,
                        'created_by'      => $userStorage->id,
                        'created_at'      => $date,
                        'approved_at'     => $created_at,
                        'approved_by'     => $userStorage->id,
                        'status'          => 1
                    );
                    $time_id = $QTime->insert($data_time);

                    //INSERT HISTORY
                    $data_history = array(
                        'staff_id'        => $staff_id,
                        'month_id'        => $m,
                        'date'            => $date,
                        'type'            => $type,
                        'reason'          => $reason,
                        'shift'           => 0,
                        'off_date'        => 0,
                        'time_id'         => $time_id,
                        'pre_time_off_id' => $id
                    );
                    $QOffHistory->insert($data_history);
                }//END INSERT TIME AND OFF HISTORY

                $status = in_array($type, array(PERMIT, OFF_TEMP))
                    ? My_Staff_Status::Temporary_Off
                    : ($type == CHILDBEARING ? My_Staff_Status::Childbearing : null);

                if ($status) {
                    $data = array('status' => $status);
                    $where = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
                    $QStaff->update($data, $where);
                }

                $db->commit();
                $flashMessenger->setNamespace('success')->addMessage('Done');
                $this->_redirect('/time/list-pre-time-off');
            }
            catch (exception $e)
            {
                $db->rollBack();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                $this->_redirect($back_url);

            }
        } // End if post
    }

    public function listPreTimeOffAction(){
        $code     = $this->getRequest()->getParam('code');
        $email    = $this->getRequest()->getParam('email');
        $name     = $this->getRequest()->getParam('name');
        $off_type = $this->getRequest()->getParam('off_type');
        $page     = $this->getRequest()->getParam('page');
        $bh       = $this->getRequest()->getParam('bh');
        $detach  = $this->getRequest()->getParam('detach');
        $total    = 0;
        $limit    = LIMITATION;
        $params   = array(
                'code'     => $code,
                'email'    => $email,
                'name'     => $name,
                'off_type' => $off_type,
                'bh'       => $bh,
                'detach' => $detach
            );

        $QPreTimeOff = new Application_Model_PreTimeOff();
        $list = $QPreTimeOff->fetchPagination($page,$limit,$total,$params);
        $this->view->list  = $list;

        $this->view->params = $params;
        $this->view->limit  = $limit;
        $this->view->total  = $total;
        $this->view->url = HOST . 'time/list-pre-time-off/' . ($params ? '?' .http_build_query($params) . '&' : '?');

        $this->view->offset           = $limit * ($page - 1);
        $flashMessenger               = $this->_helper->flashMessenger;
        $messages                     = $flashMessenger->setNamespace('error')->getMessages();
        $messages_success             = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages         = $messages;
        $this->view->messages_success = $messages_success;
    }

    public function createTimeDetailAction(){
        $id = $this->getRequest()->getParam('id');
        if($id){
            $db = Zend_Registry::get('db');
            $cols = array(
                    'p.*',
                    'staff_name' => 'CONCAT(s.firstname," ",s.lastname)',
                );
            $select = $db->select()
                ->from(array('p'=>'pre_time_off'),$cols)
                ->join(array('s'=>'staff'),'s.id= p.staff_id',array())
                ->where('p.id = ?',$id);
            $row = $db->fetchRow($select);
            $this->view->row = $row;

            $selectList = $db->select()
                ->from(array('p'=>'off_childbearing'),array('p.*'))
                ->where('pre_time_off_id = ?',$id);
            $list = $db->fetchAll($selectList);
            $this->view->list = $list;
        }
    }

    public function saveTimeDetailAction(){
        $id               = $this->getRequest()->getParam('id');
        $from_date        = $this->getRequest()->getParam('from_date');
        $to_date          = $this->getRequest()->getParam('to_date');
        $off_type         = $this->getRequest()->getParam('off_type');
        $reason           = $this->getRequest()->getParam('reason');
        $staff_id         = $this->getRequest()->getParam('staff_id');

        $db               = Zend_Registry::get('db');
        $QOffChildbearing = new Application_Model_OffChildbearing();
        $QPreTimeOff      = new Application_Model_PreTimeOff();
        $QStaff           = new Application_Model_Staff();
        $flashMessenger = $this->_helper->flashMessenger;
        $preTimeOff     = $QPreTimeOff->find($id)->current();
        $strPreTimeOff  = ( strtotime($preTimeOff->to_date) -  strtotime($preTimeOff->from_date) ) / 86400 +1;
        $staff          = $QStaff->find($staff_id)->current();
        $strTimeDetach  = 0;
        $back_url = '/time/list-pre-time-off?bh=1';
        if($this->getRequest()->isPost()){
            $db->beginTransaction();
            try {

                $created_at = date('Y-m-d H:i:s');
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                if(count($from_date) <= 0){
                    $flashMessenger->setNamespace('error')->addMessage('Please add time');
                    $this->_redirect($back_url);
                }

                foreach ($from_date as $key => $value) {
                    $tmpFrom       = explode('/', $value);
                    $tmpFrom       = date('Y-m-d',strtotime($tmpFrom[2].'-'.$tmpFrom[1].'-'.$tmpFrom[0]));
                    $tmpTo         = explode('/', $to_date[$key]);
                    $tmpTo         = date('Y-m-d',strtotime($tmpTo[2].'-'.$tmpTo[1].'-'.$tmpTo[0]));
                    $strTimeDetach += ( strtotime($tmpTo)  - strtotime($tmpFrom) )  / 86400 + 1;

                    //Insert data off childbearing    
                    $data = array(
                        'pre_time_off_id' => $id,
                        'staff_id'        => $staff_id,
                        'from_date'       => $tmpFrom,
                        'to_date'         => $tmpTo,
                        'off_type'        => $off_type[$key],
                        'reason'          => $reason[$key],
                        'created_at'      => $created_at,
                        'created_by'      => $userStorage->id
                    );
                    $idChildBearing = $QOffChildbearing->insert($data);

                    //update stauts approved
                    $data_pre = array(
                            'approved_at' => $created_at,
                            'approved_by' => $userStorage->id
                        );
                    $where_pre = $QPreTimeOff->getAdapter()->quoteInto('id = ?',$id);
                    $QPreTimeOff->update($data_pre,$where_pre);
                }

                if($strPreTimeOff != $strTimeDetach){
                    $flashMessenger->setNamespace('error')->addMessage('Total date detach is not enough!');    
                    $this->_redirect($back_url);
                }
                $db->commit();
                $flashMessenger->setNamespace('success')->addMessage('Done');
                $this->_redirect($back_url);
            } catch (Exception $e) {
                $db->rollBack();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());    
                $this->_redirect($back_url);
            }
        }//End If
    }

    // CHILDBEARING
    public function createChildbearingAction(){
        $id = $this->getRequest()->getParam('id');
        $flashMessenger = $this->_helper->flashMessenger;
        $QStaff         = new Application_Model_Staff();
        $db        = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => 'staff'), array('id', 'staff_name' =>'CONCAT(s.firstname," ",s.lastname," - ",s.code," - ",t.name)'))
                ->join(array('t' =>'team'), 't.id = s.team', array())
                ->order('s.firstname');
        $staffs = $db->fetchAll($select);
        if($id){
            $QOffChildbearing = new Application_Model_OffChildbearing();
            $row = $QOffChildbearing->find($id)->current();
            $this->view->row = $row;
            $this->view->id = $id;
        }

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->staffs = $staffs;
    }

    public function saveChildbearingAction(){
        $flashMessenger = $this->_helper->flashMessenger;
        $from_date      = $this->getRequest()->getParam('from_date');
        $to_date        = $this->getRequest()->getParam('to_date');
        $staff_id       = $this->getRequest()->getParam('staff_id');
        $reason         = trim($this->getRequest()->getParam('reason'));
        $type           = $this->getRequest()->getParam('type');
        $id             = $this->getRequest()->getParam('id');
        $back_url = $this->getRequest()->getParam('back_url');
        if(!$back_url){
            $back_url = '/time/list-childbearing';
        }

        if ($this->getRequest()->isPost())
        {
            if (!$type)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select type off');
                $this->_redirect($back_url);
            }

            if (!$from_date)
            {
                $flashMessenger->setNamespace('error')->addMessage('please select "from date"');
                $this->_redirect($back_url);
            }

            if (!$to_date)
            { 
                $flashMessenger->setNamespace('error')->addMessage('Please select "to date"');
                $this->_redirect($back_url);
            }

            if (!$staff_id)
            {
                $flashMessenger->setNamespace('error')->addMessage('Please select a staff');
                $this->_redirect($back_url);
            }

            $from_date = My_Date::normal_to_mysql($from_date);
            $to_date = My_Date::normal_to_mysql($to_date);
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $QOffChildbearing = new Application_Model_OffChildbearing();
            $data = array(
                'from_date' => $from_date,
                'to_date' => $to_date,
                'off_type' => $type,
                'staff_id' => $staff_id,
                'reason' =>$reason
            );
            if($id) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $userStorage->id;
            }else{
                $data['created_at'] =date('Y-m-d H:i:s');
                $data['created_by'] = $userStorage->id;
            }
            $result = $QOffChildbearing->save($data,$id);
            if( isset($result['code']) AND $result['code'] == 0){
                $flashMessenger->setNamespace('success')->addMessage('Done');
            }else{
                $flashMessenger->setNamespace('error')->addMessage($result['message']);
            }

            $this->_redirect($back_url);
        } // End if post
    }

    // Export Check In Report
    private function _exportCheckInReport($data, $params) {

        //echo "<pre>"; print_r($data); die;

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'Staff Code',
            'Staff Name',
            'Current Group',
            'Table Group',
            'Joined At',
            'Off Date',
            'PC First Training',
            'Working Day',
            'CheckIn No CheckOut',
            'Day Off',
            'Late Time',
            'Not Check In',
            'Sick Leave with Cert.',
            'Sick Leave without Cert.',
            'Bussiness Leave',
            'Annual Leave',
            'Ordination Leave',
            'Maternity Leave',
            'Other Leave',
			'Sterile Leave',
			'Marrirage Leave',
			'Burial Leave',
			'Military Training Leave',
			'Foreigner Go Home',
            'Wait Assign Shop',
            'Change Position',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', 'Check In For : '.date('d/m/Y', strtotime($params['period_from']))." - ".date('d/m/Y', strtotime($params['period_to'])));

        $sheet->mergeCells('A1:S1');
        $sheet->mergeCells('A2:S2');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:S1")->applyFromArray($style);

        $alpha    = 'A';
        $index    = 3;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 4;
        $cnt = 0;

        // Check Position History
        $table_pc = array(PGPB_ID);
        $table_sale = array(SALES_ID, ASM_ID, ASMSTANDBY_ID, RM_ID, RMSTANDBY_ID, 33, 34, 31);
        $table_bm = array(ABM_ID, BM_ID);
        $table_tms = array(37, 38);
        $table_pcm = array(36, TRAINING_TEAM_ID);
        $table_admin = array(SALES_ADMIN_ID);
        $table_am = array(AM_ID);

        $table_group = array(PGPB_ID, SALES_ID, BM_ID, 37, TRAINING_TEAM_ID, SALES_ADMIN_ID, AM_ID);

        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();
       
        // for ($i=0;$i<count($data); $i++) {

            for ($j=0;$j<count($data);$j++) {

                $params2 = array();

                if ( in_array($data[$j]['staff_group_id'], $table_pc) ) { 
                    $tmp_group = array_diff($table_group, $table_pc);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_sale) ) {
                    $tmp_group = array_diff($table_group, $table_sale);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_bm) ) { 
                    $tmp_group = array_diff($table_group, $table_bm);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_tms) ) { 
                    $tmp_group = array_diff($table_group, $table_tms);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_pcm) ) { 
                    $tmp_group = array_diff($table_group, $table_pcm);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_admin) ) { 
                    $tmp_group = array_diff($table_group, $table_admin);
                }
                if ( in_array($data[$j]['staff_group_id'], $table_am) ) { 
                    $tmp_group = array_diff($table_group, $table_am);
                }

                $not_check_in = 0;

                // Check Off Date
                if (is_null($data[$j]['off_date'])) { $day_end = strtotime($params['period_to']); } 
                else { 

                    if (strtotime($data[$j]['off_date']) <= strtotime($params['period_to'])) {
                        $day_end = strtotime("-1 Day", strtotime($data[$j]['off_date'])); 
                    } else {
                        $day_end = strtotime($params['period_to']); 
                    }

                }

                // Check Joined Date
                if (strtotime($params['period_from']) < strtotime($data[$j]['joined_at'])) { $day_start = strtotime($data[$j]['joined_at']); } 
                else { $day_start = strtotime($params['period_from']); }

                // Check Change Position
                $change_position = "";
                $change_position_id = $a = 0;
                foreach ($tmp_group as $key => $value) {
                    $result_table = $QStaffCheckInLog->checkTable($data[$j]['staff_id'], $value);

                    if ( !is_null($result_table['last_action']) ) { 
                        if (
                            strtotime($params['period_from']." 00:00:00") <= strtotime($result_table['last_action']) && 
                            strtotime($params['period_to']." 23:59:59") >= strtotime($result_table['last_action'])
                        ) {
                            $change_position = $result_table['table_group']; 
                            $change_position_id = $result_table['table_group_id']; 
                        }
                    }
                }

                // echo "Test : ".$change_position." | ".$change_position_id; echo "<br/>"; die;

                $training_day       = $data[$j]['training_day'];
                $work_day           = $data[$j]['work_day'];
                $work_day_no_out    = $data[$j]['work_day_no_out'];
                $off_date_leave     = $data[$j]['off_date_leave'];

                $sick_leave_2       = $data[$j]['sick_leave_2'];
                $sick_leave_1       = $data[$j]['sick_leave_1'];
                $personal_leave     = $data[$j]['personal_leave'];
                $vacation_leave     = $data[$j]['vacation_leave'];
                $ordination_leave   = $data[$j]['ordination_leave'];
                $maternity_leave    = $data[$j]['maternity_leave'];
                $other_leave        = $data[$j]['other_leave'];

				$sterile_leave		= $data[$j]['sterile_leave'];
				$marrirage_leave	= $data[$j]['marrirage_leave'];
				$burial_leave		= $data[$j]['burial_leave'];
				$military_leave		= $data[$j]['military_leave'];
				$foreigner_leave	= $data[$j]['foreigner_leave'];
                $wait_assign_shop   = $data[$j]['wait_assign_shop'];

                // Add Row of Change Position
                if ($change_position != '') {

                    $params2 = $params;

                    $params2['staff_group'] = array();

                    $params2['staff_code'] = $data[$j]['staff_code'];
                    $params2['staff_group'][0] = $change_position_id;
                    $params2['change_position'] = 1;

                    //print_r($params); echo "<br/>";
                    //print_r($params2); echo "<br/>";
                    $extra_position = $QStaffCheckInLog->exportCheckIn($params2);
                    $data2 = array();

                    foreach ($extra_position as $key2 => $value2) { $data2 = $value2; }

                    //print_r($data2); die;

                    for ($k=0;$k<count($data2);$k++) {

                        $training_day       = $training_day     + $data2[$k]['training_day'];
                        $work_day           = $work_day         + $data2[$k]['work_day'];
                        $work_day_no_out    = $work_day_no_out  + $data2[$k]['work_day_no_out'];
                        $off_date_leave     = $off_date_leave   + $data2[$k]['off_date_leave'];

                        $sick_leave_2       = $sick_leave_2     + $data2[$k]['sick_leave_2'];
                        $sick_leave_1       = $sick_leave_1     + $data2[$k]['sick_leave_1'];
                        $personal_leave     = $personal_leave   + $data2[$k]['personal_leave'];
                        $vacation_leave     = $vacation_leave   + $data2[$k]['vacation_leave'];
                        $ordination_leave   = $ordination_leave + $data2[$k]['ordination_leave'];
                        $maternity_leave    = $maternity_leave  + $data2[$k]['maternity_leave'];
                        $other_leave        = $other_leave      + $data2[$k]['other_leave'];

						$sterile_leave		= $sterile_leave	+ $data2[$k]['sterile_leave'];
						$marrirage_leave	= $marrirage_leave	+ $data2[$k]['marrirage_leave'];
						$burial_leave		= $burial_leave		+ $data2[$k]['burial_leave'];
						$military_leave		= $military_leave	+ $data2[$k]['military_leave'];
						$foreigner_leave	= $foreigner_leave	+ $data2[$k]['foreigner_leave'];
                        $wait_assign_shop   = $wait_assign_shop + $data2[$k]['wait_assign_shop'];
                    }

                }

                // Calculate Not Check In
                $total_day = (($day_end - $day_start) / (60*60*24)) + 1;

                $not_check_in = $total_day - $work_day - $work_day_no_out - $off_date_leave - 
                    $sick_leave_2 - $sick_leave_1 - $personal_leave - $vacation_leave - 
                    $ordination_leave - $maternity_leave - $other_leave - $sterile_leave - 
					$marrirage_leave - $burial_leave - $military_leave - $foreigner_leave - 
                    $wait_assign_shop;

                if ($not_check_in < 0) { 

                    $work_day_final = $work_day + $not_check_in; 
                    $not_check_in = 0;

                } else { 
                    
                    $work_day_final = $work_day; 

                    $params3 = $params;
                    $params3['staff_group'] = $data[$j]['staff_group_id'];
                    $params3['staff_code'] = $data[$j]['staff_code'];

                    $result_dup = $QStaffCheckInLog->checkDupRequest($params3);

                    if ( !empty($result_dup) ) {

                        $work_day_temp = $result_dup['chk_dup'] - $result_dup['leave_time'];
                        $work_day_final = $work_day - $result_dup['chk_dup'] + $work_day_temp; 

                        $not_check_in = $total_day - $work_day_final - $work_day_no_out - $off_date_leave - 
                            $sick_leave_2 - $sick_leave_1 - $personal_leave - $vacation_leave - 
                            $ordination_leave - $maternity_leave - $other_leave - $sterile_leave - 
                            $marrirage_leave - $burial_leave - $military_leave - $foreigner_leave - 
                            $wait_assign_shop;

                    } else {

                        $work_day_final = $work_day;
                    }

                }

                // Show Data Row
                $cnt = $cnt + 1;

                $alpha    = 'A';
                $sheet->setCellValue($alpha++.$index, $cnt);            
                $sheet->setCellValue($alpha++.$index, $data[$j]['staff_area']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['staff_code']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['staff_name']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['staff_group']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['table_group']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['joined_at']);
                $sheet->setCellValue($alpha++.$index, $data[$j]['off_date']);

                $sheet->setCellValue($alpha++.$index, $training_day);
                $sheet->setCellValue($alpha++.$index, $work_day_final);
                $sheet->setCellValue($alpha++.$index, $work_day_no_out);
                $sheet->setCellValue($alpha++.$index, $off_date_leave);
                $sheet->setCellValue($alpha++.$index, $data[$j]['sum_late_time']);
                $sheet->setCellValue($alpha++.$index, $not_check_in);

                $sheet->setCellValue($alpha++.$index, $sick_leave_2);
                $sheet->setCellValue($alpha++.$index, $sick_leave_1);
                $sheet->setCellValue($alpha++.$index, $personal_leave);
                $sheet->setCellValue($alpha++.$index, $vacation_leave);
                $sheet->setCellValue($alpha++.$index, $ordination_leave);
                $sheet->setCellValue($alpha++.$index, $maternity_leave);
                $sheet->setCellValue($alpha++.$index, $other_leave);
				
				$sheet->setCellValue($alpha++.$index, $sterile_leave);
				$sheet->setCellValue($alpha++.$index, $marrirage_leave);
				$sheet->setCellValue($alpha++.$index, $burial_leave);
				$sheet->setCellValue($alpha++.$index, $military_leave);
				$sheet->setCellValue($alpha++.$index, $foreigner_leave);
                $sheet->setCellValue($alpha++.$index, $wait_assign_shop);

                $sheet->setCellValue($alpha++.$index, $change_position);

                // $sheet->setCellValue($alpha++.$index, $total_day);

                $index++;

            }
            
        // }
        
        $filename = 'Staff_CheckIn_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function apiGetTimeByStaffcodeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
        $from_date = $this->getRequest()->getParam('from_date');
        $to_date = $this->getRequest()->getParam('to_date');

        // $staff_code = '5701386';
        // $from_date = '2018-07-21';
        // $to_date = '2018-08-20';

        $QStaff = new Application_Model_Staff();

        $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
        $result_staff = $QStaff->fetchRow($where);


        if ( (isset($staff_code) && $staff_code) && (isset($from_date) && $from_date) && (isset($to_date) && $to_date) ) {

            $params = array(
                'staff_code'    => $staff_code,
                'period_from'   => $from_date,
                'period_to'     => $to_date,

            );

            $params['staff_group'][0] = $result_staff['group_id']; 

            $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

            $temp = $QStaffCheckInLog->exportCheckIn($params);

            if (empty($temp)) { echo "No Time Data"; exit; }

            $result = $temp[0][0];

            // echo "<pre>"; print_r($result);
            echo json_encode($result);

        } else {

            echo "Wrong Input";
        }
        
    }


}
