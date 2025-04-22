<?php
$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

$back_url = '/trainer/list-training-report';
$this->view->back_url = $back_url;

try
{

    $db->beginTransaction();

    $staff_id                   = $userStorage->id;
    $this->view->staff_id       = $staff_id;
    $QStaff                     = new Application_Model_Staff();
    $QStaffTrainingReport       = new Application_Model_StaffTrainingReport();
    $QStore                     = new Application_Model_Store();
    $QStaffTrainer              = new Application_Model_StaffTrainer();
    $cachedStore                = $QStore->get_cache();
    $whereStaff                 = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id);
    $row                        = $QStaff->fetchRow($whereStaff); /*information Trainer*/
    $this->view->info_trainer   = $row;
    $areaTrainer                = $QStaffTrainer->getAreaTrainer($staff_id);
    $getArea                    = $areaTrainer['area'];
    $getProvince                = $areaTrainer['province'];
    $getDistrict                = $areaTrainer['district'];

    $this->view->areaTrainer     = $getArea;

    $nameDealer                  = $QStaffTrainingReport->getDealerArea($getDistrict);
    $this->view->dealer          = $nameDealer;


    $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'trainer'.DIRECTORY_SEPARATOR;

    $id                      = $this->getRequest()->getParam('id');
    $whereTrainingReport     = array();
    $whereTrainingReport[]   = $QStaffTrainingReport->getAdapter()->quoteInto('id = ?',$id);
    $whereTrainingReport[]   = $QStaffTrainingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowTrainingReport       = $QStaffTrainingReport->fetchRow($whereTrainingReport);
    $allPicture              = array();
    $arrayPictureTraining    = array();
    $arrayPictureList        = array();
    $allPicture              = json_decode($rowTrainingReport['picture'],true);

    if($rowTrainingReport)
    {
        /*get training report*/
        $this->view->info_training_report = $rowTrainingReport;

        if($rowTrainingReport['type'] == TRAINING_REPORT_PARTNERS)
        {
            /*check edit */
            /* check store id with area of store */
            $whereDistrictStore         = $QStore->getAdapter()->quoteInto('id = ?',$rowTrainingReport['store_id']);
            $rowDistrictStore           = $QStore->fetchRow($whereDistrictStore);
            if($rowDistrictStore)
            {
                $district               = $rowDistrictStore['district'];

                if(in_array($district,array_keys($getDistrict)) || $userStorage->group_id == ADMINISTRATOR_ID)
                {

                }
                else
                {
                    throw new Exception('Area Store NOT True with Area Login - Can not Edit');
                }
            }
            else
            {
                throw new Exception('NOT FOUND DISTRICT STORE');
            }

            $store_id          = $rowTrainingReport['store_id'];

            // get Store with dealer id
            $QStore         = new Application_Model_Store();
            $whereStore     = array();
            $whereStore[]   = $QStore->getAdapter()->quoteInto('d_id = ?',$rowTrainingReport['dealer_id']);
            $whereStore[]   = $QStore->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            $rows           = $QStore->fetchAll($whereStore);
            $arrayStore     = array();
            if($rows->count())
            {
                foreach($rows as $key => $value)
                {
                    $arrayStore[$value['id']] = $value['name'];
                }
            }

            $this->view->array_store = $arrayStore;
        }

        if($rowTrainingReport['type'] == TRAINING_REPORT_INTERNALLY)
        {
            // kiem tra khu vuc co thuoc khuu vuc cua thang trainer khong

            if(!in_array($rowTrainingReport['area'],array_keys($getArea)))
            {
                throw new Exception('Area Record Not True With Area User Login');
            }

            if(!in_array($rowTrainingReport['province'],array_keys($getProvince)))
            {
                throw new Exception('Province Record Not True With Area User Login');
            }

            $areaOfRow            = $rowTrainingReport['area'];
            $QRegionalMarket      = new Application_Model_RegionalMarket();
            $getProvinceArea      = array();
            $getProvinceArea      = $QRegionalMarket->get_region_cache($areaOfRow);
            $cachedRegionalMarket = $QRegionalMarket->get_cache();

            $result = array();

            $array_intersect = array_intersect(array_keys($getProvince),$getProvinceArea);

            foreach($array_intersect as $item )
            {
                $result[$item] = $cachedRegionalMarket[$item];
            }

            $this->view->provinceArea = $result;

        }

        // get image
        $direct     = HOST.'public/photo/trainer/'.$allPicture['user_id'].'/'.$allPicture['direct'].'/';

        // get list picture training
        foreach($allPicture as $key => $value)
        {
            if(preg_match('/picture_list_pg/',$key))
            {
                $arrayPictureList[$key] = $direct.$value;
            }
            if(preg_match('/picture_training/',$key))
            {
                $arrayPictureTraining[$key] = $direct.$value;
            }
        }
        if(count($arrayPictureTraining))
            $this->view->picture_training = $arrayPictureTraining;
        if(count($arrayPictureList))
            $this->view->picture_list_pg = $arrayPictureList;
    }

    /*submit form - listen Someone Like You */
    if($this->getRequest()->getMethod()=='POST') {

        /*params request*/
        $date                   = $this->getRequest()->getParam('date');
        $dealer                 = $this->getRequest()->getParam('dealer',null);
        $store                  = $this->getRequest()->getParam('store',null);
        $type                   = $this->getRequest()->getParam('type');
        $note                   = $this->getRequest()->getParam('note');
        $from_date              = $this->getRequest()->getParam('from_date');
        $to_date                = $this->getRequest()->getParam('to_date');
        $quantity_trainees      = $this->getRequest()->getParam('quantity_trainees');
        $quantity_participant   = $this->getRequest()->getParam('quantity_participant');
        $quantity_disqualified  = $this->getRequest()->getParam('quantity_disqualified');
        $quantity_remain        = $this->getRequest()->getParam('quantity_remain');
        $area                   = $this->getRequest()->getParam('area',null);
        $province               = $this->getRequest()->getParam('province',null);
        $picture_training_haved = $this->getRequest()->getParam('picture_training_haved');
        $picture_list_pg_haved  = $this->getRequest()->getParam('picture_list_pg_haved');

        $upload = new Zend_File_Transfer();

        $files  = $upload->getFileInfo();


        /*Edit form*/
        if($id)
        {
            $dir = getcwd().DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'trainer'.DIRECTORY_SEPARATOR. $allPicture['user_id'].DIRECTORY_SEPARATOR.$allPicture['direct'].DIRECTORY_SEPARATOR;

            // check xoa het anh ma khong up cai moi
            $array_diff_picture_pg_list = array_diff(array_keys($arrayPictureList),$picture_list_pg_haved);
            if(!$array_diff_picture_pg_list and !$picture_list_pg_haved)
                throw new Exception('Must Upload Picture!');
            foreach($array_diff_picture_pg_list as $key => $value)
            {
                $file_name = $allPicture[$value];
                if(is_file($dir.$file_name))
                    unlink($dir.$file_name);
                unset($allPicture[$value]);
            }

            $array_diff_picture_training = array_diff(array_keys($arrayPictureTraining),$picture_training_haved);
            if(!$array_diff_picture_training and !$picture_training_haved)
                throw new Exception('Must Upload Picture');
            foreach($array_diff_picture_training as $key => $value)
            {
                $file_name = $allPicture[$value];
                if(is_file($dir.$file_name))
                    unlink($dir.$file_name);
                unset($allPicture[$value]);
            }

            // moi upload buc anh len
            $upload->setOptions(array('ignoreNoFile'=>true));

            //check function
            if (function_exists('finfo_file'))

                $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

            $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');

            $upload->addValidator('ExcludeExtension', false, 'php,sh');

            $uploaded_dir .= DIRECTORY_SEPARATOR . $allPicture['user_id']. DIRECTORY_SEPARATOR . $allPicture['direct'] . DIRECTORY_SEPARATOR;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            if (!$upload->isValid()) {
                $errors = $upload->getErrors();
                $sError = null;

                if ($errors and isset($errors[0]))
                {
                    switch ($errors[0]) {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                            $sError = 'The file(s) you selected weren\'t the type we were expecting';
                            break;
                    }

                    throw new Exception($sError);
                }

            }

            $count = 0;
            foreach ($files as $file => $info)
            {
                if (isset($info['name']) and $info['name'])
                {
                    $old_name  = $info['name'];
                    $tExplode  = explode('.', $old_name);
                    $extension = end($tExplode);
                    $new_name  = md5(uniqid('', true)) . '.' . $extension;

                    $upload->addFilter('Rename', array('target' => $uploaded_dir .
                        DIRECTORY_SEPARATOR . $new_name));

                    $upload->receive(array($file));

                    $lastPictureTraining = count($allPicture) - 3;
                    $count = $count + $lastPictureTraining;

                    if(preg_match('/picture_list_pg/',$file))
                    {
                        $allPicture['picture_list_pg_'.$count] = $new_name;
                    }

                    if(preg_match('/picture_training/',$file))
                    {
                        $allPicture['picture_training_'.$count] = $new_name;
                    }

                }
            }

            if(!$type)
            {
                throw new Exception('Please Choose Type!');
            }

            if($type == TRAINING_REPORT_INTERNALLY)
            {
                $dealer = $store = $from_date =  $to_date = $quantity_participant =  $quantity_disqualified =  $quantity_remain = null;
            }

            if($type == TRAINING_REPORT_PARTNERS)
            {
                $area = $province = $from_date =  $to_date = $quantity_participant =  $quantity_disqualified =  $quantity_remain =  null;
            }

            if($type == TRAINING_REPORT_NEW_STAFF)
            {
                $dealer = $store = $date = $quantity_trainees = $area = $province = null;
            }

            $data = array(
                'date'                  => $date,
                'type'                  => $type,
                'area'                  => $area,
                'province'              => $province,
                'staff_id'              => $staff_id,
                'dealer_id'             => $dealer,
                'store_id'              => $store,
                'from_date'             => $from_date,
                'to_date'               => $to_date,
                'quantity_participant'  => $quantity_participant,
                'quantity_disqualified' => $quantity_disqualified,
                'quantity_remain'       => $quantity_remain,
                'picture'               => json_encode($allPicture),
                'note'                  => $note,
                'quantity_trainees'     => $quantity_trainees,
                'updated_at'            => date('Y-m-d H:i:s'),
                'updated_by'            => $staff_id
            );

            $QStaffTrainingReport->update($data,$whereTrainingReport);

        }
        else
        {


            if(!$type)
            {
                throw new Exception('Please choose Type');

            }
            else
            {
                if($type ==  TRAINING_REPORT_INTERNALLY && !$area && !$province)
                {
                    throw new Exception('Area OR Province Not Choose !');
                }

                if($type ==  TRAINING_REPORT_PARTNERS && !$dealer && !$store)
                {
                    throw new Exception('Dealer OR Store Not Choose !');
                }

                if($type == TRAINING_REPORT_NEW_STAFF && !$from_date && !$to_date && !$quantity_participant && !$quantity_disqualified && !$quantity_remain)
                {
                    throw new Exception('From date Or To Date Or Quantity Participant Or Quantity Disqualified Or Quantity Remain Not Choose ! ');
                }
            }

            if($type == TRAINING_REPORT_INTERNALLY)
            {
                $dealer = $store = $from_date =  $to_date = $quantity_participant =  $quantity_disqualified =  $quantity_remain = null;
            }

            if($type == TRAINING_REPORT_PARTNERS)
            {
                $area = $province = $from_date =  $to_date = $quantity_participant =  $quantity_disqualified =  $quantity_remain =  null;
            }

            if($type == TRAINING_REPORT_NEW_STAFF)
            {
                $dealer = $store = $date = $quantity_trainees = $area = $province = null;
            }

            $whereStaffTrainingReport = array();

            if($type == TRAINING_REPORT_PARTNERS)
            {
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('type = ?',TRAINING_REPORT_PARTNERS);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('date = ?',$date);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('staff_id = ?',$staff_id);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('dealer_id = ?',$dealer);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('store_id = ?',$store);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            }

            if($type == TRAINING_REPORT_INTERNALLY)
            {
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('type = ?',TRAINING_REPORT_INTERNALLY);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('date = ?',$date);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('area = ?',$area);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('province = ?',$province);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('staff_id = ?',$staff_id);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            }

            if($type == TRAINING_REPORT_NEW_STAFF)
            {
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('type = ?',TRAINING_REPORT_NEW_STAFF);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('from_date = ?',$from_date);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('to_date = ?',$to_date);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('staff_id = ?',$staff_id);
                $whereStaffTrainingReport[] = $QStaffTrainingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            }

            $rowStaffTrainingReport     = $QStaffTrainingReport->fetchRow($whereStaffTrainingReport);

            if($rowStaffTrainingReport)
            {
                throw new Exception('Date and Type is training !');
            }


            /*save form*/
            $upload->setOptions(array('ignoreNoFile'=>true));

            //check functionc
            if (function_exists('finfo_file'))

                $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

            $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');

            $upload->addValidator('ExcludeExtension', false, 'php,sh');

            $uniqid = uniqid();

            $uploaded_dir .= DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            $arrayUploadInformation = array();

            $arrayUploadInformation['direct'] = $uniqid;

            $arrayUploadInformation['user_id']= $userStorage->id;

            if (!$upload->isValid()) {
                $errors = $upload->getErrors();
                $sError = null;

                if ($errors and isset($errors[0]))
                {
                    switch ($errors[0]) {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                            $sError = 'The file(s) you selected weren\'t the type we were expecting';
                            break;
                    }

                    throw new Exception($sError);
                }

            }

            foreach ($files as $file => $info) {

                $old_name  = $info['name'];
                if(!$old_name)
                {
                    throw new Exception ('Must Upload Picture!');
                }
                $tExplode  = explode('.', $old_name);
                $extension = end($tExplode);
                $new_name  = md5(uniqid('', true)) . '.' . $extension;

                $upload->addFilter('Rename', array('target' => $uploaded_dir .
                    DIRECTORY_SEPARATOR . $new_name));
                $upload->receive(array($file));

                $arrayUploadInformation[$file] = $new_name;
            }


            $data = array_filter(
                    array(
                        'date'                  => $date,
                        'type'                  => $type,
                        'area'                  => $area,
                        'province'              => $province,
                        'staff_id'              => $staff_id,
                        'dealer_id'             => $dealer,
                        'store_id'              => $store,
                        'from_date'             => $from_date,
                        'to_date'               => $to_date,
                        'quantity_participant'  => $quantity_participant,
                        'quantity_disqualified' => $quantity_disqualified,
                        'quantity_remain'       => $quantity_remain,
                        'picture'               => json_encode($arrayUploadInformation),
                        'note'                  => $note,
                        'quantity_trainees'     => $quantity_trainees,
                        'created_at'            => date('Y-m-d H:i:s'),
                        'created_by'            => $staff_id
                    )
                );

            $QStaffTrainingReport->insert($data);

            // to do log
            $info = array('Insert Training Report', 'new' => $data);
            $QLog->insert(array(
                'info'       => json_encode($info),
                'user_id'    => $userStorage->id,
                'ip_address' => $ip,
                'time'       => date('Y-m-d H:i:s'),
            ));
        }

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');
        $this->_redirect($back_url);
    }



}
catch (Exception $e)
{
    $db->rollback();
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect($back_url);
}
