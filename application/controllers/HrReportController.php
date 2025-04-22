<?php
/**
 * Xuất các report cho HR, dành cho view ở local
 */
class HrReportController extends My_Controller_Action
{
    /**
     * Giao diện chính; chọn thời gian để xuất các loại báo cáo
     */
    public function indexAction()
    {
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date   = $this->getRequest()->getParam('to_date', date('d/m/Y'));
        $export    = $this->getRequest()->getParam('export');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'from'      => $from,
            'to'        => $to,
        );

        $this->view->params = $params;

        if ( isset( $export ) && intval( $export ) > 0 ) {
            if ( $export == 1 ) {
                My_Report_Hr::kpiPg($from, $to);
                exit;
            }

            if ( $export == 2 ) {
                My_Report_Hr::kpiSale($from, $to);
                exit;
            }

            if ( $export == 3 ) {
                My_Report_Hr::kpiLeader($from, $to);
                exit;
            }
        }
    }

    public function pcComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $log = $QGoodKpi->report_kpiPC(null,$params);

        if (isset($export) && intval($export) == 5) { My_Report_Hr::com_pc_export($log, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $log;
        $this->_helper->viewRenderer->setRender('com/pc');
        
    }

    public function pcComTotalAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');
        $export_flag     = $this->getRequest()->getParam('export_flag');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        if (isset($export_flag) && intval($export_flag) == 1) { $params['report_type'] = 'UPC'; }
        if (isset($export_flag) && intval($export_flag) == 2) { $params['report_type'] = 'BKK'; }

        if (isset($export) && intval($export) == 6) { $params['report_type'] = 'UPC'; }
        if (isset($export) && intval($export) == 7) { $params['report_type'] = 'BKK'; }

        //print_r($params);

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QAsm       = new Application_Model_Asm();
        $QGoodKpi   = new Application_Model_GoodKpiLog();
        $QOIT       = new Application_Model_OppoIndividualTarget();
        $QPcActive  = new Application_Model_PcActive();
        $QPcChk     = new Application_Model_PcCheckInLog();
        $QPAE       = new Application_Model_PcAddonException();

        $rd_result = $QAsm->getRDListForPCTotal($params);
        $data = $QGoodKpi->report_kpiPC(null,$params);

        $EOL_List = $PCU_List_01 = $PCU_List_02 = array();
/*
        // Get Commission EOL
        $EOL_result = $QGoodKpi->comission_eol($params);
        for ($i=0;$i<count($EOL_result);$i++) { 

            if ( $EOL_result[$i]['total_unit'] >= 2 ) {
                $EOL_List[ $EOL_result[$i]['staff_code'] ] = $EOL_result[$i]['total_unit'] * 100; 
            } else {
                $EOL_List[ $EOL_result[$i]['staff_code'] ] = $EOL_result[$i]['total_unit'] * 0; 
            }

        }
*/
        // Get PC Un-Punish By Memo [Total Price < 200000]
        $pc_unpunish = $QGoodKpi->get_pc_unpunish($params);
        if ( !empty($pc_unpunish) ) {

            $pc_unpunish_list = array();
            foreach ($pc_unpunish as $key => $value) {
                $pc_unpunish_list[$value['staff_code']] = $value['unpunish_type'];
            }

            for ($i=0;$i<count($pc_unpunish);$i++) { 
                
                // Conditoin 01 : 200k
                if ( $pc_unpunish[$i]['con_01'] == 1 ) {
                    $PCU_List_01[ $pc_unpunish[$i]['staff_code'] ] = $pc_unpunish[$i]['staff_id'];
                }

                // Condition 02 : Index
                if ( $pc_unpunish[$i]['con_02'] == 1 ) {
                    $PCU_List_02[ $pc_unpunish[$i]['staff_code'] ] = $pc_unpunish[$i]['staff_id'];
                }
                 
            }
        }

        $result = array();
        // $area_list = array(81,82,83,85,86,90,91,92,94,95,97,98,99,100,101,103,104,105,106,107,108,110,115,116);
        // $flag = 1;

        for ($i=0;$i<count($data);$i++) {

            $flag = 1;
            // if ( in_array($data[$i]['area_id'], $area_list) ) { $flag = 1; }

            $com_real = 0;

            $result[$i]['area_id']       = $data[$i]['area_id'];
            $result[$i]['area_name']     = $data[$i]['area_name'];
            $result[$i]['total_sellout'] = $data[$i]['total_sellout'];
            $result[$i]['com_pc']        = $data[$i]['com_pc'];
            $result[$i]['total_price']   = $data[$i]['total_price'];

            // Calculate Work Day
            $staff_created = $data[$i]['staff_joined'];
            $staff_offdate = $data[$i]['staff_offdate'];
            
            if (!is_null($staff_offdate) && $staff_offdate <= $params['to']." 23:59:59") { 
                $now = strtotime($staff_offdate); 
                $created_at = strtotime($staff_created);
            } else { 
                $now = strtotime($params['to']." 23:59:59"); 
                $created_at = strtotime($staff_created);
            }

            $diffdate = $now - $created_at;
            $work_day = floor($diffdate/(60*60*24)) + 1; // plus 1 day

            $store_level = $pc_target = $pc_level = $etest = $debug = '';
            $store_org_id = $store_type_id = '';
            $addon_rate = $addon_flag = 1;

            // Check PC Un-Punish Condition 2 : Index 
            if ( in_array($data[$i]['staff_code'], array_keys($PCU_List_02)) ) {
                $flag = 0;
                // $remark = "ยกเว้นค่าคอมฯพิเศษ (Index) - ".$pc_unpunish_list[ $data[$i]['staff_code'] ];
            }

            if ($flag == 1) { 
                // Check Add-on 01 : Not PC Stand By
                if ( $data[$i]['pc_stand_by'] == 0 ) {
                    // Check Add-on 02 : Not New PC
                    if ( $work_day >= 30 ) {
                        // Check Add-on 03 : Check Number of Store 
                        if ( $data[$i]['cnt_store'] >= 1 ) {

                            $store_level = $data[$i]['st_level'];
                            $store_org_id = $data[$i]['st_org_id'];
                            $store_type_id = $data[$i]['st_type_id'];

                            // Check Add-on XX : PC Exception
                            if ( $data[$i]['cnt_store'] > 1 ) {

                                $params_addon = array(
                                    'staff_id'  => $data[$i]['staff_id'],
                                    'from'      => $params['from'],
                                    'to'        => $params['to'],
                                );

                                $result_pae = $QPAE->getPcExceptionStore($params_addon);

                                if ( !empty($result_pae) ) {
                                    $store_level = $result_pae['st_level'];
                                    $addon_flag = 0;
                                }
                                
                            } 

                            if ( $data[$i]['cnt_store'] == 1 ) { $addon_flag = 0; }

                            if ( $addon_flag == 0 ) {

                                $where = array();
                                $where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                $where[] = $QOIT->getAdapter()->quoteInto('from_date >= ?', $params['from']);
                                $where[] = $QOIT->getAdapter()->quoteInto('to_date <= ?', $params['to']);

                                $result_target = $QOIT->fetchRow($where);

                                // Check Add-on 04 : Check PC Target
                                if ( !empty($result_target) ) {

                                    $pc_target = $result_target['target_price'];

                                    $where = array();
                                    $where[] = $QPcActive->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at >= ?', $params['from']." 00:00:00");
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at <= ?', $params['to']." 23:59:59");

                                    $result_pc_grade = $QPcActive->fetchRow($where);

                                    // Check Add-on 05 : Check PC Grade
                                    if ( !empty($result_pc_grade) && $result_pc_grade['etest_count'] == 2 ) { 

                                        $work_point = $val_ach = $grade_point = $achive = $com_index = 0;

                                        // $pc_level = $result_pc_grade['grade'];
                                        $etest = $result_pc_grade['etest_result'];

                                        // Check Add-on 06 : Check PC Work Hour
                                        $where = array();
                                        $where[] = $QPcChk->getAdapter()->quoteInto('status = ?', 'Y');
                                        $where[] = $QPcChk->getAdapter()->quoteInto('action_id = ?', 1);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in >= ?', $params['from']." 00:00:00");
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in <= ?', $params['to']." 23:59:59");

                                        $result_pc_chk = $QPcChk->fetchAll($where);

                                        $work_hour = count($result_pc_chk);

                                        // Calculate Com Index 
                                        $work_point = -5;
                                        if ( $work_hour >= 25 ) { $work_point = 0; } 
                                        if ( $work_hour >= 28 ) { $work_point = 10; } 
/*
                                        switch ($pc_level) {
                                            case 'A': ($etest == 'Y') ? $grade_point = 20 : $grade_point = 10; break;
                                            case 'B': ($etest == 'Y') ? $grade_point = 10 : $grade_point = 5; break;
                                            case 'C': ($etest == 'Y') ? $grade_point = 5 : $grade_point = 0; break;
                                            default : $grade_point =  0; break;
                                        }
*/
                                        if ($etest == 'Y') { $grade_point = 20; } 
                                        else { $grade_point = 0; }

                                        $achive = round(( $data[$i]['total_price'] / $pc_target ) * 100, 2);
                                        $val_ach = round( ( ($data[$i]['total_price'] * $achive) / 100 ) / 10000 , 2);

                                        $com_index = $work_point + $grade_point + $val_ach;

                                        if ( $store_level == 'S' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }
                                            if ($com_index > 70) { $addon_rate = 1.3; }
                                            
                                        }

                                        if ( $store_level == 'A' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

            if ( in_array($data[$i]['staff_code'], array('6005578','6101212','6202681','5801652','6204185','6006936')) ) {
                $addon_rate = 1;
            }

            // Commission EOL
            if (!empty($EOL_List)) {
                if (in_array($data[$i]['staff_code'], array_keys($EOL_List) )) { 
                    $com_eol = $EOL_List[ $data[$i]['staff_code'] ]; 
                } else { $com_eol = 0; }
            }
            
            // Check Price Threshold
            $price_threshold = 200000;

            if ( $data[$i]['total_price'] < $price_threshold ) {

                $params['staff_code'] = $data[$i]['staff_code'];
                // check total sellout of this staff (All Store and Area)
                $sellout_result = $QGoodKpi->getAllSelloutByStaff($params);

                if ( $sellout_result['total_price'] < $price_threshold ) {

                    if ($work_day < 30) {

                        //$remark = "ยกเว้นเงื่อนไข - ทำงานไม่ถึง 30 วัน";

                    } else {

                        // Check PC Un-Punish Condition 1 : 200K 
                        if (!in_array($data[$i]['staff_code'], array_keys($PCU_List_01))) {
                            $addon_rate = 0;
                            // $remark = "ยอดขายไม่ถึง ".number_format($price_threshold)." บาท ไม่ได้รับค่าคอมฯ";
                        } else {
                            // $remark = "ยกเว้นเงื่อนไขจากแผนก Training";
                            // $remark = "ยกเว้นเงื่อนไข 200K - ".$pc_unpunish_list[ $data[$i]['staff_code'] ];
                        }
                        
                    }
                        
                } else {
                    $addon_rate = 1;
                    // $remark = "โอนย้ายเขต";
                }

            }

            $result[$i]['com_real'] = round( ($data[$i]['com_pc'] * $addon_rate) + $com_eol, 0);

        }

        $log = array();
        foreach ($result as $key => $value) {
            $log[ $value['area_id'] ]['area_name']         = $value['area_name'];
            $log[ $value['area_id'] ]['total_sellout']    += $value['total_sellout'];
            $log[ $value['area_id'] ]['com_pc']           += $value['com_pc'];
            $log[ $value['area_id'] ]['total_price']      += $value['total_price'];
            $log[ $value['area_id'] ]['com_real']         += $value['com_real'];
            $log[ $value['area_id'] ]['cnt_staff']        += 1;
        }

        //echo "<pre>"; print_r($log); die;

        if (isset($export) && intval($export) == 6) { My_Report_Hr::com_total_pc($rd_result,$log,$params); }
        if (isset($export) && intval($export) == 7) { My_Report_Hr::com_total_pc($rd_result,$log,$params); }

        // Send Data to View
        $this->view->rd_result = $rd_result;
        $this->view->params = $params;
        $this->view->log = $log;
        $this->_helper->viewRenderer->setRender('com/pc-total');
        
    }

    public function bmDealerComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        // $name            = $this->getRequest()->getParam('name');
        // $staff_code      = $this->getRequest()->getParam('staff_code');
        // $phone_number    = $this->getRequest()->getParam('phone_number');
        // $area_id         = $this->getRequest()->getParam('area_id');
        // $regional_market = $this->getRequest()->getParam('regional_market');
        // $district        = $this->getRequest()->getParam('district');
        // $store           = $this->getRequest()->getParam('store');
        // $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            // 'name'            => $name,
            // 'staff_code'      => trim($staff_code),
            // 'phone_number'    => $phone_number,
            // 'area_id'         => $area_id,
            // 'regional_market' => $regional_market,
            // 'district'        => $district,
            // 'store'           => $store,
            // 'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $log = $QGoodKpi->getComBMbyPC($params);

        if (isset($export) && intval($export) == 17) { My_Report_Hr::com_bm_dealer_export($log, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $log;
        $this->_helper->viewRenderer->setRender('com/bm-dealer');
        
    }

    public function bmOppoComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        // $name            = $this->getRequest()->getParam('name');
        // $staff_code      = $this->getRequest()->getParam('staff_code');
        // $phone_number    = $this->getRequest()->getParam('phone_number');
        // $area_id         = $this->getRequest()->getParam('area_id');
        // $regional_market = $this->getRequest()->getParam('regional_market');
        // $district        = $this->getRequest()->getParam('district');
        // $store           = $this->getRequest()->getParam('store');
        // $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            // 'name'            => $name,
            // 'staff_code'      => trim($staff_code),
            // 'phone_number'    => $phone_number,
            // 'area_id'         => $area_id,
            // 'regional_market' => $regional_market,
            // 'district'        => $district,
            // 'store'           => $store,
            // 'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $log = $QGoodKpi->getAccComBMbyOppo($params);

        if (isset($export) && intval($export) == 18) { My_Report_Hr::com_bm_oppo_export($log, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $log;
        $this->_helper->viewRenderer->setRender('com/bm-oppo');
        
    }

    public function saleUpcComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $log = $QGoodKpi->report_kpiSale($params);

        if (isset($export) && intval($export) == 7) { My_Report_Hr::com_sale_upc_export($log, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $log;
        $this->_helper->viewRenderer->setRender('com/sale-upc');
        
    }

    public function saleBkkComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $result = $QGoodKpi->report_kpiSaleBKK_Dealer($params);

        // Get ASM Details
        $QAsm = new Application_Model_Asm();
        $result_asm = $QAsm->get_asm_for_com($area_id);

        for ($i=0;$i<count($result_asm);$i++) { $asm_tmp[$i] = $result_asm[$i]['staff_name']; }

        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        // check pc of thi month base on from filter
        $result_ss = array();
        for ($i=0;$i<count($result);$i++) { 

            $where = array();
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $result[$i]['store_id']);
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = 0');
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('FROM_UNIXTIME(joined_at, "%Y-%m-%d 00:00:00") <= ?', 
                date('Y-m-01 00:00:00', strtotime($params['from'])) );
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('( FROM_UNIXTIME(released_at, "%Y-%m-%d 23:59:59") >= ? 
                OR released_at IS NULL )', date('Y-m-t 23:59:59', strtotime($params['from'])));

            $result_ss = $QStoreStaffLog->fetchAll($where);

            if ($result[$i]['total_unit'] >= 1 ) {
                if (count($result_ss) > 0) {
                    $result[$i]['store_share'] = 0;
                } else {
                    $result[$i]['store_share'] = $result[$i]['com_rate'] / 2;
                    $result[$i]['com_rate'] = $result[$i]['com_rate'] / 2;
                }
            }

        }

        // Group Data By Staff Code
        for($i=0;$i<count($result);$i++) {

            $total_unit  = $data[ $result[$i]['staff_code'] ]['total_unit'] + $result[$i]['total_unit'];
            $total_price  = $data[ $result[$i]['staff_code'] ]['total_price'] + $result[$i]['total_price'];
            $com_rate    = $data[ $result[$i]['staff_code'] ]['com_rate'] + $result[$i]['com_rate'];
            $store_share = $data[ $result[$i]['staff_code'] ]['store_share'] + $result[$i]['store_share'];

            $data[ $result[$i]['staff_code'] ] = array(
                'staff_code'  => $result[$i]['staff_code'],
                'staff_name'  => $result[$i]['staff_name'],
                'group_id'    => $result[$i]['group_id'],
                'group_name'  => $result[$i]['group_name'],
                'area_name'   => $result[$i]['area_name'],
                'total_price' => $result[$i]['total_price'],
                'total_unit'  => $total_unit,
                'com_rate'    => $com_rate,
                'store_share' => $store_share,
                'total_price' => $total_price,
            );

        }

        $data = array_values($data);

        if (isset($export) && intval($export) == 8) { 
            $params['asm_name'] = implode(", ", $asm_tmp);
            My_Report_Hr::com_sale_bkk_export($data, $params); 
        } else {
            $params['asm_name'] = implode("<br/>", $asm_tmp);
        }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $data;
        $this->_helper->viewRenderer->setRender('com/sale-bkk');
        
    }

    public function saleBkkCom2017Action() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $result = $QGoodKpi->com_sale_bkk_2017($params);

        if (isset($export) && intval($export) == 9) { My_Report_Hr::com_sale_bkk_2017_export($result, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $result;
        $this->_helper->viewRenderer->setRender('com/sale-bkk2017');
        
    }

    public function comTotalAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from'      => $from,
            'to'        => $to,
            'export'    => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        if (isset($export) && intval($export) == 10) { $params['report_type'] = 'UPC'; My_Report_Hr::com_total_sale($params); }
        if (isset($export) && intval($export) == 11) { $params['report_type'] = 'BKK'; My_Report_Hr::com_total_sale($params); }
        if (isset($export) && intval($export) == 12) { $params['report_type'] = 'UPC'; My_Report_Hr::com_total_asm($params); }
        if (isset($export) && intval($export) == 13) { $params['report_type'] = 'BKK'; My_Report_Hr::com_total_asm($params); }

        if (isset($export) && intval($export) == 15) { $params['report_type'] = 'UPC'; My_Report_Hr::sellin_com_total_sale($params); }
        if (isset($export) && intval($export) == 16) { $params['report_type'] = 'BKK'; My_Report_Hr::sellin_com_total_sale($params); }

        // Send Data to View
        // $this->view->params = $params;
        // $this->view->log = $result;
        // $this->_helper->viewRenderer->setRender('com/sale-bkk-2017');
        
    }

    // Sellin Commission for Sale
    public function saleSellinComAction() {
        $this->_helper->layout->disableLayout();

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'       => $from_date,
            'to_date'         => $to_date,
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $result = $QGoodKpi->sellin_com_sale($params);

        if (isset($export) && intval($export) == 14) { My_Report_Hr::sellin_com_sale_export($result, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $result;
        $this->_helper->viewRenderer->setRender('com/sale-sellin');
        
    }

    // AM Commission
    public function amComAction() {
        $this->_helper->layout->disableLayout();

        $from_date   = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date     = $this->getRequest()->getParam('to', date('d/m/Y'));
        $am_id       = $this->getRequest()->getParam('am_id');
        $export      = $this->getRequest()->getParam('export');
        $export_flag = $this->getRequest()->getParam('export_flag');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date'     => $from_date,
            'to_date'       => $to_date,
            'from'          => $from,
            'to'            => $to,
            'am_id'         => $am_id,
            'export'        => $export,         // Check for Export
            'export_flag'   => $export_flag,    // Check for Web
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $result = $QGoodKpi->com_am($params);

        if (isset($export) && intval($export) == 1) { My_Report_Hr::com_am($result, $params); }
        if (isset($export) && intval($export) == 2) { My_Report_Hr::com_am($result, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $result;
        $this->_helper->viewRenderer->setRender('com/am');
        
    }

    // AM Commission Total
    public function amComTotalAction() {
        $this->_helper->layout->disableLayout();

        $from_date   = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date     = $this->getRequest()->getParam('to', date('d/m/Y'));
        $am_id       = $this->getRequest()->getParam('am_id');
        $export      = $this->getRequest()->getParam('export');
        $export_flag = $this->getRequest()->getParam('export_flag');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        //$total = 0;

        $params = array(
            'from_date'     => $from_date,
            'to_date'       => $to_date,
            'from'          => $from,
            'to'            => $to,
            'am_id'         => $am_id,
            'export'        => $export,         // Check for Export
            'export_flag'   => $export_flag,    // Check for Web
        );

        if ($params['export'] == 1) { $params['export_flag'] = 3; }
        if ($params['export'] == 2) { $params['export_flag'] = 4; }

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QAm = new Application_Model_Am();

        $log = $QAm->getAmForTotal($params);

        $result = array();
        foreach ($log as $key => $value) {
            $result[ $value['staff_id'] ][ $value['base_kpi'] ][] = $value;
        }
        //echo "<pre>"; print_r($result);

        if (isset($export) && intval($export) == 1) { My_Report_Hr::com_total_am($result, $params); }
        if (isset($export) && intval($export) == 2) { My_Report_Hr::com_total_am($result, $params); }

        // Send Data to View
        $this->view->params = $params;
        $this->view->log = $result;
        $this->_helper->viewRenderer->setRender('com/am-total');
        
    }

    public function pgKpiAction() {

        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 1);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        $this->view->params = $params;

        if (isset($export) && intval($export) == 1) { My_Report_Hr::kpiPg($params); }
        if (isset($export) && intval($export) == 2) { My_Report_Hr::kpiTarget($params); } 
        if (isset($export) && intval($export) == 3) { My_Report_Hr::kpiTargetByStore($params); } 
        if (isset($export) && intval($export) == 4) { My_Report_Hr::kpiTargetHeroProduct($params); } 
        if (isset($export) && intval($export) == 14) { My_Report_Hr::pcLevelReport($params); } 
    }

    public function saleKpiAction()
    {
        $from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 2);
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from'            => $from,
            'to'              => $to,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'phone_number'    => $phone_number,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'export'          => $export,
        );

        $this->view->params = $params;

        // if ( isset($export) && intval($export) == 1) { My_Report_Hr::kpiSaleBKK_ORG($params); } 
        if ( isset($export) && intval($export) == 2) { My_Report_Hr::kpiSaleBKK_Dealer($params); } 
        if ( isset($export) && intval($export) == 3) { My_Report_Hr::kpiSale($params); } 
        if ( isset($export) && intval($export) == 4) { My_Report_Hr::kpiEOL($params); } 
        if ( isset($export) && intval($export) == 5) { My_Report_Hr::kpiSaleScan($params); } 
        if ( isset($export) && intval($export) == 6) { My_Report_Hr::kpiSaleScanBKK_Dealer($params); } 
    }

    public function leaderKpiAction()
    {
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date   = $this->getRequest()->getParam('to_date', date('d/m/Y'));
        $export    = $this->getRequest()->getParam('export', 0);

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'from'      => $from,
            'to'        => $to,
        );

        $this->view->params = $params;

        if (isset( $export ) && intval( $export ))
            My_Report_Hr::kpiLeader($from, $to);
    }

    public function timingAction()
    {
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date   = $this->getRequest()->getParam('to_date', date('d/m/Y'));
        $export    = $this->getRequest()->getParam('export', 0);

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'from'      => $from,
            'to'        => $to,
            'export'    => $export
        );

        $this->view->params = $params;

        if (isset( $export ) && intval( $export ))
            My_Report_Hr::kpiPg($from, $to);
    }

    /**
     * Dùng để gán các hằng số dùng tính KPI;
     *		-- bảng KPI của sales
     * 		giá trị từng biến có thể thay đổi bởi HR;
     * 		các biến mới chỉ Technology tạo thêm được
     *		-- riêng bên product thì dùng chung bảng (thêm cột pg_kpi)
     *		chỉ HR thấy các số KPI, còn bên khác chỉ thấy/sửa giá, tên sản phẩm...
     */

    // Modify by Sak
    public function settingsAction() {

        $from_date = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to_date = $this->getRequest()->getParam('to', date('d/m/Y'));
        $name   = $this->getRequest()->getParam('name');
        $price  = $this->getRequest()->getParam('price');
        $type   = $this->getRequest()->getParam('type');
        $export = $this->getRequest()->getParam('export', 0);
        $page   = $this->getRequest()->getParam('page', 1);
        $limit  = LIMITATION;
        $total  = 0;

        $params = array(
            'from'  => $from_date, 
            'to'    => $to_date, 
            'type'  => $type,
            'name'  => $name,
            'price' => $price,
            'export'=> $export);

    	$QGoogKpiLog = new Application_Model_GoodKpiLog();
        $QBrand = new Application_Model_Brand();

        $this->view->logs = $QGoogKpiLog->fetchGoodKpi($page, $limit, $total, $params);

        if ( isset($export) && $export ) {
            if ($export == 1) { 
                My_Report_Hr::ExportKPIsetting($params);
            }
	    if($export == 2){
                My_Report_Hr::ExportKPIsettingExcel($params);
            }
        }

        $this->view->params = $params;
        $this->view->brands = $QBrand->get_cache();

        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST.'hr-report/settings/'.( $params ? '?'.http_build_query($params).'&' : '?' );
        $this->view->offset = $limit*($page-1);

    	$flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();
   		$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
    }

    public function settingsCreateAction() {

        $QGood = new Application_Model_Good();
        $QGoodColor = new Application_Model_GoodColor();

        $this->view->goods = $QGood->get_cache();
        $this->view->good_colors = $QGoodColor->get_cache();
        $this->view->refer = My_Url::refer('hr-report/settings');
        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
    }

  

    public function settingsEditAction()
    {

        $id = $this->getRequest()->getParam('id');
        $refer = My_Url::refer('hr-report/settings');

        try {
            if (!$id) throw new Exception("Invalid ID", 1);

            $id = intval($id);
            $QGoodKpiLog = new Application_Model_GoodKpiLog();
            $where = $QGoodKpiLog->getAdapter()->quoteInto('id = ?', $id);
            $log_check = $QGoodKpiLog->fetchRow($where);
            if (!$log_check) throw new Exception("Wrong ID", 2);

            $this->view->log = $log_check;
            $this->view->refer = $refer;
            
            $QGood = new Application_Model_Good();
            $this->view->goods = $QGood->get_cache(); 
            
            $this->view->good_colors = $this->loadGoodColorAction($log_check['good_id']);
            /*
            $QGoodColor = new Application_Model_GoodColor();
            //$this->view->good_colors = $QGoodColor->get_cache();
            */

            $this->_helper->viewRenderer->setRender('settings-create');
        } catch (Exception $e) {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage(sprintf("[%s] %s", $e->getCode(), $e->getMessage()));
            $this->_redirect($refer);
        }
    }

    public function settingsSaveAction() {

        $tmp = Zend_Auth::getInstance()->getIdentity();
        $user_info = json_decode(json_encode($tmp), true);

        $good_id  = $this->getRequest()->getParam('good_id');
        $color_id = $this->getRequest()->getParam('color_id');
        $from     = $this->getRequest()->getParam('from');
        $to       = $this->getRequest()->getParam('to');
        $kpi      = $this->getRequest()->getParam('kpi', 0.00);
        $com_rate = $this->getRequest()->getParam('com_rate', 0.00);
        $com_rate_aec = $this->getRequest()->getParam('com_rate_aec', 0.00);
        $price    = $this->getRequest()->getParam('price', 0.00);
        $type     = $this->getRequest()->getParam('type');
        $id       = $this->getRequest()->getParam('id');
        $refer    = $this->getRequest()->getParam('refer', My_Url::refer('hr-report/settings'));
        $user_id  = $user_info['id'];
        $now      = date('Y-m-d H:i:s');

        $flashMessenger = $this->_helper->flashMessenger;
        $QGoodKpiLog = new Application_Model_GoodKpiLog();

        try {
            if ($id) {
                $where = $QGoodKpiLog->getAdapter()->quoteInto('id = ?', intval($id));
                $log_check = $QGoodKpiLog->fetchRow($where);

                if (!$log_check) throw new Exception("Invalid log", 7);
            }

            if (!$type || !isset(My_Kpi_Object::$name[$type])) throw new Exception("Invalid KPI For", 8);

            if (!$from || !date_create_from_format('d/m/Y', $from))
                throw new Exception("Invalid From date", 1);

            $from = date_create_from_format('d/m/Y', $from)->format('Y-m-d');

            if ($to && !date_create_from_format('d/m/Y', $to))
                throw new Exception("Invalid To date", 2);

            if ($to)
                $to = date_create_from_format('d/m/Y', $to)->format('Y-m-d');

            if (!$good_id) throw new Exception("Invalid product", 3);

            $QGood = new Application_Model_Good();
            $goods = $QGood->get_cache();
            if (!isset($goods[ $good_id ])) throw new Exception("Invalid product", 4);

            if ($color_id) {
                $QGoodColor = new Application_Model_GoodColor();
                $colors = $QGoodColor->get_cache();
                if (!isset($colors[ $color_id ])) throw new Exception("Invalid color", 5);
            }

            $where = array();
            $where_date = array();
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto('good_id = ?', intval($good_id));
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto('type = ?', intval($type));
            $where_date[] = $QGoodKpiLog->getAdapter()->quoteInto('from_date <= ? AND to_date >= ?', $from);

            if ($to)
                $where_date[] = $QGoodKpiLog->getAdapter()->quoteInto('from_date <= ? AND to_date >= ?', $to);

            $where[] = implode(' OR ', $where_date);

            if ($color_id)
                $where[] = $QGoodKpiLog->getAdapter()->quoteInto('color_id = ?', intval($color_id));

            if ($id)
                $where[] = $QGoodKpiLog->getAdapter()->quoteInto('id <> ?', intval($id));
            
            //print_r($where);die;
            $log_check = $QGoodKpiLog->fetchRow($where);


            if ($log_check) throw new Exception("Conflict dates", 6);

            $data = array(
                'good_id'   => intval($good_id),
                'color_id'  => intval($color_id),
                'from_date' => $from,
                'to_date'   => empty($to) ? null : $to,
                'kpi'       => floatval( $kpi ),
                'com_rate'  => floatval( $com_rate ),
                'com_rate_aec'  => floatval( $com_rate_aec ),
                'price'     => floatval( $price ),
                'type'      => intval( $type ),
            );

            if ($id) {
                $data['updated_by'] = $user_id;
                $data['updated_date'] = $now;

                $where = $QGoodKpiLog->getAdapter()->quoteInto('id = ?', intval($id));
                $QGoodKpiLog->update($data, $where);
            } else {
                $data['created_by'] = $user_id;
                $data['created_date'] = $now;

                $QGoodKpiLog->insert($data);
            }
        } catch (Exception $e) {
            $flashMessenger->setNamespace('error')->addMessage(sprintf("[%s] %s", $e->getCode(), $e->getMessage()));
        }

        $this->_redirect($refer);
    }

    public function settingsDeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $where = $QGoodKpiLog->getAdapter()->quoteInto('id = ?', intval($id));
        $QGoodKpiLog->delete($where);
        $this->_redirect(My_Url::refer('hr-report/settings'));
    }

    public function saveSettingsAction()
    {
    	if ($this->getRequest()->getMethod() == 'POST') {
    		// update cho PG
            $flashMessenger = $this->_helper->flashMessenger;

    		$QGood = new Application_Model_Good();
            $goods = $QGood->get_cache();

            $QGoodKpi = new Application_Model_GoodKpi();

            foreach ($goods as $id => $desc) {
                $pg_kpi     = $this->getRequest()->getParam('p_'.$id, 0);
                $sales_kpi  = $this->getRequest()->getParam('s_'.$id, 0);
                $leader_kpi = $this->getRequest()->getParam('l_'.$id, 0);
                $pg_kpi     = intval($pg_kpi);
                $sales_kpi  = intval($sales_kpi);
                $leader_kpi = intval($leader_kpi);

                $data = array(
                    'pg_kpi'     => $pg_kpi,
                    'sales_kpi'  => $sales_kpi,
                    'leader_kpi' => $leader_kpi,
                );

                $where = $QGoodKpi->getAdapter()->quoteInto('good_id = ?', $id);
                $it = $QGoodKpi->fetchRow($where);

                try {
                    if ($it){
                    $QGoodKpi->update($data, $where);
                    } else {
                        $data['good_id'] = $id;
                        $QGoodKpi->insert($data);
                    }
                } catch (Exception $e) {
                    $flashMessenger->setNamespace('error')->addMessage(sprintf("[%s] %s", $e->getCode(), $e->getMessage()));
                    $this->_redirect(HOST.'hr-report/settings');
                }
    		}

       		$messages = $flashMessenger->setNamespace('success')->addMessage('Done!');
    	}

    	$this->_redirect(HOST.'hr-report/settings');
    }

    public function loadGoodColorAction($good_id=null) {

        $flag = 0;
        if (!isset($good_id)) {
            $flag = 1;
            $good_id = $this->getRequest()->getParam('good_id');
        } 

        if (isset($good_id)) {

            /*
            $QGood = new Application_Model_Good();
            $where = $QGood->getAdapter()->quoteInto('id = ?', intval($good_id));
            $temp = $QGood->fetchAll($where, 'color')->toArray();

            $QGoodColor = new Application_Model_GoodColor();
            $where = $QGoodColor->getAdapter()->quoteInto("id in (".$temp[0]['color'].")");
            $result = $QGoodColor->fetchAll($where, 'name')->toArray();
            */

            $QGoodColorCombined = new Application_Model_GoodColorCombined(); 
            $result = $QGoodColorCombined->getColorByModel($good_id);

            if ($flag == 1) {
                echo json_encode($result);
                exit;

            } else {
                for ($i=0;$i<count($result);$i++) {
                    $tmp[$result[$i]['id']] = $result[$i]['name'];
                }
                return $tmp;
            }

        }
    }


    public function cardPrintAction() {
        $this->_helper->layout->disableLayout();

        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $public_id       = $this->getRequest()->getParam('public_id');
        $staff_name      = $this->getRequest()->getParam('staff_name');
        $from            = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to              = $this->getRequest()->getParam('to', date('d/m/Y'));
        $export          = $this->getRequest()->getParam('export', 0);
        $chk_bkk         = $this->getRequest()->getParam('chk_bkk');

        $total = 0;

        $params = array(
            'public_id'       => $public_id,
            'staff_name'      => $staff_name,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'from'            => $from,
            'to'              => $to, 
            'export'          => $export,
            'chk_bkk'         => $chk_bkk,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        // Get Data 
        $QPcFirstTraining = new Application_Model_PcFirstTraining();

        $staff_list = $QPcFirstTraining->fetchPagination(null, null, $total, $params);

        // Send Data to View
        $this->view->params = $params;
        $this->view->staff_list = $staff_list;
        $this->_helper->viewRenderer->setRender('training/card-print');
        
    }

}

