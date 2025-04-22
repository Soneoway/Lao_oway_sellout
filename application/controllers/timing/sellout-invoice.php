<?php

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('sellout-invoice');

$id = $this->getRequest()->getParam('id');

$QPrint = new Application_Model_Print();
$QStaff = new Application_Model_Staff();

$staff = $QStaff->get_cache();

$prints = $QPrint->getInvoiceData($id);

$data = array();

 foreach ($prints as $k=>$print){

        $data[$k]['cattagory_name'] = $print['cattagory'];

        $data[$k]['Sn_print'] = $print['sn'];

        $data[$k]['print_date'] = $print['create_at'];

        $data[$k]['sale'] = $staff[$print['create_by']];

        $data[$k]['staff_number'] = $print['s_number'];

        $data[$k]['store_name'] = $print['store_name'];

        $data[$k]['address'] = $print['address'];

        $data[$k]['contact'] = $print['contact'];

        $data[$k]['procuct_name'] = $print['procuct_name'];

        $data[$k]['good_color'] = $print['good_color'];

        $data[$k]['num'] = $print['num'];

        $data[$k]['imei'] ="IMEI: ".$print['imei'];

        $data[$k]['customers'] = $print['customers'];

        $data[$k]['phone_number'] = $print['phone_number'];


        //number for mat price
        $data[$k]['unit_price'] = number_format($print['unit_price']);

        //normal price
        $data[$k]['price_origin'] = $print['unit_price'];

        $data[$k]['discount'] = $print['discount'];


    }

$this->view->data_print = $data;