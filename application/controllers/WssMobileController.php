<?php
class WssMobileController extends My_Controller_Action {

    // API #1 
    public function loginAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $user = $this->getRequest()->getParam('username');
        $pass = $this->getRequest()->getParam('password');

        //$user = '5701879';
        //$pass = '123';

        $username = $user."@oppo.in.th";
        $password = md5($pass);

        $deny = array(PGPB_ID, LEADER_ID);
        
        $db = Zend_Registry::get('db');

        $QStaff = new Application_Model_Staff();

        // for Master Password
        if ($password !== 'a0bd1bd4c2cbd10a57adb9b24daa8ad8') {
            $where[] = $QStaff->getAdapter()->quoteInto('password = ?', $password);
        }
        
        $where[] = $QStaff->getAdapter()->quoteInto('email = ?', $username);
        $where[] = $QStaff->getAdapter()->quoteInto('group_id NOT IN (?)', $deny);
        $where[] = $QStaff->getAdapter()->quoteInto('status = 1');

        $result = $QStaff->fetchRow($where); 

        $data = array();
        $store_list = array();

        if (isset($result)) {
            $params['staff_id'] = $result['id'];
/*
            if ($result['group_id'] == LEADER_ID) {

                $QSL = new Application_Model_StoreLeader();
                $ss_result = $QSL->getStoreList($params);

            } else { 
*/
                $QSS = new Application_Model_StoreStaff();
                $ss_result = $QSS->getStoreList($params);
/*   
            }
*/
/*
            if ($result['group_id'] == ADMINISTRATOR_ID) {

                $QStore = new Application_Model_Store();
                $where_st[] = $QStore->getAdapter()->quoteInto('del IS NULL');
                $where_st[] = $QStore->getAdapter()->quoteInto('rank <> 3');
                $st_result = $QStore->fetchAll($where_st);

                for ($i=0;$i<count($st_result);$i++) {
                    $ss_result[$i]['store_id']   = $st_result[$i]['id'];
                    $ss_result[$i]['store_name'] = $st_result[$i]['name'];
                }

            } else { 

                $QSS = new Application_Model_StoreStaff();
                $ss_result = $QSS->getStoreList($params);
        
            }
*/

            for ($i=0;$i<count($ss_result);$i++) {
                $store_list[$i] = array(
                    'shop_id'   => $ss_result[$i]['store_id'],
                    'shop_name' => $ss_result[$i]['store_name']
                );
            }
            
            $data = array(
                'status'    => 1, 
                'user_name' => $user,
                'user_id'   => $result['id'],
            );

            for ($i=0;$i<count($store_list);$i++) {
                $data['shop_list'][$i] = $store_list[$i];
            }

            //print_r($data);
            echo json_encode($data);
        } else {

            $data = array(
                'status'    => 0,
                'user_name' => null,
                'user_id'   => null,
                'shop_list' => null
            );

            echo json_encode($data);
        }

    }

    // API #2
    public function sendAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // start : data test
/*
        $params[0] = array(
            'user_id'   => '207',
            'shop_id'   => '6021',
            'barcode'   => '868346020175012',
            'scan_date' => '2016-07-01 13:30:00',
            'send_date' => '2016-07-02 15:00:00'
        );

        $params[1] = array(
            'user_id'   => '208',
            'shop_id'   => '6021',
            'barcode'   => '869299026360410',
            'scan_date' => '2016-07-01 12:30:00',
            'send_date' => '2016-07-02 15:00:00'
        );

        $params[2] = array(
            'user_id'   => '207',
            'shop_id'   => '8778',
            'barcode'   => '869106022357177',
            'scan_date' => '2016-07-01 12:30:00',
            'send_date' => '2016-07-02 15:00:00'
        );

        $params[3] = array(
            'user_id'   => '207',
            'shop_id'   => '8778',
            'barcode'   => '869106022357178',
            'scan_date' => '2016-07-01 12:30:00',
            'send_date' => '2016-07-02 15:00:00'
        );

        $a = json_encode($params);
        $data = json_decode($a, ture);
*/
        // end : data test

        $data = json_decode( $this->getRequest()->getParam('data'), true );

        // remove duplicatae imei
        $cnt_store_all = array();
        foreach($data as $item) { 
            $unique_imei[ $item['barcode'] ] = $item; 
            $cnt_store[ $item['shop_id'] ][ $item['user_id'] ] = 0; 
            $cnt_store_all[ $item['shop_id'] ][ $item['user_id'] ] = $cnt_store_all[ $item['shop_id'] ][ $item['user_id'] ] + 1;
        }

        foreach ($unique_imei as $value) { $data_unique[] = $value; }

        //print_r($data_unique); echo "<br/> <br/>";
        //print_r($cnt_store); echo "<br/> <br/>";

        $QImei = new Application_Model_WebImei();
        $QTiming = new Application_Model_Timing();
        $QTimingSale = new Application_Model_TimingSale();

        $status = 1;
        $cnt = 0;
        $result = array();
        $msg_list = array();
        $all_imei = count($data_unique);

        $now = date('Y-m-d H:i:s');
        
        for($i=0;$i<$all_imei;$i++) {

            $flag = 0;
            $info = "";
            $result_info = "";

            // step 01 : check timing_sale from catty
            $where_ts = $QTimingSale->getAdapter()->quoteInto('imei = ?', $data_unique[$i]['barcode']);
            $resutl_ts = $QTimingSale->fetchRow($where_ts);

            // step 02 : check imei from wms
            $where_imei = $QImei->getAdapter()->quoteInto('imei_sn = ?', $data_unique[$i]['barcode']);
            $result_imei = $QImei->fetchRow($where_imei);

            if ($resutl_ts) {

                $result_info = 'imei นี้ถูกรายงานยอดไปแล้วค่ะ!';
                $status = 0;

            } elseif (!$result_imei) {

                $result_info = 'ไม่มี imei นี้ในระบบค่ะ!';
                $status = 0;

            } else {

                //$checkImei = $QTiming->checkImeiDealer($data_unique[$i]['barcode'], $data_unique[$i]['shop_id']);

                if ($result_imei['stock_shop_status'] == 2) {
/*
                    if () {


                    } else {*/
                        $result_info = 'Imei Expired - Last Scan Date : '.date('d/m/Y H:i:s', strtotime($result_imei['stock_shop_date']));
                        $status = 0;
                    //}

                } 
/*
                elseif ($checkImei == false) { 

                    $result_info = 'imei ไม่ถูกช่องทาง';
                    $status = 0;

                } 
*/
                else {

                    $arr = array(
                        'stock_shop_id'     =>  $data_unique[$i]['shop_id'],
                        'stock_shop_status' =>  1,
                        'stock_shop_date'   =>  $now,
                        'stock_shop_scan'   =>  $data_unique[$i]['scan_date']
                    );

                    $where = $QImei->getAdapter()->quoteInto('imei_sn = ?', $data_unique[$i]['barcode']);
                    $QImei->update($arr, $where);
                    $cnt = $cnt + 1;
                    $cnt_store[ $data_unique[$i]['shop_id'] ][ $data_unique[$i]['user_id'] ] = $cnt_store[ $data_unique[$i]['shop_id'] ][ $data_unique[$i]['user_id'] ] + 1;
                    $flag = 1;

                }

            } 

            // show only False Imei
            if ($flag == 0) {
                $msg_list[$i] = "[".$data_unique[$i]['barcode']."] ".$result_info;
            }

        }

        // all imei all store
        $result = array(
            'status' => $status, 
            'result' => "Result : ".$cnt."/".$all_imei,
            'Message'=> array_values($msg_list)
        );

        // Save Log 
        $QLog = new Application_Model_StockShopLogs();

        // prepare data for different user_id in same shop
        $i = 0;
        foreach($cnt_store as $key => $item) { 
            foreach($item as $key2 => $item2) { 
                $pre_data[$i] = array(
                    'shop_id' => $key,
                    'user_id' => $key2,
                    'cnt'     => $item2,
                    'total'   => $cnt_store_all[ $key ][ $key2 ]
                );
                $i++;
            }
        }

        //print_r($cnt_store); echo "<br/><br/>"; 
        //print_r($pre_data); echo "<br/><br/>";
        
        for ($i=0;$i<count($pre_data);$i++) {

            $data_log = array(
                'store_id'      =>  $pre_data[$i]['shop_id'],
                'total_imei'    =>  $pre_data[$i]['total'],
                'success_imei'  =>  $pre_data[$i]['cnt'],
                'created_by'    =>  $pre_data[$i]['user_id'],
                'created_at'    =>  date('Y-m-d H:i:s')
            );

            $QLog->insert($data_log);
            
            //print_r($data_log); echo "<br/><br/>"; 
        }

        //print_r($cnt_store_all); echo "<br/><br/>";
        //print_r($cnt_store); echo "<br/><br/>";
        //print_r($result);
        echo json_encode($result);
    }

    // API #3
    public function historyAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $user = $this->getRequest()->getParam('user_id');
        //$user = '57';

        if ($user) {
            $db = Zend_Registry::get('db');
            $QSSL = new Application_Model_StockShopLogs();
            $result = $QSSL->getHistory($user);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }
    }

    // API #4
    public function shopinventoryAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //$user = '5730';
        $user = $this->getRequest()->getParam('user_id');

        if ($user) {
            $db = Zend_Registry::get('db');
            $QSSL = new Application_Model_StockShopLogs();
            $result = $QSSL->getShopInventory($user);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }

    }

    // API #5
    public function shopinventorybystoreAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $params['store_id'] = $this->getRequest()->getParam('shop_id');
        $params['from'] = date("01/m/Y");
        $params['to'] = date("t/m/Y");

        if ($params['store_id']) {
            $db = Zend_Registry::get('db');
            $QTiming = new Application_Model_Timing();
            $result = $QTiming->short_report_by_stock_shop($params);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }

    }

    // API #6
    public function shopinventorylowAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //$user = '5730';
        $user = $this->getRequest()->getParam('user_id');

        if ($user) {
            $db = Zend_Registry::get('db');
            $QSSL = new Application_Model_StockShopLogs();
            $result = $QSSL->getShopInventorylow($user);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }

    }

    // API #7
    public function shopinventorybyselloutAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $params['store_id'] = $this->getRequest()->getParam('shop_id');
        $params['from'] = date("01/m/Y");
        $params['to'] = date("t/m/Y");

        if ($params['store_id']) {
            $db = Zend_Registry::get('db');
            $QTiming = new Application_Model_Timing();
            $result = $QTiming->short_report_by_stock_shop_sellout($params);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }

    }

    // API #8
    public function shopinventorybyallscanAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $params['store_id'] = $this->getRequest()->getParam('shop_id');
        $params['from'] = date("01/m/Y");
        $params['to'] = date("t/m/Y");

        if ($params['store_id']) {
            $db = Zend_Registry::get('db');
            $QTiming = new Application_Model_Timing();
            $result = $QTiming->short_report_by_stock_shop_all_scan($params);

            //echo "<pre>"; print_r($result); echo "</pre>"; 
            echo json_encode($result);
        } else {
            echo "No Parameter";
        }

    }

}