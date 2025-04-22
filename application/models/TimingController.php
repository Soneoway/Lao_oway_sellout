<?php

class TimingController extends My_Controller_Action
{

    public function indexAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'index.php';
    }

    public function expiredAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'expired.php';
    }

    public function kpiAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'kpi.php';
    }

    public function expiredSubmitAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'expired-submit.php';
    }

    public function editAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'edit.php';
    }

    public function saveHappytimeAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'save-happytime.php';
    }

    public function saveAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'save.php';
    }

    public function createAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'create.php';
    }

     public function createHappytimeAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'create-happytime.php';
    }

    public function uploadAction()
    {

        require_once 'timing' . DIRECTORY_SEPARATOR . 'upload.php';

    }

    public function analyticsAreaAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-area.php';

    }

    public function imeiAction()
    {

    }

    public function duplicatedImeiAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'duplicated-imei.php';
    }

    public function imeiReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'imei-report.php';
    }

    // iFrame Function
    public function imeiReportConfirmAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'imei-report-confirm.php';
    }

    public function viewAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'view.php';
    }

    public function delAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'del.php';
    }

    public function approveAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'approve.php';
    }

    public function scheduleAction()
    {

    }

    public function eventsAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'events.php';
    }

    public function loadModelAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'load-model.php';
    }

    public function analyticsAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics.php';
    }

    public function analyticsStoreAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-store.php';
    }

    public function analyticsProductAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-product.php';
    }

    public function productShortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'product-short-report.php';
    }

    public function shortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'short-report.php';
    }

    public function storeShortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'store-short-report.php';
    }

    public function updateNoteAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'update-note.php';
    }

    // AJAX function
    public function checkImeiAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'check-imei.php';
    }

    // AJAX function
    public function checkImeiHappytimeAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'check-imei-happytime.php';
    }

    public function checkImeiExportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'check-imei-export.php';
    }

    public function getImeiCustomerAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'get-imei-customer.php';
    }

    public function analyticsDealerAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-dealer.php';
    }

    public function analyticsDealerAllAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-dealer-all.php';
    }

    public function analyticsDealerDetailsAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-dealer-details.php';
    }

    public function analyticsInventoryByDealerAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR .
            'analytics-inventory-by-dealer.php';
    }

    public function analyticsInventoryByDealerDetailAction(){
        require_once 'timing'.DIRECTORY_SEPARATOR.'analytics-inventory-by-dealer-detail.php';
    }

    public function manageNotActivatedAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'manage-not-activated.php';
    }

    public function kpiOverviewAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'kpi-overview.php';
    }

    public function kpiHrCheckAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'kpi-hr-check.php';
    }

    public function analyticsDealerOppoclubAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-dealer-oppoclub.php';
    }

    public function analyticsDealerOppoclubS2Action()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-dealer-oppoclub-s2.php';
    }

    public function shopInventoryAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'shop-inventory.php';
    }
    public function shopInventoryLogsAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'shop-inventory-logs.php';
    }
    
    public function stockShopTimingAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'stock-shop-timing.php';
    }

    // AJAX function
    public function stockShopSaveAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'stock-shop-save.php';
    }

    public function analyticsWeeklyReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'analytics-weekly-report.php';
    }
    public function salesScanPerformanceAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'sales-scan-performance.php';
    }
    public function stockShopShortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'stock-shop-short-report.php';
    }
    public function stockShopSelloutShortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'stock-shop-sellout-short-report.php';
    }
    public function kpiPcShortReportAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'kpi-pc-short-report.php';
    }
    public function timingIssueAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-issue'.DIRECTORY_SEPARATOR.'timing-issue.php';
    }
    public function timingIssueEditAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-issue'.DIRECTORY_SEPARATOR.'timing-issue-edit.php';
    }
    public function timingIssueSaveAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-issue'.DIRECTORY_SEPARATOR.'timing-issue-save.php';
    }
    public function timingIssueRemarkAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-issue'.DIRECTORY_SEPARATOR.'timing-issue-remark.php';
    }
    public function amSelloutAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'am-sellout.php';
    }
    public function headcountAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'headcount'.DIRECTORY_SEPARATOR.'headcount.php';
    }
    public function staffResignAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'staff-resign'.DIRECTORY_SEPARATOR.'staff-resign.php';
    }
    public function distributorDoiAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'distributor-doi'.DIRECTORY_SEPARATOR.'distributor-doi.php';
    }
    public function distributorDoiExAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'distributor-doi-ex'.DIRECTORY_SEPARATOR.'distributor-doi-ex.php';
    }

    public function comAmAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'com-am'.DIRECTORY_SEPARATOR.'com-am.php';
    }

    public function competitorReportAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'competitor-report'.DIRECTORY_SEPARATOR.'competitor-report.php';
    }

    public function kaSelloutAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'ka-sellout'.DIRECTORY_SEPARATOR.'ka-sellout.php';
    }

    public function krSelloutAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'kr-sellout'.DIRECTORY_SEPARATOR.'kr-sellout.php';
    }

    public function krBiReportAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'kr-bi-report'.DIRECTORY_SEPARATOR.'kr-bi-report.php';
    }

    public function accReportAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'acc-report'.DIRECTORY_SEPARATOR.'acc-report.php';
    }

    public function accReportLogAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'acc-report-log'.DIRECTORY_SEPARATOR.'acc-report-log.php';
    }

    public function storePreOrderAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'store-pre-order'.DIRECTORY_SEPARATOR.'store-pre-order.php';
    }

    public function storeFocusAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'store-focus'.DIRECTORY_SEPARATOR.'store-focus.php';
    }

    public function storeFocusByBrandAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'store-focus-by-brand'.DIRECTORY_SEPARATOR.'store-focus-by-brand.php';
    }

    public function storeFocusByModelAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'store-focus-by-model'.DIRECTORY_SEPARATOR.'store-focus-by-model.php';
    }

    public function selloutAnalysisByAreaAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'sellout-analysis-by-area'.DIRECTORY_SEPARATOR.'sellout-analysis-by-area.php';
    }

    public function selloutAnalysisByModelAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'sellout-analysis-by-model'.DIRECTORY_SEPARATOR.'sellout-analysis-by-model.php';
    }

    public function selloutAnalysisByChannelAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'sellout-analysis-by-channel'.DIRECTORY_SEPARATOR.'sellout-analysis-by-channel.php';
    }

    public function factoryReportByWeekAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'factory-report-by-week'.DIRECTORY_SEPARATOR.'factory-report-by-week.php';
    }

    // PC & Shop Level
    public function pcShopLevelAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'pc-shop-level'.DIRECTORY_SEPARATOR.'pc-shop-level.php';
    }

    // Timing All Brand
    public function timingAllBrandAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-all-brand'.DIRECTORY_SEPARATOR.'timing-all-brand.php';
    }
    public function timingAllBrandEditAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-all-brand'.DIRECTORY_SEPARATOR.'timing-all-brand-edit.php';
    }
    public function timingAllBrandSaveAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'timing-all-brand'.DIRECTORY_SEPARATOR.'timing-all-brand-save.php';
    }

    // PC Working Status 
    public function pcWorkingStatusAction() {
        require_once 'timing'.DIRECTORY_SEPARATOR.'pc-working-status'.DIRECTORY_SEPARATOR.'pc-working-status.php';
    }
    public function pcWorkingStatusDetailAction() {
        require_once 'timing'.DIRECTORY_SEPARATOR.'pc-working-status'.DIRECTORY_SEPARATOR.'pc-working-status-detail.php';
    }

    // Channel PC Status
    public function channelPcStatusAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'channel-pc-status'.DIRECTORY_SEPARATOR.'channel-pc-status.php';
    }

    // Area Daily Arrival Order
    public function areaDailyArrivalOrderAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'area-daily-arrival-order'.DIRECTORY_SEPARATOR.'area-daily-arrival-order.php';
    }

    // Trade In Report
    public function tradeInAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'trade-in'.DIRECTORY_SEPARATOR.'trade-in.php';
    }

    // Shop Visit Report
    public function visitShopAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'visit-shop'.DIRECTORY_SEPARATOR.'visit-shop.php';
    }

    // Online Sellout Report
    public function onlineSelloutAction()
    {
        require_once 'timing'.DIRECTORY_SEPARATOR.'online-sellout'.DIRECTORY_SEPARATOR.'online-sellout.php';
    }

    // API 
    public function apiSaveTimingAction()
    {
        require_once 'timing' . DIRECTORY_SEPARATOR . 'api-save-timing.php';
    }

    public function apiSaveTimingIssueAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($_POST['imei'] && $_POST['staff_id']) {
            $QTimingIssue = new Application_Model_TimingIssue();

            $issue_type = $_POST['issue_type'];
            $staff_id = $_POST['staff_id'];
            $imei = $_POST['imei'];
            $store = $_POST['store'];
            $timing_date = $_POST['timing_date'];

            $where = $QTimingIssue->getAdapter()->quoteInto('imei = ?', $imei);
            $timing_issue = $QTimingIssue->fetchRow($where);

            if ($timing_issue['imei']) {
                $result['msg'] = 'มีข้อมูลการแจ้งปัญหานี้อยู่แล้ว !!';
                $result['success'] = 'FAIL';
            } else {

                $data = array(
                    'issue_type' => $issue_type,
                    'imei' => $imei,
                    'store_id' => $store,
                    'staff_id' => $staff_id,
                    'timing_date' => $timing_date
                );

                $insert = $QTimingIssue->insert($data);

                if ($insert) {
                    $result['msg'] = 'แจ้งปัญหาการรายงานยอดเรียบร้อย';
                    $result['success'] = 'COMPLETE';
                } else {
                    $result['msg'] = 'บันทึกข้อมูลผิดพลาด !!';
                    $result['success'] = 'FAIL';
                }
            }
        } else {
            $result['msg'] = 'No parameter !';
            $result['success'] = 'FAIL';
        }

        echo json_encode($result);
    }

    //---------------private function-----------------------
    private function checkImei($imei, $timing_sales_id, &$info, $tool = null, $reimport = false)
    {
        /**
         * Note: by phamquocbuu
         * Check IMEI theo bảng imei_exception
         *      co trong bang exception thi uu tien lay trong do de check co fai imei demo hay khong
         * Check IMEI theo bảng imei
         *      Không tồn tại -> thông báo
         *      Tồn tại -> bước sau
         * Check trong bảng acti
         *      Ngày acti trước 30 ngày -> từ chối chấm công
         *      Ngược lại, check trong phần timing
         *          Tồn tại
         *              So sánh thời gian chấm công
         *                  Chấm hơn 30 ngày -> từ chối
         *                  Ngược lại -> thông báo
         *          Không tồn tại -> chấm công
         */

        // // Check IMEI theo bảng imei
        // $QWebImei = new Application_Model_WebImei();
        // $where = array();
        // // $where[] = $QWebImei->getAdapter()->quoteInto('out_date > ?', 0);
        // $where[] = $QWebImei->getAdapter()->quoteInto('into_date IS NOT NULL AND into_date <> ?', 0);
        // $where[] = $QWebImei->getAdapter()->quoteInto('out_date IS NOT NULL AND out_date <> ?', 0);
        // $where[] = $QWebImei->getAdapter()->quoteInto('imei_sn = ?', $imei);

        // $result = $QWebImei->fetchRow($where);
        // if (!$result || !preg_match('/^[0-9]{15}$/', $imei))
        // {
        //     //not existed in list sales out
        //     return 2;
        // }

        // $info['activated_at'] = $result['activated_date'];
        // $info['out_date'] = $result['out_date'];

        // //check trong bảng lock_imei
        // $QLockedImei = new Application_Model_LockedImei();
        // $where = $QLockedImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
        // $locked = $QLockedImei->fetchRow($where);

        // if ($locked)
        // {
        //     return 6; // hàng cấm, éo tính
        // }

        // //check trong bảng imei_demo_fpt
        // $QDemo = new Application_Model_ImeiDemoFpt();
        // $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
        // $locked = $QDemo->fetchRow($where);

        // if ($locked)
        // {
        //     return 7; // hàng demo của fpt, éo tính
        // }

        // //check hàng demo, for staff, for lending
        // // check trong bang imei_exception
        // $QImeiException = new Application_Model_ImeiException();
        // $where = $QImeiException->getAdapter()->quoteInto('imei_sn = ?', $imei);
        // $imei_exception = $QImeiException->fetchRow($where);
        // //check hàng demo
        // if ($imei_exception)
        // { // neu co trong exception thi uu tien lay trong do

        //     // kiem tra type cua exception: 1: xuat cho retailer; 2: xuat demo ...
        //     if ($imei_exception['type'] == 2)
        //     {
        //         return 7; // hàng demo, éo tính
        //     }

        //     if ($imei_exception['type'] == 3)
        //     {
        //         return 8; // hàng staff, éo tính
        //     }

        //     if ($imei_exception['type'] == 4)
        //     {
        //         return 9; // hàng lending, éo tính
        //     }

        // } else
        // {
        //     //check trong bảng imei_demo_fpt
        //     $QDemo = new Application_Model_ImeiDemoFpt();
        //     $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
        //     $locked = $QDemo->fetchRow($where);

        //     if ($locked)
        //     {
        //         return 7; // hàng demo, éo tính
        //     }

        //     //check hàng demo
        //     $QMarket = new Application_Model_Market();
        //     $where = array();
        //     $where[] = $QMarket->getAdapter()->quoteInto('sn = ?', $result['sales_sn']);
        //     $where[] = $QMarket->getAdapter()->quoteInto('outmysql_time >= ?',
        //         '2014-06-01 00:00:00');
        //     $market = $QMarket->fetchRow($where);

        //     if ($market)
        //     {
        //         if ($market['type'] == 2 && !in_array($imei, array('865884024323595')))
        //         {
        //             return 7; // hàng demo, éo tính
        //         }

        //         if ($market['type'] == 3)
        //         {
        //             return 8; // hàng staff, éo tính
        //         }

        //         if ($market['type'] == 4)
        //         {
        //             return 9; // hàng lending, éo tính
        //         }
        //     }
        // }

        //check trong phần timing
      $QTimingSale = new Application_Model_TimingSale();
        $where = array();
        $where[] = $QTimingSale->getAdapter()->quoteInto('id <> ?', $timing_sales_id);
        $where[] = $QTimingSale->getAdapter()->quoteInto('imei = ?', $imei);

        $ts = $QTimingSale->fetchRow($where);

        // IMEI đã chấm công
        if ($ts)
        {
            $QTiming = new Application_Model_Timing();
            $where = $QTiming->getAdapter()->quoteInto('id = ?', $ts['timing_id']);
            $result = $QTiming->fetchRow($where);

            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            if (!$userStorage || !isset($userStorage->id)) return 99;

            $user_id = $userStorage->id;

            // if ($result && $result['staff_id'] == $user_id)
            // {
            //     $info['date'] = $result['created_at'];

            //     $QStore = new Application_Model_Store();
            //     $store_rs = $QStore->find($result['store']);
            //     $store = $store_rs->current();

            //     $info['store'] = $store['name'];

            //     return 1; // nó chấm rồi
            // } else
            // {
                $QStaff = new Application_Model_Staff();
                $staff_rs = $QStaff->find($result['staff_id']);
                $staff = $staff_rs->current();

                $QStore = new Application_Model_Store();
                $store_rs = $QStore->find($result['store']);
                $store = $store_rs->current();

                // Get O-Guard Info
                $QOGuard = new Application_Model_OGuard();
                $where_og = $QOGuard->getAdapter()->quoteInto('imei_sn = ?', $imei);
                $og_result = $QOGuard->fetchRow($where_og);

                // Get Sim Locked Info
                $QPackedSim = new Application_Model_PackedSim();
                $where_ps = $QPackedSim->getAdapter()->quoteInto('imei_sn = ?', $imei);
                $ps_result = $QPackedSim->fetchRow($where_ps);

                // Get Good Name and Good Color
                $QImei = new Application_Model_WebImei();
                $where = $QImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
                $wh_result = $QImei->fetchRow($where);

                $QGood = new Application_Model_Good();
                $good_rs = $QGood->find($wh_result['good_id']);
                $good_name = $good_rs->current();

                $QGoodColor = new Application_Model_GoodColor();
                $gc_rs = $QGoodColor->find($wh_result['good_color']);
                $good_color = $gc_rs->current();

                $QGroup = new Application_Model_Group();
                $group_rs = $QGroup->find($staff['group_id']);
                $group = $group_rs->current();

                $QRegionalMarket = new Application_Model_RegionalMarket();
                $rm_rs = $QRegionalMarket->find($store['regional_market']);
                $province = $rm_rs->current();

                $QArea = new Application_Model_Area();
                $area_rs = $QArea->find($province['area_id']);
                $area = $area_rs->current();

                // if ($staff) {
                //     $info['staff'] = $staff['firstname'] . ' ' . $staff['lastname'] . ' | ' .
                //         preg_replace('/oppomobile.vn/', '', $staff['email']);
                //     $info['staff_id'] = $staff['id'];
                // }

                if ($staff) {
                    $info['staff'] = "[".$staff['code']."] ".$staff['firstname']." ".$staff['lastname'];

                }

                if ($store) {
                    $info['store_id'] = $store['id'];
                    $info['store'] = $store['name'];
                }

                if ($good_name) {
                    $info['good_name'] = $good_name['name'];
                }

                if ($good_color) {
                    $info['good_color'] = $good_color['name'];
                }

                if ($group) {
                    $info['group'] = $group['name'];
                }

                if ($area) {
                    $info['area'] = $area['name'];
                }

                $info['date'] = $result['created_at'];
                $info['active_date'] = $wh_result['activated_date'];
                $info['timing_sales_id'] = $ts['id'];

                if ($ts['pre_order_status'] == 1) { $info['pre_order_status'] = "Yes"; }
                else { $info['pre_order_status'] = "No"; }

                $info['warrant_no'] = $ts['warrant_no'];

                if ( !is_null($ps_result['imei_sn']) ) { 

                    $info['sim_locked'] = "Yes"; 
                    $info['sim_activated_at'] = $ps_result['sim_activated_at'];

                } else { 

                    $info['sim_locked'] = "No"; 
                    $info['sim_activated_at'] = "n/a";

                }

                if ( !is_null($og_result['imei_sn']) ) { $info['o_guard'] = "Yes"; } 
                else { $info['o_guard'] = "No"; }

                // kiểm tra phòng trường hợp bảng imei acti ko có imei này
                // bán hơn 1 tháng
                if (strtotime(date('Y-m-d')) - strtotime($result['created_at']) >
                    IMEI_ACTIVATION_EXPIRE * 24 * 3600)
                    return 5;

                return 3; // thằng khác chấm
            // }
        } 
        // Add by Sak
        else {
            $QImei = new Application_Model_WebImei();
            $where = $QImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
            $result = $QImei->fetchRow($where);

            if ($result) {
                if ($result['sales_sn'] == '') {
                    // PO but not Sales Out
                    return 10;
                } else {
                    // ready to Sales Out
                    return 11;    
                }
            } else {
                return 2;
            }
        }

        // IMEI chưa chấm công
        return 0;
    }

    private function checkImei30DaysActivated($imei)
    {
        $db = Zend_Registry::get('db');
        $db->getProfiler()->setEnabled(true);
        //Check trong bảng acti
        $QImeiActivation = new Application_Model_ImeiActivation();
        $where = array();
        $where[] = $QImeiActivation->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $tmp_date = date_sub(date_create(), new DateInterval('P' .
            IMEI_ACTIVATION_EXPIRE . 'D'))->format('Y-m-d 00:00:00');
        $where[] = $QImeiActivation->getAdapter()->quoteInto('activated_at < ?', $tmp_date);
        $imei_activation = $QImeiActivation->fetchRow($where);

        if ($imei_activation)
        { // IMEI đã acti hơn IMEI_ACTIVATION_EXPIRE ngày
            return 1;
        }

        return 0;
        //
    }

    private function getCustomerInfo($imei = '', &$customer)
    {
        $QTimingSale = new Application_Model_TimingSale();
        $where = $QTimingSale->getAdapter()->quoteInto('imei=?', $imei);
        $timing_sales = $QTimingSale->fetchRow($where);

        if ($timing_sales)
        {
            $QCustomer = new Application_Model_Customer();
            $where = $QCustomer->getAdapter()->quoteInto('id = ?', $timing_sales->
                customer_id);

            $customer = $QCustomer->fetchRow($where);

            if ($customer)
                return $customer->toArray();
        }

        return false;
    }

    // Export Report By Store : Export Sell All Brand
    private function _exportExcelbrand($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Store',
            'Store Area',
            'OPPO Sell Out Quantity',
            'Samsung Sell Out Quantity',
            'Vivo Sell Out Quantity',
            'Other Sell Out Quantity',
            'From Date',
            'To Date',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $i = 1;

        foreach($data as $item) {
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i++);
            $sheet->setCellValue($alpha++.$index, $item['name']);
            $sheet->setCellValue($alpha++.$index, $item['area']);
            $sheet->setCellValue($alpha++.$index, $item['oppo']);
            $sheet->setCellValue($alpha++.$index, $item['samsung']);
            $sheet->setCellValue($alpha++.$index, $item['vivo']);
            $sheet->setCellValue($alpha++.$index, $item['other']);
            $sheet->setCellValue($alpha++.$index, $params['from']);
            $sheet->setCellValue($alpha++.$index, $params['to']);
            $index++;

        }
        
        $filename = 'Store_Brand_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report By Store : Export Excel
    private function _exportExcelOrgDealer($data,$params) {
        //echo "sss"; print_r($params); die;
    //     require_once 'PHPExcel.php';
    //     $PHPExcel = new PHPExcel();

    //     $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    //     $created_report_by = "[".$userStorage->code."] ".$userStorage->firstname." ".$userStorage->lastname;
    //     $created_report_at = date("Y-m-d H:i:s");

    //     $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
    //     $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

    //     $heads = array(
    //         'NO.',
    //         'Store ID',
    //         'Store Type',
    //         'Store Name',
    //         'Shop ID',
    //         'Shop Code',
    //         'Store Status',
    //         'Store Rank',
    //         'Store Level',
    //         'Market Type',
    //         'Market Name ID',
    //         'Market Name',
    //         'IT Junction',
    //         'Is PreOrder',
    //         'Grand Area',
    //         'Area Name',
    //         'Provice',
    //         'District', 
    //         'ASM/Leader/Sale',
    //     );

    //     $QTiming = new Application_Model_Timing();
    //     $good_list = $QTiming->getAllModel();

    //     $QStoreMarket = new Application_Model_StoreMarket();
    //     $store_market = $QStoreMarket->get_cache();

    //     $QStoreStaff = new Application_Model_StoreStaff();

    //     for ($i=0;$i<count($good_list);$i++) {
    //         array_push($heads, $good_list[$i]['good_name']."_".$good_list[$i]['color_name']);
    //     }

    //     array_push($heads, 'Total Sell Out', 'Last 1 Month', 'Last 2 Month', 
    //         //'Total Activated (For Commission)', 
    //         'Created Report By '.$created_report_by." AT ".$created_report_at);

    //     $PHPExcel->setActiveSheetIndex(0);
    //     $sheet    = $PHPExcel->getActiveSheet();

    //     $alpha    = 'A';
    //     $index    = 1;

    //     foreach($heads as $key) {
    //         $sheet->setCellValue($alpha.$index, $key);
    //         $alpha++;
    //     }

    //     $sellout_by_model = $QTiming->getSelloutByModel($from, $to);

    //     // Set Grand Area of BKK
    //     $grand_e1 = array(81,82,83,110,111,112);
    //     $grand_e2 = array(85,86,87,115,88,89,116,117);
    //     $grand_e3 = array(90,91,92,93,113);
    //     $grand_e4 = array(94,95,96);
    //     $grand_e5 = array(97,109);
    //     $grand_w1 = array(98,99,100,101,102,114);
    //     $grand_w2 = array(103,104,105);
    //     $grand_w3 = array(106,107,108);

    //     $grand_area = "";

    //     $index = 2;
    //     for ($i=0;$i<count($data);$i++) {

    //         if ( in_array($data[$i]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
    //         else if ( in_array($data[$i]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
    //         else if ( in_array($data[$i]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
    //         else { $grand_area = $data[$i]['area']; }

    //         $result_ss_sale = $QStoreStaff->getSale($data[$i]['st_id']);

    //         if ($data[$i]['last_02'] == null) { $tmp_qnt_last_02 = 0; } else { $tmp_qnt_last_02 = $data[$i]['last_02']; }
    //         if ($data[$i]['last_01'] == null) { $tmp_qnt_last_01 = 0; } else { $tmp_qnt_last_01 = $data[$i]['last_01']; }
    //         if ($data[$i]['sellout'] == null) { $tmp_qnt = 0; } else { $tmp_qnt = $data[$i]['sellout']; }
    //         //if ($data[$i]['activated'] == null) { $tmp_active = 0; } else { $tmp_active = $data[$i]['activated']; }

    //         if ($data[$i]['total_price'] == null) { $tmp_price = 0; } else { $tmp_price = $data[$i]['total_price']; }

    //         //if ($data[$i]['com_activated'] == null) { $tmp_com_active = 0; } else { $tmp_com_active = $data[$i]['com_activated']; }

    //         if ($data[$i]['it_junction'] == 1) { $it_junction = "Yes"; } else { $it_junction = "No"; }
    //         if ($data[$i]['is_pre_order'] == 1) { $is_pre_order = "Yes"; } else { $is_pre_order = "No"; }

    //         if ($data[$i]['st_del'] == 1) { $st_del = "Disabled"; } else { $st_del = "Active"; }

    //         $alpha    = 'A';
    //         $sheet->setCellValue($alpha++.$index, $i+1);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_shopid']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_shopcode']);
    //         $sheet->setCellValue($alpha++.$index, $st_del);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_rank']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['st_grade']);
    //         $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_type']);
    //         $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_name_id']);
    //         $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_name']);
    //         $sheet->setCellValue($alpha++.$index, $it_junction);
    //         $sheet->setCellValue($alpha++.$index, $is_pre_order);
    //         $sheet->setCellValue($alpha++.$index, $grand_area);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['area']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
    //         $sheet->setCellValue($alpha++.$index, $data[$i]['district']);
    //         $sheet->setCellValue($alpha++.$index, $result_ss_sale['sale_name']);

    //         for ($j=0;$j<count($good_list);$j++) { 
    //             if ($tmp_qnt == 0) { 
    //                 $sheet->setCellValue($alpha++.$index, 0);
    //             } else {
    //                 if ( isset($sellout_by_model[ $data[$i]['st_id'] ][ $good_list[$j]['good_id'] ][ $good_list[$j]['color_id'] ]) ) {
    //                     $sheet->setCellValue($alpha++.$index, $sellout_by_model[ $data[$i]['st_id'] ][ $good_list[$j]['good_id'] ][ $good_list[$j]['color_id'] ]);
    //                 } else {
    //                     $sheet->setCellValue($alpha++.$index, 0);
    //                 }
    //             }
    //         }

    //         /*
    //         for ($j=0;$j<count($good_list);$j++) {
    //             if ($tmp_qnt == 0) { 
    //                 $sheet->setCellValue($alpha++.$index, 0);
    //             } else {
    //                 $sellout_by_model = $QTiming->getSelloutByModel($data[$i]['st_id'], $good_list[$j]['good_id'], $good_list[$j]['color_id'], $from, $to);
    //                 $sheet->setCellValue($alpha++.$index, $sellout_by_model);
    //             }
    //         }
    //         */

    //         //$sheet->setCellValue($alpha++.$index, $tmp_active);
    //         $sheet->setCellValue($alpha++.$index, $tmp_qnt);
    //         //$sheet->setCellValue($alpha++.$index, $tmp_price);

    //         $sheet->setCellValue($alpha++.$index, $tmp_qnt_last_01);
    //         $sheet->setCellValue($alpha++.$index, $tmp_qnt_last_02);
    //         //$sheet->setCellValue($alpha++.$index, $tmp_com_active);
            
    //         $index++;
    //     }
        
    //     $filename = 'ORG_Dealer_list_'.date('d/m/Y');
    //     $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    //     $objWriter->save('php://output');
    //     exit;
    // }

         $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $db = Zend_Registry::get('db');
        set_time_limit(0);
        error_reporting(~E_ALL);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);
        $filename = 'Export Return List - '.date('d-m-Y H-i-s').'.csv';
        // output headers so that the file is downloaded rather than displayed
        while (@ob_end_clean());
        ob_start();
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $file_path = APPLICATION_PATH.'/../public/files/sales/export/'.$userStorage->id.'/'.uniqid();
        if (!file_exists($file_path))
            mkdir($file_path, 0777, true);


        $path = $file_path.'/'.$filename;
        $output = fopen($path, 'w+');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $heads = array(
            'NO.',
            'Store ID',
            'Store Type',
            'Store Name',
            'Shop ID',
            'Shop Code',
            'Store Status',
            'Store Rank',
            'Store Level',
            'Market Type',
            'Market Name ID',
            'Market Name',
            'IT Junction',
            'Is PreOrder',
            'Grand Area',
            'Area Name',
            'Provice',
            'District', 
            'ASM/Leader/Sale',
        );
        fputcsv($output, $heads);

        $QTiming = new Application_Model_Timing();
        $good_list = $QTiming->getAllModel();

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QStoreStaff = new Application_Model_StoreStaff();

        $result = $db->query($sql);
        print_r($result);die;

        $i = 1;

        foreach($data as $item) {

            if ($item['return_type']==1){
                $order_type="Defective";
            }else if ($item['return_type']==2){
                $order_type="Adjustment";
            }else if ($item['return_type']==3){
                $order_type="Demo";
            }else{
                $order_type="-";
            }
            if (isset($goods) && isset($goods[$item['good_id']]))
                $good_name = $goods[$item['good_id']];
            if (isset($goodColors) && isset($goodColors[$item['good_color']]))
                $good_color = $goodColors[$item['good_color']];
            $total = $item['total_qty']*$item['price'];
            $row = array();
            $row[] = $i++;
            $row[] = $item['sn_ref'];
            $row[] = $item['creditnote_sn'];
            $row[] = $distributors[$item['d_id']];
            $row[] = $order_type;
            $row[] = $good_name;
            $row[] = $good_color;
            $row[] = $item['total_qty'];
            $row[] = number_format($item['price']);
            $row[] = number_format($total);
            $row[] = $item['add_time'];
            $row[] = $warehouses_cached[$item['warehouse_id']];
            $row[] = $item['finance_group'];
            $row[] = $item['text'];

            fputcsv($output, $row);
            unset($item);
            unset($row);
        }

        fclose($output);

        ob_flush();
        ob_start();
        while (@ob_end_flush());

        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;

        $file = fopen($path, 'r');
        $content = fread($file, filesize($path));
        var_dump(filesize($path));
        var_dump($content);

        exit;
    }

    // Export Report By Store : Export CSV List [Same As List] 
    private function _exportExcelStoreList($result,$params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Store_list_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_by = "[".$userStorage->code."] ".$userStorage->firstname." ".$userStorage->lastname;
        $created_report_at = date("Y-m-d H:i:s");

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $heads = array(
            'NO.',
            'Store ID',
            // 'Store Type',
            // 'Store Operation',
            'Store Name',
            // 'Store Level',
            // 'Shop ID',
            // 'Shop Code',
            'Shop Status',
            // 'Store Rank',
            'Distributor ID (Chain1)',
            'Distributor Name (Chain1)',
            // 'Market Type',
            // 'Market Name',
            // 'IT Junction',
            // 'Grand Area',
            'Area Name',
            'Province',
            'District', 
            'ASM/Leader/Sale',
            // 'PC [Code]',
            // 'PC [Name]',
            // 'PC Stand By [Code]',
            // 'PC Stand By [Name]',
            'PC Manager',
            'Store Price Target',
            'Last 2 Month',
            'Last 1 Month',
            'Total Sell Out',
            'Total Price',
            "Total Sell Out [".$params['selected_product_name']."]",
            'Created Report By '.$created_report_by." AT ".$created_report_at,
        );

        fputcsv($output, $heads);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QStoreStaff = new Application_Model_StoreStaff();
        $QSPT = new Application_Model_StorePriceTarget();

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        $no = 1;

        foreach ($result as $data) {

            if ( in_array($data[$i]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data[$i]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data[$i]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data[$i]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data[$i]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data[$i]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data['area']; }

            $result_ss_sale = array();
            $result_ss_pcm = array();

            $result_ss_sale = $QStoreStaff->getSale($data['st_id']);
            $result_ss_pcm = $QStoreStaff->getPCM($data['st_id']);

            $params2 = array();
            $params2 = array(
                'store_id'  => $data['st_id'],
                'from'      => $params['from'],
                'to'        => $params['to'],
            );

            $st_target = $QSPT->checkTargetByStore($params2, $data['st_id']);

            if ($data['last_02'] == null) { $tmp_qnt_last_02 = 0; } else { $tmp_qnt_last_02 = $data['last_02']; }
            if ($data['last_01'] == null) { $tmp_qnt_last_01 = 0; } else { $tmp_qnt_last_01 = $data['last_01']; }

            if ($data['sellout'] == null) { $tmp_qnt = 0; } else { $tmp_qnt = $data['sellout']; }
            if ($data['total_price'] == null) { $tmp_price = 0; } else { $tmp_price = $data['total_price']; }

            if ($data['sellout_hero'] == null) { $tmp_qnt_hero = 0; } else { $tmp_qnt_hero = $data['sellout_hero']; }

            if ($data['it_junction'] == 1) { $it_junction = "Yes"; } else { $it_junction = "No"; }
            if ($data['st_del'] == 1) { $st_del = "Disabled"; } else { $st_del = "Active"; }

            $row = array();
            $row[] = $no++;
            $row[] = $data['st_id'];
            // $row[] = $data['st_type'];
            // $row[] = $data['st_operation'];
            $row[] = $data['st_name'];
            // $row[] = $data['st_level'];
            // $row[] = $data['st_shopid'];
            // $row[] = $data['st_shopcode'];
            $row[] = $st_del;
            // $row[] = $data['st_rank'];
            $row[] = $data['d_id'];
            $row[] = $data['d_name'];
            // $row[] = $store_market[ $data['st_id'] ]['market_type'];
            // $row[] = $store_market[ $data['st_id'] ]['market_name'];
            // $row[] = $it_junction;
            // $row[] = $grand_area;
            $row[] = $data['area'];
            $row[] = $data['province'];
            $row[] = $data['district'];
            $row[] = $result_ss_sale['sale_name'];
            
            // PC 
            $staff_list = $QStoreStaff->getPC($data['st_id'],0);
            $cnt = count($staff_list);
            $pc_name = "";
            $pc_code = "";

            // if ($cnt > 0 ) {
            //     for ($j=0;$j<$cnt;$j++) {
            //         if ($j != $cnt-1) { 
            //             $pc_name = $pc_name.$staff_list[$j]['pc_name'].""; 
            //             $pc_code = $pc_code.$staff_list[$j]['pc_code']." / "; 
            //         } else { 
            //             $pc_name = $pc_name.$staff_list[$j]['pc_name'].""; 
            //             $pc_code = $pc_code.$staff_list[$j]['pc_code']; 
            //         }
            //     }
            // } else { $pc_name = "-"; $pc_code = "-"; }

            // // PC Stand By
            // $staff_list = $QStoreStaff->getPC($data['st_id'],1);
            // $cnt = count($staff_list);
            // $pc_stand_by_name = "";
            // $pc_stand_by_code = "";

            // if ($cnt > 0 ) {
            //     for ($j=0;$j<$cnt;$j++) {
            //         if ($j != $cnt-1) { 
            //             $pc_stand_by_name = $pc_stand_by_name.$staff_list[$j]['pc_name'].""; 
            //             $pc_stand_by_code = $pc_stand_by_code.$staff_list[$j]['pc_code']." / "; 
            //         } else { 
            //             $pc_stand_by_name = $pc_stand_by_name.$staff_list[$j]['pc_name'].""; 
            //             $pc_stand_by_code = $pc_stand_by_code.$staff_list[$j]['pc_code']; 
            //         }
            //     }
            // } else { $pc_stand_by_name = "-"; $pc_stand_by_code = "-"; }

            // $row[] = $pc_code;
            // $row[] = $pc_name;
            // $row[] = $pc_stand_by_code;
            // $row[] = $pc_stand_by_name;
            $row[] = $result_ss_pcm['pcm_name'];
            $row[] = $st_target['target_price'];
            $row[] = $tmp_qnt_last_02;
            $row[] = $tmp_qnt_last_01;
            $row[] = $tmp_qnt;
            $row[] = $tmp_price;
            $row[] = $tmp_qnt_hero;

            fputcsv($output, $row);
        }

        exit;
    }

    // Export Report By Store : Export BS Store List 
    private function _exportExcelBsStoreList($params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'BS_Store_List_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $heads = array(
            'NO.',
            'Shop Status',
            // 'Store Level',
            // 'Store ID',
            // 'Store Type',
            'Store Name',
            'Store Target',
            'Focus Model Target',
            'Last 2 Month',
            'Last 1 Month',
            'Total Sell Out',
            'Total Price',
            "Total Sell Out [".$params['selected_product_name']."]",
            // 'Operator Package',
            // 'OP ID',
            'Rental Fee',
            'Service Fee',
            'Gross Profit',
            'Distributor ID (Chain1)',
            'Distributor Name (Chain1)',
            'Distributor Stock',
            // 'Distributor Stock [Scan]',
            // 'Market Name',
            'Area Name',
            'Province',
            'District', 
            'RD',
            'ASM',
            // 'BM (No.)',
            'PC (No.)',
            'BM',
            'PC [Code]',
            'PC Stand By [Code]',
            // 'Contract period with OPPO Start',
            // 'Contract period with OPPO End',
            // 'Contract period with Market Start',
            // 'Contract period with Market End',
            'Shop Size',
            // 'Deposts',
            // 'Experience Shop',
            // 'Room Type',
            // 'Shop Acreage',
            // 'Shop Version',
        );

        fputcsv($output, $heads);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QStoreStaff = new Application_Model_StoreStaff();
        $QBCT = new Application_Model_BmComTarget();
        $QAsm = new Application_Model_Asm();

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->analytic_bs_store($params);

        // echo "<pre>"; print_r($result); 

        $no = 1;
        foreach ($result as $data) {

            $params2 = array();
            $params2 = array(
                'store_id'  => $data['st_id'],
                'from'      => $params['from'],
                'to'        => $params['to'],
            );

            $st_target = $QBCT->getBsStoreTarget($params2);
            // echo "<br/>"; print_r($st_target);

            if ($data['last_02'] == null) { $tmp_qnt_last_02 = 0; } else { $tmp_qnt_last_02 = $data['last_02']; }
            if ($data['last_01'] == null) { $tmp_qnt_last_01 = 0; } else { $tmp_qnt_last_01 = $data['last_01']; }

            if ($data['sellout'] == null) { $tmp_qnt = 0; } else { $tmp_qnt = $data['sellout']; }
            if ($data['sellout_hero'] == null) { $tmp_qnt_hero = 0; } else { $tmp_qnt_hero = $data['sellout_hero']; }

            if ($data['total_price'] == null) { $tmp_price = 0; } else { $tmp_price = $data['total_price']; }

            if ($data['st_del'] == 1) { $st_del = "Disabled"; } else { $st_del = "Active"; }

            $gross_profit = 0;
            if ( $data['total_price'] != 0 ) { $gross_profit = $data['total_price'] - $data['cost_price']; }
            
            $row = array();
            $row[] = $no++;
            $row[] = $st_del;
            // $row[] = $data['st_level'];
            // $row[] = $data['st_id'];
            // $row[] = $data['st_type'];
            $row[] = $data['st_name'];

            $row[] = $st_target['target'];
            $row[] = $st_target['target_focus'];

            $row[] = $tmp_qnt_last_02;
            $row[] = $tmp_qnt_last_01;
            $row[] = $tmp_qnt;
            $row[] = $tmp_price;
            $row[] = $tmp_qnt_hero;

            // $row[] = $data['operator_package'];
            // $row[] = $data['d_code'];
            $row[] = $data['rental_fee'];
            $row[] = $data['service_fee'];
            // $row[] = $data['total_price']." | ".$data['cost_price'];
            $row[] = $gross_profit;
            
            $row[] = $data['d_id'];
            $row[] = $data['d_name'];

            // Get Current Stock of Distributor 
            if ( $data['d_id'] ) {
                $d_stock = $QTiming->getCurrentStockByDID($data['d_id']);
                $row[] = $d_stock['stock'];
                // $row[] = $d_stock['stock_scan'];
            } else {
                $row[] = '';
                $row[] = '';
            }

           // $row[] = $store_market[ $data['st_id'] ]['market_name'];
            $row[] = $data['area'];
            $row[] = $data['province'];
            $row[] = $data['district'];

            // RD 
            $result_rd = $QAsm->getStaffByAreaID($data['area_id'], 1);
            // echo "<br/>"; print_r($result_rd); 
            $cnt = count($result_rd);
            $rd_name = "";
            // $rd_code = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { 
                        $rd_name = $rd_name.$result_rd[$j]['staff_name']." / "; 
                        // $rd_code = $rd_code.$result_rd[$j]['staff_code']." / "; 
                    } else { 
                        $rd_name = $rd_name.$result_rd[$j]['staff_name']; 
                        // $rd_code = $rd_code.$result_rd[$j]['staff_code']; 
                    }
                }
            } else { $rd_name = "-"; /*$rd_code = "-";*/ }

            // ASM
            $result_asm = $QAsm->getStaffByAreaID($data['area_id'], 2);
            // echo "<br/>"; print_r($result_asm);
            $cnt = count($result_asm);
            $asm_name = "";
            // $asm_code = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { 
                        $asm_name = $asm_name.$result_asm[$j]['staff_name']." / "; 
                        // $asm_code = $asm_code.$result_asm[$j]['staff_code']." / "; 
                    } else { 
                        $asm_name = $asm_name.$result_asm[$j]['staff_name']; 
                        // $asm_code = $asm_code.$result_asm[$j]['staff_code']; 
                    }
                }
            } else { $asm_name = "-"; /*$asm_code = "-";*/ }

            // BM 
            $result_ss_bm = $QStoreStaff->getBM($data['st_id']);
            // echo "<br/>"; print_r($result_ss_bm); 
            $cnt_bm = count($result_ss_bm);
            $bm_name = "";
            // $bm_code = "";

            if ($cnt_bm > 0 ) {
                for ($j=0;$j<$cnt_bm;$j++) {
                    if ($j != $cnt_bm-1) { 
                        $bm_name = $bm_name.$result_ss_bm[$j]['sale_name']." / "; 
                        // $bm_code = $bm_code.$result_ss_bm[$j]['sale_code']." / "; 
                    } else { 
                        $bm_name = $bm_name.$result_ss_bm[$j]['sale_name']; 
                        // $bm_code = $bm_code.$result_ss_bm[$j]['sale_code']; 
                    }
                }
            } else { $bm_name = "-"; /*$bm_code = "-";*/ }
            
            // PC 
            $staff_list = $QStoreStaff->getPC($data['st_id'],0);
            $cnt_pc = count($staff_list);
            // $pc_name = "";
            $pc_code = "";

            if ($cnt_pc > 0 ) {
                for ($j=0;$j<$cnt_pc;$j++) {
                    if ($j != $cnt_pc-1) { 
                        // $pc_name = $pc_name.$staff_list[$j]['pc_name']." (".$staff_list[$j]['pc_phone'].") / "; 
                        $pc_code = $pc_code.$staff_list[$j]['pc_code']." / "; 
                    } else { 
                        // $pc_name = $pc_name.$staff_list[$j]['pc_name']." (".$staff_list[$j]['pc_phone'].")"; 
                        $pc_code = $pc_code.$staff_list[$j]['pc_code']; 
                    }
                }
            } else { /*$pc_name = "-";*/ $pc_code = "-"; }

            // PC Stand By
            $staff_list = $QStoreStaff->getPC($data['st_id'],1);
            $cnt = count($staff_list);
            // $pc_stand_by_name = "";
            $pc_stand_by_code = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { 
                        // $pc_stand_by_name = $pc_stand_by_name.$staff_list[$j]['pc_name']." (".$staff_list[$j]['pc_phone'].") / "; 
                        $pc_stand_by_code = $pc_stand_by_code.$staff_list[$j]['pc_code']." / "; 
                    } else { 
                        // $pc_stand_by_name = $pc_stand_by_name.$staff_list[$j]['pc_name']." (".$staff_list[$j]['pc_phone'].")"; 
                        $pc_stand_by_code = $pc_stand_by_code.$staff_list[$j]['pc_code']; 
                    }
                }
            } else { /*$pc_stand_by_name = "-";*/ $pc_stand_by_code = "-"; }


            // $row[] = $rd_code;
            // $row[] = $asm_code;
            // $row[] = $bm_code;
            // $row[] = $pc_name;
            // $row[] = $pc_stand_by_name;

            $row[] = $rd_name;
            $row[] = $asm_name;

            // $row[] = $cnt_bm;
            $row[] = $cnt_pc;

            $row[] = $bm_name;
            $row[] = $pc_code;
            $row[] = $pc_stand_by_code;
            
            // $row[] = $data['ct_oppo_start'];
            // $row[] = $data['ct_oppo_end'];
            // $row[] = $data['ct_market_start'];
            // $row[] = $data['ct_market_end'];
            $row[] = $data['shop_size'];
            // $row[] = $data['deposit'];
            // $row[] = $data['exp_shop'];
            // $row[] = $data['room_type'];
            // $row[] = number_format($data['shop_acreage'],2);
            // $row[] = number_format($data['shop_version'],1);

            fputcsv($output, $row);
        }

        exit;
    }

    // Export Report By Store : Export BS Store Stock Scan 
    private function _exportExcelBsStockScan($params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'BS_Stock_Scan_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        // $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        // $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $QGood = new Application_Model_Good();
        $good_list = $QGood->getBsStockScanProductList();

        $heads = array(
            'NO.',
            'Store ID',
            'Distributor ID',
            'Distributor Name',
            'Store Name',
            'Area',
            'RD Name',
            'Store Type',
            'Store Status',
            'Room Type',
            'Shop Acreage',
            'Shop Version',
        );

        for ($i=0;$i<count($good_list);$i++) {

            $column_name = $good_list[$i]['product_name']." ( ".$good_list[$i]['color_name']." )";
            array_push($heads, $column_name);

            // DOI of Current Product 
            if ( $good_list[$i]['product_id'] != $good_list[$i+1]['product_id'] ) {

                $column_name = "Safty Stock - ".$good_list[$i]['product_name'];
                array_push($heads, $column_name);

                $column_name = "DOI - ".$good_list[$i]['product_name'];
                array_push($heads, $column_name);

            }

        }

        fputcsv($output, $heads);

        $QAsm = new Application_Model_Asm();

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->getBsStoreStockScan($params);

        // echo "<pre>"; print_r($result); 

        $no = 1;
        foreach ($result as $data) {

            $row = array();
            $row[] = $no++;
            $row[] = $data['st_id'];
            $row[] = $data['d_id'];
            $row[] = $data['d_name'];
            $row[] = $data['st_name'];
            $row[] = $data['area'];

            // RD 
            $result_rd = $QAsm->getStaffByAreaID($data['area_id'], 1);
            $cnt = count($result_rd);
            $rd_name = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { 
                        $rd_name = $rd_name.$result_rd[$j]['staff_name']." / "; 
                    } else { 
                        $rd_name = $rd_name.$result_rd[$j]['staff_name']; 
                    }
                }
            } else { $rd_name = "-"; }

            $row[] = $rd_name;
            $row[] = $data['st_type'];
            $row[] = $data['st_status'];
            $row[] = $data['room_type'];
            $row[] = number_format($data['shop_acreage'],2);
            $row[] = number_format($data['shop_version'],1);

            // echo "<pre>"; print_r($row); die;

            $cnt_stock = 0;
            $params_stock = array();
            for ($i=0;$i<count($good_list);$i++) {
                
                $params_stock['store_id']   = $data['st_id'];
                $params_stock['d_id']       = $data['d_id'];
                $params_stock['good_id']    = $good_list[$i]['product_id'];
                $params_stock['color_id']   = $good_list[$i]['color_id'];

                $result_stock = $QTiming->getBsStockScanByStoreID($params_stock);
                // $row[] = $result_stock['stock_scan']." [".$result_stock['sellin_scan']."/".$result_stock['sellout']."]";
                $row[] = $result_stock['stock_scan'];

                $cnt_stock += $result_stock['stock_scan'];

                // echo "<pre>"; print_r($result_stock); die;

                // DOI of Current Product 
                if ( $good_list[$i]['product_id'] != $good_list[$i+1]['product_id'] ) {

                    $safety_stock = $day_left = 0;
                    $params_stock['doi_rate'] = 28;
                    $result_sellout = $QTiming->getSelloutBsDOI($params_stock);

                    if ( !empty($result_sellout) ) {
                        $selling_rate = round($result_sellout['sellout'] / $params_stock['doi_rate'], 2);
                        $safety_stock = round($selling_rate * 14, 0);
                        $day_left = round($cnt_stock / $selling_rate, 2);

                        // $row[] = $result_sellout['sellout']." | ".$safety_stock;
                        $row[] = $safety_stock;
                        $row[] = $day_left;

                    } else {
                        $row[] = '';
                        $row[] = '';     
                    }

                    $cnt_stock = 0;
                }

            }

            fputcsv($output, $row);
        }

        exit;
    }

    // Export Report By Area : Export Excel
    private function _exportExcelArea($data) {

         $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_by = "[".$userStorage->code."] ".$userStorage->firstname." ".$userStorage->lastname;
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Grand Area',
            'Area',
            'Sellout',
            'Activated',
            'Activated [ 3, 7 ] Sale',
            'Activated [ 0, 7 ] PC',
            'Current Sale',
            'Current PC',
            'Created Report By '.$created_report_by." AT ".$created_report_at,
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QStoreStaff = new Application_Model_StoreStaff();

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            if ( in_array($data[$i]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data[$i]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data[$i]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data[$i]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data[$i]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data[$i]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data[$i]['area_name']; }

            $result_pc = array();
            $result_sale = array();

            $result_sale = $QStoreStaff->getSaleByArea($data[$i]['area_id']);
            $result_pc = $QStoreStaff->getPCByArea($data[$i]['area_id']);
            
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $grand_area);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activated']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activated_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activated_pc']);
            $sheet->setCellValue($alpha++.$index, count($result_sale));
            $sheet->setCellValue($alpha++.$index, count($result_pc));
            $index++;
        }
        
        $filename = 'Area_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report By Product : Export Excel
    private function _exportExcelProduct($data) {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Staff Code',
            'Reporter',
            'Staff Group',
            // 'Grand Area',
            'Area',
            // 'Province',
            'Sub_Area',
            'Store',
            'Product Code',
            'Product Name',
            'Color',
            'Sell Out',    
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            if ( in_array($data[$i]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data[$i]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data[$i]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data[$i]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data[$i]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data[$i]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data[$i]['area_name']; }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['reporter']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            // $sheet->setCellValue($alpha++.$index, $grand_area);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store']);
            //$sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_desc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total']);
            $index++;
        }
        
        $filename = 'Product_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
        
    }

    private function _exportExcel($data)
    {

        set_time_limit(0);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Name',
            'Title',
            'Province',
            'Area',
            'Phone Number',
            'Sales',
            );


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

        foreach ($data as $item)
        {
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $item['firstname'] . ' ' . $item['lastname']);
            $sheet->setCellValue($alpha++ . $index, $item['title']);
            $sheet->setCellValue($alpha++ . $index, $item['regional_market']);
            $sheet->setCellValue($alpha++ . $index, $item['area']);
            $sheet->getCell($alpha++ . $index)->setValueExplicit($item['phone_number'],
                PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++ . $index, !empty($item['total']) ? $item['total'] :
                '0');
            $index++;
        }

        $filename = 'TIMING_REPORT_' . date('d/m/Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    private function count_product_per_staff($staff_ids = array())
    {
        $db = Zend_Registry::get('db');

        $start = date('Y-m-01 00:00:00');
        $end = date('Y-m-' . date('t') . ' 23:59:59');

        $select = $db->select()->from(array('t' => 'timing'), array(
            't.staff_id',
            't.from',
            't.approved_at'))->join(array('s' => 'timing_sale'),
            's.timing_id = t.id AND t.approved_at IS NOT NULL', array('product_count' =>
                'COUNT(s.product_id)'))->group('t.staff_id');

        $select->where('t.from >= ?', $start);
        $select->where('t.from <= ?', $end);

        if (is_array($staff_ids) and $staff_ids)
            $select->where('staff_id IN (?)', $staff_ids);

        $result = $db->fetchAll($select);

        $staff = array();
        foreach ($result as $value)
        {
            $staff[$value['staff_id']] = $value['product_count'];
        }

        return $staff;
    }

    private function _exportExcel_timing_by_month($params)
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;
        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);

        $filename = 'Timing By Month - ' . date('d/m/Y H:i:s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $output = fopen('php://output', 'w');

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
            'Code',
            'Name',
            'Title',
            'Joined At',
            'Off Date'
            );

        for ($i = 1; $i <= 31; $i++)
        {
            $heads[] = $i;
        }

        $heads[] = 'Tổng ngày công';
        $heads[] = 'Ca gãy';

        fputcsv($output, $heads);

        $db = Zend_Registry::get('db');

        if (!in_array($group_id, array(HR_ID, ADMINISTRATOR_ID, BOARD_ID)))
        {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
            $ids = join(',',$list_regions);
            $where = "WHERE s.regional_market in ($ids) ORDER BY s.id";
        }

        $sql = "SELECT s.id, s.code, t.`from`, t.shift FROM timing t
                    INNER JOIN staff s
                    ON t.staff_id=s.id
                    AND t.`from` >= ?
                    AND t.`from` <= ?
                    AND t.approved_at IS NOT NULL
                    AND t.approved_at <> ''
                    AND t.approved_at <> 0
                    -- AND s.group_id IN (?, ?)
                    ORDER BY s.id
        ";

        $timings = $db->query($sql, array(
            $from,
            $to,
            PGPB_ID,
            SALES_ID));

        $timing_staffs = array();



        $sql = "SELECT
                    s.id,
                    s.`code`,
                    s.firstname,
                    s.lastname,
                    a.`name`,
                    s.off_date,
                    s.joined_at,
                    ta.`name` as title
                    FROM
                        timing t
                    INNER JOIN staff s ON t.staff_id = s.id
                    AND t.`from` >= ?
                    AND t.`from` <= ?
                    INNER JOIN regional_market r ON r.id=s.regional_market
                    INNER JOIN team ta ON s.title = ta.id
                    INNER JOIN area a ON a.id=r.area_id
                    GROUP BY s.`id`
                ORDER BY s.id";
        $staffs = $db->query($sql, array($from, $to));

        $staffs_all = array();

        foreach ($staffs as $key => $staff)
        {
            $staffs_all[$staff['id']] = array(
                "code"      => $staff['code'],
                "name"      => $staff['firstname'] . " " . $staff['lastname'],
                "area"      => $staff['name'],
                "off_date"  => $staff['off_date'],
                "joined_at" => $staff['joined_at'],
                "title"     => $staff['title']
            );
        }

        if ($timings)
        {
            foreach ($timings as $key => $timing)
            {
                if (!isset($timing_staffs[$timing['id']]))
                {
                    $timing_staffs[$timing['id']] = array();
                }

                $d = date('d', strtotime($timing['from']));
                $id = $timing['id'];
                $timing_staffs[$id][$d] = $timing['shift'];
            }

            foreach ($staffs_all as $id => $staff)
            {
                if (empty($staff['off_date'])
                    || ( isset($staff['off_date'])
                        and ( strtotime($staff['off_date']) >= strtotime($from))
                        )
                    )
                {

                    $row    = array();
                    $row[0] = $staff['area'];
                    $row[1] = $staff['code'];
                    $row[2] = $staff['name'];
                    $row[3] = $staff['title'];
                    $row[4] = $staff['joined_at'];
                    $row[5] = $staff['off_date'];
                    $d      = count($row);
                    $ca_gay = 0;
                    $num    = 0;

                    // lấy ngày nghỉ
                    $off_month = false; // kiêm tra tháng hiện nghỉ có trùng tháng lấy dữ liệu
                    if ($staff['off_date'] == null OR $staff['off_date'] == 0)
                    {
                        $off_day = 0;
                    } else
                    {
                        $off_day = date('d', strtotime($staff['off_date']));
                        if (isset($staff['off_date'])
                            AND ( strtotime($staff['off_date']) >= strtotime($from) AND strtotime($staff['off_date']) <= strtotime($to)))
                        {
                            $off_month = true;
                        }
                    }

                    $off_day = intval($off_day);

                    for ($i = 1; $i <= 31; $i++)
                    {
                        if ($i < 10)
                        {
                            $a = "0" . $i;
                        } else
                        {
                            $a = $i;
                        }

                        if (isset($timing_staffs[$id][$a]))
                        {

                            if ($off_day > 0 and $off_month == true)
                            {
                                // đã nghĩ và trong tháng cần lấy dữ liệu
                                if (intval($i) < $off_day)
                                {
                                    $row[$d + $i - 1] = 'X';
                                    $num++;
                                    // ca gãy
                                    if ($timing_staffs[$id][$a] == 2)
                                    {
                                        $ca_gay++;
                                    }
                                }

                            } else
                            {
                                $row[$d + $i - 1] = 'X';
                                $num++;

                                // ca gãy
                                if ($timing_staffs[$id][$a] == 2)
                                {
                                    $ca_gay++;
                                }
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
                    $row[] = $ca_gay;

                    ksort($row);

                    fputcsv($output, $row);
                }
            }
        }

        exit;
    }

    // private function _exportExcelExpired($data)
    // {
    //     set_time_limit();
    //     ini_set('memory_limit', -1);
    //     ini_set('display_error', 0);
    //     error_reporting(E_ALL);

    //     require_once 'PHPExcel.php';
    //     $PHPExcel = new PHPExcel();
    //     $heads = array(
    //         'STT',
    //         'Mã NV',
    //         'NV KV Phụ trách',
    //         'Ngày công',
    //         'Tên cửa hàng',
    //         'Tổng cộng',
    //         );

    //     $PHPExcel->setActiveSheetIndex(0);
    //     $sheet = $PHPExcel->getActiveSheet();

    //     $alpha = 'A';
    //     $index = 1;

    //     foreach ($heads as $key)
    //     {
    //         $sheet->setCellValue($alpha . $index, $key);
    //         $alpha++;
    //     }

    //     $index++;


    //     $filename = 'Sell out by PG - ' . date('d-m-Y H-i-s');
    //     $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    //     $objWriter->save('php://output');

    //     exit;
    // }

    private static function cmp($a, $b)
    {
        if ($a['point'] == $b['point'])
        {
            return 0;
        }
        return ($a['point'] < $b['point']) ? 1 : -1;
    }

    private function num2alpha($n)
    {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n % 26 + 0x41) . $r;
        return $r;
    }

    public function imeiActivatedAction(){

        $from = $this->getRequest()->getParam('from',date('01/m/Y'));
        $to = $this->getRequest()->getParam('to',date('d/m/Y'));
        $sort = $this->getRequest()->getParam('sort','good_desc');
        $desc  = $this->getRequest()->getParam('desc',1);
        $good_id = $this->getRequest()->getParam('good_id');
        $params = array(
            'from'    => $from,
            'to'      => $to,
            'good_id' => $good_id,
            'sort'    => $sort,
            'desc'    => $desc,

        );
        $this->view->params = $params;
        $db = Zend_Registry::get('db');

        $selectGood = $db->select()
            ->from(array('p'=>WAREHOUSE_DB.'.good'),array('id','desc'))
            ->where('p.cat_id = ?',11)
            ->order('desc')
            ;
        $goods = $db->fetchPairs($selectGood);
        $this->view->goods = $goods;

        if($from AND $to){
            $tmpFrom = explode('/', $from);
            $tmpFrom = $tmpFrom[2].'-'.$tmpFrom[1].'-'.$tmpFrom[0];
            $tmpTo   = explode('/', $to);
            $tmpTo   = $tmpTo[2].'-'.$tmpTo[1].'-'.$tmpTo[0].' 23:59:59';
            /*
            $cols = array(
                'total' => 'COUNT(DISTINCT imei_sn)'
            );
            $select = $db->select()
                ->from(array('p'=>WAREHOUSE_DB.'.imei'),$cols)
                ->where('p.activated_date >= ?',$tmpFrom)
                ->where('p.activated_date <= ?',$tmpTo)
                ;
            $total = $db->fetchOne($select);
            $this->view->total = $total;
            */
            $cols2 = array(
                    'total' => 'COUNT(DISTINCT p.imei_sn)',
                    'good_name' => 'g.name',
                    'good_desc' => 'g.desc'
                );
            $selectGoodActivated = $db->select()
                ->from(array('p'=>WAREHOUSE_DB.'.imei'),$cols2)
                ->join(array('g'=>WAREHOUSE_DB.'.good'),'g.id = p.good_id',array())
                ->where('p.activated_date >= ?',$tmpFrom)
                ->where('p.activated_date <= ?',$tmpTo)
                ->where('p.activated_date IS NOT NULL')
                ->where('p.activated_date <> 0')
                ->group('g.id')
                ;

            if($good_id) {
                $selectGoodActivated->where('g.id = ?',$good_id);
            }
            if($sort){
                    if($desc == 1){
                        $strOrder = $sort.' DESC';
                    }else{
                        $strOrder = $sort.' ASC';
                    }
                    $selectGoodActivated->order($strOrder);
            }

            $list = $db->fetchAll($selectGoodActivated);
            $total = 0;
            if($list){
                foreach($list as $key => $value ):
                    $total += $value['total'];
                endforeach;
            }

            $this->view->total = $total;
            $this->view->list = $list;
            $this->view->sort = $sort;
            $this->view->desc = $desc;
            $this->view->url = HOST . 'timing/imei-activated/' . ($params ? '?' .
            http_build_query($params) . '&' : '?');
        }

    }

    // Export OPPO Club - Store With Chain : Export Excel
    private function _exportExcelOPStoreAll($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Distributor Name',
            'Distributor Type',
            'Store Name',
            'Store Type',
            'Store Rank',
            'Area',
            'Province',
            'District'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->get_store_all_oppoclub($params);

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $d_type = "-";
            if ($data[$i]['d_rank'] == 7) { $d_type = "Dealer"; }
            if ($data[$i]['d_rank'] == 8) { $d_type = "HUB"; }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $d_type);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_rank']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);
            $index++;
        }
        
        $filename = 'OPPO_Club_Store_All_list_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export OPPO Club - Store By Timing : Export Excel
    private function _exportExcelOPStoreActive($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Distributor Name',
            'Distributor Type',
            'Store Name',
            'Store Type',
            'Store Rank',
            'Area',
            'Province',
            'District',    
            'Total Sellout',
            'Activated',
            'Total Activated'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->get_store_active_oppoclub($params);

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $d_type = "-";
            if ($data[$i]['d_rank'] == 7) { $d_type = "Dealer"; }
            if ($data[$i]['d_rank'] == 8) { $d_type = "HUB"; }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $d_type);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_rank']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['count']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['active']);
            $index++;
        }
        
        $filename = 'OPPO_Club_Store_Active_list_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export OPPO Club - Store By Timing [HUB] : Export Excel
    private function _exportExcelOPStoreHUBActive($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $season = "";

        $heads_row1 = array(
            'NO.',
            'Store ID',
            'Store Name',
            'Store Rank',
            'Area',
            'Province',
            'District',    
            'QUATER 1','','','','','','','','','','','','',
            'QUATER 2','','','','','','','','','','','','',
            'QUATER 3','','','','','','','','','','','','',
            'QUATER 4','','','','','','','','','','','','',
            'Summary Sellout 1-4 QUATER',
            'Summary Activated 1-4 QUATER',
            'Summary Total Activated 1-4 QUATER'
        );

        if (isset($params['season']) && $params['season'] == 2) {
            $season = "S2_";

            $heads_row2 = array(
                '','','','','','','',
                'Grade','March','',''   ,'April','',''      ,'May','',''        ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );

        } else {

            $heads_row2 = array(
                '','','','','','','',
                'Grade','April','',''   ,'May','',''        ,'June','',''       ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );
        }
        

        $heads_row3 = array(
            '','','','','','','','',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '','',''
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index  = 1;

        $merge_col = array('A','B','C','D','E','F','G','BH','BI','BJ');

        foreach($heads_row1 as $key) {
            $sheet->setCellValue($alpha.$index, $key);  
            $alpha++;
        }

        // reset value
        $alpha  = 'A';
        $index  = 1;
        $index2  = 2;
        $index3  = 3;

        foreach($heads_row2 as $key) {
            $sheet->setCellValue($alpha.$index2, $key);  
            if (in_array($alpha, $merge_col)) { $sheet->mergeCells($alpha.$index.":".$alpha.$index3); }
            $alpha++;
        }

        $alpha  = 'A';
        foreach($heads_row3 as $key) {
            $sheet->setCellValue($alpha.$index3, $key);  
            $alpha++;
        }

        // Merge Row 1 [Seperate Quater]
        $sheet->mergeCells('H1:T1');
        $sheet->mergeCells('U1:AG1');
        $sheet->mergeCells('AH1:AT1');
        $sheet->mergeCells('AU1:BG1');

        // Merge Row 2 [Month of Quater 1]
        $sheet->mergeCells('I2:K2'); 
        $sheet->mergeCells('L2:N2');
        $sheet->mergeCells('O2:Q2');
        $sheet->mergeCells('R2:T2');

        // Merge Row 2 [Month of Quater 2]
        $sheet->mergeCells('V2:X2'); 
        $sheet->mergeCells('Y2:AA2');
        $sheet->mergeCells('AB2:AD2');
        $sheet->mergeCells('AE2:AG2');

        // Merge Row 2 [Month of Quater 3]
        $sheet->mergeCells('AI2:AK2'); 
        $sheet->mergeCells('AL2:AN2');
        $sheet->mergeCells('AO2:AQ2');
        $sheet->mergeCells('AR2:AT2');

        // Merge Row 2 [Month of Quater 4]
        $sheet->mergeCells('AV2:AX2'); 
        $sheet->mergeCells('AY2:BA2');
        $sheet->mergeCells('BB2:BD2');
        $sheet->mergeCells('BE2:BG2');

        // Merge Coloumn Grade 
        $sheet->mergeCells('H2:H3');
        $sheet->mergeCells('U3:U3');
        $sheet->mergeCells('AH2:AH3'); 
        $sheet->mergeCells('AU2:AU3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:BJ3")->applyFromArray($style);
        $data = array();
        $total = 0;


        $QTiming = new Application_Model_Timing();
        $data = $QTiming->get_storeHUB_active_oppoclub($params);
        //print_r($data);die;

        $index = 4;
        for ($i=0;$i<count($data); $i++) {

            if (isset($params['season']) && $params['season'] == 2) {

                // Season 02 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_03_2018']) ? $data[$i]['QCnt_03_2018'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_04_2018']) ? $data[$i]['QCnt_04_2018'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_05_2018']) ? $data[$i]['QCnt_05_2018'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2018']) ? $data[$i]['QCnt_07_2018'] : "0";         
                $count_q2_02 = isset($data[$i]['QCnt_08_2018']) ? $data[$i]['QCnt_08_2018'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2018']) ? $data[$i]['QCnt_09_2018'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2018']) ? $data[$i]['QCnt_10_2018'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2018']) ? $data[$i]['QCnt_11_2018'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2018']) ? $data[$i]['QCnt_12_2018'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2019']) ? $data[$i]['QCnt_01_2019'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2019']) ? $data[$i]['QCnt_02_2019'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2019']) ? $data[$i]['QCnt_03_2019'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_03_2018']) ? $data[$i]['Q_03_2018'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_04_2018']) ? $data[$i]['Q_04_2018'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_05_2018']) ? $data[$i]['Q_05_2018'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2018']) ? $data[$i]['Q_07_2018'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2018']) ? $data[$i]['Q_08_2018'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2018']) ? $data[$i]['Q_09_2018'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2018']) ? $data[$i]['Q_10_2018'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2018']) ? $data[$i]['Q_11_2018'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2018']) ? $data[$i]['Q_12_2018'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2019']) ? $data[$i]['Q_01_2019'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2019']) ? $data[$i]['Q_02_2019'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2019']) ? $data[$i]['Q_03_2019'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_03_2018']) ? $data[$i]['QA_03_2018'] : "0";
                $active_q1_02 = isset($data[$i]['QA_04_2018']) ? $data[$i]['QA_04_2018'] : "0";
                $active_q1_03 = isset($data[$i]['QA_05_2018']) ? $data[$i]['QA_05_2018'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2018']) ? $data[$i]['QA_07_2018'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2018']) ? $data[$i]['QA_08_2018'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2018']) ? $data[$i]['QA_09_2018'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2018']) ? $data[$i]['QA_10_2018'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2018']) ? $data[$i]['QA_11_2018'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2018']) ? $data[$i]['QA_12_2018'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2019']) ? $data[$i]['QA_01_2019'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2019']) ? $data[$i]['QA_02_2019'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2019']) ? $data[$i]['QA_03_2019'] : "0";

            } else { 

                // Season 01 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_04_2016']) ? $data[$i]['QCnt_04_2016'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_05_2016']) ? $data[$i]['QCnt_05_2016'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_06_2016']) ? $data[$i]['QCnt_06_2016'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2016']) ? $data[$i]['QCnt_07_2016'] : "0";
                $count_q2_02 = isset($data[$i]['QCnt_08_2016']) ? $data[$i]['QCnt_08_2016'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2016']) ? $data[$i]['QCnt_09_2016'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2016']) ? $data[$i]['QCnt_10_2016'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2016']) ? $data[$i]['QCnt_11_2016'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2016']) ? $data[$i]['QCnt_12_2016'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2017']) ? $data[$i]['QCnt_01_2017'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2017']) ? $data[$i]['QCnt_02_2017'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2017']) ? $data[$i]['QCnt_03_2017'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_04_2016']) ? $data[$i]['Q_04_2016'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_05_2016']) ? $data[$i]['Q_05_2016'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_06_2016']) ? $data[$i]['Q_06_2016'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2016']) ? $data[$i]['Q_07_2016'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2016']) ? $data[$i]['Q_08_2016'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2016']) ? $data[$i]['Q_09_2016'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2016']) ? $data[$i]['Q_10_2016'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2016']) ? $data[$i]['Q_11_2016'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2016']) ? $data[$i]['Q_12_2016'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2017']) ? $data[$i]['Q_01_2017'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2017']) ? $data[$i]['Q_02_2017'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2017']) ? $data[$i]['Q_03_2017'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_04_2016']) ? $data[$i]['QA_04_2016'] : "0";
                $active_q1_02 = isset($data[$i]['QA_05_2016']) ? $data[$i]['QA_05_2016'] : "0";
                $active_q1_03 = isset($data[$i]['QA_06_2016']) ? $data[$i]['QA_06_2016'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2016']) ? $data[$i]['QA_07_2016'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2016']) ? $data[$i]['QA_08_2016'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2016']) ? $data[$i]['QA_09_2016'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2016']) ? $data[$i]['QA_10_2016'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2016']) ? $data[$i]['QA_11_2016'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2016']) ? $data[$i]['QA_12_2016'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2017']) ? $data[$i]['QA_01_2017'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2017']) ? $data[$i]['QA_02_2017'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2017']) ? $data[$i]['QA_03_2017'] : "0";

            }

            // Summary Sellout
            $total_count_q1 = $count_q1_01 + $count_q1_02 + $count_q1_03;
            $total_count_q2 = $count_q2_01 + $count_q2_02 + $count_q2_03;
            $total_count_q3 = $count_q3_01 + $count_q3_02 + $count_q3_03;
            $total_count_q4 = $count_q4_01 + $count_q4_02 + $count_q4_03;

            $sum_total_count = $total_count_q1 + $total_count_q2 + $total_count_q3 + $total_count_q4;

            $total_sellout_q1 = $sellout_q1_01 + $sellout_q1_02 + $sellout_q1_03;
            $total_sellout_q2 = $sellout_q2_01 + $sellout_q2_02 + $sellout_q2_03;
            $total_sellout_q3 = $sellout_q3_01 + $sellout_q3_02 + $sellout_q3_03;
            $total_sellout_q4 = $sellout_q4_01 + $sellout_q4_02 + $sellout_q4_03;

            $sum_total_sellout = $total_sellout_q1 + $total_sellout_q2 + $total_sellout_q3 + $total_sellout_q4;

            $total_activate_q1 = $active_q1_01 + $active_q1_02 + $active_q1_03;
            $total_activate_q2 = $active_q2_01 + $active_q2_02 + $active_q2_03;
            $total_activate_q3 = $active_q3_01 + $active_q3_02 + $active_q3_03;
            $total_activate_q4 = $active_q4_01 + $active_q4_02 + $active_q4_03;

            $sum_total_activate = $total_activate_q1 + $total_activate_q2 + $total_activate_q3 + $total_activate_q4;

            // Calculate Grade for Store [HUB]
            $d1_level = "-";
            $d2_level = "-";
            $d3_level = "-";
            $d4_level = "-";

            if ( $count_q1_01 != 0 && $count_q1_02 != 0 && $count_q1_03 != 0 ) {

                if ( $count_q1_01 >= 50 && $count_q1_02 >= 50 && $count_q1_03 >= 50 ) { $d1_level = "Silver"; } 
                if ( $count_q1_01 >= 100 && $count_q1_02 >= 100 && $count_q1_03 >= 100 ) { $d1_level = "Gold"; } 
                if ( $count_q1_01 >= 200 && $count_q1_02 >= 200 && $count_q1_03 >= 200 ) { $d1_level = "Platinum"; } 
                
            } 

            if ( $count_q2_01 != 0 && $count_q2_02 != 0 && $count_q2_03 != 0 ) {

                if ( $count_q2_01 >= 50 && $count_q2_02 >= 50 && $count_q2_03 >= 50 ) { $d2_level = "Silver"; } 
                if ( $count_q2_01 >= 100 && $count_q2_02 >= 100 && $count_q2_03 >= 100 ) { $d2_level = "Gold"; } 
                if ( $count_q2_01 >= 200 && $count_q2_02 >= 200 && $count_q2_03 >= 200 ) { $d2_level = "Platinum"; } 

            } 

            if ( $count_q3_01 != 0 && $count_q3_02 != 0 && $count_q3_03 != 0 ) {

                if ( $count_q3_01 >= 50 && $count_q3_02 >= 50 && $count_q3_03 >= 50 ) { $d3_level = "Silver"; } 
                if ( $count_q3_01 >= 100 && $count_q3_02 >= 100 && $count_q3_03 >= 100 ) { $d3_level = "Gold"; } 
                if ( $count_q3_01 >= 200 && $count_q3_02 >= 200 && $count_q3_03 >= 200 ) { $d3_level = "Platinum"; } 
                
            } 

            if ( $count_q4_01 != 0 && $count_q4_02 != 0 && $count_q4_03 != 0 ) {

                if ( $count_q4_01 >= 50 && $count_q4_02 >= 50 && $count_q4_03 >= 50 ) { $d4_level = "Silver"; }
                if ( $count_q4_01 >= 100 && $count_q4_02 >= 100 && $count_q4_03 >= 100 ) { $d4_level = "Gold"; } 
                if ( $count_q4_01 >= 200 && $count_q4_02 >= 200 && $count_q4_03 >= 200 ) { $d4_level = "Platinum"; } 

            } 

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_rank']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);

            // Quater 1
            $sheet->setCellValue($alpha++.$index, $d1_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_01);
            $sheet->setCellValue($alpha++.$index, $count_q1_01);
            $sheet->setCellValue($alpha++.$index, $active_q1_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_02);
            $sheet->setCellValue($alpha++.$index, $count_q1_02);
            $sheet->setCellValue($alpha++.$index, $active_q1_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_03);
            $sheet->setCellValue($alpha++.$index, $count_q1_03);
            $sheet->setCellValue($alpha++.$index, $active_q1_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q1);
            $sheet->setCellValue($alpha++.$index, $total_count_q1);
            $sheet->setCellValue($alpha++.$index, $total_activate_q1);

            // Quater 2
            $sheet->setCellValue($alpha++.$index, $d2_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_01);
            $sheet->setCellValue($alpha++.$index, $count_q2_01);
            $sheet->setCellValue($alpha++.$index, $active_q2_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_02);
            $sheet->setCellValue($alpha++.$index, $count_q2_02);
            $sheet->setCellValue($alpha++.$index, $active_q2_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_03);
            $sheet->setCellValue($alpha++.$index, $count_q2_03);
            $sheet->setCellValue($alpha++.$index, $active_q2_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q2);
            $sheet->setCellValue($alpha++.$index, $total_count_q2);
            $sheet->setCellValue($alpha++.$index, $total_activate_q2);

            // Quater 3
            $sheet->setCellValue($alpha++.$index, $d3_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_01);
            $sheet->setCellValue($alpha++.$index, $count_q3_01);
            $sheet->setCellValue($alpha++.$index, $active_q3_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_02);
            $sheet->setCellValue($alpha++.$index, $count_q3_02);
            $sheet->setCellValue($alpha++.$index, $active_q3_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_03);
            $sheet->setCellValue($alpha++.$index, $count_q3_03);
            $sheet->setCellValue($alpha++.$index, $active_q3_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q3);
            $sheet->setCellValue($alpha++.$index, $total_count_q3);
            $sheet->setCellValue($alpha++.$index, $total_activate_q3);

            // Quater 4
            $sheet->setCellValue($alpha++.$index, $d4_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_01);
            $sheet->setCellValue($alpha++.$index, $count_q4_01);
            $sheet->setCellValue($alpha++.$index, $active_q4_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_02);
            $sheet->setCellValue($alpha++.$index, $count_q4_02);
            $sheet->setCellValue($alpha++.$index, $active_q4_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_03);
            $sheet->setCellValue($alpha++.$index, $count_q4_03);
            $sheet->setCellValue($alpha++.$index, $active_q4_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q4);
            $sheet->setCellValue($alpha++.$index, $total_count_q4);
            $sheet->setCellValue($alpha++.$index, $total_activate_q4);

            $sheet->setCellValue($alpha++.$index, $sum_total_sellout);
            $sheet->setCellValue($alpha++.$index, $sum_total_count);
            $sheet->setCellValue($alpha++.$index, $sum_total_activate);

            $index++;
        }
        
        $filename = 'OPPO_Club_StoreHUB_Active_list_'.$season.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export OPPO Club - Export Excel By Dealer
    private function _exportExcelOPPOClub($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $season = "";

        $heads_row1 = array(
            'NO.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Type',
            'Area',
            'Province',
            'District',    
            'Stores',
            'Stores Active',
            'QUATER 1','','','','','','','','','','','','',
            'QUATER 2','','','','','','','','','','','','',
            'QUATER 3','','','','','','','','','','','','',
            'QUATER 4','','','','','','','','','','','','',
            'Summary Sellout 1-4 QUATER',
            'Summary Activated 1-4 QUATER',
            'Summary Total Activated 1-4 QUATER',
        );

        if (isset($params['season']) && $params['season'] == 2) {
            $season = "S2_";

            // Season 02 
            $heads_row2 = array(
                '','','','','','','','','',
                'Grade','March','',''   ,'April','',''      ,'May','',''        ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );
        } else {
            // Season 01
            $heads_row2 = array(
                '','','','','','','','','',
                'Grade','April','',''   ,'May','',''        ,'June','',''       ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );
        }
        

        $heads_row3 = array(
            '','','','','','','','','',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '','',''
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index  = 1;

        $merge_col = array('A','B','C','D','E','F','G','H','I','BJ','BK','BL');

        foreach($heads_row1 as $key) {
            $sheet->setCellValue($alpha.$index, $key);  
            $alpha++;
        }

        // reset value
        $alpha  = 'A';
        $index  = 1;
        $index2  = 2;
        $index3  = 3;

        foreach($heads_row2 as $key) {
            $sheet->setCellValue($alpha.$index2, $key);  
            if (in_array($alpha, $merge_col)) { $sheet->mergeCells($alpha.$index.":".$alpha.$index3); }
            $alpha++;
        }

        $alpha  = 'A';
        foreach($heads_row3 as $key) {
            $sheet->setCellValue($alpha.$index3, $key);  
            $alpha++;
        }

        // Merge Row 1 [Seperate Quater]
        $sheet->mergeCells('J1:V1');
        $sheet->mergeCells('W1:AI1');
        $sheet->mergeCells('AJ1:AV1');
        $sheet->mergeCells('AW1:BI1');

        // Merge Row 2 [Month of Quater 1]
        $sheet->mergeCells('K2:M2'); 
        $sheet->mergeCells('N2:P2');
        $sheet->mergeCells('Q2:S2');
        $sheet->mergeCells('T2:V2');

        // Merge Row 2 [Month of Quater 2]
        $sheet->mergeCells('X2:Z2'); 
        $sheet->mergeCells('AA2:AC2');
        $sheet->mergeCells('AD2:AF2');
        $sheet->mergeCells('AG2:AI2');

        // Merge Row 2 [Month of Quater 3]
        $sheet->mergeCells('AK2:AM2'); 
        $sheet->mergeCells('AN2:AP2');
        $sheet->mergeCells('AQ2:AS2');
        $sheet->mergeCells('AT2:AV2');

        // Merge Row 2 [Month of Quater 4]
        $sheet->mergeCells('AX2:AZ2'); 
        $sheet->mergeCells('BA2:BC2');
        $sheet->mergeCells('BD2:BF2');
        $sheet->mergeCells('BG2:BI2');

        // Merge Coloumn Grade 
        $sheet->mergeCells('J2:J3');
        $sheet->mergeCells('W3:W3');
        $sheet->mergeCells('AJ2:AJ3'); 
        $sheet->mergeCells('AW2:AW3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:BL3")->applyFromArray($style);
        //$sheet->getDefaultStyle()->applyFromArray($style);
        $data = array();
        $total = 0;

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->report_by_dealer_oppoclub(null,null,$total,$params);
        //print_r($data);die;

        $index = 4;
        for ($i=0;$i<count($data); $i++) {

            $d_type = "-";
            if ($data[$i]['d_rank'] == 7) { $d_type = "Dealer"; }
            if ($data[$i]['d_rank'] == 8) { $d_type = "HUB"; }

            if (isset($params['season']) && $params['season'] == 2) {

                // Season 02 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_03_2018']) ? $data[$i]['QCnt_03_2018'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_04_2018']) ? $data[$i]['QCnt_04_2018'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_05_2018']) ? $data[$i]['QCnt_05_2018'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2018']) ? $data[$i]['QCnt_07_2018'] : "0";         
                $count_q2_02 = isset($data[$i]['QCnt_08_2018']) ? $data[$i]['QCnt_08_2018'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2018']) ? $data[$i]['QCnt_09_2018'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2018']) ? $data[$i]['QCnt_10_2018'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2018']) ? $data[$i]['QCnt_11_2018'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2018']) ? $data[$i]['QCnt_12_2018'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2019']) ? $data[$i]['QCnt_01_2019'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2019']) ? $data[$i]['QCnt_02_2019'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2019']) ? $data[$i]['QCnt_03_2019'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_03_2018']) ? $data[$i]['Q_03_2018'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_04_2018']) ? $data[$i]['Q_04_2018'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_05_2018']) ? $data[$i]['Q_05_2018'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2018']) ? $data[$i]['Q_07_2018'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2018']) ? $data[$i]['Q_08_2018'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2018']) ? $data[$i]['Q_09_2018'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2018']) ? $data[$i]['Q_10_2018'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2018']) ? $data[$i]['Q_11_2018'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2018']) ? $data[$i]['Q_12_2018'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2019']) ? $data[$i]['Q_01_2019'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2019']) ? $data[$i]['Q_02_2019'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2019']) ? $data[$i]['Q_03_2019'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_03_2018']) ? $data[$i]['QA_03_2018'] : "0";
                $active_q1_02 = isset($data[$i]['QA_04_2018']) ? $data[$i]['QA_04_2018'] : "0";
                $active_q1_03 = isset($data[$i]['QA_05_2018']) ? $data[$i]['QA_05_2018'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2018']) ? $data[$i]['QA_07_2018'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2018']) ? $data[$i]['QA_08_2018'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2018']) ? $data[$i]['QA_09_2018'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2018']) ? $data[$i]['QA_10_2018'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2018']) ? $data[$i]['QA_11_2018'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2018']) ? $data[$i]['QA_12_2018'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2019']) ? $data[$i]['QA_01_2019'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2019']) ? $data[$i]['QA_02_2019'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2019']) ? $data[$i]['QA_03_2019'] : "0";

            } else { 

                // Season 01 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_04_2016']) ? $data[$i]['QCnt_04_2016'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_05_2016']) ? $data[$i]['QCnt_05_2016'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_06_2016']) ? $data[$i]['QCnt_06_2016'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2016']) ? $data[$i]['QCnt_07_2016'] : "0";
                $count_q2_02 = isset($data[$i]['QCnt_08_2016']) ? $data[$i]['QCnt_08_2016'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2016']) ? $data[$i]['QCnt_09_2016'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2016']) ? $data[$i]['QCnt_10_2016'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2016']) ? $data[$i]['QCnt_11_2016'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2016']) ? $data[$i]['QCnt_12_2016'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2017']) ? $data[$i]['QCnt_01_2017'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2017']) ? $data[$i]['QCnt_02_2017'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2017']) ? $data[$i]['QCnt_03_2017'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_04_2016']) ? $data[$i]['Q_04_2016'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_05_2016']) ? $data[$i]['Q_05_2016'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_06_2016']) ? $data[$i]['Q_06_2016'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2016']) ? $data[$i]['Q_07_2016'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2016']) ? $data[$i]['Q_08_2016'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2016']) ? $data[$i]['Q_09_2016'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2016']) ? $data[$i]['Q_10_2016'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2016']) ? $data[$i]['Q_11_2016'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2016']) ? $data[$i]['Q_12_2016'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2017']) ? $data[$i]['Q_01_2017'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2017']) ? $data[$i]['Q_02_2017'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2017']) ? $data[$i]['Q_03_2017'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_04_2016']) ? $data[$i]['QA_04_2016'] : "0";
                $active_q1_02 = isset($data[$i]['QA_05_2016']) ? $data[$i]['QA_05_2016'] : "0";
                $active_q1_03 = isset($data[$i]['QA_06_2016']) ? $data[$i]['QA_06_2016'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2016']) ? $data[$i]['QA_07_2016'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2016']) ? $data[$i]['QA_08_2016'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2016']) ? $data[$i]['QA_09_2016'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2016']) ? $data[$i]['QA_10_2016'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2016']) ? $data[$i]['QA_11_2016'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2016']) ? $data[$i]['QA_12_2016'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2017']) ? $data[$i]['QA_01_2017'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2017']) ? $data[$i]['QA_02_2017'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2017']) ? $data[$i]['QA_03_2017'] : "0";

            }

            // Summary Sellout
            $total_count_q1 = $count_q1_01 + $count_q1_02 + $count_q1_03;
            $total_count_q2 = $count_q2_01 + $count_q2_02 + $count_q2_03;
            $total_count_q3 = $count_q3_01 + $count_q3_02 + $count_q3_03;
            $total_count_q4 = $count_q4_01 + $count_q4_02 + $count_q4_03;

            $sum_total_count = $total_count_q1 + $total_count_q2 + $total_count_q3 + $total_count_q4;

            $total_sellout_q1 = $sellout_q1_01 + $sellout_q1_02 + $sellout_q1_03;
            $total_sellout_q2 = $sellout_q2_01 + $sellout_q2_02 + $sellout_q2_03;
            $total_sellout_q3 = $sellout_q3_01 + $sellout_q3_02 + $sellout_q3_03;
            $total_sellout_q4 = $sellout_q4_01 + $sellout_q4_02 + $sellout_q4_03;

            $sum_total_sellout = $total_sellout_q1 + $total_sellout_q2 + $total_sellout_q3 + $total_sellout_q4;

            $total_activate_q1 = $active_q1_01 + $active_q1_02 + $active_q1_03;
            $total_activate_q2 = $active_q2_01 + $active_q2_02 + $active_q2_03;
            $total_activate_q3 = $active_q3_01 + $active_q3_02 + $active_q3_03;
            $total_activate_q4 = $active_q4_01 + $active_q4_02 + $active_q4_03;

            $sum_total_activate = $total_activate_q1 + $total_activate_q2 + $total_activate_q3 + $total_activate_q4;

            if ($data[$i]['d1_level'] == '' || is_null($data[$i]['d1_level']) ) { $d1_level = "-"; }
            else { $d1_level = $data[$i]['d1_level']; }
            if ($data[$i]['d2_level'] == '' || is_null($data[$i]['d2_level']) ) { $d2_level = "-"; }
            else { $d2_level = $data[$i]['d2_level']; }
            if ($data[$i]['d3_level'] == '' || is_null($data[$i]['d3_level']) ) { $d3_level = "-"; }
            else { $d3_level = $data[$i]['d3_level']; }
            if ($data[$i]['d4_level'] == '' || is_null($data[$i]['d4_level']) ) { $d4_level = "-"; }
            else { $d4_level = $data[$i]['d4_level']; }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $d_type);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_active']);

            // Quater 1
            $sheet->setCellValue($alpha++.$index, $d1_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_01);
            $sheet->setCellValue($alpha++.$index, $count_q1_01);
            $sheet->setCellValue($alpha++.$index, $active_q1_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_02);
            $sheet->setCellValue($alpha++.$index, $count_q1_02);
            $sheet->setCellValue($alpha++.$index, $active_q1_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_03);
            $sheet->setCellValue($alpha++.$index, $count_q1_03);
            $sheet->setCellValue($alpha++.$index, $active_q1_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q1);
            $sheet->setCellValue($alpha++.$index, $total_count_q1);
            $sheet->setCellValue($alpha++.$index, $total_activate_q1);

            // Quater 2
            $sheet->setCellValue($alpha++.$index, $d2_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_01);
            $sheet->setCellValue($alpha++.$index, $count_q2_01);
            $sheet->setCellValue($alpha++.$index, $active_q2_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_02);
            $sheet->setCellValue($alpha++.$index, $count_q2_02);
            $sheet->setCellValue($alpha++.$index, $active_q2_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_03);
            $sheet->setCellValue($alpha++.$index, $count_q2_03);
            $sheet->setCellValue($alpha++.$index, $active_q2_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q2);
            $sheet->setCellValue($alpha++.$index, $total_count_q2);
            $sheet->setCellValue($alpha++.$index, $total_activate_q2);

            // Quater 3
            $sheet->setCellValue($alpha++.$index, $d3_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_01);
            $sheet->setCellValue($alpha++.$index, $count_q3_01);
            $sheet->setCellValue($alpha++.$index, $active_q3_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_02);
            $sheet->setCellValue($alpha++.$index, $count_q3_02);
            $sheet->setCellValue($alpha++.$index, $active_q3_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_03);
            $sheet->setCellValue($alpha++.$index, $count_q3_03);
            $sheet->setCellValue($alpha++.$index, $active_q3_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q3);
            $sheet->setCellValue($alpha++.$index, $total_count_q3);
            $sheet->setCellValue($alpha++.$index, $total_activate_q3);

            // Quater 4
            $sheet->setCellValue($alpha++.$index, $d4_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_01);
            $sheet->setCellValue($alpha++.$index, $count_q4_01);
            $sheet->setCellValue($alpha++.$index, $active_q4_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_02);
            $sheet->setCellValue($alpha++.$index, $count_q4_02);
            $sheet->setCellValue($alpha++.$index, $active_q4_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_03);
            $sheet->setCellValue($alpha++.$index, $count_q4_03);
            $sheet->setCellValue($alpha++.$index, $active_q4_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q4);
            $sheet->setCellValue($alpha++.$index, $total_count_q4);
            $sheet->setCellValue($alpha++.$index, $total_activate_q4);

            $sheet->setCellValue($alpha++.$index, $sum_total_sellout);
            $sheet->setCellValue($alpha++.$index, $sum_total_count);
            $sheet->setCellValue($alpha++.$index, $sum_total_activate);

            $index++;
        }
        
        $filename = 'OPPO_Club_'.$season.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export OPPO Club - Export Excel By Store
    private function _exportExcelOPPOClubByStore($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $season = "";

        $heads_row1 = array(
            'NO.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Type',
            'Distributor Area',
            'Distributor Province',
            'Distributor District',
            'Store ID',
            'Store Name',
            'Store Rank',
            'Store Type', 
            'Store Area',
            'Store Province',
            'Store District',    
            'QUATER 1','','','','','','','','','','','','',
            'QUATER 2','','','','','','','','','','','','',
            'QUATER 3','','','','','','','','','','','','',
            'QUATER 4','','','','','','','','','','','','',
            'Summary Sellout 1-4 QUATER',
            'Summary Activated 1-4 QUATER',
            'Summary Total Activated 1-4 QUATER', 
        );

        if (isset($params['season']) && $params['season'] == 2) {
            $season = "S2_";

            // Season 02
            $heads_row2 = array(
                '','','','','','','','','','','','','','',
                'Grade','March','',''   ,'April','',''      ,'May','',''        ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );

        } else {

            // Season 01
            $heads_row2 = array(
                '','','','','','','','','','','','','','',
                'Grade','April','',''   ,'May','',''        ,'June','',''       ,'Summary','','',
                'Grade','July','',''    ,'August','',''     ,'September','',''  ,'Summary','','',
                'Grade','October','','' ,'November','',''   ,'December','',''   ,'Summary','','',
                'Grade','January','','' ,'Febuary','',''    ,'March','',''      ,'Summary','','',
                '','',''
            );
        }

        $heads_row3 = array(
            '','','','','','','','','','','','','','','',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            'Sellout','Activated','Total Activated',
            '','',''
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index  = 1;

        $merge_col = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','BO','BP','BQ');

        foreach($heads_row1 as $key) {
            $sheet->setCellValue($alpha.$index, $key);  
            $alpha++;
        }

        // reset value
        $alpha  = 'A';
        $index  = 1;
        $index2  = 2;
        $index3  = 3;

        foreach($heads_row2 as $key) {
            $sheet->setCellValue($alpha.$index2, $key);  
            if (in_array($alpha, $merge_col)) { $sheet->mergeCells($alpha.$index.":".$alpha.$index3); }
            $alpha++;
        }

        $alpha  = 'A';
        foreach($heads_row3 as $key) {
            $sheet->setCellValue($alpha.$index3, $key);  
            $alpha++;
        }

        // Merge Row 1 [Seperate Quater]
        $sheet->mergeCells('O1:AA1');
        $sheet->mergeCells('AB1:AN1');
        $sheet->mergeCells('AO1:BA1');
        $sheet->mergeCells('BB1:BN1');

        // Merge Row 2 [Month of Quater 1]
        $sheet->mergeCells('P2:R2'); 
        $sheet->mergeCells('S2:U2');
        $sheet->mergeCells('V2:X2');
        $sheet->mergeCells('Y2:AA2');

        // Merge Row 2 [Month of Quater 2]
        $sheet->mergeCells('AC2:AE2'); 
        $sheet->mergeCells('AF2:AH2');
        $sheet->mergeCells('AI2:AK2');
        $sheet->mergeCells('AL2:AN2');

        // Merge Row 2 [Month of Quater 3]
        $sheet->mergeCells('AP2:AR2'); 
        $sheet->mergeCells('AS2:AU2');
        $sheet->mergeCells('AV2:AX2');
        $sheet->mergeCells('AY2:BA2');

        // Merge Row 2 [Month of Quater 4]
        $sheet->mergeCells('BC2:BE2'); 
        $sheet->mergeCells('BF2:BH2');
        $sheet->mergeCells('BI2:BK2');
        $sheet->mergeCells('BL2:BN2');

        // Merge Coloumn Grade 
        $sheet->mergeCells('O2:O3');
        $sheet->mergeCells('AB2:AB3');
        $sheet->mergeCells('AO2:AO3'); 
        $sheet->mergeCells('BB2:BB3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:BQ3")->applyFromArray($style);
        //$sheet->getDefaultStyle()->applyFromArray($style);
        $data = array();
        $total = 0;


        $QTiming = new Application_Model_Timing();
        $data = $QTiming->report_by_dealer_oppoclub_by_store($params);
        //print_r($data);die;

        $index = 4;
        for ($i=0;$i<count($data); $i++) {

            $d_type = "-";
            if ($data[$i]['d_rank'] == 7) { $d_type = "Dealer"; }
            if ($data[$i]['d_rank'] == 8) { $d_type = "HUB"; }

            if (isset($params['season']) && $params['season'] == 2) {

                // Season 02 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_03_2018']) ? $data[$i]['QCnt_03_2018'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_04_2018']) ? $data[$i]['QCnt_04_2018'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_05_2018']) ? $data[$i]['QCnt_05_2018'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2018']) ? $data[$i]['QCnt_07_2018'] : "0";         
                $count_q2_02 = isset($data[$i]['QCnt_08_2018']) ? $data[$i]['QCnt_08_2018'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2018']) ? $data[$i]['QCnt_09_2018'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2018']) ? $data[$i]['QCnt_10_2018'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2018']) ? $data[$i]['QCnt_11_2018'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2018']) ? $data[$i]['QCnt_12_2018'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2019']) ? $data[$i]['QCnt_01_2019'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2019']) ? $data[$i]['QCnt_02_2019'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2019']) ? $data[$i]['QCnt_03_2019'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_03_2018']) ? $data[$i]['Q_03_2018'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_04_2018']) ? $data[$i]['Q_04_2018'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_05_2018']) ? $data[$i]['Q_05_2018'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2018']) ? $data[$i]['Q_07_2018'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2018']) ? $data[$i]['Q_08_2018'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2018']) ? $data[$i]['Q_09_2018'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2018']) ? $data[$i]['Q_10_2018'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2018']) ? $data[$i]['Q_11_2018'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2018']) ? $data[$i]['Q_12_2018'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2019']) ? $data[$i]['Q_01_2019'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2019']) ? $data[$i]['Q_02_2019'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2019']) ? $data[$i]['Q_03_2019'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_03_2018']) ? $data[$i]['QA_03_2018'] : "0";
                $active_q1_02 = isset($data[$i]['QA_04_2018']) ? $data[$i]['QA_04_2018'] : "0";
                $active_q1_03 = isset($data[$i]['QA_05_2018']) ? $data[$i]['QA_05_2018'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2018']) ? $data[$i]['QA_07_2018'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2018']) ? $data[$i]['QA_08_2018'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2018']) ? $data[$i]['QA_09_2018'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2018']) ? $data[$i]['QA_10_2018'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2018']) ? $data[$i]['QA_11_2018'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2018']) ? $data[$i]['QA_12_2018'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2019']) ? $data[$i]['QA_01_2019'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2019']) ? $data[$i]['QA_02_2019'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2019']) ? $data[$i]['QA_03_2019'] : "0";

            } else { 

                // Season 01 
                // Sellout count Activated before 3 after 7 
                $count_q1_01 = isset($data[$i]['QCnt_04_2016']) ? $data[$i]['QCnt_04_2016'] : "0";
                $count_q1_02 = isset($data[$i]['QCnt_05_2016']) ? $data[$i]['QCnt_05_2016'] : "0";
                $count_q1_03 = isset($data[$i]['QCnt_06_2016']) ? $data[$i]['QCnt_06_2016'] : "0";

                $count_q2_01 = isset($data[$i]['QCnt_07_2016']) ? $data[$i]['QCnt_07_2016'] : "0";
                $count_q2_02 = isset($data[$i]['QCnt_08_2016']) ? $data[$i]['QCnt_08_2016'] : "0";
                $count_q2_03 = isset($data[$i]['QCnt_09_2016']) ? $data[$i]['QCnt_09_2016'] : "0";

                $count_q3_01 = isset($data[$i]['QCnt_10_2016']) ? $data[$i]['QCnt_10_2016'] : "0";
                $count_q3_02 = isset($data[$i]['QCnt_11_2016']) ? $data[$i]['QCnt_11_2016'] : "0";
                $count_q3_03 = isset($data[$i]['QCnt_12_2016']) ? $data[$i]['QCnt_12_2016'] : "0";

                $count_q4_01 = isset($data[$i]['QCnt_01_2017']) ? $data[$i]['QCnt_01_2017'] : "0";
                $count_q4_02 = isset($data[$i]['QCnt_02_2017']) ? $data[$i]['QCnt_02_2017'] : "0";
                $count_q4_03 = isset($data[$i]['QCnt_03_2017']) ? $data[$i]['QCnt_03_2017'] : "0";

                // Sellout All
                $sellout_q1_01 = isset($data[$i]['Q_04_2016']) ? $data[$i]['Q_04_2016'] : "0";
                $sellout_q1_02 = isset($data[$i]['Q_05_2016']) ? $data[$i]['Q_05_2016'] : "0";
                $sellout_q1_03 = isset($data[$i]['Q_06_2016']) ? $data[$i]['Q_06_2016'] : "0";

                $sellout_q2_01 = isset($data[$i]['Q_07_2016']) ? $data[$i]['Q_07_2016'] : "0";
                $sellout_q2_02 = isset($data[$i]['Q_08_2016']) ? $data[$i]['Q_08_2016'] : "0";
                $sellout_q2_03 = isset($data[$i]['Q_09_2016']) ? $data[$i]['Q_09_2016'] : "0";

                $sellout_q3_01 = isset($data[$i]['Q_10_2016']) ? $data[$i]['Q_10_2016'] : "0";
                $sellout_q3_02 = isset($data[$i]['Q_11_2016']) ? $data[$i]['Q_11_2016'] : "0";
                $sellout_q3_03 = isset($data[$i]['Q_12_2016']) ? $data[$i]['Q_12_2016'] : "0";

                $sellout_q4_01 = isset($data[$i]['Q_01_2017']) ? $data[$i]['Q_01_2017'] : "0";
                $sellout_q4_02 = isset($data[$i]['Q_02_2017']) ? $data[$i]['Q_02_2017'] : "0";
                $sellout_q4_03 = isset($data[$i]['Q_03_2017']) ? $data[$i]['Q_03_2017'] : "0";

                // Activated 
                $active_q1_01 = isset($data[$i]['QA_04_2016']) ? $data[$i]['QA_04_2016'] : "0";
                $active_q1_02 = isset($data[$i]['QA_05_2016']) ? $data[$i]['QA_05_2016'] : "0";
                $active_q1_03 = isset($data[$i]['QA_06_2016']) ? $data[$i]['QA_06_2016'] : "0";

                $active_q2_01 = isset($data[$i]['QA_07_2016']) ? $data[$i]['QA_07_2016'] : "0";
                $active_q2_02 = isset($data[$i]['QA_08_2016']) ? $data[$i]['QA_08_2016'] : "0";
                $active_q2_03 = isset($data[$i]['QA_09_2016']) ? $data[$i]['QA_09_2016'] : "0";

                $active_q3_01 = isset($data[$i]['QA_10_2016']) ? $data[$i]['QA_10_2016'] : "0";
                $active_q3_02 = isset($data[$i]['QA_11_2016']) ? $data[$i]['QA_11_2016'] : "0";
                $active_q3_03 = isset($data[$i]['QA_12_2016']) ? $data[$i]['QA_12_2016'] : "0";

                $active_q4_01 = isset($data[$i]['QA_01_2017']) ? $data[$i]['QA_01_2017'] : "0";
                $active_q4_02 = isset($data[$i]['QA_02_2017']) ? $data[$i]['QA_02_2017'] : "0";
                $active_q4_03 = isset($data[$i]['QA_03_2017']) ? $data[$i]['QA_03_2017'] : "0";

            }

            // Summary Sellout
            $total_count_q1 = $count_q1_01 + $count_q1_02 + $count_q1_03;
            $total_count_q2 = $count_q2_01 + $count_q2_02 + $count_q2_03;
            $total_count_q3 = $count_q3_01 + $count_q3_02 + $count_q3_03;
            $total_count_q4 = $count_q4_01 + $count_q4_02 + $count_q4_03;

            $sum_total_count = $total_count_q1 + $total_count_q2 + $total_count_q3 + $total_count_q4;

            $total_sellout_q1 = $sellout_q1_01 + $sellout_q1_02 + $sellout_q1_03;
            $total_sellout_q2 = $sellout_q2_01 + $sellout_q2_02 + $sellout_q2_03;
            $total_sellout_q3 = $sellout_q3_01 + $sellout_q3_02 + $sellout_q3_03;
            $total_sellout_q4 = $sellout_q4_01 + $sellout_q4_02 + $sellout_q4_03;

            $sum_total_sellout = $total_sellout_q1 + $total_sellout_q2 + $total_sellout_q3 + $total_sellout_q4;

            $total_activate_q1 = $active_q1_01 + $active_q1_02 + $active_q1_03;
            $total_activate_q2 = $active_q2_01 + $active_q2_02 + $active_q2_03;
            $total_activate_q3 = $active_q3_01 + $active_q3_02 + $active_q3_03;
            $total_activate_q4 = $active_q4_01 + $active_q4_02 + $active_q4_03;

            $sum_total_activate = $total_activate_q1 + $total_activate_q2 + $total_activate_q3 + $total_activate_q4;

            // Calculate Grade for Store [HUB]
            $d1_level = "-";
            $d2_level = "-";
            $d3_level = "-";
            $d4_level = "-";

            if ( $count_q1_01 != 0 && $count_q1_02 != 0 && $count_q1_03 != 0 ) {

                if ( $count_q1_01 >= 50 && $count_q1_02 >= 50 && $count_q1_03 >= 50 ) { $d1_level = "Silver"; } 
                if ( $count_q1_01 >= 100 && $count_q1_02 >= 100 && $count_q1_03 >= 100 ) { $d1_level = "Gold"; } 
                if ( $count_q1_01 >= 200 && $count_q1_02 >= 200 && $count_q1_03 >= 200 ) { $d1_level = "Platinum"; } 
                
            } 

            if ( $count_q2_01 != 0 && $count_q2_02 != 0 && $count_q2_03 != 0 ) {

                if ( $count_q2_01 >= 50 && $count_q2_02 >= 50 && $count_q2_03 >= 50 ) { $d2_level = "Silver"; } 
                if ( $count_q2_01 >= 100 && $count_q2_02 >= 100 && $count_q2_03 >= 100 ) { $d2_level = "Gold"; } 
                if ( $count_q2_01 >= 200 && $count_q2_02 >= 200 && $count_q2_03 >= 200 ) { $d2_level = "Platinum"; } 

            } 

            if ( $count_q3_01 != 0 && $count_q3_02 != 0 && $count_q3_03 != 0 ) {

                if ( $count_q3_01 >= 50 && $count_q3_02 >= 50 && $count_q3_03 >= 50 ) { $d3_level = "Silver"; } 
                if ( $count_q3_01 >= 100 && $count_q3_02 >= 100 && $count_q3_03 >= 100 ) { $d3_level = "Gold"; } 
                if ( $count_q3_01 >= 200 && $count_q3_02 >= 200 && $count_q3_03 >= 200 ) { $d3_level = "Platinum"; } 
                
            } 

            if ( $count_q4_01 != 0 && $count_q4_02 != 0 && $count_q4_03 != 0 ) {

                if ( $count_q4_01 >= 50 && $count_q4_02 >= 50 && $count_q4_03 >= 50 ) { $d4_level = "Silver"; }
                if ( $count_q4_01 >= 100 && $count_q4_02 >= 100 && $count_q4_03 >= 100 ) { $d4_level = "Gold"; } 
                if ( $count_q4_01 >= 200 && $count_q4_02 >= 200 && $count_q4_03 >= 200 ) { $d4_level = "Platinum"; } 

            } 

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $d_type);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_district']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_rank']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district']);

            // Quater 1
            $sheet->setCellValue($alpha++.$index, $d1_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_01);
            $sheet->setCellValue($alpha++.$index, $count_q1_01);
            $sheet->setCellValue($alpha++.$index, $active_q1_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_02);
            $sheet->setCellValue($alpha++.$index, $count_q1_02);
            $sheet->setCellValue($alpha++.$index, $active_q1_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q1_03);
            $sheet->setCellValue($alpha++.$index, $count_q1_03);
            $sheet->setCellValue($alpha++.$index, $active_q1_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q1);
            $sheet->setCellValue($alpha++.$index, $total_count_q1);
            $sheet->setCellValue($alpha++.$index, $total_activate_q1);

            // Quater 2
            $sheet->setCellValue($alpha++.$index, $d2_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_01);
            $sheet->setCellValue($alpha++.$index, $count_q2_01);
            $sheet->setCellValue($alpha++.$index, $active_q2_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_02);
            $sheet->setCellValue($alpha++.$index, $count_q2_02);
            $sheet->setCellValue($alpha++.$index, $active_q2_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q2_03);
            $sheet->setCellValue($alpha++.$index, $count_q2_03);
            $sheet->setCellValue($alpha++.$index, $active_q2_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q2);
            $sheet->setCellValue($alpha++.$index, $total_count_q2);
            $sheet->setCellValue($alpha++.$index, $total_activate_q2);

            // Quater 3
            $sheet->setCellValue($alpha++.$index, $d3_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_01);
            $sheet->setCellValue($alpha++.$index, $count_q3_01);
            $sheet->setCellValue($alpha++.$index, $active_q3_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_02);
            $sheet->setCellValue($alpha++.$index, $count_q3_02);
            $sheet->setCellValue($alpha++.$index, $active_q3_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q3_03);
            $sheet->setCellValue($alpha++.$index, $count_q3_03);
            $sheet->setCellValue($alpha++.$index, $active_q3_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q3);
            $sheet->setCellValue($alpha++.$index, $total_count_q3);
            $sheet->setCellValue($alpha++.$index, $total_activate_q3);

            // Quater 4
            $sheet->setCellValue($alpha++.$index, $d4_level);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_01);
            $sheet->setCellValue($alpha++.$index, $count_q4_01);
            $sheet->setCellValue($alpha++.$index, $active_q4_01);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_02);
            $sheet->setCellValue($alpha++.$index, $count_q4_02);
            $sheet->setCellValue($alpha++.$index, $active_q4_02);
            $sheet->setCellValue($alpha++.$index, $sellout_q4_03);
            $sheet->setCellValue($alpha++.$index, $count_q4_03);
            $sheet->setCellValue($alpha++.$index, $active_q4_03);
            $sheet->setCellValue($alpha++.$index, $total_sellout_q4);
            $sheet->setCellValue($alpha++.$index, $total_count_q4);
            $sheet->setCellValue($alpha++.$index, $total_activate_q4);

            $sheet->setCellValue($alpha++.$index, $sum_total_sellout);
            $sheet->setCellValue($alpha++.$index, $sum_total_count);
            $sheet->setCellValue($alpha++.$index, $sum_total_activate);

            $index++;
        }
        
        $filename = 'OPPO_Club_By_Store'.$season.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelAreaCoverage($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'ASM Code',
            'ASM Name',
            'Area',
            'Sale Code',
            'Sale Name',
            'Store Active',
            'Store All'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getAreaCoverage($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['asm_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['asm_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_active']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_all']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Area_Coverage_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelInventorySellout($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Area',
            'Sale ID',
            'Sale Code',
            'Sale Name',
            'Store ID',
            'Store Name',
            'Store Area',
            'Sellout'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getInventorySellout($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Inventory_Sellout_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelInventoryRemain($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Area',
            'Remain'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getInventoryRemain($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['remain']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Inventory_Remain_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelInventoryDistributorChain($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Area',
            'Store ID',
            'Store Name',
            'Store Area',
            'Sale Code',
            'Sale Name',
            'Group'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getDistributorChain($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Inventory_Distributor_Chain_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelSalePerformance($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Store Area',
            'Staff ID',
            'Staff Name',
            'Group',
            'Joined At',
            'Created At',
            'Sellout [All]',
            'Sellout [F1]'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getSalePerformance($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_join']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_created']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_hero']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Sale_Performance_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelMondayReport($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area',
            'PC [All]',
            'PC [Active]',
            'Sellout [PC]',
            'Sellout [All]',
            'Store [Have Retailer]',
            'Store [Active]',
            'PC [KPI >= 50]',
            'PC [KPI < 50]'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getMondayReport($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_staff_active']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sellout_pc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sellout_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_retailer']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_active']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pckpi_more_50']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pckpi_less_50']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Monday_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelFridayReport($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Grand Area',
            'Area',
            'Store [Sellout >= 2]',
            'Store [Sellout >= 2 : No AM]',
            'Store [Have Retailer]',
            'Store [Have Retailer : No AM]',
            'PC',
            'PC Stand By',
            'Sale',
            'RD',
            'ASM',
            'ASM Stand By',
            'AM',
            'Sale Leader',
            'Sale Event',
            'Total Sellout [All]',
            'Total Sellout [No AM]'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getFridayReport($params);

        $index  = 2;

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        for ($i=0;$i<count($data); $i++) {

            if ( in_array($data[$i]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data[$i]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data[$i]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data[$i]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data[$i]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data[$i]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data[$i]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data[$i]['area_name']; }
            
            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $grand_area);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_more_2']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_more_2_no_am']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_retailer']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_retailer_no_am']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc_stand_by']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_rm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_asm_standby']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_am']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_event']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sellout_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sellout_no_am']);
            $index++;
        }
        
        $filename = 'Weekly_Report_Friday_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report : Shop Inventory - Export Excel
    private function _exportExcelShopInventory($data) {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Store ID',
            'Store Name',
            'Area',
            'Province',
            'Staff Code',
            'ASM / Sale',
            'Group',
            'Stock',
            'Sellout'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_province']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_district']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sale_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_stock']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_sellout']);
            $index++;
        }
        
        $filename = 'ShopInventory_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
        
    }

    private function _exportExcelT3Report($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area',
            'Province',
            'District',
            'Store',
            'Store [Sellout >= 4]',
            'Total Sellout',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getT3Report($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['district_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_more_4']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_all']);

            $index++;
        }
        
        $filename = 'Weekly_Report_T3_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }


    private function _exportExcelAreaGFK($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area',
            '%GFK',
            'Actual Unit',
            'Unit',
            'Score',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoodKpiLog->getAreaGFK($params);

        $index  = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, number_format($data[$i]['gfk'],3)."%");
            $sheet->setCellValue($alpha++.$index, number_format($data[$i]['sellout_actual']));
            $sheet->setCellValue($alpha++.$index, number_format($data[$i]['sellout']));
            $sheet->setCellValue($alpha++.$index, number_format($data[$i]['score'],2));

            $index++;
        }
        
        $filename = 'Weekly_Report_AreaGFK_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelRmGFK($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Staff Code',
            'Staff Name',
            'Group',
            'Area',
            '%GFK',
            'Actual Unit',
            'Unit',
            'Score',
            'Script'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $alpha  = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $data = array();
        $total_sellout = 0;

        $QAsm = new Application_Model_Asm();
        $QArea = new Application_Model_Area();
        $QGoodKpiLog = new Application_Model_GoodKpiLog();

        $area_list = $QArea->get_cache();
        $rm_list = $QAsm->get_rm_list();
        $data_temp = $QGoodKpiLog->getAreaGFK($params);

        foreach ($data_temp as $key => $value) {
            $data[$value['area_id']] = $value;
            $total_sellout = $total_sellout + $value['sellout'];
        }

        $index  = 2;
        for ($i=0;$i<count($rm_list); $i++) {

            $where = array();
            $where[] = $QAsm->getAdapter()->quoteInto('staff_id = ?', $rm_list[$i]['staff_id']);
            $where[] = $QAsm->getAdapter()->quoteInto('partner = ?', 0);
            $result_asm = $QAsm->fetchAll($where);

            $gfk = 0; 
            $sellout = $sellout_actual = 0;
            $area_name = "";
            $sql_script = "";
            
            for ($j=0;$j<count($result_asm);$j++) {
                $gfk = $gfk + $data[ $result_asm[$j]['area_id'] ]['gfk'];
                $sellout = $sellout + $data[ $result_asm[$j]['area_id'] ]['sellout'];
                $sellout_actual = $sellout_actual + $data[ $result_asm[$j]['area_id'] ]['sellout_actual'];

                if ($j==0) { $area_name = $area_list[ $result_asm[$j]['area_id'] ]; } 
                else { $area_name = $area_name." ,".$area_list[ $result_asm[$j]['area_id'] ]; }

            }

            $score = ( $sellout / ( ( $total_sellout * $gfk ) / 100 ) ) * 60;

            for ($k=0;$k<count($result_asm);$k++) {
                if ($k==0) {
                    $sql_script = "INSERT INTO hr.sale_com_rm (staff_id, area_id, score, from_date, to_date, created_by, created_at) VALUES (".$rm_list[$i]['staff_id'].", ".$result_asm[$k]['area_id'].", ".number_format($score,2).", '".$from." 00:00:00', '".$to." 23:59:59', 57, '".date('Y-m-d H:i:s')."');";
                } else {
                    $sql_script = $sql_script."INSERT INTO hr.sale_com_rm (staff_id, area_id, score, from_date, to_date, created_by, created_at) VALUES (".$rm_list[$i]['staff_id'].", ".$result_asm[$k]['area_id'].", ".number_format($score,2).", '".$from." 00:00:00', '".$to." 23:59:59', 57, '".date('Y-m-d H:i:s')."');";
                }
            }

            $alpha  = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $rm_list[$i]['staff_code']." | ".$total_sellout);
            $sheet->setCellValue($alpha++.$index, $rm_list[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $rm_list[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $area_name);
            $sheet->setCellValue($alpha++.$index, number_format($gfk,3)."%");
            $sheet->setCellValue($alpha++.$index, number_format($sellout_actual));
            $sheet->setCellValue($alpha++.$index, number_format($sellout));
            $sheet->setCellValue($alpha++.$index, number_format($score,2));
            $sheet->setCellValue($alpha++.$index, $sql_script);

            $index++;
        }
        
        $filename = 'Weekly_Report_RM_GFK_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Sale Scan Performance : Export 
    private function _exportExcelAll($data,$params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'STAFF CODE',
            'SCAN BY',
            'GROUP',
            'AREA',
            'TOTAL STORE',
            'TOTAL STORE SCAN',
            'TOTAL SCAN IMEI',
            'TOTAL SUCCESS IMEI'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        $i = 1;
            
        
        foreach($data as $item) {
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i++);
            $sheet->setCellValue($alpha++.$index, $item['sale_code']);
            $sheet->setCellValue($alpha++.$index, $item['sale_name']);
            $sheet->setCellValue($alpha++.$index, $item['group']);
            $sheet->setCellValue($alpha++.$index, $item['area']);
            $sheet->setCellValue($alpha++.$index, $item['allstore']);
            $sheet->setCellValue($alpha++.$index, $item['scan_store']);
            $sheet->setCellValue($alpha++.$index, $item['sum_t_imei']);
            $sheet->setCellValue($alpha++.$index, $item['sum_s_imei']);
            $index++;

        }
        
        $filename = 'Selas_Scan_Performance'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Manage/Store : Export By Market Type
    private function _exportExcelByMarketType($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads_row1 = array(
            'Area',
            'Standard Shopping Mall'    ,'','','','','','','','',
            'Supermarket'               ,'','','','','','','','',
            'Stand Alone'               ,'','','','','','','','',
            'Local Shopping Mall'       ,'','','','','','','','',
            'Mini market'               ,'','','','','','','','',
            'Event'                     ,'','','','','','','','',
        );

        $heads_row2 = array(
            '',
            'Store-Active','','','Store-All','','','Sellout','','',
            'Store-Active','','','Store-All','','','Sellout','','',
            'Store-Active','','','Store-All','','','Sellout','','',
            'Store-Active','','','Store-All','','','Sellout','','',
            'Store-Active','','','Store-All','','','Sellout','','',
            'Store-Active','','','Store-All','','','Sellout','','',
        );

        $heads_row3 = array(
            '',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
            'ORG','Dealer','BranShop','ORG','Dealer','BranShop','ORG','Dealer','BranShop',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads_row1 as $key) {
            $sheet->setCellValue($alpha.$index, $key);  
            $alpha++;
        }

        // reset value
        $alpha  = 'A';
        $index  = 1;
        $index2  = 2;
        $index3  = 3;

        foreach($heads_row2 as $key) {
            $sheet->setCellValue($alpha.$index2, $key);  
            $alpha++;
        }

        $alpha  = 'A';
        foreach($heads_row3 as $key) {
            $sheet->setCellValue($alpha.$index3, $key);  
            $alpha++;
        }

        // Merge Row 1 
        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:J1');
        $sheet->mergeCells('K1:S1');
        $sheet->mergeCells('T1:AB1');
        $sheet->mergeCells('AC1:AK1');
        $sheet->mergeCells('AL1:AT1');
        $sheet->mergeCells('AU1:BC1');

        // Merge Row 2 
        $sheet->mergeCells('B2:D2');
        $sheet->mergeCells('E2:G2');
        $sheet->mergeCells('H2:J2');

        $sheet->mergeCells('K2:M2');
        $sheet->mergeCells('N2:P2');
        $sheet->mergeCells('Q2:S2');

        $sheet->mergeCells('T2:V2');
        $sheet->mergeCells('W2:Y2');
        $sheet->mergeCells('Z2:AB2');

        $sheet->mergeCells('AC2:AE2');
        $sheet->mergeCells('AF2:AH2');
        $sheet->mergeCells('AI2:AK2');

        $sheet->mergeCells('AL2:AN2');
        $sheet->mergeCells('AO2:AQ2');
        $sheet->mergeCells('AR2:AT2');

        $sheet->mergeCells('AU2:AW2');
        $sheet->mergeCells('AX2:AZ2');
        $sheet->mergeCells('BA2:BC2');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:BC3")->applyFromArray($style);

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getReortByMarketType($params);
        
        $index = 4;
        foreach($data as $item) {

            $alpha    = 'A';
            //$sheet->setCellValue($alpha++.$index, $i++);
            $sheet->setCellValue($alpha++.$index, $item['area_name']);

            // Standard Shoping Mall
            $sheet->setCellValue($alpha++.$index, $item['store_active_01_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_01_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_01_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_01_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_01_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_01_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_01_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_01_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_01_bs']);

            // Supermarket
            $sheet->setCellValue($alpha++.$index, $item['store_active_02_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_02_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_02_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_02_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_02_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_02_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_02_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_02_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_02_bs']);

            // Stand Alone
            $sheet->setCellValue($alpha++.$index, $item['store_active_03_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_03_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_03_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_03_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_03_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_03_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_03_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_03_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_03_bs']);

            // Local Shopping Mall
            $sheet->setCellValue($alpha++.$index, $item['store_active_04_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_04_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_04_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_04_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_04_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_04_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_04_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_04_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_04_bs']);

            // Mini market
            $sheet->setCellValue($alpha++.$index, $item['store_active_05_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_05_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_05_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_05_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_05_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_05_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_05_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_05_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_05_bs']);

            // Event
            $sheet->setCellValue($alpha++.$index, $item['store_active_06_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_06_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_active_06_bs']);

            $sheet->setCellValue($alpha++.$index, $item['store_all_06_org']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_06_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['store_all_06_bs']);

            $sheet->setCellValue($alpha++.$index, $item['sellout_06_org']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_06_dealer']);
            $sheet->setCellValue($alpha++.$index, $item['sellout_06_bs']);

            $index++;

        }
        
        $filename = 'Report_By_Market_Type_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function saveTimingIssueAction() {

        $this->_helper->layout->disableLayout();

        $QTimingIssue    = new Application_Model_TimingIssue();
        //$userStorage    = Zend_Auth::getInstance()->getStorage()->read();

        $issue_type     = $_POST['issue_type'];
        $staff_id       = $_POST['staff_id'];
        $imei           = $_POST['imei'];
        $store          = $_POST['store'];
        $timing_date    = date("Y-m-d H:i:s", $_POST['timing_date']);

        $where = $QTimingIssue->getAdapter()->quoteInto('imei = ?', $imei);
        $result = $QTimingIssue->fetchRow($where);

        if ($result['imei']) {
            exit('-1');
        } else {

            $data = array(
                'issue_type'    => $issue_type,
                'imei'          => $imei,
                'store_id'      => $store,
                'staff_id'      => $staff_id,
                'timing_date'   => $timing_date
            );

            $result_insert = $QTimingIssue->insert($data);
            exit('-2');
        }
        
        
        //if ($result) {  exit('-1'); } else {  exit('-2'); }
        //exit();
        //return $result;
    }

    private function _exportTimingIssue($data,$params) {
        //echo "sss"; print_r($params); die;
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        //$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        //$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $heads = array(
            'NO.',
            'Staff Area',
            'Staff Code',
            'Staff Name',
            'Group',
            'Store Area',
            'Store ID',
            'Store Name',
            'Imei',
            'Model',
            'Color',
            'Old Data',
            'Request Timing Date',
            'Timing Date',
            'Activated Date',
            'DateDiff',
            'Issue Type',
            'Issue Status',
            'Remark',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        // Issue Status
        $issue_status_list = array(
            '0' => 'Waiting',
            '1' => 'Finish',
            '2' => 'Reject',
            '3' => 'In Progress',
        );

        // Issue Type
        $issue_type_list = array(
            '1' => 'Imei Not Exists',
            '2' => 'Store Not Exists',
            '3' => 'Distributor Not Exists',
            '4' => 'Store Type / Distributor Type',
            '5' => 'Store Chain 1 Not Exists',
            '6' => 'Mapping Distributor',
        );

        $index = 2;
        for ($i=0;$i<count($data);$i++) {

            if ($data[$i]['old_data'] == 1) { $old_data = "Yes"; } else { $old_data = "No"; }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->getCell($alpha++.$index)->setValueExplicit($data[$i]['imei'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++.$index, $data[$i]['model']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['color']);
            $sheet->setCellValue($alpha++.$index, $old_data);
            $sheet->setCellValue($alpha++.$index, $data[$i]['request_timing_date']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['timing_date']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activated_date']);

            // Date Diff
            $act_diff = "";
            $date_01 = date_create(strtok($data[$i]['timing_date']," "));

            // check diff date of timing date and actvated date
            if ($data[$i]['activated_date']) {
                $date_02 = date_create(strtok($data[$i]['activated_date']," "));
                $act_diff = date_diff($date_01, $date_02)->format("%R%a");
            } else {
                $act_diff = "";
            }
            
            $sheet->setCellValue($alpha++.$index, $act_diff);
            $sheet->setCellValue($alpha++.$index, $issue_type_list[ $data[$i]['issue_type'] ]);
            $sheet->setCellValue($alpha++.$index, $issue_status_list[ $data[$i]['status_id'] ]);
            $sheet->setCellValue($alpha++.$index, $data[$i]['remark']);
            
            //$PHPExcel->getActiveSheet()->getStyle('I'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

            $index++;
        }
        
        $filename = 'Timing_Issue_list_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function timingIssueRemarkSaveAction() {

        $this->_helper->layout->disableLayout();

        $QTimingIssue   = new Application_Model_TimingIssue();
        $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
        $ti_id      = $_POST['ti_id'];
        $remark     = $_POST['remark'];
        $status_id  = $_POST['status_id'];

        $where = $QTimingIssue->getAdapter()->quoteInto('id = ?', $ti_id);

        $data = array(
            'remark'     => $remark,
            'status'     => $status_id,
            'updated_by' => $userStorage->id,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Submit Button
        if ($status_id == 4) { unset($data['status']); }

        $result = $QTimingIssue->update($data, $where);

        //if ($result) {  exit('-1'); } else {  exit('-2'); }
        exit();
    }


    // Export Report By Area - Current PC : Export Excel
    private function _exportExcelPCByArea($data) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Group',
            'PC Stand By',
            'Created_at',
            'Off Date',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pc_stand_by']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_created_at']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_off_date']);
            $index++;
        }
        
        $filename = 'Area_PC_list_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report By Area - Current Sale : Export Excel
    private function _exportExcelSaleByArea($data) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area Name',
            'Staff Code',
            'Staff Name',
            'Group',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $index++;
        }
        
        $filename = 'Area_Sale_list_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report By Store : Export Brand Shop Manager's Commission
    private function _exportExcelComBM($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area',
            'Store ID',
            'Store Type',
            'Store Name',
            'Target',
            'Sellout',
            'Percent Achieve',
            'Price',
            'Price (EX VAT)',
            'Commission',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $data = $QGoodKpi->getComBM($params);

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $pa = 0;
            $price = 0;
            $bm_com = 0;
            
            if ( isset($data[$i]['price']) && $data[$i]['price'] ) { $price = $data[$i]['price']; } 

            $price_ex_vat = ($price * 100 ) / 107;

            if ( isset($data[$i]['st_target']) && $data[$i]['st_target'] ) { 

                $pa = ( $data[$i]['sellout'] / $data[$i]['st_target'] ) * 100; 

                if ( $pa >= 60 ) { $bm_com = ($price_ex_vat * 0.6) / 100; }
                if ( $pa >= 80 ) { $bm_com = ($price_ex_vat * 1) / 100; }
                if ( $pa >= 100 ) { $bm_com = ($price_ex_vat * 1.5) / 100; }

            } else {
                $data[$i]['st_target'] = '-';
            }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_target']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, number_format($pa,2));
            $sheet->setCellValue($alpha++.$index, number_format($price,2));
            $sheet->setCellValue($alpha++.$index, number_format($price_ex_vat,2));
            $sheet->setCellValue($alpha++.$index, number_format($bm_com));

            $index++;
        }
        
        $filename = 'BM_Commission_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    private function _exportExcelSellin($data) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'IMEI',
            'Model',
            'Color',
            'Distributor ID',
            'Distributor Type',
            'Distributor Name',            
            // 'Current Sale',
            // 'Current PC',
            'Area',
            'Store ID',
            'Store Type',
            'Store NAME',
            'Shop ID',
            'Shop Code',
            'Timing Date'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();


            $alpha    = 'A';
            $index    = 1;

            foreach($heads as $key) {
                $sheet->setCellValue($alpha.$index, $key);
                $alpha++;
            }


           $index = 2;
       
        for ($i=0;$i<count($data); $i++) {
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);            
            $sheet->setCellValue($alpha++.$index, (String)$data[$i]['imei_sn']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']."(".$data[$i]['desc'].")");
            $sheet->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['org_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['title']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['salse']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['pc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['org_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['shop_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['shop_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);
            $index++;
        }
        
        $filename = 'AM_SELL_OUT_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : PC By Model Daily
    private function _exportExcelPcByModel($params) {

        // change date format
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        // Model : CPH1607, CPH1701, A37fw, CPH1715, CPH1717, CPH1613, CPH1723
        $model_list = array(208,211,255,256,266,267,299);

        $QGood = new Application_Model_Good();
        $where = $QGood->getAdapter()->quoteInto('id IN (?)', $model_list);
        $model_result = $QGood->fetchAll($where);

        // Start PHPExcel
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'NO.',
            'Grand Area',
            'Area',
            'Staff Code',
            'Staff Name',
            'Group',
            'Store ID',
            'Store Name',
            'Store Type',
            'Market Type',
            'Market Name',
            'Distributor ID',
            'Distributor Name',
            'PC Total Sellout',
        );

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('d-M-Y', strtotime("+".$i." Day", strtotime($from)));
            array_push($heads, $day);
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $QTiming = new Application_Model_Timing();
        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        for($i=0;$i<count($model_result);$i++) {

            switch ($model_result[$i]['id']) {
                // A37 [A37f, A37fw]
                case 255: 
                    $sheet_name = "A37"; 
                    $params['product_id'] = array(255,140);
                    break;

                default : 
                    $sheet_name = $model_result[$i]['name']; 
                    $params['product_id'] = $model_result[$i]['id'];
                    break;
            } 

            $data = array();
            $sub_sheet = $PHPExcel->createSheet($i);

            $alpha  = 'A';
            $index = 1;
            $cnt = 1;

            foreach($heads as $key) {
                $sub_sheet->setCellValue($alpha.$index, $key);

                // Check If Column >= 15
                if ($cnt >= 15) {

                    $tmp = date('D', strtotime($key));

                    if ($tmp == 'Sun' || $tmp == 'Sat') {
                        $sub_sheet->getStyle($alpha.$index)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'F5A9BC'))));
                    }
                    
                }

                $alpha++;
                $cnt++;
            }

            $data = $QTiming->getPcByModel($params);

            //print_r($data); //die;

            $index  = 2;

            // Set Grand Area of BKK
            $grand_e1 = array(81,82,83,110,111,112);
            $grand_e2 = array(85,86,87,115,88,89,116,117);
            $grand_e3 = array(90,91,92,93,113);
            $grand_e4 = array(94,95,96);
            $grand_e5 = array(97,109);
            $grand_w1 = array(98,99,100,101,102,114);
            $grand_w2 = array(103,104,105);
            $grand_w3 = array(106,107,108);

            $grand_area = "";

            for ($j=0;$j<count($data); $j++) {

                if ( in_array($data[$j]['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
                else if ( in_array($data[$j]['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
                else if ( in_array($data[$j]['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
                else if ( in_array($data[$j]['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
                else if ( in_array($data[$j]['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
                else if ( in_array($data[$j]['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
                else if ( in_array($data[$j]['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
                else if ( in_array($data[$j]['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
                else { $grand_area = $data[$j]['area_name']; }
                
                $alpha  = 'A';
                $sub_sheet->setCellValue($alpha++.$index, $j+1);
                $sub_sheet->setCellValue($alpha++.$index, $grand_area);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['area_name']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_code']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_name']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_group']);

                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_id']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_name']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_type']);

                $sub_sheet->setCellValue($alpha++.$index, $store_market[ $data[$j]['st_id'] ]['market_type']);
                $sub_sheet->setCellValue($alpha++.$index, $store_market[ $data[$j]['st_id'] ]['market_name']);

                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['d_id']);
                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['d_name']);

                $sub_sheet->setCellValue($alpha++.$index, $data[$j]['total_sellout']);

                for ($k=0;$k<$period_loop;$k++) { $sub_sheet->setCellValue($alpha++.$index, $data[$j]['sellout_'.$k]); }  

                $sub_sheet->getStyle('N'.$index)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'CEF6E3')
                        )
                    )
                );   
                
                $index++;
            }

            $sub_sheet->setTitle($sheet_name);

        }
        
        
        $filename = 'Weekly_Report_PC_By_Model_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Head Count By RD
    private function _exportHeadCountByRD($data) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'NO.',
            'Area',
            'RD Code',
            'RD Name',
            'PC',
            'Sale Area',
            'Sale Event',
            'ASM',            
            'RD',
            'TMS Leader',
            'TMS',
            'PCM Leader',
            'PCM',
            'Total Staff',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:N1")->applyFromArray($style);

        $index = 2;

        $sum_staff = 0;

        $sum_pc = 0; 
        $sum_sale = 0;
        $sum_sale_event = 0;

        $sum_asm = 0;
        $sum_rd = 0;

        $sum_tms_leader = 0;
        $sum_tms = 0;
        $sum_pcm_leader = 0;
        $sum_pcm = 0;
       
        for ($i=0;$i<count($data); $i++) {

            $sum_staff = $data[$i]['cnt_pc'] + $data[$i]['cnt_sale'] + $data[$i]['cnt_sale_event'] + 
                        $data[$i]['cnt_asm'] + $data[$i]['cnt_rd'] + $data[$i]['cnt_tms_leader'] + 
                        $data[$i]['cnt_tms'] + $data[$i]['cnt_pcm_leader'] + $data[$i]['cnt_pcm'];

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);            
            $sheet->setCellValue($alpha++.$index, $data[$i]['zone']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale_event']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_asm']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_rd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_tms']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pcm']);
            $sheet->setCellValue($alpha++.$index, $sum_staff);

            $sheet->getStyle('A'.$index)->applyFromArray($style);
            $sheet->getStyle('B'.$index)->applyFromArray($style);
            $sheet->getStyle('C'.$index)->applyFromArray($style);

            $index++;

            $sum_pc         = $sum_pc + $data[$i]['cnt_pc'];
            $sum_sale       = $sum_sale + $data[$i]['cnt_sale'];
            $sum_sale_event = $sum_sale_event + $data[$i]['cnt_sale_event'];

            $sum_asm        = $sum_asm + $data[$i]['cnt_asm'];
            $sum_rd         = $sum_rd + $data[$i]['cnt_rd'];

            $sum_tms_leader = $sum_tms_leader + $data[$i]['cnt_tms_leader'];
            $sum_tms        = $sum_tms + $data[$i]['cnt_tms'];
            $sum_pcm_leader = $sum_pcm_leader + $data[$i]['cnt_pcm_leader'];
            $sum_pcm        = $sum_pcm + $data[$i]['cnt_pcm'];
        }

        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, "Total");            
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_pc);
        $sheet->setCellValue($alpha++.$index, $sum_sale);
        $sheet->setCellValue($alpha++.$index, $sum_sale_event);
        $sheet->setCellValue($alpha++.$index, $sum_asm);
        $sheet->setCellValue($alpha++.$index, $sum_rd);
        $sheet->setCellValue($alpha++.$index, $sum_tms_leader);
        $sheet->setCellValue($alpha++.$index, $sum_tms);
        $sheet->setCellValue($alpha++.$index, $sum_pcm_leader);
        $sheet->setCellValue($alpha++.$index, $sum_pcm);
        $sheet->setCellValue($alpha++.$index, "");

        $sheet->mergeCells('A'.$index.':D'.$index); 
        $sheet->getStyle('A'.$index)->applyFromArray($style);

        $filename = 'Staff_HeadCount_By_RD_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Head Count By Position
    private function _exportHeadCountByPosition($data) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Position',
            'BKK',
            'UPC',
            'Total',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:D1")->applyFromArray($style);

        $index = 2;

        $sum_staff = 0;

        $sum_bkk = 0; 
        $sum_upc = 0;
        $sum_total = 0;
       
        for ($i=0;$i<count($data); $i++) {

            $sum_staff = $data[$i]['cnt_bkk'] + $data[$i]['cnt_upc'];

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);            
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_bkk']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_upc']);
            $sheet->setCellValue($alpha++.$index, $sum_staff);

            $index++;

            $sum_bkk    = $sum_bkk + $data[$i]['cnt_bkk'];
            $sum_upc    = $sum_upc + $data[$i]['cnt_upc'];
            $sum_total  = $sum_total + $sum_staff;

        }

        $alpha    = 'A';        
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_bkk);
        $sheet->setCellValue($alpha++.$index, $sum_upc);
        $sheet->setCellValue($alpha++.$index, $sum_total);

        $filename = 'Staff_HeadCount_By_Position_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Head Count : Staff List By RD
    private function _exportStaffByRD($data) {

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
            'RD Code',
            'RD Name',
            'RD Group',
            'Staff Code',
            'Staff Name',
            'Staff Group',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:D1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rd_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_group']);

            $index++;

        }

        $filename = 'Staff_List_HeadCount_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Staff Resign : Staff List
    private function _exportStaffResignList($data) {

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
            'Staff Code',
            'Staff Name',
            'Group',
            'Phone Number',
            'Joined At',
            'Off Date',
            'Area',
            'Grand Area',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:H1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['staff_phone'].'"');
            $sheet->setCellValue($alpha++.$index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['off_date']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);

            $index++;

        }

        $filename = 'Staff_Resign_List_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : Sale Hero Product Sellout 
    private function _exportExcelSaleHero($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // change date format
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Store Area',
            'Staff Code',
            'Staff Name',
            'Group',
            'Target [F5]',
            'Sellout [F5]',
            'Achieve',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $QOppoSaleTarget = new Application_Model_OppoSaleTarget();

        $data = $QTiming->getSaleHero($params);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $where = array();
            $where[] = $QOppoSaleTarget->getAdapter()->quoteInto('area_id = ?', $data[$i]['area_id']);
            $where[] = $QOppoSaleTarget->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
            $where[] = $QOppoSaleTarget->getAdapter()->quoteInto('from_date <= ?', $from);
            $where[] = $QOppoSaleTarget->getAdapter()->quoteInto('to_date >= ?', $to);

            $hero_target = $QOppoSaleTarget->fetchRow($where);

            $achieve = 0;
            if ( isset($hero_target['target_hero']) && $hero_target['target_hero'] ) {
                $achieve = ( $data[$i]['hero_sellout'] / $hero_target['target_hero'] ) * 100;
            }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);            
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $hero_target['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_sellout']);
            $sheet->setCellValue($alpha++.$index, number_format($achieve,2)."%");
            $index++;

        }
        
        $filename = 'SALES_HERO_SELL_OUT_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : ASM Hero Product Sellout 
    private function _exportExcelAsmHero($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // change date format
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Zone',
            'Staff Code',
            'Staff Name',
            'Group',
            'Area Target [F5]',
            'Sellout [F5]',
            'Achieve',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $QOppoAreaTarget = new Application_Model_OppoAreaTarget();

        $data = $QTiming->getAsmHero($params);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $where = array();
            $where[] = $QOppoAreaTarget->getAdapter()->quoteInto('area_id = ?', $data[$i]['area_id']);
            $where[] = $QOppoAreaTarget->getAdapter()->quoteInto('from_date <= ?', $from);
            $where[] = $QOppoAreaTarget->getAdapter()->quoteInto('to_date >= ?', $to);
            $hero_target = $QOppoAreaTarget->fetchRow($where);

            $achieve = 0;
            if ( isset($hero_target['target_hero']) && $hero_target['target_hero'] ) {
                $achieve = ( $data[$i]['hero_sellout'] / $hero_target['target_hero'] ) * 100;
            }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);            
            $sheet->setCellValue($alpha++.$index, $data[$i][' zone']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $hero_target['target_hero']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['hero_sellout']);
            $sheet->setCellValue($alpha++.$index, number_format($achieve,2)."%");
            $index++;
        }
        
        $filename = 'ASM_HERO_SELL_OUT_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : Hero Product Perforomance Report 
    private function _exportExcelHeroPerformance($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // change date format
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Grand Area',
            'Area',
            'Store Active',
            'Sale',
            'PC',
            'MTD Sellout',
            'Total Sellout',
            'F5 (CPH1723)',
            'F5 6GB (CPH1727)',
            'F5 Youth (CPH1725)',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getHeroPerformance($params);
        //print_r($data);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_more_2']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_sale']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_mtd']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_hero_01']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_hero_03']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_hero_02']);

            $index++;
        }
        
        $filename = 'Hero_Product_Performance_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Competitor Report : Export 
    private function _exportCompetitorReport($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // change date format
        // $d = explode('/', $params['from']);
        // $from = $d[2].'-'.$d[1].'-'.$d[0];

        // $d = explode('/', $params['to']);
        // $to = $d[2].'-'.$d[1].'-'.$d[0];

        $heads_02 = array_keys($data[0]);

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        if (isset($params['store_focus']) && $params['store_focus'] == 0) { 

            // array_splice($heads_02, count($heads_02) - 14, 14); 
            for ($i=0;$i<11;$i++) { array_pop($heads_02); }

            $heads_01 = array(
                'Timing Date',
                'Area',
                'Store ID',
                'Store Name',
                // 'Staff Code',
                // 'Staff Name',
                // 'Group',
                'City Type',
                'Channel Type',
                'Total Unit [All Brand]',
                'Total Unit [OPPO]',
                'Total Unit [Huawei]',
                'Total Unit [VIVO]',
                'Total Unit [Samsung]',
            );

        } else { 

            array_splice($heads_02, count($heads_02) - 13, 13); 

            $heads_01 = array(
                'Timing Date',
                'Area',
                'Store ID',
                'Store Name',
                // 'Staff Code',
                // 'Staff Name',
                // 'Group',
                'City Type',
                'Channel Type',
                'Total Unit [All Brand]',
                'Total Unit [Huawei]',
                'Total Unit [VIVO]',
                'Total Unit [Samsung]',
            );

        }

        $heads = array_merge_recursive($heads_01, $heads_02);

        // echo "<pre>"; 
        // print_r($heads_01); echo "<br/>";
        // print_r($heads_02); echo "<br/>";
        // print_r($heads); echo "<br/>";
        // die;

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['timing_date']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);

            // $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['city_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['channel_type']);


            if (isset($params['store_focus']) && $params['store_focus'] == 0) {

                $total_unit = $data[$i]['total_unit'] + $data[$i]['total_oppo'];

                $sheet->setCellValue($alpha++.$index, $total_unit);
                $sheet->setCellValue($alpha++.$index, $data[$i]['total_oppo']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_1']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_2']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_3']);

            } else {

                $sheet->setCellValue($alpha++.$index, $data[$i]['total_unit']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_1']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_2']);
                $sheet->setCellValue($alpha++.$index, $data[$i]['com_3']);

            }

            for ($j=0;$j<count($heads_02); $j++) {
                $sheet->setCellValue($alpha++.$index, $data[$i][ $heads_02[$j] ]);
            }

            $index++;
        }
        
        $filename = 'Competitor_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // KA Sellout : Export 
    private function _exportKeyAccountSellout($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // change date format
        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $heads_02 = array_keys($data[0]);
        array_splice($heads_02, 0, 3);

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads_01 = array(
            'No.',
            'Channel',
        );

        $heads = array_merge_recursive($heads_01, $heads_02);

        array_push($heads, "Total");

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['channel_name']);

            for ($j=0;$j<$period_loop;$j++) {
                $day_text =  date('d-M', strtotime("+".$j." Day", strtotime($from)));
                $sheet->setCellValue($alpha++.$index, $data[$i][$day_text]);
            }

            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);

            $index++;
        }
        
        $filename = 'KA_Sellout_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Distributor DOI : List
    private function _exportDOIList($data) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Distributor ID',
            'Distributor Name',
            'Distributor Type',
            'Grand Area',
            'Area',

            // 'Sellout',
            'Activated',
            
            'Stock',
            'Selling Rate',
            'Safety Stock',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:J1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['stock']);
            $sheet->setCellValue($alpha++.$index, number_format($data[$i]['selling_rate']));
            $sheet->setCellValue($alpha++.$index, $data[$i]['safety_stock']);

            $index++;

        }

        $filename = 'Distributor_DOI_List_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

     public function _exportDOIImeiList($data){

             set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Distributor ID',
            'Distributor Name',
            // 'Distributor Type',
            // 'Grand Area',
            'Area',

            // 'Sellout',
            // 'Activated',
            'Imei',
            
            // 'Stock',
            // 'Selling Rate',
            // 'Safety Stock',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:J1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['d_type']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_area']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['imeisn']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['stock']);
            // $sheet->setCellValue($alpha++.$index, number_format($data[$i]['selling_rate']));
       $index++;

        }

        $filename = 'Distributor_DOI_Imei_List_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function apiGetSelloutBySaleAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');

        if (isset($staff_code) && $staff_code) {

            // Test 6103724
            $QStaff = new Application_Model_Staff();
            $QGoodKpiLog = new Application_Model_GoodKpiLog();

            $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
            $result_staff = $QStaff->fetchRow($where);

            $now = date('Y-m-d');

            //echo $result_staff['joined_at']; echo "<br/>"; echo $now; echo "<br/>";

            $mon_diff = ((strtotime($now) - strtotime($result_staff['joined_at'])) / (60*60*24*30));
            // echo $mon_diff; echo "<br/>";

            for ($i=0;$i<$mon_diff;$i++) {

                $start = date('Y-m-01', strtotime("+".$i." Month", strtotime( $result_staff['joined_at'] )));
                $end = date('Y-m-t', strtotime("+".$i." Month", strtotime( $result_staff['joined_at'] )));

                $month_year = date('M_Y', strtotime("+".$i." Month", strtotime( $result_staff['joined_at'] )));

                $params = array(
                    'from'       => $start,
                    'to'         => $end,
                    'staff_code' => $staff_code,
                );

                $temp = $QGoodKpiLog->com_sale_bkk_2017($params);

                // if (empty($temp)) { echo "No Commission Data"; exit; }

                $result[$month_year] = $temp[0];
            }

            // echo "<pre>"; print_r($result);
            echo json_encode($result);

        } else {

            echo "No Input";
        }
        
    }

    public function apiGetSelloutByStoreAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $store_id = $this->getRequest()->getParam('store_id');
        $from_date = $this->getRequest()->getParam('from_date');
        $to_date = $this->getRequest()->getParam('to_date');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        if ( (isset($store_id) && $store_id) && (isset($from_date) && $from_date) && (isset($to_date) && $to_date) ) {

            // Test 6103724
            $QStaff = new Application_Model_Staff();
            $QGoodKpiLog = new Application_Model_GoodKpiLog();

            $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
            $result_staff = $QStaff->fetchRow($where);

            $now = date('Y-m-d');

            //echo $result_staff['joined_at']; echo "<br/>"; echo $now; echo "<br/>";

            $mon_diff = round((strtotime($to_date) - strtotime($from_date)) / (60*60*24*30),0);
            // echo $mon_diff; echo "<br/>";

            for ($i=0;$i<$mon_diff;$i++) {

                $start = date('Y-m-01', strtotime("+".$i." Month", strtotime( $from_date )));
                $end = date('Y-m-t', strtotime("+".$i." Month", strtotime( $from_date)));

                $month_year = date('M_Y', strtotime("+".$i." Month", strtotime( $from_date )));

                $select = $db->select()
                    ->from(array('st' => 'store'), array('sellouts' => new Zend_Db_Expr("COUNT(ts.imei)") ))
                    ->join(array('t'  => 'timing')              , 'st.id = t.store'     , array())
                    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id' , array())
                    ->join(array('i'  => WAREHOUSE_DB.'.imei')  , 'ts.imei = i.imei_sn' , array())
                    ->where('t.created_at >= ?', $start.' 00:00:00')
                    ->where('t.created_at <= ?', $end.' 23:59:59')
                    ->where('i.activated_date IS NOT NULL', 1)
                    ->where('st.id = ?', $store_id);

                // echo $select;
                $temp = $db->fetchAll($select);

                $result[$month_year] = $temp[0];
            }

            // echo "<pre>"; print_r($result);
            echo json_encode($result);

        } else {

            echo "Invalid Input";
        }
        
    }

    // iOPPO API : Analysis By Area Days
    public function apiGetAnalysisByAreaDaysAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $area_id = array();

        $from           = $this->getRequest()->getParam('from');
        $to             = $this->getRequest()->getParam('to');
        $product_id     = $this->getRequest()->getParam('product_id');
        $area_id        = $this->getRequest()->getParam('area_id');
        $asm_code       = $this->getRequest()->getParam('asm_code');
        $report_model   = $this->getRequest()->getParam('report_model');

        $QStaff = new Application_Model_Staff();

        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('code = ?', $asm_code);
        $result_staff = $QStaff->fetchRow($where);

        $params = array(
            'from'          => date('d/m/Y', strtotime($from)),
            'to'            => date('d/m/Y', strtotime($to)),
            'product_id'    => $product_id,
            'area_id'       => $area_id,
            'asm'           => $result_staff['id'],
            'report_model'  => $report_model,
        );

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->analysisByAreaDays($params);

        echo json_encode($result);
        exit;

    }

    // iOPPO API : Analysis By Area Months
    public function apiGetAnalysisByAreaMonthsAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $area_id = array();

        $from           = $this->getRequest()->getParam('from');
        $to             = $this->getRequest()->getParam('to');
        $product_id     = $this->getRequest()->getParam('product_id');
        $area_id        = $this->getRequest()->getParam('area_id');
        $asm_code            = $this->getRequest()->getParam('asm_code');
        $report_model   = $this->getRequest()->getParam('report_model');

        $QStaff = new Application_Model_Staff();
        
        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('code = ?', $asm_code);
        $result_staff = $QStaff->fetchRow($where);

        $params = array(
            'from'          => date('d/m/Y', strtotime($from)),
            'to'            => date('d/m/Y', strtotime($to)),
            'product_id'    => $product_id,
            'area_id'       => $area_id,
            'asm'           => $result_staff['id'],
            'report_model'  => $report_model,
        );

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->analysisByAreaMonths($params);

        echo json_encode($result);
        exit;

    }

    // iOPPO API : Analysis By Channel Days
    public function apiGetAnalysisByChannelDaysAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $area_id = array();

        $from           = $this->getRequest()->getParam('from');
        $to             = $this->getRequest()->getParam('to');
        $product_id     = $this->getRequest()->getParam('product_id');
        $asm_code            = $this->getRequest()->getParam('asm_code');

        $QStaff = new Application_Model_Staff();
        
        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('code = ?', $asm_code);
        $result_staff = $QStaff->fetchRow($where);

        $params = array(
            'from'          => date('d/m/Y', strtotime($from)),
            'to'            => date('d/m/Y', strtotime($to)),
            'product_id'    => $product_id,
            'asm'           => $result_staff['id'],
        );

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->analysisByChannelDays($params);

        echo json_encode($result);
        exit;

    }

    // iOPPO API : Analysis By Channel Months
    public function apiGetAnalysisByChannelMonthsAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $area_id = array();

        $from           = $this->getRequest()->getParam('from');
        $to             = $this->getRequest()->getParam('to');
        $product_id     = $this->getRequest()->getParam('product_id');
        $asm_code            = $this->getRequest()->getParam('asm_code');

        $QStaff = new Application_Model_Staff();
        
        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('code = ?', $asm_code);
        $result_staff = $QStaff->fetchRow($where);

        $params = array(
            'from'          => date('d/m/Y', strtotime($from)),
            'to'            => date('d/m/Y', strtotime($to)),
            'product_id'    => $product_id,
            'asm'           => $result_staff['id'],
        );

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->analysisByChannelMonths($params);

        echo json_encode($result);
        exit;

    }

    // OPPO Staff Tools API : Get BS Stock Scan
    public function apiGetBsStockScanAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $store_id = $this->getRequest()->getParam('store_id');

        $params = array(
            'store_id' => $store_id,
        );

        if ( isset($store_id) && $store_id ) {

            $QGood = new Application_Model_Good();
            $QTiming = new Application_Model_Timing();

            $product_list = $QGood->getBsStockScanProductList();
            // echo "<pre>"; print_r($product_list);

            $result = array();
            for ($i=0;$i<count($product_list);$i++) {

                $params['good_id'] = $product_list[$i]['product_id'];
                $params['color_id'] = $product_list[$i]['color_id'];

                $data = $QTiming->getBsStockScanByStoreID($params);

                $result[$i]['product_name'] = $product_list[$i]['product_name'];
                $result[$i]['color_name']   = $product_list[$i]['color_name'];
                $result[$i]['sellout']      = $data['sellout'];
                $result[$i]['sellin_scan']  = $data['sellin_scan'];
                $result[$i]['stock_scan']   = $data['stock_scan'];
            }

            // echo "<pre>"; print_r($result);
            echo json_encode($result);

        } else {
            echo "No Store ID";
        }

        exit;

    }

    // Register Warranty API : Check Timing Sale
    public function apiCheckRegWarrantyImeiAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $imei = $this->getRequest()->getParam('imei');

        $result = array();
        $product_list = array(399,403); // Reno, Reno 10X

        if ( isset($imei) && $imei ) {

            $db = Zend_Registry::get('db');

            $get = array(
                'imei_sn'   => 'i.imei_sn',
                'good_id'   => 'i.good_id',
                'good_code' => 'g.name',
                'good_name' => 'g.desc',
                'color_id'  => 'gc.id',
                'color_name'=> 'gc.name',
                'ts_id'     => 'ts.id',
            );

            $select = $db->select()
                ->from(array('i' => WAREHOUSE_DB.'.imei'), $get)
                ->join(array('g' => WAREHOUSE_DB.'.good')       , 'i.good_id = g.id'    , array())
                ->join(array('gc'=> WAREHOUSE_DB.'.good_color') , 'i.good_color = gc.id', array())
                ->joinLeft(array('ts' => 'timing_sale')         , 'i.imei_sn = ts.imei' , array())
                ->where('i.imei_sn = ?', trim($imei));

            $data = $db->fetchRow($select);

            if ( !empty($data) ) { 

                if ( !in_array($data['good_id'], $product_list) ) {

                    $result['good_code'] = $data['good_code'];
                    $result['good_name'] = $data['good_name'];
                    $result['color_name'] = $data['color_name'];
                    $result['status'] = 2;
                    $result['msg'] = "Model นี้ไม่ร่วมรายการค่ะ!"; 

                } else if ( !isset($data['ts_id']) ) { 

                    $result['good_code'] = $data['good_code'];
                    $result['good_name'] = $data['good_name'];
                    $result['color_name'] = $data['color_name'];
                    $result['status'] = 3;
                    $result['msg'] = "IMEI นี้ยังไม่ได้ถูกขายค่ะ!"; 

                } else {

                    $result['good_code'] = $data['good_code'];
                    $result['good_name'] = $data['good_name'];
                    $result['color_name'] = $data['color_name'];
                    $result['status'] = 1;
                    $result['msg'] = "Success"; 

                }

            } else {

                $result['status'] = 0;
                $result['msg'] = "ไม่มี IMEI นี้ในระบบค่ะ!"; 

            }

        } else {

            $result['status'] = '-';
            $result['msg'] = "No IMEI"; 

        }

        echo json_encode($result);
        exit;

    }

    // Function Sum Time
    function sum_the_time($time1, $time2) {

        $times = array($time1, $time2);
        $seconds = 0;

        foreach ($times as $time) {
            list($hour,$minute,$second) = explode(':', $time);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }

        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);
        $seconds -= $minutes*60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    // Export PC & Shop Level
    private function _exportPcShopLevel($params) {

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        $d1 = explode('/', $params['from']);
        $params['from'] = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $params['to'] = $d2[2].'-'.$d2[1].'-'.$d2[0];

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $heads_01 = array(
            '','','','','','','','','','','','',
            'Perforomance','','','','','','',
            'Knowledge','',
            'Onboard','',
            '',
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads_01 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $heads_02 = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'Staff Level',

            'Grand Area',
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Status',
            'Store Level',
            'Market Name',

            'A (%)',
            'R (%)',
            'F (%)',
            'Unit Sellout',
            'Price Sellout',
            'Target',
            'Achieve (%)',

            // 'PC Active',
            'E-Test',

            'Work Days',
            'Work Hours',

            // 'Summary',

            'Index',
            'Addon Rate',
            'Remark',
        );

        $alpha = 'A';
        $index = 2;

        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->mergeCells('M1:S1');
        $sheet->mergeCells('T1:U1');
        $sheet->mergeCells('V1:W1');

        $sheet->getStyle("A1:Z2")->applyFromArray($style);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QTiming = new Application_Model_Timing();
        // $QGoodKpiLog = new Application_Model_GoodKpiLog();

        $QStaff = new Application_Model_Staff();
        $QOIT = new Application_Model_OppoIndividualTarget();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $QPcActive  = new Application_Model_PcActive();
        $QPcChk = new Application_Model_PcCheckInLog();
        $QPAE = new Application_Model_PcAddonException();

        // Get Data 
        $data = $QTiming->getPcShopLevel($params);

        $index = 3;

        for ($i=0;$i<count($data); $i++) {

            $market_name = $store_market[ $data[$i]['st_id'] ]['market_name'];

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_level']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['grand_area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_level']);
            $sheet->setCellValue($alpha++.$index, $market_name);

            // Get Commission + Sellout
            // $params['staff_code'] = $data[$i]['staff_code'];
            // $com_info = $QGoodKpiLog->report_kpiPC(null,$params);

            $pc_sellout = $sellout_a = $sellout_r = $sellout_f = $pc_target = $pc_achieve = $total_price ='';
            $shop_check = $pc_etest = $work_day = $work_hour = '';
            $sellout_info = $etest_info = $checkin_info = array();

            // Check If have PC
            if ( isset($data[$i]['staff_id']) && $data[$i]['staff_id'] ) {

                // $shop_check = $data[$i]['shop_check'];
                $pc_etest = $data[$i]['etest'];

                $params['staff_id'] = $data[$i]['staff_id'];
                $sellout_info = $QTiming->getSelloutForPcShopLevel($params);

                // echo "<pre>"; print_r($sellout_info); die;

                if ( !empty($sellout_info) ) {

                    $total_price = $sellout_info['total_price'];

                    $pc_sellout = $sellout_info['total_sellout'];
                    $sellout_a = round(($sellout_info['sellout_a'] / $pc_sellout)*100,1);
                    $sellout_r = round(($sellout_info['sellout_r'] / $pc_sellout)*100,1);
                    $sellout_f = round(($sellout_info['sellout_f'] / $pc_sellout)*100,1);

                } 

                $where = array();
                $where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                $where[] = $QOIT->getAdapter()->quoteInto('from_date <= ?', $params['from']);
                $where[] = $QOIT->getAdapter()->quoteInto('to_date >= ?', $params['to']);
                $target_info = $QOIT->fetchRow($where);

                if ( !empty($target_info) ) {
                    $pc_target = $target_info['target_price'];

                    if ( !empty($sellout_info) ) {
                        $pc_achieve = round( ( $total_price / $pc_target ) * 100 , 2);
                    }
                }
/*
                // Get E-Test
                $etest_info = $QStaff->getETestByPC($params);

                $pc_etest = '';
                if ( !empty($etest_info) ) {
                    $tmp_pc_etest = ROUND(( $etest_info[0]['sum_answer_score'] / $etest_info[0]['sum_total_score'] ) * 100, 0);

                    if ( $tmp_pc_etest >= 90 ) { $pc_etest = 'Pass'; }
                    else { $pc_etest = 'Not Pass'; }
                }
*/
                // Get Check In By Staff 
                $params['group_id'] = PGPB_ID;
                $params['period_from'] = $params['from'];
                $params['period_to'] = $params['to'];
                $checkin_info = $QStaffCheckInLog->getCheckInList($params);
                
                if ( !empty($checkin_info) ) {

                    $work_day = count($checkin_info);

                    $sum_time = '00:00:00';
                    foreach ($checkin_info as $key => $value) {

                        if ( is_null($value['check_out']) || ($value['work_perform'] == 'Adjusted By System #01') ) {
                            // Do Nothing!
                        } else {

                            $start_date = new DateTime($value['check_in']);
                            $time_diff = $start_date->diff(new DateTime($value['check_out']));

                            $time_temp = $time_diff->h.":".$time_diff->i.":".$time_diff->s;
                            $sum_time = $this->sum_the_time($sum_time, $time_temp);

                        }

                    }

                    // echo "SUM Time : ".$sum_time; 
                    // echo "<pre>"; print_r($checkin_info); die;

                    $tmp = explode(":", $sum_time);
                    $work_hour = round($tmp[0] / $work_day, 2);

                }


                // Calculate for Index Rate
                
                $pc_level = $etest = $addon_remark = '';;
                $addon_rate = $addon_flag = 1;

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
                $work_day_all = floor($diffdate/(60*60*24)) + 1; // plus 1 day

                $debug = "";
                $work_point = $val_ach = $grade_point = $achive = $com_index = 0;

                // Check Add-on 01 : Not PC Stand By
                if ( $data[$i]['pc_stand_by'] == 0 ) {

                    // Check Add-on 02 : Not New PC
                    if ( $work_day_all >= 30 ) {

                        // Check Add-on 03 : Check Number of Store 
                        if ( $sellout_info['cnt_store'] >= 1 ) {

                            $store_level = $data[$i]['st_level'];

                            // Check Add-on XX : PC Exception
                            if ( $sellout_info['cnt_store'] > 1 ) {

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

                            if ( $sellout_info['cnt_store'] == 1 ) { $addon_flag = 0; }

                            if ( $addon_flag == 0 ) { 
                                
                                // Check Add-on 04 : Check PC Target
                                if ( $pc_target != '' ) {

                                    $where = array();
                                    $where[] = $QPcActive->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at >= ?', $params['from']." 00:00:00");
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at <= ?', $params['to']." 23:59:59");

                                    $result_pc_grade = $QPcActive->fetchRow($where);

                                    // Check Add-on 05 : Check PC Grade
                                    if ( !empty($result_pc_grade) && $result_pc_grade['etest_count'] == 2 ) { 

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

                                        $work_hours = count($result_pc_chk);

                                        // Calculate Com Index 
                                        $work_point = -5;
                                        if ( $work_hours >= 25 ) { $work_point = 0; } 
                                        if ( $work_hours >= 28 ) { $work_point = 10; } 
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

                                        $val_ach = round( ( ($total_price * $pc_achieve) / 100 ) / 10000 , 2);

                                        $com_index = $work_point + $grade_point + $val_ach;

                                        if ( $store_level == '*S' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }
                                            if ($com_index > 70) { $addon_rate = 1.3; }

                                        }

                                        if ( $store_level == 'A' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }

                                        }

                                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ทดสอบไม่ครบ 2 ครั้ง'; }

                                } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ไม่มี PC Target'; } 

                            } else { $addon_remark = 'ไม่เข้าเงื่อนไข : Store > 1'; }

                        } else { $addon_remark = 'ไม่เข้าเงื่อนไข : None'; }

                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : PC ทำงานยังไม่ถึง 30 วัน'; }

                } else { $addon_remark = 'ไม่เข้าเงื่อนไข : เป็น PC Stand By'; }

            }

            $debug = 
                "   Staff ID : ".$data[$i]['staff_id']." | 
                    PC Stand By : ".$data[$i]['pc_stand_by']." | 
                    Cnt Store : ".$sellout_info['cnt_store']." | 
                    Store Level : ".$store_level." | 
                    Addon Flag : ".$addon_flag." | 
                    Target : ".$pc_target." | 
                    Sellout : ".$pc_sellout." | 
                    Price : ".$total_price." | 
                    Achieve : ".$pc_achieve." | 
                    Val_Ach : ".$val_ach." | 
                    Work Point : ".$work_point." | 
                    Grade Point : ".$grade_point." | 
                    Index : ".$com_index." 
                ";

            $sheet->setCellValue($alpha++.$index, $sellout_a);
            $sheet->setCellValue($alpha++.$index, $sellout_r);
            $sheet->setCellValue($alpha++.$index, $sellout_f);
            $sheet->setCellValue($alpha++.$index, $pc_sellout);
            $sheet->setCellValue($alpha++.$index, $total_price);
            $sheet->setCellValue($alpha++.$index, $pc_target);
            $sheet->setCellValue($alpha++.$index, $pc_achieve);

            // $sheet->setCellValue($alpha++.$index, $shop_check);
            $sheet->setCellValue($alpha++.$index, $pc_etest);

            $sheet->setCellValue($alpha++.$index, $work_day);
            $sheet->setCellValue($alpha++.$index, $work_hour);

            // $sheet->setCellValue($alpha++.$index, '');

            $sheet->setCellValue($alpha++.$index, $com_index);
            $sheet->setCellValue($alpha++.$index, $addon_rate);
            $sheet->setCellValue($alpha++.$index, $addon_remark);
            $sheet->setCellValue($alpha++.$index, $debug);

            $index++;

        }

        $filename = 'PC_Shop_Level_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export PC & Shop Level By Area
    private function _exportShopLevelByArea($params) {

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        $d1 = explode('/', $params['from']);
        $params['from'] = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $params['to'] = $d2[2].'-'.$d2[1].'-'.$d2[0];

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $heads = array(
            'Area Name',
            '*S','(%)',
            'A','(%)',
            'B','(%)',
            'C','(%)',
            'Sum',
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:J1")->applyFromArray($style);

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getShopLevelByArea($params);

        $index = 2;
        $achieve_s = $achieve_s = $achieve_s = $achieve_s = 0;

        for ($i=0;$i<count($data); $i++) {

            $achieve_s = round( ( $data[$i]['cnt_s'] / $data[$i]['cnt_store'] ) * 100 , 0);
            $achieve_a = round( ( $data[$i]['cnt_a'] / $data[$i]['cnt_store'] ) * 100 , 0);
            $achieve_b = round( ( $data[$i]['cnt_b'] / $data[$i]['cnt_store'] ) * 100 , 0);
            $achieve_c = round( ( $data[$i]['cnt_c'] / $data[$i]['cnt_store'] ) * 100 , 0);

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_s']);
            $sheet->setCellValue($alpha++.$index, $achieve_s);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_a']);
            $sheet->setCellValue($alpha++.$index, $achieve_a);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_b']);
            $sheet->setCellValue($alpha++.$index, $achieve_b);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_c']);
            $sheet->setCellValue($alpha++.$index, $achieve_c);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store']);

            $index++;

        }

        $filename = 'Shop_Level_By_Area_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export KA Sellout Details By Store, Model, Color compare Last Month
    private function _exportKaSelloutDetails($data,$params) {

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        if (is_array($params['selected_date'])) {
            $month_now = date('M-Y', strtotime($params['selected_date'][0]));
            $month_last = date('M-Y', strtotime("-1 Month", strtotime($params['selected_date'][0])));
        } else {
            $month_now = date('M-Y', strtotime($params['selected_date']));
            $month_last = date('M-Y', strtotime("-1 Month", strtotime($params['selected_date'])));
        }

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $heads = array(
            'Area',
            'Store ID',
            'Store Name',
            'Store Status',
            'Store Rank',
            'Store Type',
            'Shop ID',
            'Shop Code',
            'Market Type',
            'Market Name',
            'Model Code',
            'Model Name',
            'Color',
            'Timing Day',
            'Price '.$month_now,
            'Price '.$month_last,
            $month_now,
            $month_last,
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:Q1")->applyFromArray($style);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_rank']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_shopid']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_shopcode']);
            $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_type']);
            $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_desc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_color']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['timing_day']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['sum_price_now']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sum_price_last']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_now']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_last']);
            $index++;

        }

        $filename = 'KA_Sellout_Details_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : Operator Stock
    private function _exportExcelOprStock($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $heads = array(
            'No.',
            'Distributor ID',
            'Distributor Name',
            'Model Code',
            'Model Name',
            'Sellin',
            'Sellout',
            'Stock',
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:H1")->applyFromArray($style);

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getOperatorStock($params);

        $index = 2;
        $cnt = 0;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['model_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['model_desc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellin']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['stock']);
            $index++;

        }

        $filename = 'Operator_Stock_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Weekly Report : Achieve Report
    private function _exportExcelAchieveReport($params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $month_text = date('d M Y', strtotime($from));
        $time = date("H:i", strtotime(date('Y-m-d H:i:s')));

        $heads_01 = array("Report Sellout ".$month_text." By Catty System",'','');

        $alpha = 'A';
        $index = 1;

        foreach($heads_01 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $heads_02 = array(
            'No.',
            'Area',
            // 'GFK',
            // 'Target',
            // 'Sellout',
            '%Achieved',
        );

        $alpha = 'A';
        $index = 2;

        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $heads_03 = array('','',$time);

        $alpha = 'A';
        $index = 3;

        foreach($heads_03 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->mergeCells("A1:C1");
        $sheet->mergeCells("A2:A3");
        $sheet->mergeCells("B2:B3");

        $QTiming = new Application_Model_Timing();
        $data = $QTiming->getSelloutForAchieveReport($params);

        $total_target = $params['ach_target'];

        $index = 4;
        $cnt = 0;
        $sum_sellout = 0;

        for ($i=0;$i<count($data); $i++) {

            $area_target = round( ($data[$i]['area_gfk'] * $total_target) / 100, 0);
            $area_achieve = round( ($data[$i]['sellout'] / $area_target) * 100, 2);

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['area_gfk']);
            // $sheet->setCellValue($alpha++.$index, $area_target);
            // $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $area_achieve."%");
            $index++;

            $sum_sellout = $sum_sellout + $data[$i]['sellout'];
        }

        $total_achieve = round( ($sum_sellout / $total_target) * 100, 2);

        // Last Row
        $alpha = 'A';
        $sheet->setCellValue($alpha++.$index, "Total");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $total_achieve."%");

        $sheet->mergeCells("A".$index.":B".$index);
        $sheet->getStyle("A1:C".$index)->applyFromArray($style);

        $filename = 'Achieve_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // KR Sellout : Export Excel 
    private function _exportKrSellout($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $d1 = explode('/', $params['from']);
        $d2 = explode('/', $params['to']);

        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $month = date('M', strtotime($to));
        $month_last = date('M', strtotime("-31 Days", strtotime($to)));

        $month_text = $d1[0]."-".$d2[0]." ".$month;
        $month_last_text = $d1[0]."-".$d2[0]." ".$month_last;

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Distributor ID',
            'Distributor Name',
            $month_last_text,
            $month_text,
            'Diff',
            'Growth',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:G1")->applyFromArray($style);

        $index = 2;
        $sum_sellout = $sum_sellout_last = $sellout_diff = $growth_rate = 0;
       
        for ($i=0;$i<count($data); $i++) {

            $sellout_diff = $data[$i]['sellout'] - $data[$i]['sellout_last'];
            $growth_rate = round( ( $sellout_diff / $data[$i]['sellout_last'] ) * 100, 0);

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_last']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $sellout_diff);
            $sheet->setCellValue($alpha++.$index, $growth_rate."%");

            $sum_sellout_last += $data[$i]['sellout_last'];
            $sum_sellout += $data[$i]['sellout'];

            $index++;
        }

        $sellout_diff = $sum_sellout - $sum_sellout_last;
        $growth_rate = round( ( $sellout_diff / $sum_sellout_last ) * 100, 0);

        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, "Grand Total");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_sellout_last);
        $sheet->setCellValue($alpha++.$index, $sum_sellout);
        $sheet->setCellValue($alpha++.$index, $sellout_diff);
        $sheet->setCellValue($alpha++.$index, $growth_rate."%");

        $sheet->mergeCells("A".$index.":C".$index);
        $sheet->getStyle("A".$index.":C".$index)->applyFromArray($style);
        
        $filename = 'KR_Sellout_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // KR Sellout : Export Product Report 
    private function _exportKrProductReport($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $d1 = explode('/', $params['from']);
        $d2 = explode('/', $params['to']);

        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $month = date('M', strtotime($to));
        $month_text = $d1[0]."-".$d2[0]." ".$month;
        $today_text = $d2[0]." ".$month;

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Model Code',
            'Model Name',
            $today_text,
            $month_text,
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:E1")->applyFromArray($style);

        $index = 2;
        $sum_sellout = $sum_sellout_today = 0;
       
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_desc']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_today']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);

            $sum_sellout_today += $data[$i]['sellout_today'];
            $sum_sellout += $data[$i]['sellout'];

            $index++;
        }

        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, "Total");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, "");
        $sheet->setCellValue($alpha++.$index, $sum_sellout_today);
        $sheet->setCellValue($alpha++.$index, $sum_sellout);

        $sheet->mergeCells("A".$index.":C".$index);
        $sheet->getStyle("A".$index.":C".$index)->applyFromArray($style);
        
        $filename = 'KR_Product_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    public function apiGetPcComAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_id = $this->getRequest()->getParam('staff_id');

        if (isset($staff_id) && $staff_id) {

            $params = array(
                'staff_id' => $staff_id,
                'from'     => date('Y-m-01'),
                'to'       => date('Y-m-t'),
            );

            $QGoodKpiLog = new Application_Model_GoodKpiLog();
            $result = $QGoodKpiLog->report_kpiPC(null,$params);

            // echo "<pre>"; print_r($result);
            echo json_encode($result);

        } else {

            echo "No Input";
        }
        
    }

    // Store Pre-Order : Export Store Pre-Order 
    private function _exportStorePreOrderList($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Area',
            'RD Name',
            'Store ID',
            'Store Name',
            'Store Type',
            'Status',
            'Pre-Order Type',
            'Product',
            'Unit',
            'Sellout',
            'Sellout Pre-Order',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $QAsm = new Application_Model_Asm();

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $result_rd = $QAsm->getStaffByAreaID($data[$i]['area_id'], 1);

            $cnt = $i + 1;

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $result_rd[0]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pre_order_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['unit']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout_pre']);

            $index++;
        }
        
        $filename = 'Store_PreOrder_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Factory Report By Week : Export By Store Type 
    private function _exportSelloutByStoreType($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads_01 = array(
            'No.',
            'Model Code',
            'Model Name',
            'Channel',
            'Store Type',
        );

        for ($i=0;$i<$params['week_no'];$i++) {
            $week_txt = $i+1;
            $heads_02[$i] = 'Week #'.$week_txt;
        }

        array_push($heads_02, 'Total');

        $heads = array_merge($heads_01,$heads_02);

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A".$index.":".$alpha.$index)->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['channel_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_type']);

            for ($j=1;$j<=$params['week_no'];$j++) {
                $sheet->setCellValue($alpha++.$index, $data[$i]['W'.$j]);
            }

            $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);

            $index++;
        }
        
        $filename = 'Sellout_By_StoreType_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Acc Stock Report Log 
    private function _exportStockLogList($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Model Code',
            'Model Name',
            'Color',
            'IN/OUT',
            'Unit',
            'Record By [Staff Code]',
            'Record By [Staff Name]',
            'Record Date',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A".$index.":".$alpha.$index)->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $record_type = 'OUT';
            if ( $data[$i]['record_type'] == 1 ) { $record_type = 'IN'; } 

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $sheet->setCellValue($alpha++.$index, $record_type);
            $sheet->setCellValue($alpha++.$index, $data[$i]['unit']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['record_date']);

            $index++;
        }
        
        $filename = 'ACC_Stock_Log_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Acc Stock Report
    private function _exportAccStock($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Model Code',
            'Model Name',
            'Color',
            'Stock',
            'Last Updated Date',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A".$index.":".$alpha.$index)->applyFromArray($style);

        $index = 2;

        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['stock']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['updated_at']);

            $index++;
        }
        
        $filename = 'ACC_Stock_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // PC & Shop Level : Summary PC Shop Index S, A
    private function _exportSummaryIndex($params, $flag) {

        $d1 = explode('/', $params['from']);
        $params['from'] = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $params['to'] = $d2[2].'-'.$d2[1].'-'.$d2[0];

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $QTiming = new Application_Model_Timing();

        $QStaff = new Application_Model_Staff();
        $QOIT = new Application_Model_OppoIndividualTarget();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $QPcActive  = new Application_Model_PcActive();
        $QPcChk = new Application_Model_PcCheckInLog();
        $QPAE = new Application_Model_PcAddonException();

        $params['store_type'] = '';
        $params['flag'] = $flag;

        $staff_list = $QTiming->getPcShopLevel($params);

        $temp = $temp2 = array();
        $store_bkk_s = $store_bkk_a = 0;
        $store_upc_s = $store_upc_a = 0;
        $i = 0;

        foreach ($staff_list as $key => $value) {

            $temp[$value['area_name']][] = $value;

            $temp2[$i]['area_name'] = $value['area_name'];
            $temp2[$i]['st_id'] = $value['st_id'];
            $temp2[$i]['st_level'] = $value['st_level'];

            $i++;
        }

        $temp2 = array_map("unserialize", array_unique(array_map("serialize", $temp2)));
        $temp2 = array_values($temp2);

        foreach ($temp2 as $key => $value) {
            if (strpos($value['area_name'], 'BKK') !== false) { 
                if ( $value['st_level'] == '*S' ) { $store_bkk_s = $store_bkk_s + 1; }
                if ( $value['st_level'] == 'A' ) { $store_bkk_a = $store_bkk_a + 1; }
            } else { 
                if ( $value['st_level'] == '*S' ) { $store_upc_s = $store_upc_s + 1; }
                if ( $value['st_level'] == 'A' ) { $store_upc_a = $store_upc_a + 1; }
            }
        }

        ksort($temp);

        // echo "BKK : ".$store_bkk_s." | ".$store_bkk_a; echo "<br/>";
        // echo "UPC : ".$store_upc_s." | ".$store_upc_a; echo "<br/>";

        // echo "<pre>"; print_r($staff_list); 
        // echo "<pre>"; print_r($temp); die;

        $focus_level = array('*S','A');
        $data = array();

        foreach ($temp as $key => $value) {

            $s_more_100 = $s_more_70 = $s_less_70 = $s_less_50 = $s_less_30 = $cnt_pc_s = 0;
            $a_more_100 = $a_more_70 = $a_less_70 = $a_less_50 = $a_less_30 = $cnt_pc_a = 0;

            for ($i=0;$i<count($value);$i++) {

                $pc_sellout = $sellout_a = $sellout_r = $sellout_f = $pc_target = $pc_achieve = $total_price ='';
                $shop_check = $pc_etest = $work_day = $work_hour = '';
                $sellout_info = $etest_info = $checkin_info = array();

                $shop_check = $value[$i]['shop_check'];

                if ( isset($value[$i]['staff_id']) && $value[$i]['staff_id'] != '' ) {

                    $params['staff_id'] = $value[$i]['staff_id'];
                    $sellout_info = $QTiming->getSelloutForPcShopLevel($params);

                    // echo "<pre>"; print_r($sellout_info); die;

                    if ( !empty($sellout_info) ) {

                        $total_price = $sellout_info['total_price'];

                        $pc_sellout = $sellout_info['total_sellout'];
                        $sellout_a = round(($sellout_info['sellout_a'] / $pc_sellout)*100,1);
                        $sellout_r = round(($sellout_info['sellout_r'] / $pc_sellout)*100,1);
                        $sellout_f = round(($sellout_info['sellout_f'] / $pc_sellout)*100,1);

                    } 

                    $where = array();
                    $where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                    $where[] = $QOIT->getAdapter()->quoteInto('from_date <= ?', $params['from']);
                    $where[] = $QOIT->getAdapter()->quoteInto('to_date >= ?', $params['to']);
                    $target_info = $QOIT->fetchRow($where);

                    // echo "<pre>"; print_r($target_info); die;

                    if ( !empty($target_info) ) {
                        $pc_target = $target_info['target_price'];

                        if ( !empty($sellout_info) ) {
                            $pc_achieve = round( ( $total_price / $pc_target ) * 100 , 2);
                        }
                    }

                    // Get Check In By Staff 
                    $params['group_id'] = PGPB_ID;
                    $params['period_from'] = $params['from'];
                    $params['period_to'] = $params['to'];
                    $checkin_info = $QStaffCheckInLog->getCheckInList($params);

                    if ( !empty($checkin_info) ) {

                        $work_day = count($checkin_info);

                        $sum_time = '00:00:00';
                        foreach ($checkin_info as $key2 => $value2) {

                            if ( is_null($value2['check_out']) || ($value2['work_perform'] == 'Adjusted By System #01') ) {
                                // Do Nothing!
                            } else {

                                $start_date = new DateTime($value2['check_in']);
                                $time_diff = $start_date->diff(new DateTime($value2['check_out']));

                                $time_temp = $time_diff->h.":".$time_diff->i.":".$time_diff->s;
                                $sum_time = $this->sum_the_time($sum_time, $time_temp);

                            }

                        }

                        // echo "SUM Time : ".$sum_time; 
                        // echo "<pre>"; print_r($checkin_info); die;

                        $tmp = explode(":", $sum_time);
                        $work_hour = round($tmp[0] / $work_day, 2);

                    }

                     // Calculate for Index Rate
                        
                    $pc_level = $etest = $addon_remark = '';;
                    $addon_rate = $addon_flag = 1;

                    // Calculate Work Day
                    $staff_created = $value[$i]['staff_joined'];
                    $staff_offdate = $value[$i]['staff_offdate'];
                    
                    if (!is_null($staff_offdate) && $staff_offdate <= $params['to']." 23:59:59") { 
                        $now = strtotime($staff_offdate); 
                        $created_at = strtotime($staff_created);
                    } else { 
                        $now = strtotime($params['to']." 23:59:59"); 
                        $created_at = strtotime($staff_created);
                    }

                    $diffdate = $now - $created_at;
                    $work_day_all = floor($diffdate/(60*60*24)) + 1; // plus 1 day

                    $work_point = $val_ach = $grade_point = $achive = $com_index = 0;

                    // Check Add-on 02 : Not New PC
                    if ( $work_day_all >= 30 ) {

                        // Check Add-on 03 : Check Number of Store 
                        if ( $sellout_info['cnt_store'] >= 1 ) {

                            $store_level = $value[$i]['st_level'];

                            // Check Add-on XX : PC Exception
                            if ( $sellout_info['cnt_store'] > 1 ) {

                                $params_addon = array(
                                    'staff_id'  => $value[$i]['staff_id'],
                                    'from'      => $params['from'],
                                    'to'        => $params['to'],
                                );

                                $result_pae = $QPAE->getPcExceptionStore($params_addon);

                                if ( !empty($result_pae) ) {
                                    $store_level = $result_pae['st_level'];
                                    $addon_flag = 0;
                                }
                                
                            } 

                            if ( $sellout_info['cnt_store'] == 1 ) { $addon_flag = 0; }

                            if ( $addon_flag == 0 ) { 
                                
                                // Check Add-on 04 : Check PC Target
                                if ( $pc_target != '' ) {

                                    $where = array();
                                    $where[] = $QPcActive->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at >= ?', $params['from']." 00:00:00");
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at <= ?', $params['to']." 23:59:59");

                                    $result_pc_grade = $QPcActive->fetchRow($where);

                                    // Check Add-on 05 : Check PC Grade
                                    if ( !empty($result_pc_grade) && $result_pc_grade['etest_count'] == 2 ) { 

                                        // $pc_level = $result_pc_grade['grade'];
                                        $etest = $result_pc_grade['etest_result'];

                                        // Check Add-on 06 : Check PC Work Hour
                                        $where = array();
                                        $where[] = $QPcChk->getAdapter()->quoteInto('status = ?', 'Y');
                                        $where[] = $QPcChk->getAdapter()->quoteInto('action_id = ?', 1);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in >= ?', $params['from']." 00:00:00");
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in <= ?', $params['to']." 23:59:59");

                                        $result_pc_chk = $QPcChk->fetchAll($where);

                                        $work_hours = count($result_pc_chk);

                                        // Calculate Com Index 
                                        $work_point = -5;
                                        if ( $work_hours >= 25 ) { $work_point = 0; } 
                                        if ( $work_hours >= 28 ) { $work_point = 10; } 
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

                                        $val_ach = round( ( ($total_price * $pc_achieve) / 100 ) / 10000 , 2);

                                        $com_index = $work_point + $grade_point + $val_ach;

                                        // echo $store_level." - ".$com_index."<br/>";

                                        if ( $store_level == '*S' ) { 

                                            // if ($com_index < 30) { $addon_rate = 0.8; }
                                            // if ($com_index > 60) { $addon_rate = 1.2; }
                                            // if ($com_index > 70) { $addon_rate = 1.3; }

                                            if ($com_index > 100) { $s_more_100 += 1; }
                                            else if ($com_index >= 70) { $s_more_70 += 1; }

                                            if ($com_index < 30) { $s_less_30 += 1; }
                                            else if ($com_index <= 60) { $s_less_50 += 1; }
                                            else if ($com_index < 70) { $s_less_70 += 1; }
                                            
                                            $cnt_pc_s += 1;
                                        }

                                        if ( $store_level == 'A' ) { 

                                            // if ($com_index < 30) { $addon_rate = 0.8; }
                                            // if ($com_index > 60) { $addon_rate = 1.2; }

                                            if ($com_index > 100) { $a_more_100 += 1; }
                                            else if ($com_index >= 70) { $a_more_70 += 1; }

                                            if ($com_index < 30) { $a_less_30 += 1; }
                                            else if ($com_index <= 60) { $a_less_50 += 1; }
                                            else if ($com_index < 70) { $a_less_70 += 1; }

                                            $cnt_pc_a += 1;
                                        }

                                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ทดสอบไม่ครบ 2 ครั้ง'; }

                                } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ไม่มี PC Target'; } 

                            } else { $addon_remark = 'ไม่เข้าเงื่อนไข : Store > 1'; }

                        } else { $addon_remark = 'ไม่เข้าเงื่อนไข : None'; }

                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : PC ทำงานยังไม่ถึง 30 วัน'; }

                }

            }

            for ($i=0;$i<count($focus_level); $i++) {

                if (strpos($key, 'BKK') !== false) { $data[$key][$i]['zone'] = 'BKK'; }
                else { $data[$key][$i]['zone'] = 'UPC'; }
                
                $data[$key][$i]['area_name'] = $key;
                $data[$key][$i]['shop_level'] = $focus_level[$i];

                switch ($focus_level[$i]) {
                    case '*S':
                        $data[$key][$i]['cnt_pc'] = $cnt_pc_s;
                        $data[$key][$i]['less_30'] = $s_less_30;
                        $data[$key][$i]['less_50'] = $s_less_50;
                        $data[$key][$i]['less_70'] = $s_less_70;
                        $data[$key][$i]['more_70'] = $s_more_70;
                        $data[$key][$i]['more_100'] = $s_more_100;
                        break;
                    case 'A':
                        $data[$key][$i]['cnt_pc'] = $cnt_pc_a;
                        $data[$key][$i]['less_30'] = $a_less_30;
                        $data[$key][$i]['less_50'] = $a_less_50;
                        $data[$key][$i]['less_70'] = $a_less_70;
                        $data[$key][$i]['more_70'] = $a_more_70;
                        $data[$key][$i]['more_100'] = $a_more_100;
                        break;
                    default: break;
                }

            }

        }

        $data_bkk = $data_upc = $data_all = array();
        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {

                if ( $value2['zone'] == 'BKK' ) {

                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['zone']          = $value2['zone'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['shop_level']    = $value2['shop_level'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['cnt_pc']        += $value2['cnt_pc'];

                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['less_30']       += $value2['less_30'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['less_50']       += $value2['less_50'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['less_70']       += $value2['less_70'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['more_70']       += $value2['more_70'];
                    $data_bkk[ $value2['zone']."|".$value2['shop_level'] ]['more_100']      += $value2['more_100'];

                } else {

                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['zone']          = $value2['zone'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['shop_level']    = $value2['shop_level'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['cnt_pc']        += $value2['cnt_pc'];

                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['less_30']       += $value2['less_30'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['less_50']       += $value2['less_50'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['less_70']       += $value2['less_70'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['more_70']       += $value2['more_70'];
                    $data_upc[ $value2['zone']."|".$value2['shop_level'] ]['more_100']      += $value2['more_100'];

                }

                $data_all[ "All|".$value2['shop_level'] ]['zone']       = 'All';
                $data_all[ "All|".$value2['shop_level'] ]['shop_level'] = $value2['shop_level'];
                $data_all[ "All|".$value2['shop_level'] ]['cnt_pc']     += $value2['cnt_pc'];

                $data_all[ "All|".$value2['shop_level'] ]['less_30']    += $value2['less_30'];
                $data_all[ "All|".$value2['shop_level'] ]['less_50']    += $value2['less_50'];
                $data_all[ "All|".$value2['shop_level'] ]['less_70']    += $value2['less_70'];
                $data_all[ "All|".$value2['shop_level'] ]['more_70']    += $value2['more_70'];
                $data_all[ "All|".$value2['shop_level'] ]['more_100']   += $value2['more_100'];

            }
        }

        // echo "End Result<br/><pre>"; print_r($data); 
        // echo "End Result<br/><pre>"; print_r($data_bkk);
        // echo "End Result<br/><pre>"; print_r($data_upc); 
        // echo "End Result<br/><pre>"; print_r($data_all); die;

        $data_bkk = array_values($data_bkk);
        $data_upc = array_values($data_upc);
        $data_all = array_values($data_all);

        // Start Excel 
        $title_list = array('BKK','UPC','All Thailand');
        
        for ($i=0;$i<count($title_list);$i++) {

            $data = array();

            switch ($title_list[$i]) {
                case 'BKK': 
                    $index_title = 1; 
                    $index_head = 2; 
                    $index = 3; 
                    $data = $data_bkk;
                    break;
                case 'UPC': 
                    $index_title = 6; 
                    $index_head = 7; 
                    $index = 8; 
                    $data = $data_upc;
                    break;
                default: 
                    $index_title = 11; 
                    $index_head = 12; 
                    $index = 13; 
                    $data = $data_all;
                    break;
            }

            $heads_01 = array($title_list[$i],'','','','','','','','','','','','');
            $alpha = 'A';

            foreach($heads_01 as $key) {
                $sheet->setCellValue($alpha.$index_title, $key);
                $alpha++;
            }

            $sheet->mergeCells("A".$index_title.":M".$index_title);

            $heads_02 = array(
                'Shop','Shop No.','PC No.',
                '< 30','Achieve',
                '<= 60','Achieve',
                '< 70','Achieve',
                '>= 70','Achieve',
                '> 100','Achieve',
            );

            $alpha = 'A';

            foreach($heads_02 as $key) {
                $sheet->setCellValue($alpha.$index_head, $key);
                $alpha++;
            }

            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            );

            $sheet->getStyle("A".$index_title.":".$alpha.$index_head)->applyFromArray($style);
                
            for ($j=0;$j<count($data); $j++) {

                switch ($title_list[$i]) {
                    case 'BKK': $cnt_store = ($data[$j]['shop_level'] == '*S') ? $store_bkk_s : $store_bkk_a; break;
                    case 'UPC': $cnt_store = ($data[$j]['shop_level'] == '*S') ? $store_upc_s : $store_upc_a; break;
                    default: $cnt_store = ($data[$j]['shop_level'] == '*S') ? $store_bkk_s + $store_upc_s : $store_bkk_a + $store_upc_a; break;
                }

                $achive_less_30 = round( ($data[$j]['less_30'] / $data[$j]['cnt_pc']) * 100, 2);
                $achive_less_50 = round( ($data[$j]['less_50'] / $data[$j]['cnt_pc']) * 100, 2);
                $achive_less_70 = round( ($data[$j]['less_70'] / $data[$j]['cnt_pc']) * 100, 2);
                $achive_more_70 = round( ($data[$j]['more_70'] / $data[$j]['cnt_pc']) * 100, 2);
                $achive_more_100 = round( ($data[$j]['more_100'] / $data[$j]['cnt_pc']) * 100, 2);

                $alpha = 'A';
                $sheet->setCellValue($alpha++.$index, $data[$j]['shop_level']);
                $sheet->setCellValue($alpha++.$index, $cnt_store);
                $sheet->setCellValue($alpha++.$index, $data[$j]['cnt_pc']);

                $sheet->setCellValue($alpha++.$index, $data[$j]['less_30']);
                $sheet->setCellValue($alpha++.$index, number_format($achive_less_30,2));

                $sheet->setCellValue($alpha++.$index, $data[$j]['less_50']);
                $sheet->setCellValue($alpha++.$index, number_format($achive_less_50,2));

                $sheet->setCellValue($alpha++.$index, $data[$j]['less_70']);
                $sheet->setCellValue($alpha++.$index, number_format($achive_less_70,2));

                $sheet->setCellValue($alpha++.$index, $data[$j]['more_70']);
                $sheet->setCellValue($alpha++.$index, number_format($achive_more_70,2));

                $sheet->setCellValue($alpha++.$index, $data[$j]['more_100']);
                $sheet->setCellValue($alpha++.$index, number_format($achive_more_100,2));

                $index++;
            }

        }

        $filename = 'Summary_PC_Shop_Index_Report'.date('d/m/Y'); 
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // PC & Shop Level : Export PC Shop Index S, A By Area 
    private function _exportPCShopIndex($params, $flag) {

        $d1 = explode('/', $params['from']);
        $params['from'] = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $params['to'] = $d2[2].'-'.$d2[1].'-'.$d2[0];

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $QArea = new Application_Model_Area();
        $QTiming = new Application_Model_Timing();
        $QStoreStaff = new Application_Model_StoreStaff();

        $QStaff = new Application_Model_Staff();
        $QOIT = new Application_Model_OppoIndividualTarget();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $QPcActive  = new Application_Model_PcActive();
        $QPcChk = new Application_Model_PcCheckInLog();
        $QPAE = new Application_Model_PcAddonException();

        $params['store_type'] = '';
        $params['flag'] = $flag;

        $staff_list = $QTiming->getPcShopLevel($params);

        $temp = $temp2 = $temp3 = array();
        $store_bkk_s = $store_bkk_a = 0;
        $store_upc_s = $store_upc_a = 0;
        $i = 0;

        foreach ($staff_list as $key => $value) {

            $temp[$value['area_name']][] = $value;

            $cnt_pc = $QStoreStaff->getPC($value['st_id'], null);

            $temp2[$i]['area_name'] = $value['area_name'];
            $temp2[$i]['st_id'] = $value['st_id'];
            $temp2[$i]['st_level'] = $value['st_level'];
            $temp2[$i]['cnt_pc'] = count($cnt_pc);

            $i++;
        }

        $temp2 = array_map("unserialize", array_unique(array_map("serialize", $temp2)));
        $temp2 = array_values($temp2);

        foreach ($temp2 as $key => $value) {
            $temp3[$value['area_name']][$value['st_level']."|PC"] += $value['cnt_pc'];
            $temp3[$value['area_name']][$value['st_level']] += 1;
        }

        ksort($temp);

        // echo "BKK : ".$store_bkk_s." | ".$store_bkk_a; echo "<br/>";
        // echo "UPC : ".$store_upc_s." | ".$store_upc_a; echo "<br/>";

        // echo "<pre>"; print_r($staff_list); 
        // echo "<pre>"; print_r($temp); 
        // echo "<pre>"; print_r($temp2); 
        // echo "<pre>"; print_r($temp3); die;

        $where = array();
        $where[] = $QArea->getAdapter()->quoteInto('id NOT IN (?)', array(48,49,72));
        if ( !empty($params['area_id']) ) { $where[] = $QArea->getAdapter()->quoteInto('id IN (?)', $params['area_id']); }

        $area_list = $QArea->fetchAll($where,'name');

        $data = array();

        foreach ($temp as $key => $value) {

            $s_more_100 = $s_more_70 = $s_less_70 = $s_less_50 = $s_less_30 = $cnt_pc_s = 0;
            $a_more_100 = $a_more_70 = $a_less_70 = $a_less_50 = $a_less_30 = $cnt_pc_a = 0;

            for ($i=0;$i<count($value);$i++) {

                $pc_sellout = $sellout_a = $sellout_r = $sellout_f = $pc_target = $pc_achieve = $total_price ='';
                $shop_check = $pc_etest = $work_day = $work_hour = '';
                $sellout_info = $etest_info = $checkin_info = array();

                $shop_check = $value[$i]['shop_check'];

                if ( isset($value[$i]['staff_id']) && $value[$i]['staff_id'] != '' ) {

                    $params['staff_id'] = $value[$i]['staff_id'];
                    $sellout_info = $QTiming->getSelloutForPcShopLevel($params);

                    // echo "<pre>"; print_r($sellout_info); die;

                    if ( !empty($sellout_info) ) {

                        $total_price = $sellout_info['total_price'];

                        $pc_sellout = $sellout_info['total_sellout'];
                        $sellout_a = round(($sellout_info['sellout_a'] / $pc_sellout)*100,1);
                        $sellout_r = round(($sellout_info['sellout_r'] / $pc_sellout)*100,1);
                        $sellout_f = round(($sellout_info['sellout_f'] / $pc_sellout)*100,1);

                    } 

                    $where = array();
                    $where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                    $where[] = $QOIT->getAdapter()->quoteInto('from_date <= ?', $params['from']);
                    $where[] = $QOIT->getAdapter()->quoteInto('to_date >= ?', $params['to']);
                    $target_info = $QOIT->fetchRow($where);

                    // echo "<pre>"; print_r($target_info); die;

                    if ( !empty($target_info) ) {
                        $pc_target = $target_info['target_price'];

                        if ( !empty($sellout_info) ) {
                            $pc_achieve = round( ( $total_price / $pc_target ) * 100 , 2);
                        }
                    }

                    // Get Check In By Staff 
                    $params['group_id'] = PGPB_ID;
                    $params['period_from'] = $params['from'];
                    $params['period_to'] = $params['to'];
                    $checkin_info = $QStaffCheckInLog->getCheckInList($params);

                    if ( !empty($checkin_info) ) {

                        $work_day = count($checkin_info);

                        $sum_time = '00:00:00';
                        foreach ($checkin_info as $key2 => $value2) {

                            if ( is_null($value2['check_out']) || ($value2['work_perform'] == 'Adjusted By System #01') ) {
                                // Do Nothing!
                            } else {

                                $start_date = new DateTime($value2['check_in']);
                                $time_diff = $start_date->diff(new DateTime($value2['check_out']));

                                $time_temp = $time_diff->h.":".$time_diff->i.":".$time_diff->s;
                                $sum_time = $this->sum_the_time($sum_time, $time_temp);

                            }

                        }

                        // echo "SUM Time : ".$sum_time; 
                        // echo "<pre>"; print_r($checkin_info); die;

                        $tmp = explode(":", $sum_time);
                        $work_hour = round($tmp[0] / $work_day, 2);

                    }

                     // Calculate for Index Rate
                        
                    $pc_level = $etest = $addon_remark = '';;
                    $addon_rate = $addon_flag = 1;

                    // Calculate Work Day
                    $staff_created = $value[$i]['staff_joined'];
                    $staff_offdate = $value[$i]['staff_offdate'];
                    
                    if (!is_null($staff_offdate) && $staff_offdate <= $params['to']." 23:59:59") { 
                        $now = strtotime($staff_offdate); 
                        $created_at = strtotime($staff_created);
                    } else { 
                        $now = strtotime($params['to']." 23:59:59"); 
                        $created_at = strtotime($staff_created);
                    }

                    $diffdate = $now - $created_at;
                    $work_day_all = floor($diffdate/(60*60*24)) + 1; // plus 1 day

                    $work_point = $val_ach = $grade_point = $achive = $com_index = 0;

                    // Check Add-on 02 : Not New PC
                    if ( $work_day_all >= 30 ) {

                        // Check Add-on 03 : Check Number of Store 
                        if ( $sellout_info['cnt_store'] >= 1 ) {

                            $store_level = $value[$i]['st_level'];

                            // Check Add-on XX : PC Exception
                            if ( $sellout_info['cnt_store'] > 1 ) {

                                $params_addon = array(
                                    'staff_id'  => $value[$i]['staff_id'],
                                    'from'      => $params['from'],
                                    'to'        => $params['to'],
                                );

                                $result_pae = $QPAE->getPcExceptionStore($params_addon);

                                if ( !empty($result_pae) ) {
                                    $store_level = $result_pae['st_level'];
                                    $addon_flag = 0;
                                }
                                
                            } 

                            if ( $sellout_info['cnt_store'] == 1 ) { $addon_flag = 0; }

                            if ( $addon_flag == 0 ) { 
                                
                                // Check Add-on 04 : Check PC Target
                                if ( $pc_target != '' ) {

                                    $where = array();
                                    $where[] = $QPcActive->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at >= ?', $params['from']." 00:00:00");
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at <= ?', $params['to']." 23:59:59");

                                    $result_pc_grade = $QPcActive->fetchRow($where);

                                    // Check Add-on 05 : Check PC Grade
                                    if ( !empty($result_pc_grade) && $result_pc_grade['etest_count'] == 2 ) { 

                                        // $pc_level = $result_pc_grade['grade'];
                                        $etest = $result_pc_grade['etest_result'];

                                        // Check Add-on 06 : Check PC Work Hour
                                        $where = array();
                                        $where[] = $QPcChk->getAdapter()->quoteInto('status = ?', 'Y');
                                        $where[] = $QPcChk->getAdapter()->quoteInto('action_id = ?', 1);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('staff_id = ?', $value[$i]['staff_id']);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in >= ?', $params['from']." 00:00:00");
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in <= ?', $params['to']." 23:59:59");

                                        $result_pc_chk = $QPcChk->fetchAll($where);

                                        $work_hours = count($result_pc_chk);

                                        // Calculate Com Index 
                                        $work_point = -5;
                                        if ( $work_hours >= 25 ) { $work_point = 0; } 
                                        if ( $work_hours >= 28 ) { $work_point = 10; } 
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

                                        $val_ach = round( ( ($total_price * $pc_achieve) / 100 ) / 10000 , 2);

                                        $com_index = $work_point + $grade_point + $val_ach;

                                        // echo $store_level." - ".$com_index."<br/>";

                                        if ( $store_level == '*S' ) { 

                                            // if ($com_index < 30) { $addon_rate = 0.8; }
                                            // if ($com_index > 60) { $addon_rate = 1.2; }
                                            // if ($com_index > 70) { $addon_rate = 1.3; }

                                            if ($com_index > 100) { $s_more_100 += 1; }
                                            else if ($com_index >= 70) { $s_more_70 += 1; }

                                            if ($com_index < 30) { $s_less_30 += 1; }
                                            else if ($com_index <= 60) { $s_less_50 += 1; }
                                            else if ($com_index < 70) { $s_less_70 += 1; }
                                            
                                            $cnt_pc_s += 1;
                                        }

                                        if ( $store_level == 'A' ) { 

                                            // if ($com_index < 30) { $addon_rate = 0.8; }
                                            // if ($com_index > 60) { $addon_rate = 1.2; }

                                            if ($com_index > 100) { $a_more_100 += 1; }
                                            else if ($com_index >= 70) { $a_more_70 += 1; }

                                            if ($com_index < 30) { $a_less_30 += 1; }
                                            else if ($com_index <= 60) { $a_less_50 += 1; }
                                            else if ($com_index < 70) { $a_less_70 += 1; }

                                            $cnt_pc_a += 1;
                                        }

                                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ทดสอบไม่ครบ 2 ครั้ง'; }

                                } else { $addon_remark = 'ไม่เข้าเงื่อนไข : ไม่มี PC Target'; } 

                            } else { $addon_remark = 'ไม่เข้าเงื่อนไข : Store > 1'; }

                        } else { $addon_remark = 'ไม่เข้าเงื่อนไข : None'; }

                    } else { $addon_remark = 'ไม่เข้าเงื่อนไข : PC ทำงานยังไม่ถึง 30 วัน'; }

                }

            }
                
            $data[$key]['area_name']    = $key;

            $data[$key]['s_cnt_store']  = $temp3[$key]['*S'];
            $data[$key]['s_cnt_pc_all'] = $temp3[$key]['*S|PC'];
            $data[$key]['s_cnt_pc']     = $cnt_pc_s;
            $data[$key]['s_less_30']    = $s_less_30;
            $data[$key]['s_less_50']    = $s_less_50;
            $data[$key]['s_less_70']    = $s_less_70;
            $data[$key]['s_more_70']    = $s_more_70;
            $data[$key]['s_more_100']   = $s_more_100;

            $data[$key]['a_cnt_store']  = $temp3[$key]['A'];
            $data[$key]['a_cnt_pc_all'] = $temp3[$key]['A|PC'];
            $data[$key]['a_cnt_pc']     = $cnt_pc_a;
            $data[$key]['a_less_30']    = $a_less_30;
            $data[$key]['a_less_50']    = $a_less_50;
            $data[$key]['a_less_70']    = $a_less_70;
            $data[$key]['a_more_70']    = $a_more_70;
            $data[$key]['a_more_100']   = $a_more_100;

        }

        $data = array_values($data);

        // echo "End Result<br/><pre>"; print_r($data); die;

        // Start Excel 
        $heads_01 = array(
            'Area',
            'S','','','','','','','','','','','','',
            'A','','','','','','','','','','','','',
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads_01 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $heads_02 = array(
            '',
            'All Shop','All PC','PC No.','<30','Achieve','<=60','Achieve','<70','Achieve','>=70','Achieve','>100','Achieve',
            'All Shop','All PC','PC No.','<30','Achieve','<=60','Achieve','<70','Achieve','>=70','Achieve','>100','Achieve',
        );

        $alpha = 'A';
        $index = 2;

        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:N1');
        $sheet->mergeCells('O1:AA1');

        $sheet->getStyle("A1:".$alpha."2")->applyFromArray($style);

        $index = 3;
       
        for ($i=0;$i<count($data); $i++) {

            $s_achive_less_30 = round( ($data[$i]['s_less_30'] / $data[$i]['s_cnt_pc']) * 100, 2);
            $s_achive_less_50 = round( ($data[$i]['s_less_50'] / $data[$i]['s_cnt_pc']) * 100, 2);
            $s_achive_less_70 = round( ($data[$i]['s_less_70'] / $data[$i]['s_cnt_pc']) * 100, 2);
            $s_achive_more_70 = round( ($data[$i]['s_more_70'] / $data[$i]['s_cnt_pc']) * 100, 2);
            $s_achive_more_100 = round( ($data[$i]['s_more_100'] / $data[$i]['s_cnt_pc']) * 100, 2);

            $a_achive_less_30 = round( ($data[$i]['a_less_30'] / $data[$i]['a_cnt_pc']) * 100, 2);
            $a_achive_less_50 = round( ($data[$i]['a_less_50'] / $data[$i]['a_cnt_pc']) * 100, 2);
            $a_achive_less_70 = round( ($data[$i]['a_less_70'] / $data[$i]['a_cnt_pc']) * 100, 2);
            $a_achive_more_70 = round( ($data[$i]['a_more_70'] / $data[$i]['a_cnt_pc']) * 100, 2);
            $a_achive_more_100 = round( ($data[$i]['a_more_100'] / $data[$i]['a_cnt_pc']) * 100, 2);

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['s_cnt_store']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_cnt_pc_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_cnt_pc']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['s_less_30']);
            $sheet->setCellValue($alpha++.$index, number_format($s_achive_less_30,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_less_50']);
            $sheet->setCellValue($alpha++.$index, number_format($s_achive_less_50,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_less_70']);
            $sheet->setCellValue($alpha++.$index, number_format($s_achive_less_70,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_more_70']);
            $sheet->setCellValue($alpha++.$index, number_format($s_achive_more_70,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_more_100']);
            $sheet->setCellValue($alpha++.$index, number_format($s_achive_more_100,2));

            $sheet->setCellValue($alpha++.$index, $data[$i]['a_cnt_store']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_cnt_pc_all']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_cnt_pc']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['a_less_30']);
            $sheet->setCellValue($alpha++.$index, number_format($a_achive_less_30,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_less_50']);
            $sheet->setCellValue($alpha++.$index, number_format($a_achive_less_50,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_less_70']);
            $sheet->setCellValue($alpha++.$index, number_format($a_achive_less_70,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_more_70']);
            $sheet->setCellValue($alpha++.$index, number_format($a_achive_more_70,2));
            $sheet->setCellValue($alpha++.$index, $data[$i]['a_more_100']);
            $sheet->setCellValue($alpha++.$index, number_format($a_achive_more_100,2));

            $index++;
        }


        $filename = 'PC_Shop_Index_By_Area_Report'.date('d/m/Y'); 
        
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // PC & Shop Level : PC & Shop Number
    private function _exportPCShopNum($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        // Header #01 
        $heads_01 = array(
            'Region',
            'Leader',
            'Area',
            'S','','','',
            'A','','','',
        );

        $alpha = 'A';
        $index = 1;

        foreach($heads_01 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        // Header #02 
        $heads_02 = array(
            '','','',
            'Shop Num','PC Num','Total Shop','Total PC',
            'Shop Num','PC Num','Total Shop','Total PC',
        );

        $alpha = 'A';
        $index = 2;

        foreach($heads_02 as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."2")->applyFromArray($style);

        $sheet->mergeCells("A1:A2");
        $sheet->mergeCells("B1:B2");
        $sheet->mergeCells("C1:C2");

        $sheet->mergeCells("D1:G1");
        $sheet->mergeCells("H1:K1");

        $QAsm = new Application_Model_Asm();
        $QStoreStaff = new Application_Model_StoreStaff();

        $data = $QStoreStaff->getPCShopLevelByArea($params);

        $temp = array();
        foreach ($data as $key => $value) {
            $temp[$value['region']][] = $value;
        }

        foreach ($temp as $key => $value) {

            $total_store_s = $total_pc_s = 0;
            $total_store_a = $total_pc_a = 0;

            for ( $i=0;$i<count($value); $i++ ) { 

                $total_store_s  += $value[$i]['cnt_store_s'];
                $total_pc_s     += $value[$i]['cnt_pc_s'];
                $total_store_a  += $value[$i]['cnt_store_a'];
                $total_pc_a     += $value[$i]['cnt_pc_a'];

                $area_id = $value[$i]['area_id'];
            }

            $result_pcm_leader = $QAsm->getStaffByAreaID($area_id, 4);

            if ( !empty($result_pcm_leader) ) {
                $temp[$key]['pcm_leader'] = "[".$result_pcm_leader[0]['staff_code']."] ".$result_pcm_leader[0]['staff_name'];
            } else {
                $temp[$key]['pcm_leader'] = '';
            }
            
            $temp[$key]['total_store_s'] = $total_store_s;
            $temp[$key]['total_pc_s']    = $total_pc_s;
            $temp[$key]['total_store_a'] = $total_store_a;
            $temp[$key]['total_pc_a']    = $total_pc_a;

        }

        // echo "<pre>"; print_r($data); echo "<br/>";
        // echo "<pre>"; print_r($temp); echo "<br/>";

        $k = 0;
        foreach ($data as $key => $value) {

            $data[$k]['pcm_leader']    = $temp[ $value['region'] ]['pcm_leader'];
            $data[$k]['total_store_s'] = $temp[ $value['region'] ]['total_store_s'];
            $data[$k]['total_pc_s']    = $temp[ $value['region'] ]['total_pc_s'];
            $data[$k]['total_store_a'] = $temp[ $value['region'] ]['total_store_a'];
            $data[$k]['total_pc_a']    = $temp[ $value['region'] ]['total_pc_a'];

            $k++;
        }

        // echo "<pre>"; print_r($data); echo "<br/>";

        $index = 3;
        $pointer_start = 3;
        
        for ($i=0;$i<count($data); $i++) {

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['region']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pcm_leader']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_s']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc_s']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_store_s']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_pc_s']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_store_a']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_pc_a']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_store_a']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_pc_a']);

            if ( $data[$i]['region'] != $data[$i+1]['region'] ) {

                $sheet->mergeCells("A".$pointer_start.":A".$index);
                $sheet->mergeCells("B".$pointer_start.":B".$index);
                $sheet->mergeCells("F".$pointer_start.":F".$index);
                $sheet->mergeCells("G".$pointer_start.":G".$index);
                $sheet->mergeCells("J".$pointer_start.":J".$index);
                $sheet->mergeCells("K".$pointer_start.":K".$index);

                $sheet->getStyle("A".$pointer_start.":A".$index)->applyFromArray($style);
                $sheet->getStyle("B".$pointer_start.":B".$index)->applyFromArray($style);
                $sheet->getStyle("F".$pointer_start.":F".$index)->applyFromArray($style);
                $sheet->getStyle("G".$pointer_start.":G".$index)->applyFromArray($style);
                $sheet->getStyle("J".$pointer_start.":J".$index)->applyFromArray($style);
                $sheet->getStyle("K".$pointer_start.":K".$index)->applyFromArray($style);

                $pointer_start = $index + 1;
            }

            $index++;
        }

        $filename = 'PC_Shop_Level_Num_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Channel PC Status : Export List
    private function _exportChannelPcStatus($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Market Name',
            'Status',
            'PC',
        );

        for ($i=0;$i<$period_loop;$i++) {
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from))); 
            array_push($heads, $day_text);
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $QStoreStaff = new Application_Model_StoreStaff();

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $result_pc = $QStoreStaff->getPC($data[$i]['st_id'],0);
            $cnt_pc = count($result_pc);

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++.$index, $store_market[ $data[$i]['st_id'] ]['market_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++.$index, $cnt_pc);

            for ($j=0;$j<$period_loop;$j++) {
                $day_text =  date('d-M', strtotime("+".$j." Day", strtotime($from))); 
                $sheet->setCellValue($alpha++.$index, $data[$i][ $day_text ]);
            }

            $index++;
        }
        
        $filename = 'Channel_PC_Status_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : Trade-In Report
    private function _exportTradeInReport($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Area',
            'Store ID',
            'Store Name',
            'Store Type',
            'Staff Code',
            'Staff Name',
            'Group',
            'IMEI',
            'Product Code',
            'Product Name',
            'Color',
            'Price',
            'Timing Date',
            'IMEI (Trade-In)',
            'Brand Name',
            'Model Name',
            'ROM',
            'Grade',
            'Grade Discount',
            'Total Discount',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $QStoreStaff = new Application_Model_StoreStaff();

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['st_type']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_group']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['imei']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['product_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['price']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['timing_date']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['imei_turn']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['brand_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['model_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rom_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_grade']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_value']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_total']);

            $index++;
        }
        
        $filename = 'Trade_In_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : Imei Condition
    private function _exportImeiConditionReport($data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'IMEI (Trade-In)',
            'Brand Name',
            'Model Name',
            'ROM',
            'Turn On?',
            'iClound/Google Logout?',
            'Scratch?',
            'Scratch Number',
            'Scratch Size',
            'Device Problem',
            'Accessory',
            'Grade',
            'Grade Discount',
            'OPPO Support',
            'Shop Support',
            'Total Discount',
            'Partner Grade',
            'Partner Price',
            'Partner Check Date',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $QStoreStaff = new Application_Model_StoreStaff();

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['imei_turn']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['brand_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['model_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['rom_name']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['can_turn_on']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['can_logout']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_tytpe']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_no']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['s_size']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['problem_info']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['acc_info']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_grade']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_value']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['oppo_support']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['shop_support']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['eva_total']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['p_grade']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['p_price']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['p_date']);

            $index++;
        }
        
        $filename = 'IMEI_Condition_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : Area Daily Arrival Order
    private function _exportAreaDailyArrivalOrder($data,$params) {

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Area',
            'Distributor ID',
            'Distributor Name',
            'Address',
            'SO.',
            'Con.',
            'Created At',
            'Phone',
            'ACC',
            'Status',
            'Logistic',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $address = $data[$i]['address']." ".$data[$i]['district_name']." ".$data[$i]['amphure_name']." ".$data[$i]['provice_name']." ".$data[$i]['zipcode'];
            $status = ($data[$i]['kerry_status_code']=='POD') ? "Delivered" : "Pending";

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['d_name']);
            $sheet->setCellValue($alpha++.$index, $address);

            $sheet->setCellValue($alpha++.$index, $data[$i]['sn_ref']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['con_no']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['add_time']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_phone']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_acc']);
            $sheet->setCellValue($alpha++.$index, $status);
            $sheet->setCellValue($alpha++.$index, $data[$i]['company_logistics']);

            $index++;
        }
        
        $filename = 'Area_Daily_Arrival_Order_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : Online Sellout
    private function _exportOnlineSelloutReport($data,$params) {

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Channel',
            'Sellin',
            'Activated',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $index = 2;
       
        for ($i=0;$i<count($data); $i++) {

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, $cnt);
            $sheet->setCellValue($alpha++.$index, $data[$i]['channel']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sellin']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['activated']);

            $index++;
        }
        
        $filename = 'Online_Sellout_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : KR BI Report
    private function _exportKrBiReport($params) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        // $created_report_at = date("Y-m-d H:i:s");

        // Prepare Date 
        $d1 = explode('/', $params['from']);
        $d2 = explode('/', $params['to']);

        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $start = new DateTime($from);
        $end   = new DateTime( date("Y-m-30", strtotime($to)) );

        $diff  = $start->diff($end);
        $period_loop = $diff->format('%y') * 12 + $diff->format('%m') + 1;

        $from_last = date('Y-m-'.$d1[0], strtotime("-1 Years", strtotime($from)));

        // Prepare Data 
        $QTiming = new Application_Model_Timing();

        $kr_sellout = $QTiming->getKrBiReport($params);

        $data = array();

        foreach ($kr_sellout as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if (strpos($key2, "sellout_") !== false) { $data['sellout'][] = $value2; }
                if (strpos($key2, "price_") !== false) { $data['price'][] = $value2; }
            }
            $data['sellout'][] = $value['sellout'];
            $data['price'][] = $value['total_price'];
        }

        // Compare Last Year
        if ($params['chk_compare'] == 1) {
            $params2 = $params;

            $d1 = explode('/', $params['from']);
            $d2 = explode('/', $params['to']);

            $from_temp = $d1[2].'-'.$d1[1].'-'.$d1[0];
            $to_temp = $d2[2].'-'.$d2[1].'-'.$d2[0];

            $params2['from'] = date($d1[0].'/m/Y', strtotime("-1 Years", strtotime($from_temp)));
            $params2['to'] = date($d2[0].'/m/Y', strtotime("-1 Years", strtotime($to_temp)));

            $kr_sellout_last1 = $QTiming->getKrBiReport($params2);

            $data_last = array();

            foreach ($kr_sellout_last1 as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    if (strpos($key2, "sellout_") !== false) { $data_last['sellout'][] = $value2; }
                    if (strpos($key2, "price_") !== false) { $data_last['price'][] = $value2; }
                }
                $data_last['sellout'][] = $value['sellout'];
                $data_last['price'][] = $value['total_price'];
            }
        }

        $topic_list = array('sellout','price');

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array('Topic');

        for ($i=0;$i<$period_loop;$i++) {
            $month_text = date('M Y', strtotime("+".$i." Month", strtotime($from)));
            array_push($heads, $month_text);
        }

        array_push($heads, 'Total');

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:".$alpha."1")->applyFromArray($style);

        $index = 2;

        // Data Set 01
        foreach ($topic_list as $key => $value) {

            $alpha = 'A';
            $sheet->setCellValue($alpha++.$index, ucfirst($value));

            for ($j=0;$j<=$period_loop;$j++) { 

                if ( $value == 'sellout' ) {
                    $sheet->setCellValue($alpha++.$index, $data['sellout'][$j]);
                } else {
                    $sheet->setCellValue($alpha++.$index, $data['price'][$j]);
                }

            }

            $index++;
        }

        // Check Compare 
        if ($params['chk_compare'] == 1) {

            $index++;

            // Diff Table 
            $heads = array('Topic');

            for ($i=0;$i<$period_loop;$i++) {
                $month_text = date('M', strtotime("+".$i." Month", strtotime($from)));
                array_push($heads, $month_text);
            }

            array_push($heads, 'Total');

            $alpha = 'A';

            foreach($heads as $key) {
                $sheet->setCellValue($alpha.$index, $key);
                $alpha++;
            }

            $sheet->getStyle("A".$index.":".$alpha.$index)->applyFromArray($style);

            $index++;

            foreach ($topic_list as $key => $value) {

                $alpha = 'A';
                $sheet->setCellValue($alpha++.$index, ucfirst($value));

                for ($j=0;$j<=$period_loop;$j++) { 

                    if ( $value == 'sellout' ) {
                        $sellout_diff = round((( $data['sellout'][$j] / $data_last['sellout'][$j] ) - 1) * 100, 0);
                    } else {
                        $sellout_diff = round((( $data['price'][$j] / $data_last['price'][$j] ) - 1) * 100, 0);
                    }

                    $sheet->setCellValue($alpha++.$index, $sellout_diff);
                }

                $index++;
            }

            $index++;

            // Data Set 02
            $heads = array('Topic');

            for ($i=0;$i<$period_loop;$i++) {
                $month_text = date('M Y', strtotime("+".$i." Month", strtotime($from_last)));
                array_push($heads, $month_text);
            }

            array_push($heads, 'Total');

            $alpha = 'A';

            foreach($heads as $key) {
                $sheet->setCellValue($alpha.$index, $key);
                $alpha++;
            }

            $sheet->getStyle("A".$index.":".$alpha.$index)->applyFromArray($style);

            $index++;

            foreach ($topic_list as $key => $value) {

                $alpha = 'A';
                $sheet->setCellValue($alpha++.$index, ucfirst($value));

                for ($j=0;$j<=$period_loop;$j++) { 

                    if ( $value == 'sellout' ) {
                        $sheet->setCellValue($alpha++.$index, $data_last['sellout'][$j]);
                    } else {
                        $sheet->setCellValue($alpha++.$index, $data_last['price'][$j]);
                    }

                }

                $index++;
            }

        }
        
        $filename = 'KR_BI_Report_'.date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Report : Visit Shop
    private function _exportShopVisitReport($params) {

        $total = 0;
        $report_list = array('visit_shop','stock','demo_apk');

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $stock_date = date('d_M_Y', strtotime($to));

        // Start PHPExcel
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet  = $PHPExcel->getActiveSheet();

        $QShopVisit = new Application_Model_ShopVisit();

        for($i=0;$i<count($report_list);$i++) {

            $heads = $data = array();

            switch ($i) {

                case 0: 

                    $sheet_name = $report_list[$i];

                    $heads = array(
                        'NO.',
                        'Date',
                        'Area',
                        'Store ID',
                        'Store Name',
                        'Store Type',
                        'Market Name',
                        'Status',
                        'Staff Code',
                        'Staff Name',
                        'Group',
                        'Location',
                        'Remark',
                    );

                    $sub_sheet = $PHPExcel->createSheet($i);

                    $alpha  = 'A';
                    $index = 1;

                    foreach($heads as $key) {
                        $sub_sheet->setCellValue($alpha.$index, $key);
                        $alpha++;
                    }

                    $data = $QShopVisit->fetchPagination(null, null, $total, $params);
                    //print_r($data); //die;

                    $index  = 2;

                    for ($j=0;$j<count($data); $j++) {

                        switch ($data[$j]['sv_status']) {
                            case 1: $sv_status = 'Waiting';  break;
                            case 2: $sv_status = 'Approved'; break;
                            case 3: $sv_status = 'Rejected'; break;
                            default: $sv_status = ''; break;
                        }
                        
                        $alpha  = 'A';
                        $sub_sheet->setCellValue($alpha++.$index, $j+1);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['sv_created_at']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['area_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_id']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_type']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['market_name']);

                        $sub_sheet->setCellValue($alpha++.$index, $sv_status);

                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_code']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['staff_group']);

                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['sv_location']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['sv_remark']);
                        
                        $index++;
                    }

                    break;

                case 1: 

                    $sheet_name = $report_list[$i]."_".$stock_date;

                    $heads = array(
                        'NO.',
                        'Area',
                        'Store ID',
                        'Store Name',
                        'Store Type',
                        'Market Name',
                        'Category',
                        'Product Code',
                        'Product Name',
                        'Color',
                        'Unit',
                    );

                    $sub_sheet = $PHPExcel->createSheet($i);

                    $alpha  = 'A';
                    $index = 1;

                    foreach($heads as $key) {
                        $sub_sheet->setCellValue($alpha.$index, $key);
                        $alpha++;
                    }

                    $data = $QShopVisit->getVisitShopStock($params);
                    //print_r($data); //die;

                    $index  = 2;

                    for ($j=0;$j<count($data); $j++) {
                        
                        $alpha  = 'A';
                        $sub_sheet->setCellValue($alpha++.$index, $j+1);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['area_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_id']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_type']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['market_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['cat_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['good_code']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['good_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['color_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['normal']);
                        
                        $index++;
                }

                    break;

                case 2: 

                    $sheet_name = $report_list[$i]."_".$stock_date;

                    $heads = array(
                        'NO.',
                        'Area',
                        'Store ID',
                        'Store Name',
                        'Store Type',
                        'Market Name',
                        'Category',
                        'Product Code',
                        'Product Name',
                        'Demo',
                        'APK',
                    );

                    $sub_sheet = $PHPExcel->createSheet($i);

                    $alpha  = 'A';
                    $index = 1;

                    foreach($heads as $key) {
                        $sub_sheet->setCellValue($alpha.$index, $key);
                        $alpha++;
                    }

                    $params['flag_demo'] = 1;
                    $data = $QShopVisit->getVisitShopStock($params);
                    //print_r($data); //die;

                    $index  = 2;

                    for ($j=0;$j<count($data); $j++) {
                        
                        $alpha  = 'A';
                        $sub_sheet->setCellValue($alpha++.$index, $j+1);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['area_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_id']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['st_type']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['market_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['cat_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['good_code']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['good_name']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['demo']);
                        $sub_sheet->setCellValue($alpha++.$index, $data[$j]['apk']);
                        
                        $index++;
                    }

                    break;

                default : break;

            } 

            $sub_sheet->setTitle($sheet_name);
            
        }
        
        
        $filename = 'Visit_Shop_Report_'.date('Y-m-d_H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

}


