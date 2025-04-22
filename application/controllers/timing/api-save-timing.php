<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$staff_id = $this->getRequest()->getParam('staff_id');
$store = $this->getRequest()->getParam('store_id');
$product = $this->getRequest()->getParam('product');
$model = $this->getRequest()->getParam('model');
$imei = $this->getRequest()->getParam('imei');
$customer_names = $this->getRequest()->getParam('customer_name');
$phone_numbers = $this->getRequest()->getParam('phone_number');
$emails = $this->getRequest()->getParam('email');
$addresses = $this->getRequest()->getParam('address');
$photos = $this->getRequest()->getParam('photo');
$note = $this->getRequest()->getParam('note');
$pre_order_status = $this->getRequest()->getParam('pre_order_status');
$warrant_no = $this->getRequest()->getParam('warrant_no');

$hero_product_id = array();  
// $hero_product_id = array();

if ($staff_id && $imei && $store) {
    set_time_limit(0);

    if ($pre_order_status == 1 && !in_array($product, $hero_product_id)) {
        $result['msg'] = "เฉพาะรุ่น Reno, Reno 10X และ K3 เท่านั้นที่ Pre Order ได้!";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
        echo json_encode($result);
        exit;
    // } else if ($pre_order_status == 1 && $model != 11) {
    } 
//    else if ($pre_order_status == 1 && !in_array($model, array(11,17)) ) {
//        // Check F9 Product Color
//    	if ($product == 345) {
//    		$result['msg'] = "เฉพาะรุ่น F9 สี Starry Purple และสี Jade Green เท่านั้นที่ Pre Order ได้!";
//        	$result['issue'] = null;
//        	$result['success'] = 'FAIL';
//        	echo json_encode($result);
//        	exit;
//    	}
//
//    }

    $curdate = strtotime(date("Y-m-d H:i:s"));

    // if (($curdate >= strtotime("2018-04-25 00:00:00")) && ($curdate <= strtotime("2018-04-28 23:59:59"))) {

        if (in_array($product, $hero_product_id)) {
            if ($pre_order_status == 1) {
                if (empty($warrant_no)) {
                    $result['msg'] = "กรุณากรอกหมายเลข VIP Card ด้วยคะ";
                    $result['issue'] = null;
                    $result['success'] = 'FAIL';
                    echo json_encode($result);
                    exit;
                }
                if (strlen($warrant_no) != 10) {
                    $result['msg'] = "รูปแบบ VIP Card ไม่ถูกต้อง กรุณาตรวจสอบรูปแบบใหม่คะ";
                    $result['issue'] = null;
                    $result['success'] = 'FAIL';
                    echo json_encode($result);
                    exit;
                }
            } else {
                $warrant_no = null;
            }
        }
    // }

    $db = Zend_Registry::get('db');

    $db->beginTransaction();

    $QTiming = new Application_Model_Timing();
    $QTimingSale = new Application_Model_TimingSale();

    // check imei already sell out from warehouse
    $where = $QTimingSale->getAdapter()->quoteInto('imei = ?', $imei);
    $check_timing_sale = $QTimingSale->fetchRow($where);

    if (isset($check_timing_sale['imei']) && $check_timing_sale['imei']) {
        $result['msg'] = "IMEI : " . $imei . " ถูกรายงานยอดไปแล้วค่ะ!";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
        echo json_encode($result);
        exit;
    }

    $QStaff = new Application_Model_Staff();
    $where = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
    $pc_type = $QStaff->fetchRow($where);

    // Chekc PC Stand By Already Timing First Store of Day
    if ($pc_type['pc_stand_by'] == 1) {
        $pc_stand_by_result = $QTimingSale->check_pc_stand_by($staff_id);

        if (!empty($pc_stand_by_result) && ($pc_stand_by_result['store_id'] != $store)) {
            $result['msg'] = "PC Stand by สามารถยิงยอดเข้าได้ 1 ร้านต่อ 1 วันค่ะ!";
            $result['issue'] = null;
            $result['success'] = 'FAIL';
            echo json_encode($result);
            exit;
        }
    }

    $issue = array(
        'staff_id' => $staff_id,
        'imei' => $imei,
        'store' => $store,
        'timing_date' => date('Y-m-d H:i:s'),
    );

    $checkImei = $QTiming->checkImeiDealer($imei, $store);

    if ($checkImei == 0) {

        $formatedDate = date('Y-m-d');
        $formatedFrom = '09:00:00';
        $formatedTo = '23:00:00';

        $QImei = new Application_Model_WebImei();
        $QCustomer = new Application_Model_Customer();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        $where_imei = $QImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $imei_check = $QImei->fetchRow($where_imei);

//        $upload = new Zend_File_Transfer_Adapter_Http();
//
//        $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');
//        $upload->addValidator('FilesSize', false, array('max' => '20MB'));
//        $upload->addValidator('ExcludeExtension', false, 'php,sh');
//        $upload->addValidator('Count', false, 1);
//
//        $info = pathinfo($upload->getFileName());

        try {
            $sales_id = $QStoreStaffLog->get_sales_man($store, $formatedDate);

//            if ($upload->isValid()) {
//                try {


                    // timing insert
                    $id = $QTiming->insert(array(
                        'shift' => 1,
                        'from' => $formatedDate . ' ' . $formatedFrom,
                        'to' => $formatedDate . ' ' . $formatedTo,
                        'note' => $note,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $staff_id,
                        'staff_id' => $staff_id,
                        'store' => $store,
                        'sales_id' => ($sales_id) ? $sales_id : null,
                    ));

                    //insert customer
                    if ($customer_names)
                        $customer_id = $QCustomer->insert(array(
                            'name' => $customer_names,
                            'phone_number' => $phone_numbers,
                            'email' => (isset($emails) ? $emails : null),
                            'address' => (isset($addresses) ? $addresses : null),
                            'created_at' => date('Y-m-d H:i:s')
                        ));

                    //insert timing sale
                    $timing_sales_id = $QTimingSale->insert(array(
                        'model_id' => $imei_check['good_color'],
                        'product_id' => $imei_check['good_id'],
                        'timing_id' => $id,
                        'customer_id' => (isset($customer_id) ? $customer_id : null),
                        'imei' => (isset($imei) ? $imei : null),
                        'pre_order_status' => ($pre_order_status == -1) ? 0 : $pre_order_status,
                        'warrant_no' => (!empty($warrant_no) ? $warrant_no : null),
                        'app_id' => 1,
                    ));

//                    $month = substr($formatedDate, 0, -3);
//
//                    //move from temp to folder
//                    $located_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . 'timing_sales_new' . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $formatedDate . DIRECTORY_SEPARATOR . $timing_sales_id . DIRECTORY_SEPARATOR;
//                    if (!is_dir($located_dir))
//                        @mkdir($located_dir, 0777, true);
//
//                    $new_file_name = md5($timing_sales_id . FILENAME_SALT) . "." . $info['extension'];
//
//                    $upload->addFilter('Rename', $located_dir . $new_file_name);
//
//                    $data = array('photo' => $new_file_name);
//                    $where = $QTimingSale->getAdapter()->quoteInto('id = ?', $timing_sales_id);
//                    $QTimingSale->update($data, $where);
//
//                    $upload->receive();

                    $db->commit();
                    $result['msg'] = 'การรายงานยอดขาย สำเร็จ';
                    $result['success'] = 'COMPLETE';
//                } catch (Zend_File_Transfer_Exception $e) {
//
//                }
//            } else {
//                $errors = $upload->getErrors();
//
//                $sError = null;
//
//                if ($errors and isset($errors[0]))
//                    switch ($errors[0]) {
//                        case 'fileUploadErrorIniSize':
//                            $sError = 'Upload รูปภาพไม่สำเร็จ เนื่องจากรูปภาพมีขนาดใหญ่เกินไป !!';
//                            break;
//                        case 'fileMimeTypeFalse':
//                            $sError = 'The file(s) you selected weren\'t the type we were expecting';
//                            break;
//                    }
//
//                $result['msg'] = $sError;
//                $result['issue'] = null;
//                $result['success'] = 'FAIL';
//                echo json_encode($result);
//                exit;
//            }
        } catch (Exception $e) {
            $db->rollback();
            $result['msg'] = 'โปรดตรวจสอบการทำรายการใหม่';
            $result['issue'] = null;
            $result['success'] = 'FAIL';
            echo $e;
            echo json_encode($result);
            exit;
        }
    } else if ($checkImei == 1) {
        $result['msg'] = "IMEI " . $imei . " นี้ไม่มีในระบบค่ะ กรุณาตรวจสอบหมายเลข IMEI กับใบรับประกันด้วยค่ะ";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 4) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI " . $imei . " นี้เป็น IMEI ของ ORG ไม่สามารถนำมารายงานยอดได้ค่ะ!";
        $result['issue'] = $issue;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 8) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI $imei นี้เป็น IMEI ของร้าน Seven-Eleven ไม่สามารถนำมารายงานยอดได้ค่ะ!";
        $result['issue'] = $issue;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 9) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "ร้านนี้เป็นร้าน Focus Shop กรุณารายงานยอดใน Menu : รายงานยอดขายคู่แข่ง (Report Competitor Brand) ก่อนค่ะ!";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 10) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI $imei นี้เป็น IMEI ของคลัง Grade B ไม่สามารถนำมารายงานยอดได้ค่ะ!";
        $result['issue'] = $issue;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 11) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI $imei นี้ไม่ใช่ IMEI ของ OPPO ไม่สามารถรายงานยอดได้ค่ะ!";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
    } else if ($checkImei == 12) {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI $imei นี้ยังไม่ถูก Scan เข้าหน้าร้านค่ะ!";
        $result['issue'] = null;
        $result['success'] = 'FAIL';
    } else {
        $issue['issue_type'] = $checkImei;
        $result['msg'] = "IMEI " . $imei . " นี้ไม่สามารถรายงานยอดได้ กรุณาติดต่อ Admin ประจำเขตค่ะ!";
        $result['issue'] = $issue;
        $result['success'] = 'FAIL';
    }
} else {
    $result['msg'] = 'No parameter !';
    $result['success'] = 'FAIL';
}

echo json_encode($result);