<?php
include_once APPLICATION_PATH.'/../library/My/FcmNotification.php';

if ($this->getRequest()->getMethod() == 'POST'){
    $QModel       = new Application_Model_PushNotification();

    $id           = $this->getRequest()->getParam('id');
    $title        = $this->getRequest()->getParam('title');
    $message      = $this->getRequest()->getParam('message_text');
    $file_link    = $this->getRequest()->getParam('file_link_');
    $file_edit    = $this->getRequest()->getParam('file_edit');
    $action_status      = $this->getRequest()->getParam('action_status');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    // if($file_link != ''){
        $public_path =  realpath(APPLICATION_PATH . '/../public/');
	    $uploaded_dir = $public_path .DIRECTORY_SEPARATOR. 'trainer';


	    // $fileName = date("Ymdhis").rand(111,999);

	    $upload = new Zend_File_Transfer();
	    // $upload->setOptions(array('ignoreNoFile'=>true));

	    // //check function
	    // // if (function_exists('finfo_file'))
	    // //     $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

	    // $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif,pdf');
	    // $upload->addValidator('Size', false, array('max' => '5MB'));
	    // $upload->addValidator('ExcludeExtension', false, 'php,sh');
	    $files = $upload->getFileInfo();

	    // print_r($files);

	    // if (!$upload->isValid()){
     //        $errors = $upload->getErrors();

     //        $sError = null;

     //        if ($errors and isset($errors[0]))
     //            switch ($errors[0]){
     //                case 'fileUploadErrorIniSize':
     //                    $sError = 'File size is too large';
     //                    break;
     //                case 'fileMimeTypeFalse':
     //                case 'fileExtensionFalse':
     //                    $sError = 'The file(s) you selected weren\'t the type we were expecting';
     //                    break;
     //                default:
     //                    $sError = 'The file(s) you selected weren\'t the type we were expecting';
     //                    break;
     //            }

     //        throw new Exception($sError);
     //    }
        $new_name = '';
        $val = $uploaded_dir;
        // die;
        $key = 'file_link';
        $fileInfo = (isset($files[$key]) and $files[$key]) ? $files[$key] : null;
        if (isset($fileInfo['name']) and $fileInfo['name']) {

            if (!is_dir($val))
                @mkdir($val, 0777, true);

            $upload->setDestination($val);
            $old_name = $fileInfo['name'];
            $tExplode = explode('.', $old_name);
            $extension = end($tExplode);
            $new_name = 'push-' . md5(uniqid('', true)) . '.' . $extension;
            $upload->addFilter('Rename', array('target' => $val .DIRECTORY_SEPARATOR . $new_name));
            $r = $upload->receive(array($key));

            if ($r)
                $data[$key] = $new_name;
            else{
                $messages = $upload->getMessages();
                foreach ($messages as $msg)
                    throw new Exception($msg);
            }
        }
    // }

    function UpdateFileEmpty($new_name, $file_edit){
        if($new_name){
            return $new_name;
        }else{
            return $file_edit;
        }
    }

    $data_insert = array(
        'title'        => $title,
        'message'      => $message,
        'file_link'    => $new_name,
        'action'       => $action_status,
        'created_at'   => date('Y-m-d H:i:s'),
        'created_by'   => $userStorage->id,
    );

    $data_update = array(
        'title'        => $title,
        'message'      => $message,
        'file_link'    => UpdateFileEmpty($new_name, $file_edit),
        'action'       => $action_status,
        'updated_at'   => date('Y-m-d H:i:s'),
        'updated_by'   => $userStorage->id,
    );
//    print_r($data);
//    print_r($_FILES);
//    print_r($_REQUEST);


    /*
    $token = array('TOKEN1', 'TOKEN2');
    */
    $token = array();

    $notification = array(
        'title' => $title,
        'text' => $message,
        'sound' => 'default',
        'icon' => 'ic_oppo_staff',
        'badge' => 1,
        'click_action' => 'OPEN_ACTIVITY_1'
    );

    $data = array(
        'picture_url' => HOST.'trainer/' . UpdateFileEmpty($new_name, $file_edit)
    );

    $fcm = new FcmNotification();

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->update($data_update, $where);
        if ($action_status == 2) {
            $fcm->send_notification($token, $notification, $data);
        }
    } else {
        $QModel->insert($data_insert);
        if ($action_status == 2) {
            $fcm->send_notification($token, $notification, $data);
        }
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect( ( $back_url ? $back_url : HOST.'trainer/list-push-notification' ) );