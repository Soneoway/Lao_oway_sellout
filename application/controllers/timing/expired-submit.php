<?php

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id = $this->getRequest()->getParam('id');
$imei = $this->getRequest()->getParam('imei');

try {
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    if (!$userStorage) throw new Exception("Invalid user", 3);

    if (!$id || !$imei) throw new Exception("Invalid data", 2);

    $QTimingSaleExpired = new Application_Model_TimingSaleExpired();
    $where = array();
    $where[] = $QTimingSaleExpired->getAdapter()->quoteInto('id = ?', $id);
    $where[] = $QTimingSaleExpired->getAdapter()->quoteInto('imei = ?', $imei);

    $timing_sale_check = $QTimingSaleExpired->fetchRow($where);

    if (!$timing_sale_check) throw new Exception("Inalid IMEI", 4);

    $QTiming = new Application_Model_Timing();
    $where = $QTiming->getAdapter()->quoteInto('id = ?', $timing_sale_check['timing_id']);
    $timing_check = $QTiming->fetchRow($where);

    if (!$timing_check) throw new Exception("Invalid timing", 5);

    $info = array();
    $return = $this->checkImei($imei, null, $info);

    if ($return && $return != 4) {
        if ($return==1) throw new Exception("Bạn đã báo cáo IMEI này rồi, vào lúc [" . date('d/m/Y H:i:s', strtotime($info['date'])) . "] Tại cửa hàng [".$info['store']."]", 6);
        if ($return==2) throw new Exception("IMEI không tồn tại. Vui lòng xem kỹ lại IMEI.", 7);
        if ($return==3 || $return==5) throw new Exception('IMEI '.$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']
                                <a href="#" data-timing-sales-id="'.$info['timing_sales_id'].'" data-imei="'.$imei.'"
                                data-case="IMEI '.$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']"
                                data-checksum="'.sha1(md5($imei).$info['timing_sales_id']).'"
                                data-staff-first="'.$info['staff_id'].'"
                                class="send_notify">Bấm vào đây</a> để gửi yêu cầu xử lý.', 8);
        if ($return == 6) throw new Exception('IMEI này của máy tặng khách hàng, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.', 9);
        if ($return == 7) throw new Exception('IMEI này của máy demo, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.', 10);
        if ($return == 8) throw new Exception('IMEI này của máy xuất cho nhân viên, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.', 11);
        if ($return == 9) throw new Exception('IMEI này của máy mượn, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.', 12);
    }

    $data = $timing_check->toArray();
    unset($data['id']);
    $data['from'] = date('Y-m-d').' '.date('H:i:s', strtotime($timing_check['from']));
    $data['to'] = date('Y-m-d').' '.date('H:i:s', strtotime($timing_check['to']));
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = null;
    $data['approved_at'] = date('Y-m-d H:i:s');
    $data['status'] = 1;
    $data['note'] = 'IMEI Expired - Reimport';

    $new_timing_id = $QTiming->insert($data);
    
    $data = $timing_sale_check->toArray();
    $data['timing_id'] = $new_timing_id;
    $data['reimport'] = 1;
    $QTimingSale = new Application_Model_TimingSale();
    $QTimingSale->insert($data);

    $where = $QTimingSaleExpired->getAdapter()->quoteInto('id = ?', $id);
    $QTimingSaleExpired->delete($where);

    exit(json_encode(array(
        'code' => 1,
        'message' => 'Success',
    )));
} catch (Exception $e) {
    exit(json_encode(array(
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
    )));
}