<?php
$id = $this->getRequest()->getParam('id');
$staff_id = $this->getRequest()->getParam('staff_id');
$back_url = $this->getRequest()->getParam('back_url');

$flashMessenger = $this->_helper->flashMessenger;

$QRegionalMarket = new Application_Model_RegionalMarket();
$QArea = new Application_Model_Area();
$QStaffTemp = new Application_Model_StaffTemp();
$QStaff     = new Application_Model_Staff();
$QTag       = new Application_Model_Tag();
$QTagObject = new Application_Model_TagObject();
$QStaffAddress = new Application_Model_StaffAddress();

$this->view->areasCached = $QArea->get_cache();
$all_province_cache = $QRegionalMarket->get_cache();
$this->view->all_province_cache = $all_province_cache;
$this->view->provinceAllCached = $QRegionalMarket->get_cache_all();
$district_cache = $QRegionalMarket->get_district_cache();
$this->view->district_cache = $district_cache;

$this->view->staffsCached = $QStaff->get_cache();

try {

    $staffTemp = $currentStaff = null;

    if ($staff_id){
        $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
        $currentStaff = $staff = $QStaff->fetchRow($whereStaff);
        if (!$currentStaff)
            throw new Exception('Cannot find Staff record');

        $whereStaffTemp     = $QStaffTemp->getAdapter()->quoteInto('staff_id = ?', $currentStaff['id']);
        $staffTemp          = $QStaffTemp->fetchRow($whereStaffTemp);

        // check quyen view: neu la team sales admin moi gioi han
        if (CHECK_USER_EDIT_AREA){
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            if ($userStorage->title == SALES_ADMIN_TITLE){

                $QAsm = new Application_Model_Asm();
                $cachedASM     = $QAsm->get_cache($userStorage->id);
                $listAreaIds = $cachedASM['area'];

                $rowset = $QRegionalMarket->find($currentStaff['regional_market']);
                $provinceUser = $rowset->current();
                $area_user = $provinceUser['area_id'];

                if (!$listAreaIds)
                    throw new Exception('You don\'t have permission on this Area');

                if (
                    (is_array($listAreaIds) and !in_array($area_user, $listAreaIds))
                    or (!is_array($listAreaIds) and $listAreaIds != $area_user)
                )
                    throw new Exception('You don\'t have permission on this Area');
            }
        }
    }

    if ($id) {

        $whereStaffTemp     = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
        $staffTemp          = $QStaffTemp->fetchRow($whereStaffTemp);


        if ($staffTemp['staff_id']){
            $whereStaff     = $QStaff->getAdapter()->quoteInto('id = ?', $staffTemp['staff_id']);
            $currentStaff          = $QStaff->fetchRow($whereStaff);
        }

    }

    if ($staffTemp){
        $currentStaffId = $staffTemp['staff_id'];

        //get area & province
        $rowset = $QRegionalMarket->find($staffTemp['regional_market']);

        if ($rowset) {
            $tempRegionalMarket = $rowset->current();
            $this->view->tempRegionalMarket = $tempRegionalMarket;

            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $tempRegionalMarket['area_id']);

            $this->view->tempRegionalMarkets = $QRegionalMarket->fetchAll($where);

            $rowset = $QArea->find($tempRegionalMarket['area_id']);
            $this->view->tempArea = $rowset->current();
        }

        // get addresses
        $tempData = json_decode($staffTemp['data'], true);

        $this->view->tempEducation = isset($tempData['education_data']) ? $tempData['education_data'] : null;
        $this->view->tempExperience = isset($tempData['experience_data']) ? $tempData['experience_data'] : null;
        $this->view->tempRelative = isset($tempData['relative_data']) ? $tempData['relative_data'] : null;

        $this->view->tempAddresses = $tempData['address_data'];

    }

    if ($currentStaff){

        //get area & province
        $rowset = $QRegionalMarket->find($currentStaff['regional_market']);

        if ($rowset) {
            $currentRegionalMarket = $rowset->current();
            $this->view->currentRegionalMarket = $currentRegionalMarket;

            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $currentRegionalMarket['area_id']);

            $this->view->currentRegionalMarkets = $QRegionalMarket->fetchAll($where);

            $rowset = $QArea->find($currentRegionalMarket['area_id']);
            $this->view->currentArea = $rowset->current();
        }

        $currentStaffId = $currentStaff['id'];

        // get permanent address
        $whereStaffAddress = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
        $dataAddresses = $QStaffAddress->fetchAll($whereStaffAddress);
        $currentAddresses = array();
        if ($dataAddresses->count()){
            foreach ($dataAddresses as $item){
                $provinceName = isset($all_province_cache[$district_cache[$item['district']]['parent']]) ? $all_province_cache[$district_cache[$item['district']]['parent']] : '';
                $districtName = isset($district_cache[$item['district']]['name']) ? $district_cache[$item['district']]['name'] : '';
                $currentAddresses[$item['address_type']] = array(
                    'staff_id'      => $item['staff_id'],
                    'address_type'  => $item['address_type'],
                    'address'       => $item['address'],
                    'ward'          => $item['ward'],
                    'district'      => $item['district'],
                    'province_name' => $provinceName,
                    'district_name' => $districtName,
                );
            }
        }
        $this->view->currentAddresses = $currentAddresses;

        $QStaffEducation = new Application_Model_StaffEducation();
        $where = $QStaffEducation->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
        $currentEducationData = $QStaffEducation->fetchAll($where);
        $staffEducation = array();
        if ($currentEducationData->count())
            $staffEducation = $currentEducationData->toArray();
        $this->view->currentEducation = $currentEducationData;

        $QStaffExperience = new Application_Model_StaffExperience();
        $where = $QStaffExperience->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
        $currentExperienceData = $QStaffExperience->fetchAll($where);
        $staffExperience = array();
        if ($currentExperienceData->count())
            $staffExperience = $currentExperienceData->toArray();
        $this->view->currentExperience = $currentExperienceData;

        $QStaffRelative = new Application_Model_StaffRelative();
        $where = $QStaffRelative->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
        $currentRelativeData = $QStaffRelative->fetchAll($where);
        $staffRelative = array();
        if ($currentRelativeData->count())
            $staffRelative = $currentRelativeData->toArray();

        $this->view->currentRelative = $currentRelativeData;

        // get log
        $beforeData = $currentStaff->toArray();

        $beforeData['data'] = array(
            'address_data'      => $currentAddresses,
            'education_data'    => $staffEducation,
            'experience_data'   => $staffExperience,
            'relative_data'     => $staffRelative,
        );

        $this->view->beforeData = $beforeData;

        if ($staffTemp){
            $afterData = $staffTemp->toArray();
            $tempData = json_decode($afterData['data'], true);
            $afterData['data'] = $tempData;
            $this->view->afterData = $afterData;
        }
        // End of get log
    }

    // get log
    /*if ($staffTemp){
        $QStaffTempLog      = new Application_Model_StaffTempLog();
        $whereStaffTempLog  = $QStaffTempLog->getAdapter()->quoteInto('staff_temp_id = ?', $staffTemp['id']);
        $staffTempLog       = $QStaffTempLog->fetchRow($whereStaffTempLog, 'id DESC');

        $beforeData = isset($staffTempLog['before_data']) ? json_decode($staffTempLog['before_data'], true) : null;
        $afterData  = isset($staffTempLog['after_data']) ? json_decode($staffTempLog['after_data'], true) : null;

        $this->view->beforeData = $beforeData;
        $this->view->afterData = $afterData;
    }*/
    // End of get log

    $this->view->districtsByProvinceCached = $QRegionalMarket->get_district_by_province_cache();

    $this->view->currentStaff = $currentStaff;
    $this->view->staffTemp = $staffTemp;

    $this->view->id = $id;
    $this->view->staff_id = $staff_id;

    $QCompany = new Application_Model_Company();
    $this->view->companies = $QCompany->get_cache();

    $QGroup = new Application_Model_Group();
    $this->view->groups = $QGroup->get_cache();

    $QModel = new Application_Model_ContractType();
    $this->view->contract_types = $QModel->fetchAll();
    $this->view->contractTypesCached = $QModel->get_cache();

    $QModel = new Application_Model_ContractTerm();
    $this->view->contract_terms = $QModel->fetchAll();
    $this->view->contractTermsCached = $QModel->get_cache();

    $QModel = new Application_Model_Department();
    $this->view->departments = $QModel->fetchAll();

    //get teams
    $QTeam = new Application_Model_Team();
    $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();

    $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;
    $this->view->teamsCached = $QTeam->get_cache();

    $QModel = new Application_Model_Religion();
    $this->view->religions = $QModel->fetchAll();
    $this->view->religionsCached = $QModel->get_cache();

    $QModel = new Application_Model_Nationality();
    $this->view->nationalities = $QModel->fetchAll();
    $this->view->nationalitiesCached = $QModel->get_cache();

    $flashMessenger = $this->_helper->flashMessenger;
    $messages = $flashMessenger->setNamespace('error')->getMessages();
    $this->view->messages = $messages;

    $messages_success = $flashMessenger->setNamespace('success')->getMessages();
    $this->view->messages_success = $messages_success;


    //back url
    $this->view->back_url = $back_url ? $back_url : ($this->getRequest()->getServer
        ('HTTP_REFERER') ? $this->getRequest()->getServer('HTTP_REFERER') : '/staff/list-basic');

} catch (Exception $e){
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
}