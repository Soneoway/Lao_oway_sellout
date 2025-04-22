<?php
$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');

$back_url = '/trainer/list-event-report';
$this->view->back_url = $back_url;

try
{
    $db             = Zend_Registry::get('db');
    $db->beginTransaction();

    $staff_id                   = $userStorage->id;
    $regional_market            = $userStorage->regional_market;
    $QStaff                     = new Application_Model_Staff();
    $QStaffEventReport          = new Application_Model_StaffEventReport();
    $QStaffTrainingReport          = new Application_Model_StaffTrainingReport();
    $QAsm                       = new Application_Model_Asm();
    $QStore                     = new Application_Model_Store();
    $QStaffTrainer              = new Application_Model_StaffTrainer();
    $cachedStore                = $QStore->get_cache();
    $whereStaff                 = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id);
    $row                        = $QStaff->fetchRow($whereStaff); /*information Trainer*/
    $this->view->info_trainer   = $row;

    $areaTrainer                = $QStaffTrainer->getAreaTrainer($staff_id);
    $regional_market            = $areaTrainer['district'];

    $nameDealer                  = $QStaffTrainingReport->getDealerArea($regional_market);
    $this->view->dealer          = $nameDealer;


    $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'trainer'.DIRECTORY_SEPARATOR;


    $id                   = $this->getRequest()->getParam('id');
    $whereEventReport     = array();
    $whereEventReport[]   = $QStaffEventReport->getAdapter()->quoteInto('id = ?',$id);
    $whereEventReport[]   = $QStaffEventReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowEventReport       = $QStaffEventReport->fetchRow($whereEventReport);
    $allPicture           = array();
    $arrayPictureEvent    = array();
    $allPicture           = json_decode($rowEventReport['picture'],true);
    if($rowEventReport)
    {
        /*check edit */
        /* check store id with area of store */
        $whereDistrictStore         = $QStore->getAdapter()->quoteInto('id = ?',$rowEventReport['store_id']);
        $rowDistrictStore           = $QStore->fetchRow($whereDistrictStore);
        if($rowDistrictStore)
        {
            $district               = $rowDistrictStore['district'];

            if(in_array($district,array_keys($regional_market)) || $userStorage->group_id == ADMINISTRATOR_ID)
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

        /*get Event report*/
        $this->view->info_event_report = $rowEventReport;
        $dealer_id         = $rowEventReport['dealer_id'];
        $store_id          = $rowEventReport['store_id'];

        // get Store with dealer id
        $QStore         = new Application_Model_Store();
        $whereStore     = array();
        $whereStore[]   = $QStore->getAdapter()->quoteInto('d_id = ?',$dealer_id);
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

        // get image
        $direct     = HOST.'public/photo/trainer/'.$allPicture['user_id'].'/'.$allPicture['direct'].'/';

        foreach($allPicture as $key => $value)
        {
            if($key == 'direct' || $key =='user_id')
            {
                continue;
            }
            else if ($value)
            {
                $arrayPictureEvent[$key] = $direct.$value;
            }
        }
        if(count($arrayPictureEvent))
            $this->view->picture_event = $arrayPictureEvent;
    }

    /*submit form - listen Someone Like You */

    if($this->getRequest()->getMethod()=='POST') {

        /*params request*/
        $date                       = $this->getRequest()->getParam('date');
        $dealer                     = $this->getRequest()->getParam('dealer');
        $store                      = $this->getRequest()->getParam('store');
        $visitors                   = $this->getRequest()->getParam('visitors');
        $note                       = $this->getRequest()->getParam('note');
        $picture_event_report_haved = $this->getRequest()->getParam('picture_event_report_haved');

        $upload = new Zend_File_Transfer();

        $files  = $upload->getFileInfo();

        /*Edit form*/
        if($id)
        {
            foreach ($files as $file => $info)
            {
                if (!$info['name'] and !is_array($picture_event_report_haved))
                {
                    throw new Exception('You Must Upload More Than One Picture Event Report !');
                }
            }

            $array_diff = array_diff(array_keys($arrayPictureEvent),$picture_event_report_haved);

            $dir = getcwd().DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'trainer'.DIRECTORY_SEPARATOR. $allPicture['user_id'].DIRECTORY_SEPARATOR.$allPicture['direct'].DIRECTORY_SEPARATOR;

            foreach($array_diff as $key => $value)
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

            $uploaded_dir .= DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR . $allPicture['direct'] . DIRECTORY_SEPARATOR;

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
            foreach ($files as $file => $info) {
                if (isset($info['name']) and $info['name'] )
                {
                    $old_name  = $info['name'];
                    $tExplode  = explode('.', $old_name);
                    $extension = end($tExplode);
                    $new_name  = md5(uniqid('', true)) . '.' . $extension;
                    $upload->addFilter('Rename', array('target' => $uploaded_dir .
                        DIRECTORY_SEPARATOR . $new_name));
                    $upload->receive(array($file));
                    $lastPictureEvent = count($allPicture) - 3;
                    $count = $count + $lastPictureEvent;
                    $allPicture['picture_event_'.$count] = $new_name;
                }
            }

            $data = array(
                'date'              => $date,
                'staff_id'          => $staff_id,
                'dealer_id'         => $dealer,
                'store_id'          => $store,
                'visitors'          => $visitors,
                'picture'           => json_encode($allPicture),
                'note'              => $note,
                'updated_at'        => date('Y-m-d H:i:s'),
                'updated_by'        => $staff_id
            );

            $QStaffEventReport->update($data,$whereEventReport);

        }
        else
        {

            if(!$date || !$store || !$dealer || !$visitors)
            {
                throw new Exception('DATE OR STORE OR Dealer OR Visitors is null');
            }
            $whereStaffEventReport   = array();
            $whereStaffEventReport[] = $QStaffEventReport->getAdapter()->quoteInto('date = ?',$date);
            $whereStaffEventReport[] = $QStaffEventReport->getAdapter()->quoteInto('staff_id = ?',$staff_id);
            $whereStaffEventReport[] = $QStaffEventReport->getAdapter()->quoteInto('dealer_id = ?',$dealer);
            $whereStaffEventReport[] = $QStaffEventReport->getAdapter()->quoteInto('store_id = ?',$store);
            $rowStaffEventReport     = $QStaffEventReport->fetchRow($whereStaffEventReport);
            if($rowStaffEventReport)
            {
                throw new Exception('Date is training !');
            }
            /*save form*/
            $upload->setOptions(array('ignoreNoFile'=>true));

            //check function
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

            $arrayUploadInformation['direct']  = $uniqid;
            $arrayUploadInformation['user_id'] = $userStorage->id;

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
                    throw new Exception('Must Upload Picture!');
                $tExplode  = explode('.', $old_name);
                $extension = end($tExplode);
                $new_name  = md5(uniqid('', true)) . '.' . $extension;

                $upload->addFilter('Rename', array('target' => $uploaded_dir .
                    DIRECTORY_SEPARATOR . $new_name));
                $upload->receive(array($file));
                $arrayUploadInformation[$file] = $new_name;
            }
            $data = array(
                'date'              => $date,
                'staff_id'          => $staff_id,
                'dealer_id'         => $dealer,
                'store_id'          => $store,
                'visitors'          => $visitors,
                'picture'           => json_encode($arrayUploadInformation),
                'note'              => $note,
                'created_at'        => date('Y-m-d H:i:s'),
                'created_by'        => $staff_id
            );

            $QStaffEventReport->insert($data);

            // to do log
            $info = array('Insert Daily Report', 'new' => $data);
            $QLog->insert(array(
                'info'       => json_encode($info),
                'user_id'    => $userStorage->id,
                'ip_address' => $ip,
                'time'       => date('Y-m-d H:i:s'),
            ));
        }

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');
        $this->_redirect(HOST . 'trainer/list-event-report');
    }



}
catch (Exception $e)
{
    $db->rollback();
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST . 'trainer/list-event-report');
}
