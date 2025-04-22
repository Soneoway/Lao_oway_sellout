<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

if ($this->getRequest()->getMethod() != 'POST'){
    exit;
}

$str = '<script>parent.$(\'#email_modal\').find(".modal-body .alert").remove()</script>';
echo $str;

$staffFirst      = $this->getRequest()->getParam('staffFirst');
$inputIMEI       = $this->getRequest()->getParam('inputIMEI');
$inputDate       = $this->getRequest()->getParam('inputDate');
$inputShift      = $this->getRequest()->getParam('inputShift');
$inputStore      = $this->getRequest()->getParam('inputStore');
$customerName    = $this->getRequest()->getParam('inputCustomerName');
$customerPhone   = $this->getRequest()->getParam('inputCustomerPhone');
$customerAddress = $this->getRequest()->getParam('inputCustomerAddress');
$photo           = $this->getRequest()->getParam('photo');
$inputNote       = $this->getRequest()->getParam('inputNote');
$timing_sales_id = $this->getRequest()->getParam('timing_sales_id');
$checksum        = $this->getRequest()->getParam('checksum');

// check dữ liệu hợp lệ
// $cs = sha1(md5($inputIMEI).$timing_sales_id);
// if ($cs != $checksum) {
// 	// thông báo dữ liệu không hợp lệ
// 	$str = 'parent.$(\'#email_modal\').find(".modal-body")
//        .append(\'<div class="alert alert-error fade in">\' +
//          \'Dữ liệu không hợp lệ!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
//        \'</div>\')';
// 	exit('<script>'.$str.'</script>');

// }

$inputIMEI = trim($inputIMEI);

// check IMEI
if (!$inputIMEI && !preg_match('/^[0-9]{15}$/', $inputIMEI)) {
    // thông báo IMEI không hợp lệ
    $str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-error fade in">\' +
          \'IMEI không hợp lệ!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\')';
    exit('<script>'.$str.'</script>');

}

$QTiming = new Application_Model_Timing();
$checkImei = $QTiming->checkImeiDealer($inputIMEI, $inputStore);
if($checkImei == false){
    echo '<script>
        parent.palert("IMEI '.$inputIMEI.' không thuộc cửa hàng này. Có thể đây là máy mượn/chuyển giữa các cửa hàng. Công ty không tính doanh số với các máy được báo cáo sai cửa hàng. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
        parent.alert("IMEI '.$inputIMEI.' không thuộc cửa hàng này. Có thể đây là máy mượn/chuyển giữa các cửa hàng. Công ty không tính doanh số với các máy được báo cáo sai cửa hàng. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
    </script>';
    exit;
}

$tmp = explode('/', $inputDate);
$formatedDate = $tmp[2].'-'.$tmp[1].'-'.$tmp[0].' 00:00:00';

// check ngày
if ( strtotime($formatedDate ) > strtotime( date('Y-m-d H:i:s') ) ) {
    // thông báo không thể chấm công cho trước ngày hiện tại
    $str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-error fade in">\' +
          \'Không thể báo cáo cho tương lai!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\')';
    exit('<script>'.$str.'</script>');

}

// check ca chấm công

// check store


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;
$inputNote = trim($inputNote);

$QDuplicatedImei = new Application_Model_DuplicatedImei();

// kiểm tra xem staff này đã báo cáo cho IMEI này chưa
$where = array();
$where[] = $QDuplicatedImei->getAdapter()->quoteInto('imei = ?', $inputIMEI);
$where[] = $QDuplicatedImei->getAdapter()->quoteInto('staff_id = ?', $user_id);
$dup = $QDuplicatedImei->fetchRow($where);

if ($dup) {
    // thông báo trùng
    $str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-error fade in">\' +
          \'Bạn đã gửi thông báo IMEI này!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\')';
    exit('<script>'.$str.'</script>');
}

$data = array_filter(array(
	'staff_id'           => $user_id,
	'imei'               => $inputIMEI,
	'date'               => $formatedDate,
	'shift'              => $inputShift,
	'store_id'           => $inputStore,
	'customer_name'       => $customerName,
	'customer_address'    => $customerAddress,
	'customer_phone'      => $customerPhone,
	'timing_sales_first' => $timing_sales_id,
	'staff_id_first'     => $staffFirst,
	'note'               => $inputNote,
	'created_at'         => date('Y-m-d H:i:s'),
));

$last_id = $QDuplicatedImei->insert($data);

if (!$last_id) {
    // báo lỗi không insert được
    $str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-error fade in">\' +
          \'Insert Failed!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\')';
    exit('<script>'.$str.'</script>');
}

try {
    $month = date('Y-m', strtotime($formatedDate));
    //move from temp to folder
    $located_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_imei_duplicated'.DIRECTORY_SEPARATOR.$month.DIRECTORY_SEPARATOR.substr($formatedDate, 0, 10).DIRECTORY_SEPARATOR.$last_id.DIRECTORY_SEPARATOR;
    if (!is_dir($located_dir))
        @mkdir($located_dir, 0777, true);

    $tem = explode('_located_', $photo);

    if (isset($tem[1]) and $tem[1]) {
        $uniqid = $tem[0];
        $file_name = $tem[1];

        $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_imei_duplicated'.DIRECTORY_SEPARATOR;
        $uploaded_dir .= 'temp' . DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR . $file_name;


        if (is_file($uploaded_dir))
            copy($uploaded_dir, $located_dir . $file_name);

        //remove other file
        $files = glob($located_dir.'*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file) and !strstr($file, $file_name))
                unlink($file); // delete file
        }

        $data      = array('photo' => $file_name);

        $where = $QDuplicatedImei->getAdapter()->quoteInto('id = ?', $last_id);

        $QDuplicatedImei->update($data, $where);
    }

} catch (Zend_File_Transfer_Exception $e) {
    $str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-error fade in">\' +
          \'Please input valid file!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\')';
    exit('<script>'.$str.'</script>');
}

//unlink temp folder
try {
    $dirPath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR.'temp' . DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
        $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
    }
    @rmdir($dirPath);
} catch (Exception $e){}

$str = 'parent.$(\'#email_modal\').find(".modal-body")
        .append(\'<div class="alert alert-info fade in">\' +
          \'Notify Sent!<button type="button" class="close" data-dismiss="alert">&times;</button>\' +
        \'</div>\'); setTimeout(function(){parent.$("#email_modal").modal("hide")}, 2000)';

exit('<script>'.$str.'</script>');


exit;