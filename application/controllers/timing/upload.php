<?php

$upload = new Zend_File_Transfer();

//check function
if (function_exists('finfo_file'))
    $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

$upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');

$upload->addValidator('FilesSize', false, array('max' => '20MB'));

$upload->addValidator('ExcludeExtension', false, 'php,sh');

$upload->addValidator('Count', false, 1);

$from = $this->getRequest()->getParam('from');

if ($from == 'duplicate') {
    // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
    $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_imei_duplicated'.DIRECTORY_SEPARATOR;
} else {
    $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR;
}


$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$uniqid = uniqid();

$uploaded_dir .= 'temp' . DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR;

if (!is_dir($uploaded_dir))
    @mkdir($uploaded_dir, 0777, true);

$upload->setDestination($uploaded_dir);

if (!$upload->isValid()){
    $errors = $upload->getErrors();

    $sError = null;

    if ($errors and isset($errors[0]))
        switch ($errors[0]){
            case 'fileUploadErrorIniSize':
                $sError = 'รูปมีขนาดใหญ่เกินไป กรุณาลดขนาดให้เล็กลง 1 M ก่อนแนบด้วยค่ะ';
                break;
            case 'fileMimeTypeFalse':
                $sError = 'The file(s) you selected weren\'t the type we were expecting';
                break;
        }

    $result = array('error' => $sError);

} else {

    try {
        $upload->receive();


        $result = array('success' => true);

    } catch (Zend_File_Transfer_Exception $e) {

        $result = array('error' => $e->message());
    }

    $result['uniqid'] = $uniqid;
}

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

exit;