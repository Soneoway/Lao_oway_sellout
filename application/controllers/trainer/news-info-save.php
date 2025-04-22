<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel       = new Application_Model_NewsInfo();

    $id           = $this->getRequest()->getParam('id');
    $name         = $this->getRequest()->getParam('name');
    $type         = $this->getRequest()->getParam('type');
    $video_link   = $this->getRequest()->getParam('video_link');
    $img_title    = $this->getRequest()->getParam('img_title');
    $img_title_edit= $this->getRequest()->getParam('img_title_edit');
    $file_link    = $this->getRequest()->getParam('file_link');
    $file_link_edit= $this->getRequest()->getParam('file_link_edit');
    $detail       = $this->getRequest()->getParam('detail');
    $enable       = $this->getRequest()->getParam('enable');

//    echo $enable;

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
    $img_title = '';
    $file_link = '';
    $val = $uploaded_dir;
    // die;
    $key_img_title = 'img_title';
    $fileContentInfo = (isset($files[$key_img_title]) and $files[$key_img_title]) ? $files[$key_img_title] : null;
    if (isset($fileContentInfo['name']) and $fileContentInfo['name']) {

        if (!is_dir($val))
            @mkdir($val, 0777, true);

        $upload->setDestination($val);
        $old_name = $fileContentInfo['name'];
        $tExplode = explode('.', $old_name);
        $extension = end($tExplode);
        $img_title = 'imgtitle-' . md5(uniqid('', true)) . '.' . $extension;
        $upload->addFilter('Rename', array('target' => $val .DIRECTORY_SEPARATOR . $img_title));
        $r = $upload->receive(array($key_img_title));

        if ($r)
            $data[$key_img_title] = $img_title;
        else{
            $messages = $upload->getMessages();
            foreach ($messages as $msg)
                throw new Exception($msg);
        }
    }

    $key_file_content = 'file_link';
    $fileContentInfo = (isset($files[$key_file_content]) and $files[$key_file_content]) ? $files[$key_file_content] : null;
    if (isset($fileContentInfo['name']) and $fileContentInfo['name']) {

        if (!is_dir($val))
            @mkdir($val, 0777, true);

        $upload->setDestination($val);
        $old_name = $fileContentInfo['name'];
        $tExplode = explode('.', $old_name);
        $extension = end($tExplode);
        $file_link = 'filelink-' . md5(uniqid('', true)) . '.' . $extension;
        $upload->addFilter('Rename', array('target' => $val .DIRECTORY_SEPARATOR . $file_link));
        $r = $upload->receive(array($key_file_content));

        if ($r)
            $data[$key_file_content] = $file_link;
        else{
            $messages = $upload->getMessages();
            foreach ($messages as $msg)
                throw new Exception($msg);
        }
    }
    // }

    function LinkYoutube($link_youtube){
        preg_match('#(?:https://)?(?:http://)?(?:www\.)?(?:youtube\.com/(?:v/|watch\?v=)|youtu\.be/)([\w-]+)(?:\S+)?#', $link_youtube, $match);
        $embed = "https://www.youtube.com/embed/$match[1]";
        return str_replace($match[0], $embed, $link_youtube);
    }

    function UpdateFileEmpty($new_name, $file_edit){
        if($new_name){
            return $new_name;
        }else{
            return $file_edit;
        }
    }

    $data_insert = array(
        'name'         => $name,
        'type'         => $type,
        'video_link'   => LinkYoutube($video_link),
        'img_title'    => $img_title,
        'file_link'    => $file_link,
        'detail'       => $detail,
        'enable'       => $enable,
        'created_at'   => date('Y-m-d H:i:s'),
        'created_by'   => $userStorage->id,
    );

    $data_update = array(
        'name'         => $name,
        'type'         => $type,
        'video_link'   => LinkYoutube($video_link),
        'img_title'    => UpdateFileEmpty($img_title, $img_title_edit),
        'file_link'    => UpdateFileEmpty($file_link, $file_link_edit),
        'detail'       => $detail,
        'enable'       => $enable,
        'updated_at'   => date('Y-m-d H:i:s'),
        'updated_by'   => $userStorage->id,
    );

//    print_r($data);
//    print_r($_FILES);
//    print_r($_REQUEST);

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->update($data_update, $where);
    } else {
        $QModel->insert($data_insert);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect( ( $back_url ? $back_url : HOST.'trainer/list-product-info' ) );