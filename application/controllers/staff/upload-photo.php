<?php
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

    if ($this->getRequest()->getMethod() == 'POST') {
        
        // ------------------ upload
        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' .
            DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR . $staff_id;

        $arrPhoto = array(
            'photo' => $uploaded_dir,
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

                @unlink($val . $currentStaff[$key]);
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
            $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
            $QStaff->update($data, $whereStaff);
        }
        // ------------------ /upload

        $flashMessenger->setNamespace('success')->addMessage('Done');
        $this->_redirect(($back_url ? $back_url : HOST . 'staff/list-basic'));
    }

    $this->view->currentStaff = $currentStaff;

    $this->view->staff_id = $staff_id;

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