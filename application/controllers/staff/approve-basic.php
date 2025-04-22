<?php
$id                 = $this->getRequest()->getParam('id');
$staff_id           = $this->getRequest()->getParam('staff_id');
$back_url           = $this->getRequest()->getParam('back_url');
$temp_note          = $this->getRequest()->getParam('temp_note');

$flashMessenger = $this->_helper->flashMessenger;
$QTag       = new Application_Model_Tag();
$QTagObject = new Application_Model_TagObject();

if ($this->getRequest()->getMethod() == 'GET') {

    $QRegionalMarket = new Application_Model_RegionalMarket();
    $QArea = new Application_Model_Area();
    $QStaffTemp = new Application_Model_StaffTemp();
    $QStaff     = new Application_Model_Staff();
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

            // get tags
            $where = array();
            $where[] = $QTagObject->getAdapter()->quoteInto('object_id = ?', $currentStaffId);
            $where[] = $QTagObject->getAdapter()->quoteInto('type = ?', TAG_STAFF);

            $a_tags = array();

            $tags_object = $QTagObject->fetchAll($where);
            if ($tags_object)
                foreach ($tags_object as $to)
                {
                    $where = $QTag->getAdapter()->quoteInto('id = ?', $to['tag_id']);
                    $tag = $QTag->fetchRow($where);
                    if ($tag)
                        $a_tags[] = $tag['name'];
                }

            $this->view->currentTags = $a_tags;

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

        $this->view->approve = 1;
        $this->_helper->viewRenderer->setRender('create-basic');

        //back url
        $this->view->back_url = $back_url ? $back_url : ($this->getRequest()->getServer
            ('HTTP_REFERER') ? $this->getRequest()->getServer('HTTP_REFERER') : '/staff/list-basic');

    } catch (Exception $e){
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
        $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
    }

}
elseif ($this->getRequest()->getMethod() == 'POST') {
    try {
        $db = Zend_Registry::get('db');

        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);

        $db->beginTransaction();

        $QStaff             = new Application_Model_Staff();
        $QStaffTemp         = new Application_Model_StaffTemp();
        $QStaffEducation    = new Application_Model_StaffEducation();
        $QStaffExperience   = new Application_Model_StaffExperience();
        $QStaffRelative     = new Application_Model_StaffRelative();

        if ($staff_id){
            $whereStaffTemp = $QStaffTemp->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $staffTemp = $QStaffTemp->fetchRow($whereStaffTemp);

            if (!$staffTemp)
                throw new Exception('Cannot find Temporary Staff record');

            $id = $staffTemp['id'];
        }

        $whereStaffTemp     = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);

        $staffTemp          = $QStaffTemp->fetchRow($whereStaffTemp);

        if (!$staffTemp)
            throw new Exception('Cannot find Temporary record');

        if ($staffTemp['is_approved'])
            throw new Exception('This Temporary record was approved');

        $reject = $this->getRequest()->getParam('reject');

        if ($reject) {

            $whereStaffTemp     = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
            $QStaffTemp->update(array(
                'note'          => $temp_note,
                'staff_id'      => $staffTemp['staff_id'],
                'is_approved'   => -1,
            ), $whereStaffTemp);

            $db->commit();
            $flashMessenger->setNamespace('success')->addMessage('Done!');

            $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
        }

        $currentTime = date('Y-m-d H:i:s');

        $data = array(
            'firstname' => $staffTemp['firstname'],
            'lastname' => $staffTemp['lastname'],
            'gender' => $staffTemp['gender'],
            'department' => $staffTemp['department'],
            'team' => $staffTemp['team'],
            'title' => $staffTemp['title'],
            'joined_at' => $staffTemp['joined_at'],
            'regional_market' => $staffTemp['regional_market'],
            'company_id' => $staffTemp['company_id'],
            'phone_number' => $staffTemp['phone_number'],
            'dob' => $staffTemp['dob'],
            'ID_number' => $staffTemp['ID_number'],
            'ID_place' => $staffTemp['ID_place'],
            'ID_date' => $staffTemp['ID_date'],
            'nationality' => $staffTemp['nationality'],
            'religion' => $staffTemp['religion'],
            'marital_status' => $staffTemp['marital_status'],
        );

        // get is officer
        $isNotHeadOffice = $QStaff->checkIsNotHeadOffice($staffTemp['department'], $staffTemp['team'], $staffTemp['title']);
        if ($isNotHeadOffice){
            $data['is_officer'] = 0;
        } else {
            $data['is_officer'] = 1;
        }

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        // update staff
        $oldData = array();
        $staff_id = null;
        if ($staffTemp['staff_id']) {
            $staff_id = $staffTemp['staff_id'];

            $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staffTemp['staff_id']);
            $staff = $QStaff->fetchRow($whereStaff);

            if (!$staff)
                throw new Exception('Cannot find Staff record');

            $oldData = $staff->toArray();

            $data['updated_at'] = $currentTime;
            $data['updated_by'] = $userStorage->id;

            $QStaff->update($data, $whereStaff);
        }
        // insert new staff
        else {

            // generate staff code
            $data['code'] = $QStaff->genStaffCode($staffTemp['joined_at']);

            $data['password'] = md5('123456');
            $data['created_at'] = $currentTime;
            $data['created_by'] = $userStorage->id;

            $staff_id = $QStaff->insert($data);

            // insert hop dong thu viec
            $contractResult = $this->contractAdd($staff_id, $data['joined_at']);
            if ($contractResult['code'] != 1)
                throw new Exception($contractResult['message']);
        }

        // insert address
        $tempData = json_decode($staffTemp['data'], true);

        $QStaffAddress = new Application_Model_StaffAddress();

        if ($tempData) {

            $addressListData = isset($tempData['address_data']) ? $tempData['address_data'] : null;

            if ($addressListData){
                foreach ($addressListData as $item){

                    $address_data = array(
                        'staff_id'      => $staff_id,
                        'address_type'  => ((isset($item['address_type']) and $item['address_type']) ? $item['address_type'] : 0),
                        'address'       => (isset($item['address']) ? $item['address'] : ''),
                        'ward'          => (isset($item['ward']) ? $item['ward'] : ''),
                        'district'      => ((isset($item['district']) and $item['district']) ? $item['district'] : 0),
                    );

                    $whereStaffAddress = array();
                    $whereStaffAddress[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                    $whereStaffAddress[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                        $item['address_type']);

                    $staffAddress = $QStaffAddress->fetchRow($whereStaffAddress);
                    if (!$staffAddress){
                        $QStaffAddress->insert($address_data);
                    } else {
                        $QStaffAddress->update($address_data, $whereStaffAddress);
                    }
                }
            }

            // education data
            $educationListData = isset($tempData['education_data']) ? $tempData['education_data'] : null;
            // delete old education
            $whereStaffEducation = $QStaffEducation->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $QStaffEducation->delete($whereStaffEducation);
            if ($educationListData){
                foreach ($educationListData as $item){
                    $QStaffEducation->insert(array(
                        'staff_id'      => $staff_id,
                        'level'         => $item['level'],
                        'school'        => $item['school'],
                        'field_of_study'=> $item['field_of_study'],
                        'graduated_year'=> intval($item['graduated_year']),
                        'grade'         => $item['grade'],
                        'mode_of_study' => $item['mode_of_study'],
                        'default_level' => $item['default_level']
                    ));
                }
                //kiem tra chi cho phep 1 record la default
                $selectDefaultLevel = $QStaffEducation->select()
                    ->where('staff_id = ?',$id)
                    ->where('default_level = ?',1);
                $result = $QStaffEducation->fetchAll($selectDefaultLevel);
                if($result->count() > 1){
                    $flashMessenger->setNamespace('error')->addMessage('Education default is only single');
                    $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
                }   
            }

            // experience data
            $experienceListData = isset($tempData['experience_data']) ? $tempData['experience_data'] : null;

            // delete old education
            $whereStaffExperience = $QStaffExperience->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $QStaffExperience->delete($whereStaffExperience);
            if ($experienceListData){
                foreach ($experienceListData as $item){
                    $QStaffExperience->insert(array(
                        'staff_id'      => $staff_id,
                        'company_name'  => $item['company_name'],
                        'job_position'  => $item['job_position'],
                        'from_date'     => $this->_formatDate($item['from_date']),
                        'to_date'       => $this->_formatDate($item['to_date']),
                        'reason_for_leaving' => $item['reason_for_leaving'],
                    ));
                }
            }

            // relative data
            $relativeListData = isset($tempData['relative_data']) ? $tempData['relative_data'] : null;
            // delete old relative
            $whereStaffRelative = $QStaffRelative->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $QStaffRelative->delete($whereStaffRelative);
            if ($relativeListData){
                foreach ($relativeListData as $item){
                    $QStaffRelative->insert(array(
                        'staff_id'      => $staff_id,
                        'relative_type' => $item['relative_type'],
                        'full_name'     => $item['full_name'],
                        'gender'        => $item['gender'],
                        'birth_year'    => $item['birth_year'],
                        'job'           => $item['job'],
                        'work_place'    => $item['work_place'],
                    ));
                }
            }
        }

        // insert photo
        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' .
            DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR . 'temp'. DIRECTORY_SEPARATOR . $staffTemp['id'];

        $uploadedPrimarydir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' .
            DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR . $staff_id;

        $arrPhoto = array(
            'photo' => array(
                'temp' => $uploaded_dir,
                'primary' => $uploadedPrimarydir,
            ),
            'id_photo' => array(
                'temp' => $uploaded_dir.DIRECTORY_SEPARATOR.'ID_Front',
                'primary' => $uploadedPrimarydir.DIRECTORY_SEPARATOR.'ID_Front',
            ),
            'id_photo_back' => array(
                'temp' => $uploaded_dir.DIRECTORY_SEPARATOR.'ID_Back',
                'primary' => $uploadedPrimarydir.DIRECTORY_SEPARATOR.'ID_Back',
            ),
        );

        $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
        $staff = $QStaff->fetchRow($whereStaff);

        if (!$staff)
            throw new Exception('Cannot find Staff record');

        foreach ($arrPhoto as $key=>$val){
            if ($staffTemp[$key]) {
                if (!is_dir($val['primary']))
                    @mkdir($val['primary'], 0777, true);

                copy($val['temp'] . DIRECTORY_SEPARATOR . $staffTemp[$key], $val['primary'] . DIRECTORY_SEPARATOR . $staffTemp[$key]);

                $data = array($key => $staffTemp[$key]);
                $QStaff->update($data, $whereStaff);

                @unlink($val['temp'] . DIRECTORY_SEPARATOR . $staffTemp[$key]);

            }elseif ($staffTemp['is_update_'.$key]){

                $data = array($key => null);
                $QStaff->update($data, $whereStaff);

                @unlink($val['temp'] . DIRECTORY_SEPARATOR . $staff[$key]);
            }
        }

        // update note, tag, group, log
        $note = $this->getRequest()->getParam('note');
        $group_id = $this->getRequest()->getParam('group_id');
        $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
        $staff = $QStaff->fetchRow($whereStaff);

        $newData = $staff->toArray();
        if ($oldData)
            Log::w($oldData, $newData, $staff_id, LogGroup::Staff, LogType::Update);
        else
            Log::w(array(), $newData, $staff_id, LogGroup::Staff, LogType::Insert);

        $QStaff->update(array(
            'note'      => $note,
            'group_id'  => $group_id,
        ), $whereStaff);

        $tags = $this->getRequest()->getParam('tags');
        $QTag->add($tags, $staff_id, TAG_STAFF);

        // update temp is_approved = 1
        $whereStaffTemp     = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
        $QStaffTemp->delete($whereStaffTemp);

        $cache = Zend_Registry::get('cache');
        $cache->remove('staff_cache');
        
        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');

    } catch (Exception $e){

        $db->rollback();
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    }

    $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
}