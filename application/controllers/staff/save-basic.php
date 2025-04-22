<?php
$this->_helper->layout()->disableLayout(true);
$this->_helper->viewRenderer->setNoRender(true);

$back_url = $this->getRequest()->getParam('back_url');

if ($this->getRequest()->getMethod() == 'POST') {
    $flashMessenger = $this->_helper->flashMessenger;

    try {
        $QStaffTemp = new Application_Model_StaffTemp();
        $QStaff = new Application_Model_Staff();

        $id              = $this->getRequest()->getParam('id');
        $staff_id        = $this->getRequest()->getParam('staff_id');
        $photo           = $this->getRequest()->getParam('photo');
        $id_photo        = $this->getRequest()->getParam('id_photo');
        $firstname       = $this->getRequest()->getParam('firstname');
        $lastname        = $this->getRequest()->getParam('lastname');
        $gender          = $this->getRequest()->getParam('gender');
        $department      = $this->getRequest()->getParam('department');
        $team            = $this->getRequest()->getParam('team');
        $title           = $this->getRequest()->getParam('title');
        $joined_at       = $this->getRequest()->getParam('joined_at');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $company_id      = $this->getRequest()->getParam('company_id');
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $temp_note       = $this->getRequest()->getParam('temp_note');
        $tags            = $this->getRequest()->getParam('tags');
        $dob             = $this->getRequest()->getParam('dob');
        $ID_number       = $this->getRequest()->getParam('ID_number');
        $ID_place        = $this->getRequest()->getParam('ID_place');
        $ID_date         = $this->getRequest()->getParam('ID_date');
        $nationality     = $this->getRequest()->getParam('nationality');
        $religion        = $this->getRequest()->getParam('religion');
        $marital_status  = $this->getRequest()->getParam('marital_status',
            My_Staff_MaritalStatus::Single);

        // get address
        $temp_address     = $this->getRequest()->getParam('temp_address');
        $temp_ward        = $this->getRequest()->getParam('temp_ward');
        $temp_district    = $this->getRequest()->getParam('temp_district');
        $temp_province    = $this->getRequest()->getParam('temp_province');

        $perm_address     = $this->getRequest()->getParam('perm_address');
        $perm_ward        = $this->getRequest()->getParam('perm_ward');
        $perm_district    = $this->getRequest()->getParam('perm_district');
        $perm_province    = $this->getRequest()->getParam('perm_province');

        $birth_ward       = $this->getRequest()->getParam('birth_ward');
        $birth_district   = $this->getRequest()->getParam('birth_district');
        $birth_province   = $this->getRequest()->getParam('birth_province');

        $id_card_address  = $this->getRequest()->getParam('id_card_address');
        $id_card_ward     = $this->getRequest()->getParam('id_card_ward');
        $id_card_district = $this->getRequest()->getParam('id_card_district');
        $id_card_province = $this->getRequest()->getParam('id_card_province');

        $level              = $this->getRequest()->getParam('level');
        $school             = $this->getRequest()->getParam('school');
        $field_of_study     = $this->getRequest()->getParam('field_of_study');
        $graduated_year     = $this->getRequest()->getParam('graduated_year');
        $grade              = $this->getRequest()->getParam('grade');
        $mode_of_study      = $this->getRequest()->getParam('mode_of_study');
        $default_level      = $this->getRequest()->getParam('default_level');
        
        $company_name       = $this->getRequest()->getParam('company_name');
        $job_position       = $this->getRequest()->getParam('job_position');
        $from_date          = $this->getRequest()->getParam('from_date');
        $to_date            = $this->getRequest()->getParam('to_date');
        $reason_for_leaving = $this->getRequest()->getParam('reason_for_leaving');
        
        $relative_type      = $this->getRequest()->getParam('relative_type');
        $full_name          = $this->getRequest()->getParam('full_name');
        $rlt_gender         = $this->getRequest()->getParam('rlt_gender');
        $birth_year         = $this->getRequest()->getParam('birth_year');
        $job                = $this->getRequest()->getParam('job');
        $work_place         = $this->getRequest()->getParam('work_place');
        
        $del_photo          = $this->getRequest()->getParam('del_photo', 0);
        $del_id_photo       = $this->getRequest()->getParam('del_id_photo', 0);
        $del_id_photo_back  = $this->getRequest()->getParam('del_id_photo_back', 0);

        $currentTime = date('Y-m-d H:i:s');

        $postedData = $data = array(
            'firstname'       => My_String::trim($firstname),
            'lastname'        => My_String::trim($lastname),
            'gender'          => intval($gender),
            'department'      => intval($department),
            'team'            => intval($team),
            'title'           => intval($title),
            'joined_at'       => $this->_formatDate($joined_at),
            'regional_market' => intval($regional_market),
            'company_id'      => intval($company_id),
            'phone_number'    => $phone_number,
            'note'            => $temp_note,
            'dob'             => $dob,
            'ID_number'       => $ID_number,
            'ID_place'        => $ID_place,
            'ID_date'         => $this->_formatDate($ID_date),
            'nationality'     => intval($nationality),
            'religion'        => intval($religion),
            'marital_status'  => intval($marital_status),
            'is_approved'     => 0,
        );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        // check quyen view: neu la team sales admin moi gioi han
        if (CHECK_USER_EDIT_AREA){
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            if ($userStorage->title == SALES_ADMIN_TITLE){

                $QAsm = new Application_Model_Asm();
                $cachedASM     = $QAsm->get_cache($userStorage->id);
                $listAreaIds = $cachedASM['area'];

                $rowset = $QRegionalMarket->find($regional_market);
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

        $currentStaff = $staffTemp = null;

        if ($id) {
            $where = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
            $staffTemp = $QStaffTemp->fetchRow($where);

            $where = $QStaff->getAdapter()->quoteInto('id = ?', $staffTemp['staff_id']);
            $currentStaff = $QStaff->fetchRow($where);
        }

        if ($staff_id){

            $where = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
            $currentStaff = $QStaff->fetchRow($where);

            $where = $QStaffTemp->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $staffTemp = $QStaffTemp->fetchRow($where);

            if ($staffTemp){
                $id = $staffTemp['id'];
            }

        }

        if ($staffTemp) {
            $where = $QStaffTemp->getAdapter()->quoteInto('id = ?', $staffTemp['id']);

            $data['updated_at'] = $currentTime;
            $data['updated_by'] = $userStorage->id;

            $staffTempRowset = $QStaffTemp->find($id);
            $s = $staffTempRowset->current();

            $QStaffTemp->update($data, $where);

        } else {

            if (isset($currentStaff['id']) and $currentStaff['id'])
                $data['staff_id']   = $currentStaff['id'];

            $data['created_at'] = $currentTime;
            $data['created_by'] = $userStorage->id;

            $id = $QStaffTemp->insert($data);

        }

        $address_data = array();

        if ($id) {

            // ----------------- upload
            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' .
                DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $id ;

            $arrPhoto = array(
                'photo' => $uploaded_dir,
                'id_photo' => $uploaded_dir.DIRECTORY_SEPARATOR.'ID_Front',
                'id_photo_back' => $uploaded_dir.DIRECTORY_SEPARATOR.'ID_Back',
            );

            $upload = new Zend_File_Transfer();
            $upload->setOptions(array('ignoreNoFile'=>true));

            //check function
            if (function_exists('finfo_file'))
                $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

            $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');
            $upload->addValidator('Size', false, array('max' => '2MB'));
            $upload->addValidator('ExcludeExtension', false, 'php,sh');
            $files = $upload->getFileInfo();

            $hasPhoto = false;

            $data = array();
            foreach ($arrPhoto as $key=>$val){
                $del = 'del_'.$key;
                if (isset($$del) and $$del){
                    $data[$key] = null;
                    $data['is_update_'.$key] = 1;
                }

                if (isset($files[$key]['name']))
                    $hasPhoto = true;

            }

            if ($hasPhoto) {

                if (!$upload->isValid()){
                    $errors = $upload->getErrors();

                    $sError = null;

                    if ($errors and isset($errors[0]))
                        switch ($errors[0]){
                            case 'fileUploadErrorIniSize':
                            case 'fileFilesSizeTooBig':
                                $sError = 'File size is too large';
                                break;
                            case 'fileMimeTypeFalse':
                            case 'fileExtensionFalse':
                                $sError = 'The file(s) you selected weren\'t the type we were expecting';
                                break;
                            default:
                                $sError = $errors[0];
                                break;
                        }

                    throw new Exception($sError);
                }


                foreach ($arrPhoto as $key=>$val){
                    $fileInfo = (isset($files[$key]) and $files[$key]) ? $files[$key] : null;
                    if (isset($fileInfo['name']) and $fileInfo['name']) {

                        if (!is_dir($val))
                            @mkdir($val, 0777, true);

                        $upload->setDestination($val);

                        $old_name = $fileInfo['name'];

                        $tExplode = explode('.', $old_name);
                        $extension = end($tExplode);

                        $new_name = 'UPLOAD-' . md5(uniqid('', true)) . '.' . $extension;

                        $upload->addFilter('Rename', array('target' => $val .
                            DIRECTORY_SEPARATOR . $new_name));

                        $r = $upload->receive(array($key));

                        if ($r){
                            $data[$key] = $new_name;
                            $data['is_update_'.$key] = 1;
                        }else{
                            $messages = $upload->getMessages();
                            foreach ($messages as $msg)
                                throw new Exception($msg);
                        }
                    }
                }

            }

            if ($data){
                $whereStaffTemp = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
                $QStaffTemp->update($data, $whereStaffTemp);
            }
            // -------------- /upload

            if ($temp_province) {
                $address_data[My_Staff_Address::Temporary] = array(
                    'address_type' => My_Staff_Address::Temporary,
                    'address'      => $temp_address,
                    'ward'         => $temp_ward,
                    'district'     => $temp_district,
                );

                if (isset($currentStaff['id']) and $currentStaff['id'])
                    $address_data[My_Staff_Address::Temporary]['staff_id']   = $currentStaff['id'];
            }

            if ($perm_province) {
                $address_data[My_Staff_Address::Permanent] = array(
                    'address_type' => My_Staff_Address::Permanent,
                    'address' => $perm_address,
                    'ward' => $perm_ward,
                    'district' => $perm_district,
                );

                if (isset($currentStaff['id']) and $currentStaff['id'])
                    $address_data[My_Staff_Address::Permanent]['staff_id']   = $currentStaff['id'];
            }

            if ($id_card_province) {
                $address_data[My_Staff_Address::ID_Card] = array(
                    'address_type' => My_Staff_Address::ID_Card,
                    'address'      => $id_card_address,
                    'ward'         => $id_card_ward,
                    'district'     => $id_card_district,
                );

                if (isset($currentStaff['id']) and $currentStaff['id'])
                    $address_data[My_Staff_Address::ID_Card]['staff_id']   = $currentStaff['id'];
            }

            if ($birth_province) {
                $address_data[My_Staff_Address::Birth_Certificate] = array(
                    'address_type' => My_Staff_Address::Birth_Certificate,
                    'ward'         => $birth_ward,
                    'district'     => $birth_district,
                );

                if (isset($currentStaff['id']) and $currentStaff['id'])
                    $address_data[My_Staff_Address::Birth_Certificate]['staff_id']   = $currentStaff['id'];
            }

            // education
            $education_data = array();
            if ($level){
                for ($i=0; $i<count($level); $i++){
                    $education_data[] = array(
                        'level'             => $level[$i],
                        'school'            => $school[$i],
                        'field_of_study'    => $field_of_study[$i],
                        'graduated_year'    => intval($graduated_year[$i]),
                        'grade'             => $grade[$i],
                        'mode_of_study'     => $mode_of_study[$i],
                        'default_level'     => (isset($default_level[$i]) AND $default_level[$i] == 1) ? 1: 0
                        );
                }
            }
 
            // experience
            $experience_data = array();
            if ($company_name){
                for ($i=0; $i<count($company_name); $i++){
                    $experience_data[] = array(
                        'company_name'       => $company_name[$i],
                        'job_position'       => $job_position[$i],
                        'from_date'          => $from_date[$i],
                        'to_date'            => $to_date[$i],
                        'reason_for_leaving' => $reason_for_leaving[$i],
                    );
                }
            }

            // relative
            $relative_data = array();
            for ($i=0; $i<count($relative_type); $i++){
                $relative_data[] = array(
                    'relative_type' => $relative_type[$i],
                    'full_name'     => $full_name[$i],
                    'gender'        => intval($rlt_gender[$i]),
                    'birth_year'    => intval($birth_year[$i]),
                    'job'           => $job[$i],
                    'work_place'    => $work_place[$i],
                );
            }

            $dt = array(
                'address_data'      => $address_data,
                'education_data'    => $education_data,
                'experience_data'   => $experience_data,
                'relative_data'     => $relative_data,
            );
            $dataTemp = array(
                'data' => json_encode($dt));

            if ($staff_id){
                $QStaff = new Application_Model_Staff();
                $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
                $staff = $QStaff->fetchRow($whereStaff);
                $dataTemp['code'] = $staff['code'];
            }

            $postedData['data'] = $dt;

            $whereStaffTemp = $QStaffTemp->getAdapter()->quoteInto('id = ?', $id);
            $QStaffTemp->update($dataTemp, $whereStaffTemp);
        }

        // log thong tin thay doi
        $beforeData = null;
        if ($staffTemp)
            $beforeData = $staffTemp->toArray();
        elseif($currentStaff){

            $beforeData = $currentStaff->toArray();

            $currentStaffId = $currentStaff['id'];

            // get permanent address
            $QStaffAddress = new Application_Model_StaffAddress();
            $QRegionalMarket = new Application_Model_RegionalMarket();

            $all_province_cache = $QRegionalMarket->get_cache();
            $district_cache = $QRegionalMarket->get_district_cache();

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

            $QStaffEducation = new Application_Model_StaffEducation();
            $whereStaffEducation = $QStaffEducation->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
            $currentEducationData = $QStaffEducation->fetchAll($whereStaffEducation);
            $staffEducation = array();
            if ($currentEducationData->count())
                $staffEducation = $currentEducationData->toArray();


            $QStaffExperience = new Application_Model_StaffExperience();
            $whereStaffExperience = $QStaffExperience->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
            $currentExperience = $QStaffExperience->fetchAll($whereStaffExperience);
            $staffExperience = array();
            if ($currentExperience->count())
                $staffExperience = $currentExperience->toArray();

            $QStaffRelative = new Application_Model_StaffRelative();
            $where = $QStaffRelative->getAdapter()->quoteInto('staff_id = ?', $currentStaffId);
            $currentRelative = $QStaffRelative->fetchAll($where);
            $staffRelative = array();
            if ($currentRelative->count())
                $staffRelative = $currentRelative->toArray();

            $beforeData['data'] = array(
                    'address_data'      => $currentAddresses,
                    'education_data'    => $staffEducation,
                    'experience_data'   => $staffExperience,
                    'relative_data'     => $staffRelative,
                );

        }

        $QStaffTempLog = new Application_Model_StaffTempLog();
        $QStaffTempLog->insert(array(
            'staff_temp_id'     => $id,
            'before_data'       => json_encode($beforeData),
            'after_data'        => json_encode($postedData),
            'created_at'        => $currentTime,
            'created_by'        => $userStorage->id,
        ));
        // End of log thong tin thay doi

        $flashMessenger->setNamespace('success')->addMessage('Done!');

    } catch (Exception $e){
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    }

}

$this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));