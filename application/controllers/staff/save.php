<?php
$back_url = $this->getRequest()->getParam('back_url');

if ($this->getRequest()->getMethod() == 'POST') {
    $QStaff = new Application_Model_Staff();
    $flashMessenger = $this->_helper->flashMessenger;

    $id                 = $this->getRequest()->getParam('id');
    $del_photo          = $this->getRequest()->getParam('del_photo', 0);
    $del_id_photo       = $this->getRequest()->getParam('del_id_photo', 0);
    $del_id_photo_back  = $this->getRequest()->getParam('del_id_photo_back', 0);

    $tags                = $this->getRequest()->getParam('tags');
    $code                = $this->getRequest()->getParam('code');
    $contract_type       = $this->getRequest()->getParam('contract_type');
    $contract_signed_at  = $this->getRequest()->getParam('contract_signed_at');
    $contract_term       = $this->getRequest()->getParam('contract_term');
    $contract_expired_at = $this->getRequest()->getParam('contract_expired_at');
    $print_time          = $this->getRequest()->getParam('print_time');
    $department          = $this->getRequest()->getParam('department');
    $team                = $this->getRequest()->getParam('team');
    $firstname           = $this->getRequest()->getParam('firstname');
    $lastname            = $this->getRequest()->getParam('lastname');
    $title               = $this->getRequest()->getParam('title');
    $phone_number        = $this->getRequest()->getParam('phone_number');
    $joined_at           = $this->getRequest()->getParam('joined_at');
    $off_date            = $this->getRequest()->getParam('off_date');
    $off_type            = $this->getRequest()->getParam('off_type', null);
    $gender              = $this->getRequest()->getParam('gender');
    //$certificate         = $this->getRequest()->getParam('certificate');

    //education
    $levels          = $this->getRequest()->getParam('levels');
    $schools         = $this->getRequest()->getParam('school');
    $field_of_studys = $this->getRequest()->getParam('field_of_study');
    $graduated_years = $this->getRequest()->getParam('graduated_year');
    $grades          = $this->getRequest()->getParam('grade');
    $mode_of_studys  = $this->getRequest()->getParam('mode_of_study');
    $education_ids   = $this->getRequest()->getParam('education_id');
    $default_level   = $this->getRequest()->getParam('default_level');

    //experience
    $ex_experience_id      = $this->getRequest()->getParam('ex_experience_id');
    $ex_company_name       = $this->getRequest()->getParam('ex_company_name');
    $ex_job_position       = $this->getRequest()->getParam('ex_job_position');
    $ex_from_date          = $this->getRequest()->getParam('ex_from_date');
    $ex_to_date            = $this->getRequest()->getParam('ex_to_date');
    $ex_reason_for_leaving = $this->getRequest()->getParam('ex_reason_for_leaving');

    //relative
    $relative_id    = $this->getRequest()->getParam('relative_id');
    $relative_type  = $this->getRequest()->getParam('relative_type');
    $rlt_full_name  = $this->getRequest()->getParam('rlt_full_name');
    $rlt_gender     = $this->getRequest()->getParam('rlt_gender');
    $rlt_birth_year = $this->getRequest()->getParam('rlt_birth_year');
    $rlt_job        = $this->getRequest()->getParam('rlt_job');
    $rlt_work_place = $this->getRequest()->getParam('rlt_work_place');
    
    $dob = $this->getRequest()->getParam('dob');
    $company_id                   = $this->getRequest()->getParam('company_id');
    $social_insurance_time        = $this->getRequest()->getParam('social_insurance_time');
    $social_insurance_number      = $this->getRequest()->getParam('social_insurance_number');
    $personal_tax                 = $this->getRequest()->getParam('personal_tax');
    $family_allowances_registered = $this->getRequest()->getParam('family_allowances_registered');
    $is_officer                   = $this->getRequest()->getParam('is_officer', 0);

    $status              = $this->getRequest()->getParam('status');
    $marital_status      = $this->getRequest()->getParam('marital_status');
    $tags                = $this->getRequest()->getParam('tags');
    $code                = $this->getRequest()->getParam('code');
    $contract_type       = $this->getRequest()->getParam('contract_type');
    $contract_signed_at  = $this->getRequest()->getParam('contract_signed_at');
    $contract_term       = $this->getRequest()->getParam('contract_term');
    $contract_expired_at = $this->getRequest()->getParam('contract_expired_at');
    $print_time          = $this->getRequest()->getParam('print_time');
    $department          = $this->getRequest()->getParam('department');
    $team                = $this->getRequest()->getParam('team');
    $firstname           = $this->getRequest()->getParam('firstname');
    $lastname            = $this->getRequest()->getParam('lastname');
    $title               = $this->getRequest()->getParam('title');
    $phone_number        = $this->getRequest()->getParam('phone_number');
    $joined_at           = $this->getRequest()->getParam('joined_at');
    $off_date            = $this->getRequest()->getParam('off_date');
    $off_type            = $this->getRequest()->getParam('off_type', null);
    $gender              = $this->getRequest()->getParam('gender');
    $level               = $this->getRequest()->getParam('level');
    $certificate         = $this->getRequest()->getParam('certificate');

    $ID_number       = $this->getRequest()->getParam('ID_number');
    $ID_place        = $this->getRequest()->getParam('ID_place');
    $ID_date         = $this->getRequest()->getParam('ID_date');
    $nationality     = $this->getRequest()->getParam('nationality');
    $religion        = $this->getRequest()->getParam('religion');
    $note            = $this->getRequest()->getParam('note');
    $email           = $this->getRequest()->getParam('email');
    $regional_market = $this->getRequest()->getParam('regional_market');
    $change_password = $this->getRequest()->getParam('change-pass');
    $password        = $this->getRequest()->getParam('password');
    $group_id        = $this->getRequest()->getParam('group_id');
    $area_id         = $this->getRequest()->getParam('area_id');
    $code_hidden     = $this->getRequest()->getParam('code_hidden');
    // $native_place      = $this->getRequest()->getParam('native_place');

    $dob                          = $this->getRequest()->getParam('dob');
    $company_id                   = $this->getRequest()->getParam('company_id');
    $social_insurance_time        = $this->getRequest()->getParam('social_insurance_time');
    $social_insurance_number      = $this->getRequest()->getParam('social_insurance_number');
    $personal_tax                 = $this->getRequest()->getParam('personal_tax');
    $family_allowances_registered = $this->getRequest()->getParam('family_allowances_registered');
    $is_officer                   = $this->getRequest()->getParam('is_officer', 0);
    $pvi                          = $this->getRequest()->getParam('pvi', 0);
    $lock                         = $this->getRequest()->getParam('lock' , 0);
    $pc_stand_by                  = $this->getRequest()->getParam('pc_stand_by' , 0);
    $follow_locked                = $this->getRequest()->getParam('follow_locked' , 0);

    // $status = $this->getRequest()->getParam('status', My_Staff_Status::Off);
    $marital_status = $this->getRequest()->getParam('marital_status',
        My_Staff_MaritalStatus::Single);

    $shirt_size = $this->getRequest()->getParam('shirt_size');

    $firstname_en   = $this->getRequest()->getParam('firstname_en');
    $lastname_en    = $this->getRequest()->getParam('lastname_en');
    $public_id      = $this->getRequest()->getParam('public_id');

    if ( $public_id == '' ) { $public_id = null; }

    if ($off_date) {
        $off_tmp = explode('/', $off_date);
        $tmp = $off_tmp[2].'-'.$off_tmp[1].'-'.$off_tmp[0]." 23:59:59"; 
        $tmp2 = $off_tmp[2].'-'.$off_tmp[1].'-'.$off_tmp[0];
    }
    

    $data = array(
        'contract_type'                => intval($contract_type),
        'contract_signed_at'           => $this->_formatDate($contract_signed_at),
        'contract_term'                => intval($contract_term),
        'contract_expired_at'          => $this->_formatDate($contract_expired_at),
        'print_time'                   => $print_time,
        'department'                   => intval($department),
        'team'                         => intval($team),
        'firstname'                    => My_String::trim($firstname),
        'lastname'                     => My_String::trim($lastname),
        'firstname_en'                 => My_String::trim($firstname_en),
        'lastname_en'                  => My_String::trim($lastname_en),
        'title'                        => $title,
        'phone_number'                 => $phone_number,
        'joined_at'                    => $this->_formatDate($joined_at),
        'off_date'                     => $tmp,
        'gender'                       => $gender,
        'off_type'                     => intval($off_type),
        'level'                        => $level,
        'certificate'                  => $certificate,
        // 'address'                   => $address,
        // 'temporary_address'         => $temporary_address,
        // 'permanent_address'         => $permanent_address,
        // 'birth_place'               => $birth_place,
        'ID_number'                    => $ID_number,
        'ID_place'                     => $ID_place,
        'ID_date'                      => $this->_formatDate($ID_date),
        'nationality'                  => intval($nationality),
        'religion'                     => intval($religion),
        'note'                         => $note,
        'regional_market'              => intval($regional_market),
        'group_id'                     => intval($group_id),
        'social_insurance_time'        => $social_insurance_time,
        'social_insurance_number'      => $social_insurance_number,
        'personal_tax'                 => $personal_tax,
        'family_allowances_registered' => $family_allowances_registered,
        // 'native_place'              => $native_place,
        'dob'                          => $this->_formatDate($dob),
        'company_id'                   => $company_id,
        'is_officer'                   => $is_officer,
        'pvi'                          => $pvi,
        'status'                       => intval($status),
        'marital_status'               => intval($marital_status),
        'shirt_size'                   => intval($shirt_size),
        'pc_stand_by'                  => intval($pc_stand_by),
        'follow_locked'                => intval($follow_locked),
        'public_id'                    => $public_id,
    );

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $db = Zend_Registry::get('db');

    $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

    //set disabled
    if ($tmp) {
        $data['status'] = My_Staff_Status::Off;
        //$data['group_id'] = 0;

        try {

            // Check Role 
            $table_result = $QStaffCheckInLog->getTableNameByGroup($data['group_id']);

            if (!empty($table_result)) { 
            
                //Check Leave & Check In
                $chk_staff = $db->select()
                    ->from(array('chk' => $table_result['check_in']), array('staff_id' => 'chk.staff_id' ))
                    ->where('chk.action_id = ?', 1)
                    ->where('chk.staff_id = ?', $id)
                    ->where('chk.created_at >= ?', $tmp2.' 00:00:00')
                    ->where('chk.created_at <= ?', $tmp2.' 23:59:59');

                $result_chk = $db->fetchAll($chk_staff);

                $lea_staff = $db->select()
                    ->from(array('lea' => $table_result['leave']), array('staff_id' => 'lea.staff_id' ))
                    ->where('lea.staff_id = ?', $id)
                    ->where('DATE(lea.from_date) <= ?', $tmp2)
                    ->where('DATE(lea.to_date) >= ?', $tmp2);

                $result_lea = $db->fetchAll($lea_staff);

                // echo $chk_staff; echo "<br/>";
                // echo $lea_staff; echo "<br/>";

                // print_r($result_chk); echo "<br/>";
                // print_r($result_lea);

                

                if ( !empty($result_chk) || !empty($result_lea)) { 
                    //echo "AAA"; 
                    throw new Exception('Failed : Staff CheckIn/Leave on this day!'); 
                } 

            }

            // check if pc have off_date : remove store_staff then update log
            if ($data['group_id'] == My_Staff_Group::PG) { My_Staff::_removeStoreSale($id, $tmp); }

            My_Staff::clear_all_roles($id);
            //My_Staff::removeStoreForTransfer($id, $title, $tmp);
            
        } catch (Exception $e){
            $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
        }
    }

    try {
        $db->beginTransaction();

        if ($id) {
            if ($code)
                $data['code'] = $code;

            $where = $QStaff->getAdapter()->quoteInto('id = ?', $id);

            if ($change_password)
                $data['password'] = md5($password);

            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;

            $staffRowset = $QStaff->find($id);
            $s = $staffRowset->current();

            if (empty($s['old_email'])) {
                $old_email = (!empty($tmp) ? $email : '');
                $data['old_email'] = $old_email;
            }

            $email = (!empty($tmp) ? '' : $email);
            $data['email'] = $email;

            $QStaff->update($data, $where);
            // $QWS = new Application_Model_WS();    
            $data['code_hidden'] = $code_hidden;
            // $xx =  $QWS->_updateToTrade($data);

            foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            $data2 = rtrim($fields_string, '&');

            if($status == 0){
                $QStoreStaff = new Application_Model_StoreStaff();
                $QStoreStaffLog = new Application_Model_StoreStaffLog();

                $where_store_staff = $QStoreStaff->getAdapter()->quoteInto('staff_id =?',$id);
                $delete_code = $QStoreStaff->delete($where_store_staff);

            if($delete_code){
                $where_store_staff_log = $QStoreStaffLog->getAdapter()->quoteInto('staff_id =?',$id);
                $data = array('released_at' => time());
                $QStoreStaffLog->update($data,$where_store_staff_log);
                }
            }
/*
            $ch = curl_init();

            //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsupdatetstaff");
            curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/update-staff-to-trade");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close ($ch);

            if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
            $staffRowset = $QStaff->find($id);
            $s_after = $staffRowset->current();

            if(isset($lock) and $lock == 0)
            {
                $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
                $whereExpired = array();
                $whereExpired[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ?' , $id);
                $whereExpired[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
                $data = array(
                    'approved_at' => date('Y-m-d h:i:s'),
                    'approved_by' => $userStorage->id
                );
                $QTimeStaffExpired->update($data, $whereExpired);
            }

            // Log from VN
            Log::w($s->toArray(), $s_after->toArray(), $id, LogGroup::Staff, LogType::
            Update);

            // Log from TH [Regional Market, Group, Title]
            $old_title = $this->getRequest()->getParam('old_title');
            $old_group = $this->getRequest()->getParam('old_group');
            $old_regional_market = $this->getRequest()->getParam('old_regional_market');

            if ($old_title != $title) { 

                $title_log = array(
                    'staff_id'      => $id,
                    'before_id'     => $old_title,
                    'after_id'      => $title,
                    'created_by'    => $userStorage->id,
                    'created_at'    => date('Y-m-d H:i:s')
                );

                $db->insert("staff_title_log", $title_log);
            }

            if ($old_group != $group_id) { 

                if ($old_group == PGPB_ID && $group_id != PGPB_ID) {

                    $select_ss = $db->select()
                        ->from(array('ss' => 'store_staff'), array('id' => 'ss.id' ))
                        ->where('ss.is_leader = ?', 0)
                        ->where('ss.staff_id = ?', $id);

                    $result_ss = $db->fetchRow($select_ss);

                    if (!empty($result_ss)) {
                        throw new Exception('Failed : Remove PC Store Binding before change Position!!!'); 
                    }
                } 

                $group_log = array(
                    'staff_id'      => $id,
                    'before_id'     => $old_group,
                    'after_id'      => $group_id,
                    'created_by'    => $userStorage->id,
                    'created_at'    => date('Y-m-d H:i:s')
                );

                $db->insert("staff_group_log", $group_log);
                
            }

            if ($old_regional_market != $regional_market) { 

                $rm_log = array(
                    'staff_id'      => $id,
                    'before_id'     => $old_regional_market,
                    'after_id'      => $regional_market,
                    'created_by'    => $userStorage->id,
                    'created_at'    => date('Y-m-d H:i:s')
                );

                $db->insert("staff_regional_market_log", $rm_log);
            }



        } else {
            // generate staff code
            $data['code'] = $QStaff->genStaffCode($this->_formatDate($joined_at));

            $password = empty($password) ? '123456' : $password;
            $data['password'] = md5($password);

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage->id;


            $id        = $QStaff->insert($data);
            $joined_at = $this->_formatDate($joined_at);
            $result    = $this->contractAdd($id, $joined_at);
            // echo "<pre>";
            // print_r($data);
            // $QWS = new Application_Model_WS();    
            $data['area_id'] = $area_id; 
            $data['username'] = $data['code'];
            // $xx =  $QWS->_insertToTrade($data);

            foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            $data2 = rtrim($fields_string, '&');
/*
            $ch = curl_init();

            //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsinsertstaff");
            curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/insert-staff-to-trade");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close ($ch);

            if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
            if ($result['code'] == -1) {
                throw new Exception('Error when update contract term');
            }
            $staffRowset = $QStaff->find($id);
            $s_after = $staffRowset->current();

            Log::w(array(), $s_after->toArray(), $id, LogGroup::Staff, LogType::Insert);

        }

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

        if ($id) {

            $staffRowset = $QStaff->find($id);
            $s = $staffRowset->current();

            // ------------------ upload
            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' .
                DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR . $id;

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

                    @unlink($val . $s[$key]);
                }

                if (isset($files[$key]['name'])){
                    $hasPhoto = true;
                }
            }

            if ($hasPhoto) {

                if (!$upload->isValid()){
                    $errors = $upload->getErrors();

                    $sError = null;

                    if ($errors and isset($errors[0]))
                        switch ($errors[0]){
                            case 'fileUploadErrorIniSize':
                                $sError = 'File size is too large';
                                break;
                            case 'fileMimeTypeFalse':
                            case 'fileExtensionFalse':
                                $sError = 'The file(s) you selected weren\'t the type we were expecting';
                                break;
                            default:
                                $sError = 'The file(s) you selected weren\'t the type we were expecting';
                                break;
                        }

                    throw new Exception($sError);
                }


                foreach ($arrPhoto as $key => $val){
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

                        if ($r)
                            $data[$key] = $new_name;
                        else{
                            $messages = $upload->getMessages();
                            foreach ($messages as $msg)
                                throw new Exception($msg);
                        }
                    }
                }
            }

            if ($data) {
                $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $id);
                $QStaff->update($data, $whereStaff);
            }


            // ------------------ /upload

            $QStaffAddress = new Application_Model_StaffAddress();

            if ($temp_province) {
                $address_data = array(
                    'staff_id' => $id,
                    'address_type' => My_Staff_Address::Temporary,
                    'address' => $temp_address,
                    'ward' => $temp_ward,
                    'district' => $temp_district,
                );

                try {
                    $QStaffAddress->insert($address_data);
                }
                catch (exception $e) {
                    $where = array();
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                        My_Staff_Address::Temporary);

                    try {
                        $QStaffAddress->update($address_data, $where);
                    }
                    catch (exception $e) {
                        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                        $this->_redirect(HOST . 'staff');
                    }
                }
            }

            if ($perm_province) {
                $address_data = array(
                    'staff_id' => $id,
                    'address_type' => My_Staff_Address::Permanent,
                    'address' => $perm_address,
                    'ward' => $perm_ward,
                    'district' => $perm_district,
                );

                try {
                    $QStaffAddress->insert($address_data);
                }
                catch (exception $e) {
                    $where = array();
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                        My_Staff_Address::Permanent);

                    try {
                        $QStaffAddress->update($address_data, $where);
                    }
                    catch (exception $e) {
                        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                        $this->_redirect(HOST . 'staff');
                    }
                }
            }

            if ($id_card_province) {
                $address_data = array(
                    'staff_id' => $id,
                    'address_type' => My_Staff_Address::ID_Card,
                    'address' => $id_card_address,
                    'ward' => $id_card_ward,
                    'district' => $id_card_district,
                );

                try {
                    $QStaffAddress->insert($address_data);
                }
                catch (exception $e) {
                    $where = array();
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                        My_Staff_Address::ID_Card);

                    try {
                        $QStaffAddress->update($address_data, $where);
                    }
                    catch (exception $e) {
                        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                        $this->_redirect(HOST . 'staff');
                    }
                }
            }

            if ($birth_province) {
                $address_data = array(
                    'staff_id' => $id,
                    'address_type' => My_Staff_Address::Birth_Certificate,
                    'ward' => $birth_ward,
                    'district' => $birth_district,
                );

                try {
                    $QStaffAddress->insert($address_data);
                }
                catch (exception $e) {
                    $where = array();
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
                    $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                        My_Staff_Address::Birth_Certificate);

                    try {
                        $QStaffAddress->update($address_data, $where);
                    }
                    catch (exception $e) {
                        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                        $this->_redirect(HOST . 'staff');
                    }
                }
            }

            //check education
            if(count($levels) > 0 AND is_array($levels) AND is_array($schools) AND is_array($field_of_studys)
                AND is_array($graduated_years) AND is_array($grades) AND is_array($mode_of_studys)){
                $QStaffEducation = new Application_Model_StaffEducation();
                
                foreach($levels as $key => $value){

                    if($value == '' OR $value == NULL){
                        continue;
                    }

                    $dataEdu = array(
                        'staff_id'       => $id,
                        'level'          => $value,
                        'school'         => $schools[$key],
                        'field_of_study' => $field_of_studys[$key],
                        'graduated_year' => $graduated_years[$key],
                        'grade'          => $grades[$key],
                        'mode_of_study'  => $mode_of_studys[$key],
                        'default_level'  => (isset($default_level[$key]) AND $default_level[$key] == 1) ? 1:0,
                    );

                    if(isset($education_ids[$key]) AND $education_ids[$key]){
                        $where = $QStaffEducation->getAdapter()->quoteInto('id = ?',$education_ids[$key]);
                        $QStaffEducation->update($dataEdu,$where);

                    }else{
                        $QStaffEducation->insert($dataEdu);
                    }

                }
                $selectDefaultLevel = $QStaffEducation->select()
                    ->where('staff_id = ?',$id)
                    ->where('default_level = ?',1);
                $result = $QStaffEducation->fetchAll($selectDefaultLevel);
                if($result->count() > 1){
                    $flashMessenger->setNamespace('error')->addMessage('Education default is only single');
                    $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
                }    
            }

            //experience
            $QStaffExperience = new Application_Model_StaffExperience();
            if(is_array($ex_company_name) AND is_array($ex_job_position) AND is_array($ex_from_date) AND is_array($ex_from_date)
                AND is_array($ex_to_date) AND is_array($ex_reason_for_leaving) AND count($ex_company_name) > 0){
                foreach($ex_company_name as $key => $value){
                    if(trim($value) == ''){
                        continue;
                    }
                    
                    $tmp_from_date = explode('/',$ex_from_date[$key]);
                    $tmp_from_date = $tmp_from_date[2]. '-'.$tmp_from_date[1].'-'.$tmp_from_date[0];
                    $tmp_to_date = explode('/',$ex_to_date[$key]);
                    $tmp_to_date = $tmp_to_date[2].'-'.$tmp_to_date[1].'-'.$tmp_to_date[0];
                    $dataEx = array(
                        'staff_id'           => $id,
                        'company_name'       => $value,
                        'job_position'       => $ex_job_position[$key],
                        'from_date'          => date('Y-m-d',strtotime($tmp_from_date)),
                        'to_date'            => date('Y-m-d',strtotime($tmp_to_date)),
                        'reason_for_leaving' => $ex_reason_for_leaving[$key],
                    );

                    if(isset($ex_experience_id[$key]) AND $ex_experience_id[$key]){
                        $where = $QStaffExperience->getAdapter()->quoteInto('id = ?',$ex_experience_id[$key]);
                        $QStaffExperience->update($dataEx,$where);
                    }else{
                        $QStaffExperience->insert($dataEx);
                    }
                }
            }//End experience

            //relative
            if(is_array($relative_type) AND is_array($rlt_full_name) AND is_array($rlt_gender)
                AND is_array($rlt_birth_year) AND is_array($rlt_job) AND is_array($rlt_work_place) AND count($relative_type) > 0){
                $QStaffRelative = new Application_Model_StaffRelative();
                foreach($relative_type as $key => $value){
                    if(trim($value) == ''){
                        continue;
                    }
                    $dataRlt = array(
                        'staff_id'      => $id,
                        'relative_type' => intval($value),
                        'full_name'     => trim($rlt_full_name[$key]),
                        'gender'        => intval($rlt_gender[$key]),
                        'birth_year'    => ($rlt_birth_year[$key]) ? $rlt_birth_year[$key] : NULL,
                        'job'           => trim($rlt_job[$key]),
                        'work_place'    => trim($rlt_work_place[$key])
                    );
                    if(isset($relative_id[$key]) AND $relative_id[$key]){
                        $where = $QStaffRelative->getAdapter()->quoteInto('id = ?',$relative_id[$key]);
                        $QStaffRelative->update($dataRlt,$where);
                    }else{
                        $QStaffRelative->insert($dataRlt);
                    }
                }
            }//End relative
        }

        // insert tags
        $QTag = new Application_Model_Tag();
        $QTag->add($tags, $id, TAG_STAFF);

        $cache = Zend_Registry::get('cache');
        $cache->remove('staff_cache');

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');

    } catch (Exception $e){
        $db->rollback();
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    }
}

$this->_redirect(($back_url ? $back_url : HOST . 'staff'));