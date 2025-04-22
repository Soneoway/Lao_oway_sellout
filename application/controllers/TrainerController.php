<?php

class TrainerController extends My_Controller_Action
{
    public function editProfileAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'edit-profile.php';
    }

    /*list-pg*/
    public function indexAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'index.php';
    }

    /*mangage point pg*/
    public function pointAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'point.php';
    }

    public function rewardWarningAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'reward-warning.php';
    }
      
    /*Del history reward warning with ajax*/
    public function delHistoryAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-history.php';
    }

    public function trainingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'training-report.php';
    }

    public function listTrainingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-training-report.php';
    }

    public function listEventReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-event-report.php';
    }

    public function listProductInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-product-info.php';
    }

    public function listProductInfoJsonAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-product-info-json.php';
    }

    public function createProductInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'create-product-info.php';
    }

    public function removeProductInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'remove-product-info.php';
    }

    public function productInfoSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-save.php';
    }

    public function listNewsInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-news-info.php';
    }

    public function listNewsInfoJsonAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-news-info-json.php';
    }

    public function createNewsInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'create-news-info.php';
    }

    public function removeNewsInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'remove-news-info.php';
    }

    public function newsInfoSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'news-info-save.php';
    }

    public function pcLevelByAreaAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-level-by-area'.DIRECTORY_SEPARATOR.'pc-level-by-area.php';
    }

    public function pcLevelByAreaDetailAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-level-by-area'.DIRECTORY_SEPARATOR.'pc-level-by-area-detail.php';
    }

    public function pcLevelDetailAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-level-detail'.DIRECTORY_SEPARATOR.'pc-level-detail.php';
    }

    public function pcLevelByChannelAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-level-by-channel'.DIRECTORY_SEPARATOR.'pc-level-by-channel.php';
    }
   

    /*ajax get shop*/
    public function getShopAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->getRequest()->getMethod() == 'POST') {
            $dealer_id = $this->getRequest()->getParam('dealer_id');
            $QStore = new Application_Model_Store();
            $whereStore = array();
            $whereStore[] = $QStore->getAdapter()->quoteInto('d_id = ?', $dealer_id);
            $whereStore[] = $QStore->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
            $rows = $QStore->fetchAll($whereStore);

            $arrayShop = array();
            if ($rows->count()) {
                foreach ($rows as $key => $value) {
                    $arrayShop[$value['id']] = $value['name'];
                }
            }
            echo json_encode($arrayShop);
        }
    }

    public function delTrainingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-training-report.php';
    }

    public function eventReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'event-report.php';
    }

    public function delEventReportAction()
    {

        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-event-report.php';
    }

    public function listTeamBuildingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-team-building-report.php';
    }

    public function teamBuildingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'team-building-report.php';
    }

    public function delTeamBuildingReportAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-team-building-report.php';
    }

    /* ajax get province staff */
    public function getProvinceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->getRequest()->getMethod() == 'POST') {
            $cache = Zend_Registry::get('cache');
            $cache->remove('asm_cache');
            $cache->remove('regional_market_cache');

            $staff_id = $this->getRequest()->getParam('staff_id');
            $area = $this->getRequest()->getParam('area');
            $QStaffTrainer = new Application_Model_StaffTrainer();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $getProvinceArea = $QRegionalMarket->get_region_cache($area);
            $cachedRegionalMarket = $QRegionalMarket->get_cache();

            $result = array();

            if ($staff_id) {
                $AreaTrainer = $QStaffTrainer->getAreaTrainer($staff_id);

                $province = array_keys($AreaTrainer['province']);

                $array_intersect = array_intersect($province, $getProvinceArea);

                foreach ($array_intersect as $item) {
                    $result[$item] = $cachedRegionalMarket[$item];
                }

                echo json_encode($result);
            }
        }
    }

    /*ajax get all info PG or SALE*/
    public function getInfoPgSaleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_id = $this->getRequest()->getParam('staff_id');
        $QStaffTrainer = new Application_Model_StaffTrainer();
        $QStaffPoint = new Application_Model_StaffPoint();
        $QStaffRewardWarning = new Application_Model_StaffRewardWarning();
        // -----------------------------------------------------------------
        $result = array(
            'knowledge',
            'skill',
            'attitude',
            'facebook_link'
        );

        if ($staff_id) {
            $profile = $QStaffTrainer->getInfoStaff($staff_id);
            $type_evaluation = unserialize(TRAINER_EVALUATION);

            if ($profile) {
                foreach ($profile as $key => $value) {
                    $result['knowledge'] = $type_evaluation[$value['knowledge']];
                    $result['skill'] = $type_evaluation[$value['skill']];
                    $result['attitude'] = $type_evaluation[$value['attitude']];
                    $result['facebook_id'] = $value['facebook_id'];
                }
            }

            $staffPoint = $QStaffPoint->getInfoStaff($staff_id);

            if ($staffPoint) {
                foreach ($staffPoint as $key => $val) {
                    $result['point']['point'][] = $val['point'];
                    $result['point']['month'][] = $val['month'];
                    $result['point']['year'][] = $val['year'];
                }
            }
            $staffRewardWarning = $QStaffRewardWarning->getInfoStaff($staff_id);
            $typeRewardWarning = unserialize(TYPE_PG);

            if ($staffRewardWarning) {
                foreach ($staffRewardWarning as $key => $item) {
                    $result['reward_warning']['type'][] = $typeRewardWarning[$item['type']];
                    $result['reward_warning']['content'][] = $item['content'];
                    $result['reward_warning']['month'][] = $item['month'];
                    $result['reward_warning']['year'][] = $item['year'];
                }
            }

            echo json_encode($result);
        }

    }

    public function typeAssetAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'type-asset.php';
    }

    /*function save asset*/
    public function saveAssetAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'save-asset.php';

    }

    /*function del type asset*/
    public function delAssetAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-asset.php';
    }

    /*function create input order*/
    public function createInputAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'create-input.php';
    }

    /*function save order*/
    public function saveOrderAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'save-order.php';
    }

    /*function order*/
    public function ordersAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'orders.php';
    }

    /*function view order*/
    public function viewOrderAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'view-order.php';
    }

    /*function confirm order*/
    public function confirmOrderAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'confirm-order.php';
    }

    /*do confirm order*/
    public function doConfirmOrderAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'do-confirm-order.php';
    }

    /*del order*/
    public function delOrderAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-order.php';
    }

    /*function inventory asset*/
    public function inventoryAssetAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'inventory-asset.php';
    }

    /*function create output order*/
    public function createOutputAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'create-output.php';
    }

    /*function save order out*/
    public function saveOrderOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'save-order-out.php';
    }

    /*function list order out*/
    public function ordersOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'orders-out.php';
    }

    /*function view order*/
    public function viewOrderOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'view-order-out.php';
    }

    /*function confirm order out*/
    public function confirmOrderOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'confirm-order-out.php';
    }

    /*function do confirm order out*/
    public function doConfirmOrderOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'do-confirm-order-out.php';
    }

    /*function del order out */
    public function delOrderOutAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'del-order-out.php';
    }

    public function inventoryHistoryAssetAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'inventory-history-asset.php';
    }

        /* Sales Knowledge Base */
    public function knowledgeBaseAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base' . DIRECTORY_SEPARATOR . 'knowledge-base.php';
    }

    public function knowledgeBaseEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base' . DIRECTORY_SEPARATOR . 'knowledge-base-edit.php';
    }

    public function knowledgeBaseSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base' . DIRECTORY_SEPARATOR . 'knowledge-base-save.php';
    }

    public function knowledgeBaseDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base' . DIRECTORY_SEPARATOR . 'knowledge-base-delete.php';
    }


    public function knowledgeBaseExcelAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base' . DIRECTORY_SEPARATOR . 'knowledge-base-excel.php';
    }

    /* Sales Knowledge Base */
    public function knowledgeBaseTypeAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base-type' . DIRECTORY_SEPARATOR . 'knowledge-base-type.php';
    }

    public function knowledgeBaseTypeEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base-type' . DIRECTORY_SEPARATOR . 'knowledge-base-type-edit.php';
    }

    public function knowledgeBaseTypeSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base-type' . DIRECTORY_SEPARATOR . 'knowledge-base-type-save.php';
    }

    public function knowledgeBaseTypeDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'knowledge-base-type' . DIRECTORY_SEPARATOR . 'knowledge-base-type-delete.php';
    }

    /* ajax view training report */
    public function getInfoTrainingReportAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        if ($this->getRequest()->getMethod() == 'POST') {
            $id = $this->getRequest()->getParam('id');

            $QStaffTrainingReport = new Application_Model_StaffTrainingReport();
            $whereStaffTrainingReport = $QStaffTrainingReport->getAdapter()->quoteInto('id = ?', $id);
            $row = $QStaffTrainingReport->fetchRow($whereStaffTrainingReport);
            if ($row) {
                /* lay du lieu ve dc roi */
                $type = $row['type'];
                $data = '<table class="table table-responsive">';
                switch ($type) {
                    case TRAINING_REPORT_INTERNALLY : {
                        // internal thi date - area - province - note - quantity trainee
                        // date
                        $data = $data . '<tr><td>TYPE:</td><td>' . TRAINING_REPORT_INTERNALLY_NAME . '</td></tr>';
                        $date = $row['date'];
                        $data = $data . '<tr><td>Date:</td><td>' . $date . '</td></tr>';
                        // area
                        $QArea = new Application_Model_Area();
                        $AreaCached = $QArea->get_cache();
                        $area = $AreaCached[$row['area']];
                        $data = $data . '<tr><td>Area:</td><td>' . $area . '</td></tr>';
                        // province
                        $QRegionalMarket = new Application_Model_RegionalMarket();
                        $getCachedProvinceRegionalMarket = $QRegionalMarket->nget_all_province_cache();
                        $province = $getCachedProvinceRegionalMarket[$row['province']];
                        $data = $data . '<tr><td>Province:</td><td>' . $province . '</td></tr>';
                        // note
                        $note = $row['note'];
                        $data = $data . '<tr><td>Note:</td><td>' . $note . '</td></tr>';
                        // quantity trainees
                        $quantity_trainees = $row['quantity_trainees'];
                        $data = $data . '<tr><td>Quantity Trainees:</td><td>' . $quantity_trainees . '</td></tr>';

                        break;

                    }
                    case TRAINING_REPORT_PARTNERS: {
                        // partners thi co date - dealer - store- note - quantity trainees
                        // date
                        $data = $data . '<tr><td>TYPE:</td><td>' . TRAINING_REPORT_PARTNERS_NAME . '</td></tr>';
                        $date = $row['date'];
                        $data = $data . '<tr><td>Date:</td><td>' . $date . '</td></tr>';
                        // dealer name
                        $QStaffTrainer = new Application_Model_StaffTrainer();
                        $areaTrainer = $QStaffTrainer->getAreaTrainer($userStorage->id);
                        $regional_market = $areaTrainer['district'];
                        $dealers = $QStaffTrainingReport->getDealerArea($regional_market);
                        $nameDealer = $dealers[$row['dealer_id']];
                        $data = $data . '<tr><td>Dealer Name:</td><td>' . $nameDealer . '</td></tr>';
                        // store
                        $QStore = new Application_Model_Store();
                        $cachedStore = $QStore->get_cache();
                        $store = $cachedStore[$row['store_id']];
                        $data = $data . '<tr><td>Store:</td><td>' . $store . '</td></tr>';
                        // note
                        $note = $row['note'];
                        $data = $data . '<tr><td>Note:</td><td>' . $note . '</td></tr>';
                        // quantity trainees
                        $quantity_trainees = $row['quantity_trainees'];
                        $data = $data . '<tr><td>Quantity Trainees:</td><td>' . $quantity_trainees . '</td></tr>';
                        break;
                    }
                    case TRAINING_REPORT_NEW_STAFF: {
                        // voi new staff thi co from date - to date - Quantity Participant -  Quantity Disqualified - Quantity Remain
                        // from date
                        $data = $data . '<tr><td>TYPE:</td><td>' . TRAINING_REPORT_NEW_STAFF_NAME . '</td></tr>';
                        $from_date = $row['from_date'];
                        $data = $data . '<tr><td>From Date:</td><td>' . $from_date . '</td></tr>';
                        // to date
                        $to_date = $row['to_date'];
                        $data = $data . '<tr><td>From Date:</td><td>' . $to_date . '</td></tr>';
                        // Quantity Participant
                        $quantity_participant = $row['quantity_participant'];
                        $data = $data . '<tr><td>Quantity Participant:</td><td>' . $quantity_participant . '</td></tr>';
                        //Quantity Disqualified
                        $quantity_disqualified = $row['quantity_disqualified'];
                        $data = $data . '<tr><td>Quantity Disqualified:</td><td>' . $quantity_disqualified . '</td></tr>';
                        // Quantity Remain
                        $quantity_remain = $row['quantity_remain'];
                        $data = $data . '<tr><td>Quantity Remain:</td><td>' . $quantity_remain . '</td></tr>';
                        break;
                    }
                }

                // gio toi hinh anh ne -- cai nay moi cang ah
                $allPicture = json_decode($row['picture'], true);
                // get image
                $direct = HOST . 'public/photo/trainer/' . $allPicture['user_id'] . '/' . $allPicture['direct'] . '/';

                // get list picture training
                foreach ($allPicture as $key => $value) {
                    if (preg_match('/picture_list_pg/', $key)) {
                        $arrayPictureList[$key] = $direct . $value;
                    }
                    if (preg_match('/picture_training/', $key)) {
                        $arrayPictureTraining[$key] = $direct . $value;
                    }
                }

                // dua data ra ban table --- picture list pg
                $data = $data . '<tr><td colspan="2" style="text-align:center"><b>Picture List PG/PB</b></td></tr>';
                $data = $data . '<tr><td colspan="2">';
                foreach ($arrayPictureList as $k => $picture) {
                    $data = $data . '<a data-featherlight="image" href="' . $picture . '"><img src="' . $picture . '" class="img-rounded" style="width:80px; height:80px; padding:10px;"/></a>';
                }
                $data = $data . '</td></tr>';
                // picture training
                $data = $data . '<tr><td colspan="2" style="text-align:center"><b>Picture Training</b></td></tr>';
                $data = $data . '<tr><td colspan="2">';
                foreach ($arrayPictureTraining as $k => $picture) {
                    $data = $data . '<a data-featherlight="image" href="' . $picture . '"><img src="' . $picture . '" class="img-rounded" style="width:80px; height:80px; padding:10px;"/></a>';
                }
                $data = $data . '</td></tr>';
                $data = $data . '</table>';
                echo $data;

            }

        }
    }

    /* ajax view event report */
    public function getInfoEventReportAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        if ($this->getRequest()->getMethod() == 'POST') {
            $id = $this->getRequest()->getParam('id');

            $QStaffEventReport = new Application_Model_StaffEventReport();
            $whereStaffEventReport = $QStaffEventReport->getAdapter()->quoteInto('id = ?', $id);
            $row = $QStaffEventReport->fetchRow($whereStaffEventReport);
            if ($row) {
                // date - visitor - staff id - dealer id - store id - picture - note

                $data = '<table class="table table-responsive">';
                // date
                $date = $row['date'];
                $data = $data . '<tr><td>Date:</td><td>' . $date . '</td></tr>';
                // visitor
                $visitors = $row['visitors'];
                $data = $data . '<tr><td>Visitors:</td><td>' . $visitors . '</td></tr>';
                // dealer id
                $QStaffTrainingReport = new Application_Model_StaffTrainingReport();
                $QStaffTrainer = new Application_Model_StaffTrainer();
                $areaTrainer = $QStaffTrainer->getAreaTrainer($userStorage->id);
                $regional_market = $areaTrainer['district'];
                $dealers = $QStaffTrainingReport->getDealerArea($regional_market);
                $nameDealer = $dealers[$row['dealer_id']];
                $data = $data . '<tr><td>Dealer Name:</td><td>' . $nameDealer . '</td></tr>';
                // store
                $QStore = new Application_Model_Store();
                $cachedStore = $QStore->get_cache();
                $store = $cachedStore[$row['store_id']];
                $data = $data . '<tr><td>Store:</td><td>' . $store . '</td></tr>';
                // note
                $note = $row['note'];
                $data = $data . '<tr><td>Note:</td><td>' . $note . '</td></tr>';
                // picture
                $allPicture = json_decode($row['picture'], true);
                $direct = HOST . 'public/photo/trainer/' . $allPicture['user_id'] . '/' . $allPicture['direct'] . '/';
                $data = $data . '<tr><td colspan="2" style="text-align:center"><b>Picture</b></td></tr>';
                $data = $data . '<tr><td colspan="2">';
                foreach ($allPicture as $key => $value) {
                    if ($key == 'direct' || $key == 'user_id') {
                        continue;
                    } else if ($value) {
                        $data = $data . '<a data-featherlight="image" href="' . $direct . $value . '"><img src="' . $direct . $value . '" class="img-rounded" style="width:80px; height:80px; padding:10px;"/></a>';
                    }
                }
                $data = $data . '</td></tr>';
                $data = $data . '</table>';

                echo $data;

            }

        }
    }

    /* ajax view team building report */
    public function getInfoTeamBuildingReportAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        if ($this->getRequest()->getMethod() == 'POST') {
            $id = $this->getRequest()->getParam('id');

            $QStaffTeamBuildingReport = new Application_Model_StaffTeamBuildingReport();
            $whereStaffTeamBuildingReport = $QStaffTeamBuildingReport->getAdapter()->quoteInto('id = ?', $id);
            $row = $QStaffTeamBuildingReport->fetchRow($whereStaffTeamBuildingReport);
            if ($row) {
                // date - description - note - picture

                $data = '<table class="table table-responsive">';
                // date
                $date = $row['date'];
                $data = $data . '<tr><td>Date:</td><td>' . $date . '</td></tr>';
                // description
                $description = $row['description'];
                $data = $data . '<tr><td>Description:</td><td>' . $description . '</td></tr>';
                // note
                $note = $row['note'];
                $data = $data . '<tr><td>Note:</td><td>' . $note . '</td></tr>';
                // picture
                $allPicture = json_decode($row['picture'], true);
                $direct = HOST . 'public/photo/trainer/' . $allPicture['user_id'] . '/' . $allPicture['direct'] . '/';
                $data = $data . '<tr><td colspan="2" style="text-align:center"><b>Picture</b></td></tr>';
                $data = $data . '<tr><td colspan="2">';
                foreach ($allPicture as $key => $value) {
                    if ($key == 'direct' || $key == 'user_id') {
                        continue;
                    } else if ($value) {
                        $data = $data . '<a data-featherlight="image" href="' . $direct . $value . '"><img src="' . $direct . $value . '" class="img-rounded" style="width:80px; height:80px; padding:10px;"/></a>';
                    }
                }
                $data = $data . '</td></tr>';
                $data = $data . '</table>';

                echo $data;

            }

        }
    }

    /* cham cong cho nhan vien moi - pg */
    public function addTimeCheckInAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'add-time-check-in.php';
    }

    /* function save new staff --- PG - for time check in pg  */
    public function saveNewPgAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'save-new-pg.php';
    }

    /* function for view list new staff for time check in training */
    public function listNewStaffAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-new-staff.php';
    }

    /* Ajax get info new staff */
    public function getInfoNewStaffAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->getMethod() == 'POST') {
            $id = $this->getRequest()->getParam('id');
            $QStaffTraining = new Application_Model_StaffTraining();
            $whereStaffTraining = $QStaffTraining->getAdapter()->quoteInto('id = ?', $id);
            $row = $QStaffTraining->fetchRow($whereStaffTraining);
            $result = array();
            if ($row) {
                foreach ($row as $key => $value) {
                    $result[$key] = $value;
                }
                echo json_encode($result);
            }

        }
    }

    public function deleteNewStaffAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'delete-new-staff.php';
    }

    /*function list push notification*/
    public function listPushNotificationAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-push-notification.php';
    }

    /*function create push notification*/
    public function createPushNotificationAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'create-push-notification.php';
    }

    /*function save push notification*/
    public function pushNotificationSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'push-notification-save.php';
    }

    /*function delete push notification*/
    public function removePushNotificationAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'remove-push-notification.php';
    }

    /*function json push notification*/
    public function listPushNotificationJsonAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'list-push-notification-json.php';
    }

    public function reportCheckInAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-check-in' . DIRECTORY_SEPARATOR . 'report-check-in.php';
    }

    public function reportCheckInEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-check-in' . DIRECTORY_SEPARATOR . 'report-check-in-edit.php';
    }

    /* Report PC check in to web*/
    public function reportCheckInWebAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-check-in' . DIRECTORY_SEPARATOR . 'report-check-in-web.php';
    }

    /* Report PC check in to excel*/
    public function reportCheckInExcelAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-check-in' . DIRECTORY_SEPARATOR . 'report-check-in-excel.php';
    }

    // Report PC First Trainnig 
    public function pcFirstTrainingAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-first-training'.DIRECTORY_SEPARATOR.'pc-first-training.php';
    }

    public function pcFirstTrainingEditAction()
    {
        require_once 'trainer'.DIRECTORY_SEPARATOR.'pc-first-training'.DIRECTORY_SEPARATOR.'pc-first-training-edit.php';
    }

    /* Export check in by area*/
    public static function pc_check_in_by_area_export($date_title, $data, $area_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'วันเริ่มงาน',
            'อบรม New PC',
            'Working Day',
            'Day off',
            'ขาดงาน',
            'Sick leave',
            'Sick leave (มีใบรับรองแพทย์)',
            'Business Leave',
            'Annual Leave',
            'Ordination leave',
            'Maternity leave',
            'วันที่มีผลลาออก',
        );

        $cou = count($area_name);
        $arr = array();
        for ($i = 0; $i < $cou; $i++) {
            $name_area = $area_name[$i]['area_name'];
            $arr[$i] = $name_area;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "PC Check in for " . date("21/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
        $sheet->setCellValue('A3', "Area : ");

        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 5;

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $alpha = 'A';
            $cnt = $cnt + 1;

            //Sum Leave
            $all_leave = ($data[$i]['day_off_approve']) + ($data[$i]['switch_day_off']) + ($data[$i]['sick_leave']) + ($data[$i]['sick_leave_cert']) + ($data[$i]['personal_leave']) + ($data[$i]['status_reject']) + ($data[$i]['day_off_reject']) + ($data[$i]['switch_day_off_reject']) + ($data[$i]['vacation_leave']) + ($data[$i]['ordination_leave']) + ($data[$i]['maternity_leave']);


            if (date('Y-m-d', strtotime($data[$i]['off_date'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['off_date'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24);
                } else {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($date_title[0])) / (60 * 60 * 24);
                }
            } else {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        $date_range = (strtotime(date('Y-m-d')) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    }
                } else {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        if (((strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1) == $all_leave) {
                            $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        } else {
                            $date_range = (strtotime(date('Y-m-d')) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        }
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                    }
                }
            }

            $check_empty = ($data[$i]['status_approve'] + $all_leave);

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (isset($data[$i]['training']) ? $data[$i]['training'] : 0));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['status_approve'] + $data[$i]['status_wait']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['day_off_approve'] + $data[$i]['switch_day_off']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($date_range - ($all_leave + $data[$i]['status_approve'] + $data[$i]['status_wait'])));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave_cert']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['personal_leave'] + $data[$i]['status_reject'] + $data[$i]['day_off_reject'] + $data[$i]['switch_day_off_reject']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['vacation_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['ordination_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset ($check_empty) ? "" : (int)$data[$i]['maternity_leave']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['off_date']);
//            $sheet->setCellValue($alpha++ . $index, "All leave : " . $all_leave . " Date range : " . $date_range . " All working day : " . ($data[$i]['status_approve'] + $data[$i]['status_wait']));
            $sheet->setCellValue($alpha++ . $index, "");

            $index++;

        }

        $filename = 'Export-PC-Working-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    /* Export Approve Waiting PCM*/
    public static function pcm_approve_wait_export($date_title, $data, $area_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'Area',
            'Waiting',
            'PCM Code',
            'PCM Name',
        );

//        $cou = count($area_name);
//        $arr = array();
//        for ($i = 0; $i < $cou; $i++) {
//            $name_area =  $area_name[$i]['area_name'];
//            $arr[$i] = $name_area;
//        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "PC Check in for " . date("d/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
//        $sheet->setCellValue('A3', "Area : ");
//        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 3;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
//        $sheet->mergeCells('B3:G3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 4;

        // Get Data
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $cnt_data = count($data);
            $cnt = 0;

            for ($i = 0; $i < $cnt_data; $i++) {

                $alpha = 'A';
                $cnt = $cnt + 1;

                $sheet->setCellValue($alpha++ . $index, $cnt);
                $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
                $sheet->setCellValue($alpha++ . $index, ($data[$i]['status_wait'] + $data[$i]['status_leave']));
                $sheet->setCellValue($alpha++ . $index, $data[$i]['pcm_code']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['pcm_name']);

                $index++;

            }

        }

        $filename = 'Export-Approve-Waiting-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    public
    function bmReportCheckInAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'bm-report-check-in' . DIRECTORY_SEPARATOR . 'bm-report-check-in.php';
    }

    public
    function bmReportCheckInEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'bm-report-check-in' . DIRECTORY_SEPARATOR . 'bm-report-check-in-edit.php';
    }

    /* Report check in to web*/
    public
    function bmReportCheckInWebAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'bm-report-check-in' . DIRECTORY_SEPARATOR . 'bm-report-check-in-web.php';
    }

    /* Report check in to excel*/
    public function bmReportCheckInExcelAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'bm-report-check-in' . DIRECTORY_SEPARATOR . 'bm-report-check-in-excel.php';
    }

  

    /* Export BM check in by area*/
    public static function bm_check_in_by_area_export($date_title, $data, $area_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'วันเริ่มงาน',
            'Working Day',
            'Day off',
            'ขาดงาน',
            'Sick leave',
            'Sick leave (มีใบรับรองแพทย์)',
            'Business Leave',
            'Annual Leave',
            'Ordination leave',
            'Maternity leave',
            'วันที่มีผลลาออก',
        );

        $cou = count($area_name);
        $arr = array();
        for ($i = 0; $i < $cou; $i++) {
            $name_area = $area_name[$i]['area_name'];
            $arr[$i] = $name_area;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "BM Check in for " . date("21/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
        $sheet->setCellValue('A3', "Area : "); 

        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('B3:N3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 5;

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $alpha = 'A';
            $cnt = $cnt + 1;

            //Sum Leave
            $all_leave = ($data[$i]['day_off']) + ($data[$i]['switch_day_off']) + ($data[$i]['sick_leave']) + ($data[$i]['sick_leave_cert']) + ($data[$i]['personal_leave']) + ($data[$i]['vacation_leave']) + ($data[$i]['ordination_leave']) + ($data[$i]['maternity_leave']);

            if (date('Y-m-d', strtotime($data[$i]['off_date'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['off_date'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24);
                } else {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($date_title[0])) / (60 * 60 * 24);
                }
            } else {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        $date_range = (strtotime(date('Y-m-d')) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    }
                } else {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        if (((strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1) == $all_leave) {
                            $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        } else {
                            $date_range = (strtotime(date('Y-m-d')) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        }
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                    }
                }
            }

            $check_empty = ($data[$i]['status_approve'] + $all_leave);

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : $data[$i]['working_day']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['day_off'] + $data[$i]['switch_day_off']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : $date_range - ($all_leave + $data[$i]['working_day']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave_cert']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['personal_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['vacation_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['ordination_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['maternity_leave']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['off_date']);
//            $sheet->setCellValue($alpha++ . $index, "All leave : " . $all_leave . " Date range : " . $date_range . " All working day : " . $data[$i]['working_day']);
            $sheet->setCellValue($alpha++ . $index, "");

            $index++;

        }

        $filename = 'Export-BM-Working-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    
      public function salesReportCheckInAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'sales-report-check-in'. DIRECTORY_SEPARATOR . 'sales-report-check-in.php';
         // echo "ffe";
    }
     public function salesReportCheckInEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'sales-report-check-in' . DIRECTORY_SEPARATOR . 'sales-report-check-in-edit.php';

    }
     public function salesReportCheckInWebAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'sales-report-check-in' . DIRECTORY_SEPARATOR . 'sales-report-check-in-web.php';
    }
        public function salesReportCheckInExcelAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'sales-report-check-in' . DIRECTORY_SEPARATOR . 'sales-report-check-in-excel.php';
    }
   public static function sales_check_in_by_area_export($date_title, $data, $area_name)
    {
         set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'วันเริ่มงาน',
            'Working Day',
            'Day off',
            'ขาดงาน',
            'Sick leave',
            'Sick leave (มีใบรับรองแพทย์)',
            'Business Leave',
            'Annual Leave',
            'Ordination leave',
            'Maternity leave',
            'วันที่มีผลลาออก',
        );

        $cou = count($area_name);
        $arr = array();
        for ($i = 0; $i < $cou; $i++) {
            $name_area = $area_name[$i]['area_name'];
            $arr[$i] = $name_area;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "Check in for " . date("21/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
        $sheet->setCellValue('A3', "Area : "); 

        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('B3:N3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 5;

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $alpha = 'A';
            $cnt = $cnt + 1;

            //Sum Leave
            $all_leave = ($data[$i]['day_off']) + ($data[$i]['switch_day_off']) + ($data[$i]['sick_leave']) + ($data[$i]['sick_leave_cert']) + ($data[$i]['personal_leave']) + ($data[$i]['vacation_leave']) + ($data[$i]['ordination_leave']) + ($data[$i]['maternity_leave']);

            if (date('Y-m-d', strtotime($data[$i]['off_date'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['off_date'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24);
                } else {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($date_title[0])) / (60 * 60 * 24);
                }
            } else {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        $date_range = (strtotime(date('Y-m-d')) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    }
                } else {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        if (((strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1) == $all_leave) {
                            $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        } else {
                            $date_range = (strtotime(date('Y-m-d')) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        }
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                    }
                }
            }

            $check_empty = ($data[$i]['status_approve'] + $all_leave);

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : $data[$i]['working_day']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['day_off'] + $data[$i]['switch_day_off']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : $date_range - ($all_leave + $data[$i]['working_day']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave'] );
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave_cert']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['personal_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['vacation_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['ordination_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['maternity_leave']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['off_date']);
//            $sheet->setCellValue($alpha++ . $index, "All leave : " . $all_leave . " Date range : " . $date_range . " All working day : " . $data[$i]['working_day']);
            $sheet->setCellValue($alpha++ . $index, "");

            $index++;

        }

        $filename = 'Export-Sales-Working-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }
        public static function check_approve_wait_export($date_title, $data, $area_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'Area',
            'Waiting',
       
        );

//        $cou = count($area_name);
//        $arr = array();
//        for ($i = 0; $i < $cou; $i++) {
//            $name_area =  $area_name[$i]['area_name'];
//            $arr[$i] = $name_area;
//        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "PC Check in for " . date("d/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
//        $sheet->setCellValue('A3', "Area : ");
//        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 3;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
//        $sheet->mergeCells('B3:G3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 4;

        // Get Data
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $cnt_data = count($data);
            $cnt = 0;

            for ($i = 0; $i < $cnt_data; $i++) {

                $alpha = 'A';
                $cnt = $cnt + 1;

                $sheet->setCellValue($alpha++ . $index, $cnt);
                $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
                $sheet->setCellValue($alpha++ . $index, ($data[$i]['status_wait'] + $data[$i]['status_leave']));
        
                $index++;

            }

        }

        $filename = 'Export-Approve-Waiting-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }
         public function asmReportCheckInAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'asm-report-check-in'. DIRECTORY_SEPARATOR . 'asm-report-check-in.php';
         // echo "ffe";
    }
     public function asmReportCheckInEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'asm-report-check-in' . DIRECTORY_SEPARATOR . 'asm-report-check-in-edit.php';

    }
     public function asmReportCheckInWebAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'asm-report-check-in' . DIRECTORY_SEPARATOR . 'asm-report-check-in-web.php';
    }
        public function asmReportCheckInExcelAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'asm-report-check-in' . DIRECTORY_SEPARATOR . 'asm-report-check-in-excel.php';
    }
    //     public function asmReportCheckInExcelAreaAction()
    // {
    //     require_once 'trainer' . DIRECTORY_SEPARATOR . 'asm-report-check-in' . DIRECTORY_SEPARATOR . 'asm-report-check-in-excel.php';
    // }

    public static function check_in_by_area_export($date_title, $data, $area_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'วันเริ่มงาน',
            'อบรมพนักงานใหม่',
            'Working Day',
            'Day off',
            'ขาดงาน',
            'Sick leave',
            'Sick leave (มีใบรับรองแพทย์)',
            'Business Leave',
            'Annual Leave',
            'Ordination leave',
            'Maternity leave',
            'วันที่มีผลลาออก',
        );

        $cou = count($area_name);
        $arr = array();
        for ($i = 0; $i < $cou; $i++) {
            $name_area = $area_name[$i]['area_name'];
            $arr[$i] = $name_area;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
        $sheet->setCellValue('A2', "Check in for " . date("21/m/Y", strtotime($date_title[0])) . " - " . date('d/m/Y', strtotime($date_title[1])));
        $sheet->setCellValue('A3', "Area : ");

        $sheet->setCellValue('B3', implode(", ", $arr));

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('B3:O3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 5;

        $cnt_data = count($data);
        $cnt = 0;

        for ($i = 0; $i < $cnt_data; $i++) {

            $alpha = 'A';
            $cnt = $cnt + 1;

            //Sum Leave
            $all_leave = ($data[$i]['day_off_approve']) + ($data[$i]['switch_day_off']) + ($data[$i]['sick_leave']) + ($data[$i]['sick_leave_cert']) + ($data[$i]['personal_leave']) + ($data[$i]['status_reject']) + ($data[$i]['day_off_reject']) + ($data[$i]['switch_day_off_reject']) + ($data[$i]['vacation_leave']) + ($data[$i]['ordination_leave']) + ($data[$i]['maternity_leave']);


            if (date('Y-m-d', strtotime($data[$i]['off_date'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['off_date'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= $date_title[0] && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24);
                } else {
                    $date_range = (strtotime($data[$i]['off_date']) - strtotime($date_title[0])) / (60 * 60 * 24);
                }
            } else {
                if (date('Y-m-d', strtotime($data[$i]['joined_at'])) >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d', strtotime($data[$i]['joined_at'])) <= date('Y-m-d', strtotime($date_title[1]))) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        $date_range = (strtotime(date('Y-m-d')) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($data[$i]['joined_at'])) / (60 * 60 * 24) + 1;
                    }
                } else {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($date_title[0])) && date('Y-m-d') <= date('Y-m-d', strtotime($date_title[1]))) {
                        if (((strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1) == $all_leave) {
                            $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        } else {
                            $date_range = (strtotime(date('Y-m-d')) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                        }
                    } else {
                        $date_range = (strtotime($date_title[1]) - strtotime($date_title[0])) / (60 * 60 * 24) + 1;
                    }
                }
            }

            $check_empty = ($data[$i]['status_approve'] + $all_leave);

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, trim($data[$i]['staff_code']));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (isset($data[$i]['training']) ? $data[$i]['training'] : 0));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['status_approve'] + $data[$i]['status_wait']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['day_off_approve'] + $data[$i]['switch_day_off']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($date_range - ($all_leave + $data[$i]['status_approve'] + $data[$i]['status_wait'])));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['sick_leave_cert']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : ($data[$i]['personal_leave'] + $data[$i]['status_reject'] + $data[$i]['day_off_reject'] + $data[$i]['switch_day_off_reject']));
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['vacation_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset($check_empty) ? "" : (int)$data[$i]['ordination_leave']);
            $sheet->setCellValue($alpha++ . $index, !isset ($check_empty) ? "" : (int)$data[$i]['maternity_leave']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['off_date']);
//            $sheet->setCellValue($alpha++ . $index, "All leave : " . $all_leave . " Date range : " . $date_range . " All working day : " . ($data[$i]['status_approve'] + $data[$i]['status_wait']));
            $sheet->setCellValue($alpha++ . $index, "");

            $index++;

        }

        $filename = 'Export-Working-' . date('M', strtotime($date_title[1])) . '-' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }
   
    // Export Report PC First Training - For Import Staff : Export Excel
    private function _exportPcFirstTrainingExcel($data, $params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Code',
            'Gender',
            'FirstnameTH',
            'LastnameTH',
            'FirstnameEN',
            'LastnameEN',
            'Department',
            'Team',
            'Title',
            'Area',
            'Province',
            'Joined At',
            'Shop Name',
            'Email Admin',
            'Phone Number',
            'Date of Birth',
            'Shirt Size',
            'Email ASM',
            'Email RM',
            'Public ID',
            'First Training',
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
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, $data[$i]['prefix']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['firstname']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['lastname']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['firstname_en']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['lastname_en']);
            $sheet->setCellValue($alpha++.$index, "SALE");
            $sheet->setCellValue($alpha++.$index, "SALE");
            $sheet->setCellValue($alpha++.$index, "PC");
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['phone_number'].'"');
            $sheet->setCellValue($alpha++.$index, $data[$i]['dob']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['shirt_size']);
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, $data[$i]['public_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['check_in']);
            $index++;
        }
        
        $filename = 'PC_First_Training_Excel_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report PC First Training - For HR Record : Export List
    private function _exportPcFirstTrainingList($data, $params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'ลำดับที่',
            'เลขประจำตัวประชาชน',
            'รหัสพนักงาน',
            'คำนำหน้า',
            'ชื่อ-นามสกุล',
            'Staff Name',
            'ตำแหน่ง',
            'อบรมครั้งที่ 1',
            'อบรมครั้งที่ 2',
            'อบรมครั้งที่ 3',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QStaff = new Application_Model_Staff();
        $QPcCheckInLog = new Application_Model_PcCheckInLog();

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $where_staff = array();
            $where_staff[] = $QStaff->getAdapter()->quoteInto("public_id = ?", $data[$i]['public_id']);
            $result_staff = $QStaff->fetchRow($where_staff);

            $result_checkin = array();
            if (isset($result_staff['id']) && $result_staff['id'] != '') {
                $where_checkin = array();
                $where_checkin[] = $QPcCheckInLog->getAdapter()->quoteInto("staff_id = ?", $result_staff['id']);
                $where_checkin[] = $QPcCheckInLog->getAdapter()->quoteInto("store_id = 0");

                $result_checkin = $QPcCheckInLog->fetchAll($where_checkin);
            }
            
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['public_id']);
            $sheet->setCellValue($alpha++.$index, $result_staff['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['prefix']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name_en']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);


            if (isset($result_staff['id']) && $result_staff['id'] != '') {

                for ($j=0;$j<count($result_checkin);$j++) {

                    $check_in = '-';
                    //if ( isset($result_checkin[$j]['check_in']) ) { $check_in = $result_checkin[$j]['check_in']; }

                    $sheet->setCellValue($alpha++.$index, $result_checkin[$j]['check_in']);
                }
            } 
            
            
            $index++;

        }
        
        $filename = 'PC_First_Training_list_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    // Export Report PC First Training - For HR : Export SSO Hospital
    private function _exportPcFirstTrainingSso($data, $params) {

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $print_by = $userStorage->firstname." ".$userStorage->lastname;
        $print_date = date('d F Y H:i:s');

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'ลำดับที่',
            'รหัสพนักงาน',
            'คำนำหน้า',
            'ชื่อ-นามสกุล',
            'ตำแหน่ง',
            'แผนก / เขต',
            'วันเริ่มงาน',
            'สถานภาพ',
            'เลขที่บัตรประชาชน',
            'สถานพยาบาล',
            'สถานพยาบาล สำรอง 1',
            'สถานพยาบาล สำรอง 2',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Export by '.$print_by.' | '.$print_date);

        $alpha    = 'A';
        $index    = 2;

        $sheet->mergeCells('A1:J1');

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QStaff = new Application_Model_Staff();

        $index = 3;
        for ($i=0;$i<count($data); $i++) {

            $where_staff = array();
            $where_staff[] = $QStaff->getAdapter()->quoteInto("public_id = ?", $data[$i]['public_id']);
            $result_staff = $QStaff->fetchRow($where_staff);

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $result_staff['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['prefix']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, "SALE / ".$data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $result_staff['joined_at']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['marital_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['public_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sso_hospital_1']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sso_hospital_2']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['sso_hospital_3']);

            $index++;

        }

//        echo '<pre>';
//        print_r($data);
//        die;
        $filename = 'PC_First_Training_SSO_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
   
    // Export Report PC First Training - For HR Record : Export Data HR
    private function _exportDataHr($data, $params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'ชื่อต้น',
            'ชื่อตัว',
            'ชื่อสกุล',
            'ชื่ออังกฤษ',
            'วันเดือนปีเกิด',
            'เพศ',
            'สถานภาพสมรส',
            'เลขที่บัตรประชาชน',
            'วันที่หมดอายุ',
            'สถานที่ออกบัตร',
            'ที่อยู่บรรทัด 1',
            'ที่อยู่บรรทัด 2',
            'ที่อยู่บรรทัด 3',
            'ไปรษณีย์',
            'โทรศัพท์',
            'อีเมล์',
            'เลขที่ผู้เสียภาษี',
            'รหัสพนักงาน',
            'เลขที่บัตรพนักงาน',
            'ประเภทการจ้าง',
            'รหัสตำแหน่งงาน',
            'รหัสแผนก',
            'รหัสสาขา',
            'วิธีการจ่ายค่าจ้าง',
            'รหัสธนาคารโอนเงิน',
            'เลขที่บัญชีธนาคาร',
            'เลขที่กองทุนสำรองฯ',
            'วันที่สมัครกองทุน',
            'เลขที่สัญญาเงินกู้',
            'วันที่สัญญาเงินกู้',
            'วันที่สมัคร ปกสค.',
            'วันที่เริ่มงาน',
            'วันที่พ้นทดลองงาน',
            'สถานภาพ พนง.',
            'วันที่สิ้นสภาพ',
            'อัตราค่าจ้างปัจจุบัน',
            'วันที่เริ่มอัตราปัจจุบัน',
            'อัตราค่าจ้างเดิม',
            'วันที่เริ่มจ่ายโดยโปรแกรม',
            'ความถี่การจ่ายค่าจ้าง',
            'วิธีหักเข้ากองทุน',
            'ยอดสะสมพนง.ต่อครั้ง',
            'ยอดบริษัทสมทบต่อครั้ง',
            'ยอดสะสมพนง.ปีก่อน',
            'ยอดบริษัทสมทบปีก่อน',
            'ยอดสะสมพนง.ปีนี้',
            'ยอดบริษัทสมทบปีนี้',
            'ต้องการหักปกสค.',
            'ยอดปกสค.พนักงานปีนี้',
            'ยอดปกสค.บริษัทปีนี้',
            'ยอดเงินค้ำประกัน',
            'ค้ำประกันที่เก็บแล้ว',
            'เก็บค้ำประกันครั้งละ',
            'ยอดเงินกู้',
            'ยอดชำระแล้ว',
            'หักชำระครั้งละ',
            'ประเภทการหักภาษี',
            'รายได้ก่อนทำงาน',
            'ภาษีถูกหักก่อนทำงาน',
            'รายได้ก่อนโปรแกรม',
            'ภาษีถูกหักก่อนโปรแกรม',
            'ภาษีบ.จ่ายก่อนโปรแกรม',
            'แยกยื่นหรือยื่นรวม',
            'ตัวคูณประมาณรายได้',
            'จำนวนบุตรอัตรา 1',
            'จำนวนบุตรอัตรา 2',
            'จำนวนบุตรอัตรา 3',
            'จำนวนบุตรอัตรา 4',
            'ค่าซื้อหน่วยลงทุน RMF',
            'ค่าซื้อหน่วยลงทุน LTF',
            'เบี้ยประกันชีวิต',
            'กู้ยืมเพื่อที่อยู่อาศัย',
            'บริจาค',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $QStaff = new Application_Model_Staff();
        $QPcCheckInLog = new Application_Model_PcCheckInLog();

        $QStore = new Application_Model_Store();
        $QStoreStaff = new Application_Model_StoreStaff();

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $where_staff = array();
            $where_staff[] = $QStaff->getAdapter()->quoteInto("public_id = ?", $data[$i]['public_id']);
            $result_staff = $QStaff->fetchRow($where_staff);

            $store_name = "";
            if ( isset($result_staff['id']) && $result_staff['id']) {
                $result_store = $QStoreStaff->getStore($result_staff['id']);

                $storeRowset = $QStore->find($result_store);
                $store = $storeRowset->current();
                $store_name = $store['name'];
            }

            $tmp = explode(" ", $data[$i]['staff_name']);

            if ( isset($result_staff['joined_at']) && $result_staff['joined_at'] ) {
                $joined_at = date('Ymd', strtotime($result_staff['joined_at']));
                $end_probation = date('Ymd', strtotime("+149 Day" , strtotime($result_staff['joined_at'])));   
            } else {
                $joined_at = "";
                $end_probation = "";   
            }

            
            $alpha    = 'A';
            //$sheet->setCellValue($alpha++.$index, $i+1);
            $sheet->setCellValue($alpha++.$index, $data[$i]['prefix']);
            $sheet->setCellValue($alpha++.$index, $tmp[0]);
            $sheet->setCellValue($alpha++.$index, $tmp[1]);
            $sheet->setCellValue($alpha++.$index, $data[$i]['prefix_en']." ".$data[$i]['staff_name_en']);
            $sheet->setCellValue($alpha++.$index, date('Ymd', strtotime($data[$i]['dob'])));
            $sheet->setCellValue($alpha++.$index, $data[$i]['gender_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['marital_status']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['public_id']);
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, $store_name);

            for ($j=0;$j<4;$j++) { $sheet->setCellValue($alpha++.$index, ""); }

            $sheet->setCellValue($alpha++.$index, '="'.$data[$i]['phone_number'].'"');

            for ($j=0;$j<2;$j++) { $sheet->setCellValue($alpha++.$index, ""); }

            $sheet->setCellValue($alpha++.$index, $result_staff['code']);
            $sheet->setCellValue($alpha++.$index, $result_staff['code']);

            $sheet->setCellValue($alpha++.$index, "P");

            for ($j=0;$j<2;$j++) { $sheet->setCellValue($alpha++.$index, ""); }

            $sheet->setCellValue($alpha++.$index, '="001"');
            $sheet->setCellValue($alpha++.$index, "2");
            $sheet->setCellValue($alpha++.$index, "KTB");

            for ($j=0;$j<2;$j++) { $sheet->setCellValue($alpha++.$index, ""); }

            $sheet->setCellValue($alpha++.$index, $joined_at);

            for ($j=0;$j<2;$j++) { $sheet->setCellValue($alpha++.$index, ""); }

            $sheet->setCellValue($alpha++.$index, $joined_at);
            $sheet->setCellValue($alpha++.$index, $joined_at);
            $sheet->setCellValue($alpha++.$index, $end_probation);
            $sheet->setCellValue($alpha++.$index, "A");
            $sheet->setCellValue($alpha++.$index, "");
            $sheet->setCellValue($alpha++.$index, "9750");
            $sheet->setCellValue($alpha++.$index, $joined_at);
            $sheet->setCellValue($alpha++.$index, "0");
            $sheet->setCellValue($alpha++.$index, $joined_at);
            $sheet->setCellValue($alpha++.$index, "1");

            for ($j=0;$j<7;$j++) { $sheet->setCellValue($alpha++.$index, "0"); }

            $sheet->setCellValue($alpha++.$index, "Y");

            for ($j=0;$j<2;$j++) { $sheet->setCellValue($alpha++.$index, "0"); }

            $sheet->setCellValue($alpha++.$index, "15000");
            $sheet->setCellValue($alpha++.$index, "0");
            $sheet->setCellValue($alpha++.$index, "500");

            for ($j=0;$j<3;$j++) { $sheet->setCellValue($alpha++.$index, "0"); }

            $sheet->setCellValue($alpha++.$index, "3");

            for ($j=0;$j<5;$j++) { $sheet->setCellValue($alpha++.$index, "0"); }

            $sheet->setCellValue($alpha++.$index, "X");
            $sheet->setCellValue($alpha++.$index, "12");

            for ($j=0;$j<9;$j++) { $sheet->setCellValue($alpha++.$index, "0"); }

            //$sheet->setCellValue($alpha++.$index, "end");

            $index++;

        }
        
        $filename = 'Export_Data_HR_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    //Page Report E-Test Answer
    public function reportEtestAnswerAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-etest-answer'  . DIRECTORY_SEPARATOR . 'report-etest-answer.php';
    }

    //Page Report E-Test Answer
    public function reportEtestAnswerEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-etest-answer'  . DIRECTORY_SEPARATOR . 'report-etest-answer-edit.php';
    }

    // Export Report E-Test Answer
    private function _exportEtestAnswerExcel($data, $params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $QStaff = new Application_Model_Staff();
        $QuestionsAnswerLog = new Application_Model_QuestionsAnswerLog();

        $quiz_choice = $QuestionsAnswerLog->getQuestionsChoice($params['head_id']);

        $heads = array(
            'ประทับเวลา',
            'รหัสพนักงาน',
            'ชื่อ-นามสกุล',
            'ตำแหน่ง',
            'PC Stand By',
            'Shop Level',
            'Area',
            'Province',
            'Market Name',
            'PCM',
            'PCM Leader',
            'คะแนน',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;

        foreach(array_merge($heads, $quiz_choice) as $key) {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $where_staff = array();
            $where_staff[] = $QStaff->getAdapter()->quoteInto("id = ?", $data[$i]['staff_id']);
            $result_staff = $QStaff->fetchRow($where_staff);

            $pcm = $QuestionsAnswerLog->getPcmName($data[$i]['area_id']);
            $pcm_leader = $QuestionsAnswerLog->getPcmLeader($data[$i]['area_id']);
            $market_name = $QuestionsAnswerLog->getPcMarketName($data[$i]['staff_id']);

            $shop_temp = $QuestionsAnswerLog->getPcShopLevel($data[$i]['staff_id']);

            $shop_level = '-';
            if ( !empty($shop_temp) ) { $shop_level = $shop_temp[0]['shop_level']; }
            if ( $data[$i]['pc_stand_by'] == 'Yes' ) { $shop_level = '-'; }

            $result_answer = array();
            if (isset($result_staff['id']) && $result_staff['id'] != '') {
                $result_answer = $QuestionsAnswerLog->getChoiceAnswer($result_staff['id'], $data[$i]['topic_id']);
            }

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);
            $sheet->setCellValue($alpha++.$index, $result_staff['code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pc_stand_by']);
            $sheet->setCellValue($alpha++.$index, $shop_level);

            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['province_name']);
            $sheet->setCellValue($alpha++.$index, implode(", ",array_map(function($el){ return $el['name']; },$market_name)));
            $sheet->setCellValue($alpha++.$index, $data[$i]['pcm_name']);
            $sheet->setCellValue($alpha++.$index, implode(", ",array_map(function($el){ return $el['name']; },$pcm_leader)));
            $sheet->setCellValue($alpha++.$index, ($data[$i]['score']*1).' / '.$data[$i]['score_total']);

            if (isset($result_staff['id']) && $result_staff['id'] != '') {
                for ($j=0;$j<count($result_answer);$j++) {
                    $sheet->setCellValue($alpha++.$index, $result_answer[$j]['choice'].'.'.$result_answer[$j]['answer']);
                }
            }

            $index++;
        }

        $filename = 'Export_etest_answer_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    /* Report Check List */
    public function reportCheckListAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'report-check-list' . DIRECTORY_SEPARATOR . 'report-check-list.php';
    }

    /* Report Check List */
    private function exportReportCheckList($data, $params) {

        function getStatus ($status, $solve) {
            if ($status == "Y" && $solve == "N") {
                $result = 'PASS';
            } else if ($status == "Y" && $solve == "Y") {
                $result = 'Solved';
            } else {
                $result = 'NOT PASS';
            }
            return $result;
        }

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $ReportCheckList= new Application_Model_ReportCheckList();
        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $heads1 = array(
            'PC basic information',
            'PCM information'
        );

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('H1:K1');

        $sheet->setCellValue('A1', $heads1[0]);
        $sheet->setCellValue('H1', $heads1[1]);

        $title_question = $ReportCheckList->getTitleQuestions($params['topic_id']);

        $start_title = 11;
        foreach (array_count_values($title_question) AS $k => $val) {
            $sheet->mergeCellsByColumnAndRow($start_title, 1, ($start_title + $val) - 1, 1);
            $sheet->setCellValueByColumnAndRow($start_title, 1, $k);
            $start_title = $start_title + $val;
        }

        $sheet->setCellValueByColumnAndRow($start_title, 1, "ALL");

        $head_questions = $ReportCheckList->getHeadQuestions($params['topic_id']);

        $heads2 = array(
            'Staff Code',
            'Staff Name',
            'Area',
            'Market Name',
            'Store ID',
            'Store Name',
            'Store Type',
            'จำนวนครั้ง',
            'รหัสผู้ตรวจสอบ',
            'ผู้ตรวจสอบ',
            'ประทับเวลา',
        );

        $alpha2    = 'A';
        $index2    = 2;

        $question = array_merge($heads2, $head_questions);
        foreach($question as $val) {
            $sheet->setCellValue($alpha2.$index2, $val);
            $alpha2++;
        }
        $sheet->setCellValueByColumnAndRow(count($question), $index2, "SUMMARY");

        $index = 3;
        for ($i=0;$i<count($data); $i++) {
            $alpha    = 'A';

            $result_answer = $ReportCheckList->getAnswerLog($data[$i]['pc_id'], $data[$i]['id']);

            $sheet->setCellValue($alpha++.$index, $data[$i]['pc_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pc_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['market_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_type']);
            $sheet->setCellValue($alpha++.$index, "1");
            $sheet->setCellValue($alpha++.$index, $data[$i]['pcm_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['pcm_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['created_at']);

            for ($j = 0; $j < count($result_answer); $j++) {
                $sheet->setCellValue($alpha++ . $index,  $result_answer[$j]['answer']);
            }

            $sheet->setCellValue($alpha++.$index, getStatus($data[$i]['status'], $data[$i]['solve']));

            $index++;
        }

        $filename = 'Export_Check_List_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    /* New PC Follow */
    public function newPcFollowAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'new-pc-follow' . DIRECTORY_SEPARATOR . 'new-pc-follow.php';
    }

    public function newPcFollowCreateAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'new-pc-follow' . DIRECTORY_SEPARATOR . 'new-pc-follow-create.php';
    }

    public function newPcFollowDetailAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'new-pc-follow' . DIRECTORY_SEPARATOR . 'new-pc-follow-detail.php';
    }

    /* Report New PC Follow */
    private function exportReportNewFcFollow($data, $params) {

//        echo '<pre>';
//        print_r($data);
//        die;

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $heads1 = array(
            'PC basic information',
            'PCM information',
            'All',
        );

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('J1:M1');

        $sheet->setCellValue('A1', $heads1[0]);
        $sheet->setCellValue('J1', $heads1[1]);
        $sheet->setCellValue('N1', $heads1[2]);

        $heads = array(
            'Staff Code',
            'Staff Name',
            'Joined At',
            'Total Work days',
            'Area',
            'Market Name',
            'Store ID',
            'Store Name',
            'Store Type',
            'จำนวนครั้ง',
            'รหัสผู้ตรวจสอบ',
            'ผู้ตรวจสอบ',
            'ประทับเวลา',
            'SUMMARY',
        );

        $alpha2    = 'A';
        $index2    = 2;

        foreach($heads as $val) {
            $sheet->setCellValue($alpha2.$index2, $val);
            $alpha2++;
        }
        $index = 3;
        for ($i=0;$i<count($data); $i++) {
            $alpha    = 'A';

            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['joined_at']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['date_diff']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['area']);
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['market_name']) ? $data[$i]['market_name'] : '-');
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['store_id']) ? $data[$i]['store_id'] : '-');
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['store_name']) ? $data[$i]['store_name'] : '-');
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['store_type']) ? $data[$i]['store_type'] : '-');
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_number']);
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['pcm_code']) ? $data[$i]['pcm_code'] : '-');
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['pcm_name']) ? $data[$i]['pcm_name'] : '-');
            $sheet->setCellValue($alpha++.$index, isset($data[$i]['created_at']) ? $data[$i]['created_at'] : '-');

            $sheet->setCellValue($alpha++.$index, ($data[$i]['status'] == 'Y') ? 'PASS' : 'NOT PASS');

            $index++;
        }

        $filename = 'Export_New_PC_Follow_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    /* Report New PC Follow */
    private function export_PC_Level_By_Channel($data, $params) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        //Start adding next sheets
        $j=0;
        foreach ($data as $key => $value) {
            // Add new sheet
            $sheet = $PHPExcel->createSheet($j); //Setting index when creating
            //Write cells
            $sheet->setCellValue('A1', 'Type');
            $sheet->setCellValue('B1', 'Channel');
            $sheet->setCellValue('C1', 'Senior');
            $sheet->setCellValue('E1', 'Advance');
            $sheet->setCellValue('G1', 'Advance');
            $sheet->setCellValue('I1', 'ALL Level PC');

            $sheet->mergeCells('C1:D1');
            $sheet->mergeCells('E1:F1');
            $sheet->mergeCells('G1:H1');
            $sheet->mergeCells('I1:J1');

            $index = 2;
            foreach ($value as $val) {
                $sum_pc_level =$val['level_senior'] + $val['level_advance'] + $val['level_master'];

                $share_01 = round(($val['level_senior'] / $sum_pc_level) * 100, 1);
                $share_02 = round(($val['level_advance'] / $sum_pc_level) * 100, 1);
                $share_03 = round(($val['level_master'] / $sum_pc_level) * 100, 1);

                $ratio = (is_nan($share_01) ? 0 : round(($share_01 / 10), 1)) . " : " . (is_nan($share_02) ? 0 : round(($share_02 / 10), 1)) . " : " . (is_nan($share_03) ? 0 : round(($share_03 / 10), 1));

                $alpha    = 'A';
                $sheet->setCellValue($alpha++.$index, $val['org_type']);
                $sheet->setCellValue($alpha++.$index, $val['org_name']);
                $sheet->setCellValue($alpha++.$index, $val['level_senior']);
                $sheet->setCellValue($alpha++.$index, (is_nan($share_01) ? 0 : $share_01) . "%");
                $sheet->setCellValue($alpha++.$index, $val['level_advance']);
                $sheet->setCellValue($alpha++.$index, (is_nan($share_02) ? 0 : $share_02) . "%");
                $sheet->setCellValue($alpha++.$index, $val['level_master']);
                $sheet->setCellValue($alpha++.$index, (is_nan($share_03) ? 0 : $share_03) . "%");
                $sheet->setCellValue($alpha++.$index, $sum_pc_level);
                $sheet->setCellValue($alpha++.$index, $ratio);
                $index++;
            }

            // Rename sheet
            $sheet->setTitle("$key");
            $j++;
        }
        $PHPExcel->setActiveSheetIndex(0);

        $filename = 'Export_PC_Level_By_Channel_'.date('Y-m-d');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    /* E-Test Info */
    public function eTestInfoAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info.php';
    }

    public function eTestInfoCreateAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-create.php';
    }

    public function eTestInfoSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-save.php';
    }

    public function eTestInfoDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-delete.php';
    }

    public function eTestInfoQuestionAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-question.php';
    }

    public function eTestInfoQuestionSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-question-save.php';
    }


    public function eTestInfoQuestionDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'e-test-info' . DIRECTORY_SEPARATOR . 'e-test-info-question-delete.php';
    }

    /* APK Demo Statistics */
    public function apkDemoStatisticsAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'apk-demo-statistics' . DIRECTORY_SEPARATOR . 'apk-demo-statistics.php';
    }
    public function apkDemoStatisticsCreateAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'apk-demo-statistics' . DIRECTORY_SEPARATOR . 'apk-demo-statistics-create.php';
    }
    // Export Report Apk Demo Statistics By Store
    private function _exportApkDemoStatisticsByStore($data) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Area Name',
            'Region',
            'Store ID',
            'Store Name',
            'IMEI',
            'Total Usage',
            'Coverage Time',
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

        $index = 2;
        for ($i=0;$i<count($data); $i++) {

            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['region']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_id']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['imei']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_usage']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['coverage_time']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['cnt_staff']);
            $index++;
        }

        $filename = 'ApkStatisticsByStore_'.date('d-m-Y_His');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }
    // Export Report Apk Demo Statistics By Staff
    private function _exportApkDemoStatisticsByStaff($data) {

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Area Name',
            'Region',
            'Staff Code',
            'Staff Name',
            'Group Name',
            'IMEI',
            'Total Usage',
            'Coverage Time',
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
            $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['region']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['imei']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['total_usage']);
            $sheet->setCellValue($alpha++.$index, $data[$i]['coverage_time']);
            $index++;
        }

        $filename = 'ApkStatisticsByStaff_'.date('d-m-Y_His');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;
    }

    /* Shop Check */
    public function manageShopCheckAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check.php';
    }

    public function manageShopCheckCreateAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-create.php';
    }

    public function manageShopCheckSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-save.php';
    }

    public function manageShopCheckDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-delete.php';
    }

    public function manageShopCheckQuestionAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-question.php';
    }

    public function manageShopCheckQuestionSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-question-save.php';
    }


    public function manageShopCheckQuestionDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-shop-check' . DIRECTORY_SEPARATOR . 'manage-shop-check-question-delete.php';
    }

    /* Customer Survey */
    public function manageCustomerSurveyAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-customer-survey' . DIRECTORY_SEPARATOR . 'manage-customer-survey.php';
    }

    public function manageCustomerSurveyCreateAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-customer-survey' . DIRECTORY_SEPARATOR . 'manage-customer-survey-create.php';
    }

    public function manageCustomerSurveySaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-customer-survey' . DIRECTORY_SEPARATOR . 'manage-customer-survey-save.php';
    }

    public function manageCustomerSurveyDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'manage-customer-survey' . DIRECTORY_SEPARATOR . 'manage-customer-survey-delete.php';
    }

    /* Product Info New */
    public function productInfoNewAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new.php';
    }

    public function productInfoNewEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-edit.php';
    }

    public function productInfoNewSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-save.php';
    }

    public function productInfoNewDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-delete.php';
    }

    public function productInfoNewSubEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-sub-edit.php';
    }

    public function productInfoNewSubSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-sub-save.php';
    }

    public function productInfoNewSubDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-sub-delete.php';
    }

    public function productInfoNewContentEditAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-content-edit.php';
    }

    public function productInfoNewContentSaveAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-content-save.php';
    }

    public function productInfoNewContentDeleteAction()
    {
        require_once 'trainer' . DIRECTORY_SEPARATOR . 'product-info-new' . DIRECTORY_SEPARATOR . 'product-info-new-content-delete.php';
    }
}
