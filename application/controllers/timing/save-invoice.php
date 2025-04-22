<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QPrint = new Application_Model_Print();
$QWebImei = new Application_Model_WebImei();
$QStore = new Application_Model_Store();

if ($this->getRequest()->getMethod() == 'POST'){

    
    $imei                = $this->getRequest()->getParam('imei');
    $store               = $this->getRequest()->getParam('store');
    $phone_number        = $this->getRequest()->getParam('phone_number');
    $customer_name       = $this->getRequest()->getParam('customer_name');
    $discount            = $this->getRequest()->getParam('discount');
    $payment             = $this->getRequest()->getParam('payment',null);

    if($payment == 1){
        $payment_type = 1;
    }else{
        $payment_type = null;
    }


    if($imei){
        $check_imei = $QWebImei->getImeiInfo($imei);

        $good_id = $check_imei['good_id'];
        $good_color = $check_imei['color_id'];
        $price = $check_imei['price'];
        $distributor_id = $check_imei['distributor_id'];
    }
    else{
        echo "<script>alert('IMEI ".$imei." ນີ້ຍັງບໍ່ທັນມີໃນລະບົບ')</script>";
    }

    if($store){
        $where = $QStore->getAdapter()->quoteInto('id = ?', $store);
        $stores_check = $QStore->fetchRow($where);

        $store_distributor = $stores_check['d_id'];

        if($distributor_id != $store_distributor){
            echo "<script>alert('ຮ້ານທີ່ IMEI ".$imei." ນີ້ຢູ່ແມ່ນບໍ່ໄດ້ຢູ່ໃນຮ້ານທີ່ຕ້ອງການສ້າງບິນ  ກະລຸນາກວດສອບຄືນໃໝ່ອີກຄັ້ງ')</script>";

            echo '<script>parent.location.href="/timing/invoice/invoice"</script>';
            return exit();
        }
    }

    $where2 = $QPrint->getAdapter()->quoteInto('imei = ?', $imei);
    $check_create = $QPrint->fetchRow($where2);

    if($check_create){
        echo "<script>alert('".$imei." ຖືກສ້າງບິນໄປເເລ້ວ ບໍ່ສາມາດສ້າງໃໝ່ໄດ້')</script>";

        echo '<script>parent.location.href="/timing/invoice/invoice?imei='.$imei.'"</script>';
            return exit();
    }





    $data = array(
        'store'         => $store,
        'imei'          => $imei,
        'customers'     => $customer_name,
        'phone_number'  => $phone_number,
        'discount'      => $discount,
        'good_id'       => $good_id,
        'color_id'      => $good_color,
        'unit_price'    => $price,
        'num'           => 1,
        'sn'            => date('YmdHis'),
        'total_price'   => $price,
        'create_by'     => $userStorage->id,
        'create_at'     => date('Y-m-d H-i-s'),
        'payment'       => $payment_type,
        );

    $resualt = $QPrint->insert($data);
}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('ສ້າງບິນສຳເລັດ');

echo '<script>parent.location.href="/timing/print-invoice?id='.$resualt.'"</script>';
exit;