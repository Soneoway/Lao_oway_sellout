<?php
$flashMessenger              = $this->_helper->flashMessenger;
$messages                    = $flashMessenger->setNamespace('success')->getMessages();
$this->view->message_success = $messages;
$messages_error              = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages        = $messages_error;
$userStorage                 = Zend_Auth::getInstance()->getStorage()->read();
$QLog                        = new Application_Model_Log();
$ip                          = $this->getRequest()->getServer('REMOTE_ADDR');
$db                          = Zend_Registry::get('db');

$back_url = '/trainer/list-team-building-report';
$this->view->back_url = $back_url;

try
{

    $db->beginTransaction();

    $staff_id                   = $userStorage->id;
    $QStaffTeamBuildingReport   = new Application_Model_StaffTeamBuildingReport();
    $QStaff                     = new Application_Model_Staff();
    $whereStaff                 = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id);
    $row                        = $QStaff->fetchRow($whereStaff); /*information Trainer*/
    $this->view->info_trainer   = $row;

    $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'trainer'.DIRECTORY_SEPARATOR;

    $id                          = $this->getRequest()->getParam('id');
    $whereTeamBuildingReport     = array();
    $whereTeamBuildingReport[]   = $QStaffTeamBuildingReport->getAdapter()->quoteInto('id = ?',$id);
    $whereTeamBuildingReport[]   = $QStaffTeamBuildingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowTeamBuildingReport       = $QStaffTeamBuildingReport->fetchRow($whereTeamBuildingReport);
    $allPicture                  = array();
    $arrayPictureEvent           = array();
    $allPicture                  = json_decode($rowTeamBuildingReport['picture'],true);
    if($rowTeamBuildingReport)
    {
        $this->view->info_team_building = $rowTeamBuildingReport;

        $arrayPictureTeamBuilding = array();
        // get image
        $direct     = HOST.'public/photo/trainer/'.$allPicture['user_id'].'/'.$allPicture['direct'].'/';

        foreach($allPicture as $key => $value)
        {
            if($key == 'direct' || $key == 'user_id' )
            {
                continue;
            }
            else if ($value)
            {
                $arrayPictureTeamBuilding[$key] = $direct.$value;
            }
        }
        if(count($arrayPictureTeamBuilding))
            $this->view->picture_team_building = $arrayPictureTeamBuilding;
    }


    /*submit form - listen Someone Like You */

    if($this->getRequest()->getMethod()=='POST') {

        /*params request*/
        $date                       = $this->getRequest()->getParam('date');
        $note                       = $this->getRequest()->getParam('note');
        $description                = $this->getRequest()->getParam('description',null);
        $team_building_picture_haved = $this->getRequest()->getParam('team_building_picture_haved');

        $upload = new Zend_File_Transfer();

        $files  = $upload->getFileInfo();

        /*Edit form*/
        if($id)
        {
            foreach ($files as $file => $info)
            {
                if (!$info['name'] and !is_array($team_building_picture_haved))
                {
                    throw new Exception('You Must Upload More Than One Picture Event Report !');
                }
            }

            $array_diff = array_diff(array_keys($arrayPictureTeamBuilding),$team_building_picture_haved);

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

                    $lastPictureEvent = count($allPicture) - 1;
                    $count = $count + $lastPictureEvent;
                    $allPicture['picture_team_building_'.$count] = $new_name;
                }
            }

            $data = array(
                'date'              => $date,
                'staff_id'          => $staff_id,
                'description'       => $description,
                'picture'           => json_encode($allPicture),
                'note'              => $note,
                'updated_at'        => date('Y-m-d H:i:s'),
                'updated_by'        => $staff_id
            );

            $QStaffTeamBuildingReport->update($data,$whereTeamBuildingReport);

        }
        else
        {

            if(!$date || !$description)
            {
                throw new Exception('Date OR Description is null');
            }
            $whereStaffTeamBuildingReport   = array();
            $whereStaffTeamBuildingReport[] = $QStaffTeamBuildingReport->getAdapter()->quoteInto('date = ?',$date);
            $whereStaffTeamBuildingReport[] = $QStaffTeamBuildingReport->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            $whereStaffTeamBuildingReport[] = $QStaffTeamBuildingReport->getAdapter()->quoteInto('staff_id = ?',$staff_id);
            $rowStaffTeamBuildingReport     = $QStaffTeamBuildingReport->fetchRow($whereStaffTeamBuildingReport);
            if($rowStaffTeamBuildingReport)
            {
                throw new Exception('Date is team building !');
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

            $arrayUpload = array();

            $arrayUpload['direct'] = $uniqid;

            $arrayUpload['user_id'] = $userStorage->id;

            foreach ($files as $file => $info) {
                $old_name  = $info['name'];
                if(!$old_name)
                    throw new Exception('Must Upload Picture !');
                $tExplode  = explode('.', $old_name);
                $extension = end($tExplode);
                $new_name  = md5(uniqid('', true)) . '.' . $extension;

                $upload->addFilter('Rename', array('target' => $uploaded_dir .
                    DIRECTORY_SEPARATOR . $new_name));
                $upload->receive(array($file));
                $arrayUpload[$file] = $new_name;
            }


            $data = array(
                'date'              => $date,
                'staff_id'          => $staff_id,
                'description'       => $description,
                'picture'           => json_encode($arrayUpload),
                'note'              => $note,
                'created_at'        => date('Y-m-d H:i:s'),
                'created_by'        => $staff_id
            );

            $QStaffTeamBuildingReport->insert($data);

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
        $this->_redirect(HOST.'trainer/list-team-building-report');
    }

}
catch (Exception $e)
{
    $db->rollback();
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST.'trainer/list-team-building-report');
}
