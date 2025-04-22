<style>
    body{
        font-family: phetsarath ot;
    }
</style>
<?php
$this->_helper->layout->disableLayout();

$timing_sales_id = $this->getRequest()->getParam('timing_sales_id');
$is_check = $this->getRequest()->getParam('is_check', 0);
$del_check = $this->getRequest()->getParam('del', 0);

if ($is_check) {
    $imei = trim($this->getRequest()->getParam('value', ''));
    $imei = explode("\n", $imei);
} else {
    $imei = $this->getRequest()->getParam('value');
}

if (!defined("IMEI_ACTIVATION_EXPIRE"))
    define("IMEI_ACTIVATION_EXPIRE", 3);

if (is_array($imei)) { // Tool check IMEI

    $imei = array_unique($imei);

    $result = array();

    $QMobileComplete = new Application_Model_MobileComplete();
    $QTimingSale = new Application_Model_TimingSale();

    foreach ($imei as $key => $value) {

        $result_info = "";

    	$value = trim($value);

        if ($del_check == 1) {

            $where = $QMobileComplete->getAdapter()->quoteInto('imei = ?', $value );
            $result_mc = $QMobileComplete->fetchRow($where);

            if ( empty($result_mc) ) {
                $where = $QTimingSale->getAdapter()->quoteInto('imei = ?', $value );
                $del_result = $QTimingSale->delete($where);
            } else {
                $value = $value."<br/><font color='red'>( Trade-In! )</font>";
            }
            
        }

        $info = array();
        $return = $this->checkImei($value, $timing_sales_id, $info, 1);

        if ($return==1){
        //     // nó đã chấm ở một lúc nào đó
        //     $result[$value] = " | Bạn đã báo cáo IMEI này rồi, vào lúc [" . date('d/m/Y H:i:s', strtotime($info['date'])) . "] Tại cửa hàng [".$info['store']."]";

        } else if ($return == 2) {
        //     //not existed in list sales out
            $result_info = "IMEI ຍັງບໍ່ທັນມີໃນລະບົບ!";

        } else if ($return == 3 || $return == 5) {
        //     // bị thằng khác chấm trước rồi
        //     $result[$value] = " | IMEI này đã được [" . $info['staff'].'] báo cáo ở cửa hàng ['. $info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']';

        } elseif ($return == 4) {
        //     // tồn tại trong list tháng 12 về trước
            $result_info = "IMEI ນີ້ຖືກລາຍງານຍອດໄປແລ້ວ!.";

        } elseif ($return == 6) {
        //     // trong bảng lock imei - hàng tặng đại lý
            $result_info = "IMEI ນີ້ບໍ່ສາມາດນຳມາຄິດໄລ່ຄ່າຄອມໄດ້.";

        } elseif ($return == 7) {
        //     // hàng demo
            $result_info = "IMEI ນີ້ເປັນເຄື່ອງ demo, ບໍ່ສາມາດນຳມາຄິດໄລ່ຄ່າຄອມໄດ້.";

        }  elseif ($return == 8) {
        //     // hàng staff
            $result_info = "IMEI ນີ້ເປັນເຄື່ອງສຳລັບພະນັກງານ ບໍ່ສາມາດນຳມາຄິດໄລ່ຄ່າຄອມໄດ້.";

        }  elseif ($return == 9) {
        //     // hàng mượn
            $result_info = "IMEI ນີ້ເປັນເຄື່ອງຢືມ ບໍ່ສາມາດນຳມາຄິດໄລ່ຄ່າຄອມໄດ້.";

        } else if ($return == 10) {
            // ALready PO but not SALE OUT
            $result_info = "IMEI ບໍ່ສາມາດລາຍງານຍອດໄດ້ ກາລູນາຕິດຕໍ່ຫາ Admin";

        } else if ($return == 11) {
            // ready to Sales Out
            $result_info = "IMEI ມີໃນລະບົບແລ້ວ ສາມາດລາຍງານຍອດໄດ້!";

        } else
            $result_info = '';

        $result[$value] = array(
            'sales_info' => $info,
            'result' => array(
                'code' => $return,
                'info' => $result_info,
                ),
            );
    }

    // ghi log
    $QLog = new Application_Model_CheckImeiToolLog();
    $QLog->log($imei);

    // print_r($result);
    $this->view->result = $result;

} else { // Check AJAX khi chấm công

    $info = array();
    $imei = trim($imei);
    $return = $this->checkImei($imei, $timing_sales_id, $info);
	
	if (intval($imei) > 0 && strlen($imei) == 15) {
		$QCheckImeiLog = new Application_Model_CheckImeiLog();
	    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
	    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
	    // $info .= " - IMEIs (".serialize($imeis).")";
	    // 
	    //todo log
	    $QCheckImeiLog->insert( array(
	        'result' => $return,
	        // 'info' => '',
	        'imei' => $imei,
	        'user_id' => $userStorage->id,
	        'ip_address' => $ip,
	        'time' => date('Y-m-d H:i:s'),
	    ) );
	}

    if ($return==1){
        // nó đã chấm ở một lúc nào đó
        echo json_encode(
            array(
                "value" => $imei,
                "valid" => 0,
                "message" => "IMEI ນີ້ຖືກລາຍງານຍອດໄປແລ້ວ [" . date('d/m/Y H:i:s', strtotime($info['date'])) . "] ຮ້ານ [".$info['store']."]",
            )
        );

    } else if ($return==2){

        //not existed in list sales out
        echo json_encode(
            array(
                "value" => $imei,
                "valid" => 0,
                "message" => "IMEI ບໍ່ມີໃນລະບົບ",
            )
        );

    } else if ($return==3 || $return==5){

        // bị thằng khác chấm trước rồi
        echo json_encode(
            array(
                "value" => $imei,
                "valid" => 0,
                "message" => 'IMEI '.$imei.' ນີ້ຖືກຂາຍໂດຍ  ['.@$info['staff'].'] ຮ້ານ ['.$info['store'].'] ວັນທີ ['.date('d/m/Y H:i:s', strtotime($info['date'])).']
	                    	<a href="#" data-timing-sales-id="'.$info['timing_sales_id'].'" data-imei="'.$imei.'"
	                    	data-case="IMEI '.$imei.' ນີ້ຖືກຂາຍໂດຍ  ['.@$info['staff'].']  ຮ້ານ ['.$info['store'].']  ວັນທີ ['.date('d/m/Y H:i:s', strtotime($info['date'])).']"
	                    	data-checksum="'.sha1(md5($imei).$info['timing_sales_id']).'"
	                    	data-staff-first="'.@$info['staff_id'].'"
	                    	class="send_notify">ກົດໃສ່ນີ້</a> ເພື່ອລາຍງານ IMEI ຊ້ຳ.',
            )
        );

    } /*elseif ($return==5) {

        // tồn tại trong list tháng 12 về trước
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => "IMEI này đã được bán cách đây hơn 01 tháng. Bởi [" . $info['staff'].'] ở cửa hàng ['. $info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']. Công ty không tính doanh số đối với các máy đã bán ra thị trường hơn 01 tháng. Vui lòng liên hệ ASM nếu có thắc mắc.',
        ));

    } */ elseif ($return == 4) {

        // tồn tại trong bảng imei acti
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => 'IMEI ມີຍອດຊາຍທີ່ຜ່ານມາ '.IMEI_ACTIVATION_EXPIRE.' ວັນ. ບໍລິສັດຈະບໍ່ນັບຮ່ວມກັບການຂາຍເຄື່ອງທີ່ມີຍອດຂາຍກ່ວາ '.IMEI_ACTIVATION_EXPIRE.' ວັນ. ກາລູນາຕິດຕໍ່ຫາ ASM ຫາກມີຄຳຖາມ.',
        ));

    } elseif ($return == 6) {

        // tồn tại trong bảng imei acti
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => 'IMEI ນີ້ບໍ່ສາມາດນຳມາຄິດໄລ່ຄ່າຄອມໄດ້.    ກາລຸນາຕິດຕໍ່ຫາ ASM.',
        ));

    } elseif ($return == 7) {

        // tồn tại trong bảng imei acti
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => 'IMEI ນີ້ເປັນເຄື່ອງ demo, ບໍ່ສາມາດນຳມາໄລ່ຄ່າຄອມໄດ້.    ກາລຸນາຕິດຕໍ່ຫາ ASM ຫາກມີຄຳຖາມ.',
        ));

    }  elseif ($return == 8) {

        // tồn tại trong bảng imei acti
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => 'IMEI ນີ້ເປັນເຄື່ອງສຳລັບພະນັກງານ ບໍ່ສາມາດນຳມາໄລ່ຄ່າຄອມໄດ້ ກາລຸນາຕິດຕໍ່ຫາ ASM ຫາກມີຄຳຖາມ.',
        ));

    } elseif ($return == 9) {

        // tồn tại trong bảng imei acti
        echo json_encode(array(
            'value' => $imei,
            'valid' => 0,
            'message' => 'IMEI ນີ້ເປັນເຄື່ອງຢືມ, ບໍ່ສາມາດນຳມາໄລ່ຄ່າຄອມໄດ້.    ກາລຸນາຕິດຕໍ່ຫາ ASM ຫາກມີຄຳຖາມ.',
        ));

    } else
        //success
        echo json_encode(
            array(
                "value" => $imei,
                "valid" => 1,
                "message" => ""
            )
        );
    
    exit;
}
