<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true); 
$userStorage = Zend_Auth::getInstance()->getStorage()->read();


if ($this->getRequest()->getMethod() == 'POST'){

    $QStaff         = new Application_Model_Staff();
    $QStore         = new Application_Model_Store();
    $QStoreStaff    = new Application_Model_StoreStaff();
    $QStoreStaffLog = new Application_Model_StoreStaffLog();
    $QLog           = new Application_Model_Log();
    $QRegion        = new Application_Model_RegionalMarket();
    $QStoreMarket   = new Application_Model_StoreMarket();
    $QSubArea       = new Application_Model_SubArea();
    $QAreaControl   = new Application_Model_AreaControl();
    $region_cache   = $QRegion->get_cache_all();
    $QClientCode    = new Application_Model_ClientCode();
    
    $id                        = $this->getRequest()->getParam('id');
    $name                      = $this->getRequest()->getParam('name');
    $store_id                  = $this->getRequest()->getParam('store_id');
    $store_code                = $this->getRequest()->getParam('store_code');
    $company_name              = $this->getRequest()->getParam('company_name');
    $company_address           = $this->getRequest()->getParam('company_address');
    $shipping_address          = $this->getRequest()->getParam('shipping_address');
    $phone_number              = $this->getRequest()->getParam('phone_number');
    $regional_market           = $this->getRequest()->getParam('regional_market');
    $district                  = $this->getRequest()->getParam('district');
    $sub_district              = $this->getRequest()->getParam('sub_district', 0);
    $org_dealer                = $this->getRequest()->getParam('org');

    // $agency_name            = $this->getRequest()->getParam('agency_name'); // close

    $contact_point             = $this->getRequest()->getParam('contact_point');
    $contact_phone             = $this->getRequest()->getParam('contact_phone');
    $contact_email             = $this->getRequest()->getParam('contact_email');
    $mst                       = $this->getRequest()->getParam('mst');
    $rank                      = $this->getRequest()->getParam('rank', 1);
    $dis_id                    = $this->getRequest()->getParam('dis_id');
    $pic                       = $this->getRequest()->getParam('pic');
    $pic1                      = $this->getRequest()->getParam('pic1');
    $pic2                      = $this->getRequest()->getParam('pic2');
    $pic3                      = $this->getRequest()->getParam('pic3');
    $pic4                      = $this->getRequest()->getParam('pic4');
    $pic5                      = $this->getRequest()->getParam('pic5');
    $pic6                      = $this->getRequest()->getParam('pic6');
    $pic7                      = $this->getRequest()->getParam('pic7');

    $market_type               = $this->getRequest()->getParam('market_type');
    $market_name               = $this->getRequest()->getParam('market_name');

    $it_junction               = $this->getRequest()->getParam('it_junction');
    $store_level               = $this->getRequest()->getParam('store_level');

    $area_id                   = $this->getRequest()->getParam('area_id');
    $old_pic1                  = $this->getRequest()->getParam('old_pic1');

    $th_district               = $this->getRequest()->getParam('th_district');
    $oppo_id                   = $this->getRequest()->getParam('oppo_id',0);

    $agency                    = $this->getRequest()->getParam('agency');
    $start_cooperation         = $this->getRequest()->getParam('start_cooperation');
    $attribute                 = $this->getRequest()->getParam('attribute');
    $serial_number             = $this->getRequest()->getParam('serial_number');
    $address                   = $this->getRequest()->getParam('address');
    $receiver                  = $this->getRequest()->getParam('receiver');
    $tel                       = $this->getRequest()->getParam('tel');
    $warehouse_address         = $this->getRequest()->getParam('warehouse_address');
    $sale_type                 = $this->getRequest()->getParam('sale_type');

/*
    // check dealer
    $QDistributor = new Application_Model_Distributor();
    $where = $QDistributor->getAdapter()->quoteInto('id = ?', intval($d_id));
    $distributor_check = $QDistributor->fetchRow($where);

    if (!$d_id || !intval($d_id) || !$distributor_check) {
        echo '<script>
                parent.palert("Dealer นี้ ไม่อยู่ໃນລະບົບ.");
                parent.alert("Dealer นี้ ไม่อยู่ໃນລະບົບ.");
            </script>';
        exit;
    }
*/
    $county_code = 'LA00';
    $cusCode        = $QClientCode->find(4);
    $insertCode     = $cusCode[0]['digital'];


    $store_code_running = $county_code.''.$insertCode;

    // print_r($insertStoreCode); die();

    $data = array(
        'name'                  => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $name)),
        'store_id'              => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $store_id)),
        'company_name'          => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $company_name)),
        'company_address'       => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $address)),

        // 'shipping_address' => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $shipping_address)), // close
        // 'phone_number'     => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $phone_number)), // close
        // 'store_code'       => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $store_code)), // Close
        // 'company_address'  => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $company_address)), // close

        'agency'                => $agency,
        'area_id'               => $area_id,
        'province_id'           => intval($regional_market),
        'regional_market'       => intval($regional_market),
        'district'              => intval($district),
        'sub_district'          => intval($sub_district),
        'th_district'           => intval($th_district),
        'org_dealer'            => intval($org_dealer),
        'agency_name'           => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $agency_name)), 
        'contact_point'         => $contact_point,
        'contact_phone'         => $contact_phone,
        'contact_email'         => $contact_email,
        'mst'                   => $mst,
        'rank'                  => intval($rank),
        'd_id'                  => intval($dis_id),
        'it_junction'           => intval($it_junction),
        'store_grade'           => $store_level,
        'oppo_id'               => $oppo_id,
        'active_at'             => date('Y-m-d h:i:s'),
        'active_by'             => $userStorage->id,

        'leader'                => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $receiver)),
        'phone_number'          => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $tel)),
        'shipping_address'      => trim(preg_replace(array('/\s{2,}/', '/[\t\n]+/'), ' ', $warehouse_address)),
        'sale_type'             => $sale_type,
        'attribute'             => $attribute,
        'start_cooperation'     => $start_cooperation,
        'external_serial'       => $serial_number,
        'market_type'           => $market_type,
        'market_name'           => $market_name
    );

    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'STORE - ';

    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    try {

        // chỉ cho phép admin update thông tin store
        // if ($userStorage->group_id == ADMINISTRATOR_ID) {
        if ($id){
            $where = $QStore->getAdapter()->quoteInto('id = ?', $id);

            if ($userStorage->group_id != ADMINISTRATOR_ID) { unset($data['org_dealer']); unset($data['store_grade']); }
            else if ( $data['store_grade'] == '' ) { unset($data['store_grade']); }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;
            
            $QStore->update($data, $where);
            $info .= 'Update('.$id.') - Info ('.serialize($data).') ';
            $data['id'] = $id;

                // $QWS = new Application_Model_WS();    
                // $QWS->_updateStoreToTrade($data,$info);

                // Send Data to TMS 
            $data['updated_by'] = $userStorage->id;
            $data['name'] = str_replace("&", "||", $data['name']);
            foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            $data2 = rtrim($fields_string, '&');
/*
                $ch = curl_init();

                //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsupdatetstore");
                curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/update-store-to-trade");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec($ch);
                curl_close ($ch);



                if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
                // Check Store Market
                $where_sm = $QStoreMarket->getAdapter()->quoteInto('store_id = ?', $id);
                $store_market_info = $QStoreMarket->fetchRow($where_sm);

                if (!$store_market_info) {
                    $data_sm = array('store_id' => $id, 'market_name_id' => $market_name);
                    $QStoreMarket->insert($data_sm);
                } else if ($store_market_info['market_name_id'] != $market_name) {
                    $data_sm = array('market_name_id' => $market_name);
                    $QStoreMarket->update($data_sm, $where_sm);
                } 

            } else {

                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userStorage->id;
                $data['store_code'] = $store_code_running;

                if ( $data['store_grade'] == '' ) { unset($data['store_grade']); }

                $id = $QStore->insert($data);

            if($id) {

                $next_code = $cusCode[0]['digital'] + 1;
                $next_code_running = $county_code.''.$next_code;

                $data_code = array(
                    'last_code'     => $store_code_running,
                    'next_code'     => $next_code_running,
                    'digital'       => $next_code,
                    'updated_at'    => date('Y-m-d H:i:s')
                );

                $where = $QClientCode->getAdapter()->quoteInto('id = ?', 4);
                $QClientCode->update($data_code,$where);
            }

                $data['id'] = $id;

                // print_r($data);
                $info .= 'Insert('.$id.') - Info ('.serialize($data).') ';
                
                // $QWS = new Application_Model_WS();    
                // $QWS->_insertStoreToTrade($data,$info);

                // Send Data to TMS 
                $data['created_by'] = $userStorage->id;
                $data['name'] = str_replace("&", "||", $data['name']);
                foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                $data2 = rtrim($fields_string, '&');
/*
                $ch = curl_init();

                //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsinsertstore");
                curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/insert-store-to-trade");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec($ch);
                curl_close ($ch);

                if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
                $data_sm = array(
                    'store_id' => $id,
                    'market_name_id' => $market_name
                );

                $QStoreMarket->insert($data_sm);
            }
        // }

        // List of Store Staff
            $list_staff  = $pic ? explode(',', $pic) : array();
            $list_leader = $pic1 ? explode(',', $pic1) : array();
            $list_pcm    = $pic2 ? explode(',', $pic2) : array();
            $list_bm    = $pic3 ? explode(',', $pic3) : array();
            $list_asm    = $pic4 ? explode(',', $pic4) : array();
            $list_mkt    = $pic5 ? explode(',', $pic5) : array();
            $list_rd = $pic6 ? explode(',', $pic6) : array();
            $list_pcdb = $pic7 ? explode(',', $pic7) : array();
            
        // Đảm bảo dữ liệu là mảng các số, không có khoảng trắng dư
            $list_staff  = array_filter($list_staff);
            $list_leader = array_filter($list_leader);
            $list_pcm = array_filter($list_pcm);
            $list_bm = array_filter($list_bm);
            $list_asm = array_filter($list_asm);
            $list_mkt = array_filter($list_mkt);
            $list_rd = array_filter($list_rd);
            $list_pcdb = array_filter($list_pcdb);

        // 
            foreach ($list_staff as $k => $s) {
                $list_staff[$k] = intval( trim( $s ) );
            }

            foreach ($list_leader as $k =>  $s) {
                $list_leader[$k] = intval( trim( $s ) );
            }

            foreach ($list_pcm as $k =>  $s) {
                $list_pcm[$k] = intval( trim( $s ) );
            }

            foreach ($list_bm as $k =>  $s) {
                $list_bm[$k] = intval( trim( $s ) );
            }

            foreach ($list_asm as $k =>  $s) {
                $list_asm[$k] = intval( trim( $s ) );
            }
            foreach ($list_mkt as $k =>  $s) {
                $list_mkt[$k] = intval( trim( $s ) );
            }
            foreach ($list_rd as $k => $s){
                $list_rd[$k] = intval(trim($s));
            }
            
            foreach ($list_pcdb as $k => $s){
                $list_pcdb[$k] = intval(trim($s));
            } 

        // check pcm can only 1 pcm/store
            if (HOST != 'http://catty-training2.oppo.in.th/' ) {
                if (count( $list_bm ) > 1) {
                    echo '<script>
                    parent.palert("1 ร้านจะมี BrandShop Manager ได้เพียง 1 คน เท่านั้น.");
                    parent.alert("1 ร้านจะมี BrandShop Manager ได้เพียง 1 คน เท่านั้น.");
                    </script>';
                    exit;
                }
            }
            

        // check pcm can only 1 pcm/store
            if (count( $list_pcm ) > 1) {
                echo '<script>
                parent.palert("1 ร้านจะมี PC Manager ได้เพียง 1 คน เท่านั้น.");
                parent.alert("1 ร้านจะมี PC Manager ได้เพียง 1 คน เท่านั้น.");
                </script>';
                exit;
            }

        //check sale mkt can only 1 sale mkt/store
            if (count( $list_mkt ) > 1) {
                echo '<script>
                parent.palert("1 ร้านจะมี Marketting ได้เพียง 1 คน เท่านั้น.");
                parent.alert("1 ร้านจะมี Marketting ได้เพียง 1 คน เท่านั้น.");
                </script>';
                exit;
            }

        // check asm can only 1 asm/store
            if (count( $list_asm ) > 1) {
                echo '<script>
                parent.palert("1 ร้านจะมี Sale Manager ได้เพียง 1 คน เท่านั้น.");
                parent.alert("1 ร้านจะมี Sale Manager ได้เพียง 1 คน เท่านั้น.");
                </script>';
                exit;
            }

            if (count( $list_rd ) > 1) {
                echo '<script>
                parent.palert("1 ร้านจะมี RD ได้เพียง 1 คน เท่านั้น.");
                parent.alert("1 ร้านจะมี RD ได้เพียง 1 คน เท่านั้น.");
                </script>';
                exit;
            }

        // check leader can only 1 leader/store
            if (count( $list_leader ) > 1) {
                echo '<script>
                parent.palert("1 ร้านจะมี Sale / Leader / ASM ได้เพียง 1 คน เท่านั้น.");
                parent.alert("1 ร้านจะมี Sale / Leader / ASM ได้เพียง 1 คน เท่านั้น.");
                </script>';
                exit;
            }

    /*
        // check mỗi store chỉ có 1 leader
        if (count( $list_staff ) > 1 
            && ((isset($distributor_check['parent']) && $distributor_check['parent'] && $distributor_check['parent'] == 2316) 
                || (isset($distributor_check['id']) && $distributor_check['id'] && $distributor_check['id'] == 2316)
                || (isset($distributor_check['title']) && preg_match('/^TGDĐ -/', $distributor_check['title']))
                || (isset($name) && preg_match('/^TGDĐ -/', $name))
            )
        ) {
            echo '<script>
                    parent.palert("Mỗi store thuộc chuỗi TGDĐ chỉ có thể có duy nhất 01 PG.");
                    parent.alert("Mỗi store thuộc chuỗi TGDĐ chỉ có thể có duy nhất 01 PG.");
                </script>';
            exit;
        }
    */  
        if(count($list_pcdb) != '' && count($list_staff) != ''){
            echo '<script>
            parent.palert("1 ຮ້ານຈະມີ PC ຫຼື PCDB ໄດ້ພຽງຢ່າງດຽວ ");
            parent.alert("1 ຮ້ານຈະມີ PC ຫຼື PCDB ໄດ້ພຽງຢ່າງດຽວ");
            </script>';
            exit;
        }

        // check position of BrandShop Manager
        foreach ($list_bm as $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(BM_ID) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ BrandShop Manager");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ BrandShop Manager");
                </script>';
                exit;
            }
        }

        // check position of PC Manager
        foreach ($list_pcm as $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(PCM_ID,TRAINING_TEAM_ID) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ PC Manager ຫຼື Trainer");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ PC Manager ຫຼື Trainer");
                </script>';
                exit;
            }
        }

         // check position of sale Marketting
        foreach ($list_mkt as $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(LEADER_ID, 49) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ MKT");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ MKT");
                </script>';
                exit;
            }
        }


         // check position of ASM Manager
        foreach ($list_asm as $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(LEADER_ID, ASM_ID, AM_ID, RM_ID, ASMSTANDBY_ID) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ ASM");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ ASM");
                </script>';
                exit;
            }
        }

         // check position rd
        foreach ($list_rd as $staff_id) {
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(AM_ID, RM_ID, ASMSTANDBY_ID) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ RD");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ RD");
                </script>';
                exit;
            }
        }

         // check position PCDB
        foreach ($list_pcdb as $staff_id) {
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            if ( ! in_array( $staff['group_id'], array(PCDB_ID) ) ) {
                echo '<script>
                parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ PCDB");
                parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ PCDB");
                </script>';
                exit;
            }
        }

        // check position of Sale / Ledaer / ASM
        foreach ($list_leader as $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }
            
        /*    
            if ( ! in_array( $staff['group_id'], array(SALES_ID, LEADER_ID, ASM_ID, AM_ID, RM_ID, ASMSTANDBY_ID) ) ) {
                echo '<script>
                        parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ Sale / Leader / ASM");
                        parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ Sale / Leader / ASM");
                    </script>';
                exit;
            }
        */

            if ( $old_pic1 != $pic1 ) {

                if ( ! in_array( $staff['group_id'], array(AM_ID) ) ) {
                    echo '<script>
                    parent.palert("ການຜູກ Sale ໃຫ້ໃຊ້ໜ້າ Area Control");
                    parent.alert("ການຜູກ Sale ໃຫ້ໃຊ້ໜ້າ Area Control");
                    </script>';
                    exit;
                }

            } else {

                if ( ! in_array( $staff['group_id'], array(SALES_ID, LEADER_ID, ASM_ID, AM_ID, RM_ID, ASMSTANDBY_ID, RMSTANDBY_ID) ) ) {
                    echo '<script>
                    parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ Sale / Leader / ASM");
                    parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ບໍ່ແມ່ນ Sale / Leader / ASM");
                    </script>';
                    exit;
                }

            }


        }

        foreach ($list_pcdb as $k => $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }

            // check staff phải là pg/pb
            if ($staff['group_id'] != PCDB_ID) {
                echo '<script>
                parent.palert("Staff : ['.($k+1).'] '
                .$staff['firstname'] .' '.$staff['lastname'] .' | '
                . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ແມ່ນ PCDB.");

                parent.alert("Staff : ['.($k+1).'] '
                .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ແມ່ນ PCDB.");
                </script>';
                exit;
            }
            if ($staff['regional_market'] != $regional_market ) {
                $QCasual = new Application_Model_CasualWorker();
                $casuals = $QCasual->get_cache();


                if (isset( $casuals[ $staff['id'] ] ) 
                    && $casuals[ $staff['id'] ]['status'] == 1
                    && $casuals[ $staff['id'] ]['area_id'] == @$region_cache[ $regional_market ]['area_id']) {
                    
                } else {
                    
                    echo '<script>
                    parent.palert("ຂໍ້ມູນ PC : ['.($k+1).'] '
                    .$staff['firstname'] .' '.$staff['lastname'] .' | '
                    . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");

                    parent.alert("ຂໍ້ມູນ PC :  ['.($k+1).'] '
                    .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");
                    </script>';
                    exit;
                }
            }
        }

        // foreach ($list_rd as $k => $staff_id) {
        //     // check id staff có hợp lệ ko
        //     $staff = $QStaff->find($staff_id);
        //     $staff = $staff->current();

        //     if (!$staff) {
        //         echo '<script>
        //                 parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
        //                 parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
        //             </script>';
        //         exit;
        //     }

        //     // check staff phải là rd
        //     if ($staff['group_id'] != RM_ID) {
        //         echo '<script>
        //                 parent.palert("Staff : ['.($k+1).'] '
        //                     .$staff['firstname'] .' '.$staff['lastname'] .' | '
        //                     . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ແມ່ນ RD.");

        //                 parent.alert("Staff : ['.($k+1).'] '
        //                     .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ແມ່ນ RD.");
        //             </script>';
        //         exit;
        //     }
        //       if ($staff['regional_market'] != $regional_market ) {
        //             $QCasual = new Application_Model_CasualWorker();
        //             $casuals = $QCasual->get_cache();


        //             if (isset( $casuals[ $staff['id'] ] ) 
        //                 && $casuals[ $staff['id'] ]['status'] == 1
        //                 && $casuals[ $staff['id'] ]['area_id'] == @$region_cache[ $regional_market ]['area_id']) {
        
        //             } else {
        
        //                 echo '<script>
        //                         parent.palert("ຂໍ້ມູນ PC : ['.($k+1).'] '
        //                             .$staff['firstname'] .' '.$staff['lastname'] .' | '
        //                             . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");

        //                         parent.alert("ຂໍ້ມູນ PC :  ['.($k+1).'] '
        //                             .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");
        //                     </script>';
        //                 exit;
        //             }
        //     }
        // }

        // check position of PC 
        foreach ($list_staff as $k => $staff_id) {
            // check id staff có hợp lệ ko
            $staff = $QStaff->find($staff_id);
            $staff = $staff->current();

            if (!$staff) {
                echo '<script>
                parent.palert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                parent.alert("ບໍ່ເຫັນ Staff ໃນລະບົບ");
                </script>';
                exit;
            }

            // check staff phải là pg/pb
            if ($staff['group_id'] != PGPB_ID) {
                echo '<script>
                parent.palert("Staff : ['.($k+1).'] '
                .$staff['firstname'] .' '.$staff['lastname'] .' | '
                . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ແມ່ນ PC.");

                parent.alert("Staff : ['.($k+1).'] '
                .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ແມ່ນ PC.");
                </script>';
                exit;
            }

            
            // check region
            /*
            // ngoại trừ HN với HCM
            $QRegion = new Application_Model_RegionalMarket();
            $where = $QRegion->getAdapter()->quoteInto('area_id IN (?)', array(HCMC1, HCMC2, HCMC3, HCMC4, HCMC5, HN1, HN2, HN3, HN4));
            $hcm_hn_regions = $QRegion->fetchAll($where);
            $hcm_hn_regions_arr = array();

            foreach ($hcm_hn_regions as $key => $value) {
                $hcm_hn_regions_arr[] = $value['id'];
            }
    */
            if ($staff['regional_market'] != $regional_market ) {
                //if ( ! (in_array($regional_market, $hcm_hn_regions_arr) && in_array($staff['regional_market'], $hcm_hn_regions_arr)) ) {

                    // check ngoại lệ region
                $QCasual = new Application_Model_CasualWorker();
                $casuals = $QCasual->get_cache();


                if (isset( $casuals[ $staff['id'] ] ) 
                    && $casuals[ $staff['id'] ]['status'] == 1
                    && $casuals[ $staff['id'] ]['area_id'] == @$region_cache[ $regional_market ]['area_id']) {
                    
                } else {
                    
                    echo '<script>
                    parent.palert("ຂໍ້ມູນ PC : ['.($k+1).'] '
                    .$staff['firstname'] .' '.$staff['lastname'] .' | '
                    . preg_replace('/'.EMAIL_SUFFIX.'/', '', $staff['email']) .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");

                    parent.alert("ຂໍ້ມູນ PC :  ['.($k+1).'] '
                    .$staff['firstname'] .' '.$staff['lastname'] .' | '. $staff['email'] .' ບໍ່ໄດ້ຢູ່ແຂວງດຽວກັນກັບຮ້ານ.");
                    </script>';
                    exit;
                }
                //}
            }
        }

        $where = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);

        $old = $QStoreStaff->fetchAll($where);
        $old_members = array();

        if ($old->count()) {
            foreach ($old as $item) {
                $old_members[] = $item->staff_id;
            }
        }

        // staff
        $join_staffs = array_diff($list_staff, $old_members);
        $join_staffs = is_array($join_staffs) ? array_filter($join_staffs) : null;

        $release_staffs = array_diff($old_members, $list_staff);
        $release_staffs = is_array($release_staffs) ? array_filter($release_staffs) : null;
        //
        // leader
        $join_leaders = array_diff($list_leader, $old_members);
        $join_leaders = is_array($join_leaders) ? array_filter($join_leaders) : null;

        $release_leaders = array_diff($old_members, $list_leader);
        $release_leaders = is_array($release_leaders) ? array_filter($release_leaders) : null;
        //
        // pcm 
        $join_pcm = array_diff($list_pcm, $old_members);
        $join_pcm = is_array($join_pcm) ? array_filter($join_pcm) : null;

        $release_pcm = array_diff($old_members, $list_pcm);
        $release_pcm = is_array($release_pcm) ? array_filter($release_pcm) : null;

         // mkt
        $join_mkt = array_diff($list_mkt, $old_members);
        $join_mkt = is_array($join_mkt) ? array_filter($join_mkt) : null;

        $release_mkt = array_diff($old_members, $list_mkt);
        $release_mkt = is_array($release_mkt) ? array_filter($release_mkt) : null;

        //pcdb
        $join_pcdb = array_diff($list_pcdb, $old_members);
        $join_pcdb = is_array($join_pcdb) ? array_filter($join_pcdb) : null;

        $release_pcdb = array_diff($old_members, $list_pcdb);
        $release_pcdb = is_array($release_pcdb) ? array_filter($release_pcdb) : null;

        //rd
        $join_rd = array_diff($list_rd, $old_members);
        $join_rd = is_array($join_rd) ? array_filter($join_rd) : null;

        $release_rd = array_diff($old_members, $list_rd);
        $release_rd = is_array($release_rd) ? array_filter($release_rd) : null;


        // asm 
        $join_asm = array_diff($list_asm, $old_members);
        $join_asm = is_array($join_asm) ? array_filter($join_asm) : null;

        $release_asm = array_diff($old_members, $list_asm);
        $release_asm = is_array($release_asm) ? array_filter($release_asm) : null;
        //
        // bm 
        $join_bm = array_diff($list_bm, $old_members);
        $join_bm = is_array($join_bm) ? array_filter($join_bm) : null;

        $release_bm = array_diff($old_members, $list_bm);
        $release_bm = is_array($release_bm) ? array_filter($release_bm) : null;

        $QPcCheckInLog = new Application_Model_PcCheckInLog();

        if ($join_staffs) {
            foreach ($join_staffs as $staff_id) {
                $time = time();

                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 0,
                    'joined_at' => $time,
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);

                // Update Store ID of First Training on the day binding 
                $data_chk = array('store_id' => $id);

                $where = array();
                $where[] = $QPcCheckInLog->getAdapter()->quoteInto('DATE(check_in) = DATE(FROM_UNIXTIME(?))', $time);
                $where[] = $QPcCheckInLog->getAdapter()->quoteInto('staff_id = ?', $staff_id );
                $where[] = $QPcCheckInLog->getAdapter()->quoteInto('store_id = ?', 0);
                $staff_checkin = $QPcCheckInLog->update($data_chk, $where); 

                $where2 = array();
                $where2[] = $QPcCheckInLog->getAdapter()->quoteInto('staff_id = ?', $staff_id );
                $where2[] = $QPcCheckInLog->getAdapter()->quoteInto('store_id <> ?', 0);
                $staff_chk = $QPcCheckInLog->fetchAll($where2); 

                if (count($staff_chk) == 1) {
                    $data_staff = array('joined_at' => new Zend_Db_Expr("DATE(FROM_UNIXTIME(".$time."))") );
                    $where3 = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id );
                    $QStaff->update($data_staff,$where3);
                }


            }
        }

        // PC
        if ($release_staffs) {
            foreach ($release_staffs as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 0);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 0);

                $QStoreStaff->delete($where);
            }
        }

          // Marketting MKT
        if ($release_mkt) {
            foreach ($release_mkt as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 5);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 5);

                $QStoreStaff->delete($where);
            }
        }

        if ($join_mkt) {
            foreach ($join_mkt as $staff_id) {
                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 5,
                    'joined_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);
            }
        }

        // Sale / Leader / ASM 
        if ($release_leaders) {
            foreach ($release_leaders as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);

                $QStoreStaff->delete($where);
            }
        }

        if ($join_leaders) {
            foreach ($join_leaders as $staff_id) {
                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 1,
                    'joined_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);
            }
        }

        // PC Manager
        if ($release_pcm) {
            foreach ($release_pcm as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 2);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 2);

                $QStoreStaff->delete($where);
            }
        }

           //pcdb
            //pcdb
        if ($join_pcdb) {
            foreach ($join_pcdb as $staff_id) {
                $time = time();

                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 7,
                    'joined_at' => $time,
                );
                // ghi log
                $QStoreStaffLog->insert($data);
                unset($data['joined_at']); 
                $QStoreStaff->insert($data);
            }
        }

        if ($release_pcdb) {
            foreach ($release_pcdb as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 7);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 7);

                $QStoreStaff->delete($where);
            }
        }
           //rd
        if ($join_rd) {
            foreach ($join_rd as $staff_id) {
                $time = time();

                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 6,
                    'joined_at' => $time,
                );
                // ghi log
                $QStoreStaffLog->insert($data);
                unset($data['joined_at']); 
                $QStoreStaff->insert($data);
            }
        }

        if ($release_rd) {
            foreach ($release_rd as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 6);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 6);

                $QStoreStaff->delete($where);
            }
        }

        if ($join_pcm) {
            foreach ($join_pcm as $staff_id) {
                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 2,
                    'joined_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);
            }
        }

        // ASM Manager
        if ($release_asm) {
            foreach ($release_asm as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 4);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 4);

                $QStoreStaff->delete($where);
            }
        }

        if ($join_asm) {
            foreach ($join_asm as $staff_id) {
                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 4,
                    'joined_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);
            }
        }

        // BrandShop Manager
        if ($release_bm) {
            foreach ($release_bm as $staff_id) {

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 3);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->update($data, $where);

                $where = array();
                $where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 3);

                $QStoreStaff->delete($where);
            }
        }

        if ($join_bm) {
            foreach ($join_bm as $staff_id) {
                $data = array(
                    'staff_id' => $staff_id,
                    'store_id' => $id,
                    'is_leader' => 3,
                    'joined_at' => time(),
                );

                // ghi log
                $QStoreStaffLog->insert($data);

                unset($data['joined_at']);
                
                $QStoreStaff->insert($data);
            }
        }

        if ($pic4) {
            $info .= ' - ASM('.$pic4.')';
        }

        if ($pic3) {
            $info .= ' - BM('.$pic3.')';
        }

        if ($pic2) {
            $info .= ' - PCM('.$pic2.')';
        }

        if ($pic1) {
            $info .= ' - Sales('.$pic1.')';
        }

        if ($pic) {
            $info .= ' - PC('.$pic.')';
        }

        if ($pic5) {
            $info .=' - MKT('.$pic5.')';
        }

        if ($pic6) {
            $info .=' - RD('.$pic6.')';
        }

        if ($pic7) {
            $info .=' - PCDB('.$pic7.')';
        }

        //todo log
        $QLog->insert( array (
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        // Check Current Area Control
        // $ac_where = $QAreaControl->getAdapter()->quoteInto('sub_district = ?', $sub_district);
        // $ac_result = $QAreaControl->fetchRow($ac_where);

        $sa_where = $QSubArea->getAdapter()->quoteInto('id = ?', $agency);
        $sa_result = $QSubArea->fetchRow($sa_where);

	//print_r($old_pic1); exit();

        // Check Disable Store
        $st_where = $QStore->getAdapter()->quoteInto('id = ?', $id);
        $st_result = $QStore->fetchRow($st_where);

        if (isset($old_pic1) && $old_pic1 == "" && is_null($st_result['del']) ) {

            if ( !empty($sa_result) ) {
                $staff_id = $sa_result['staff_id'];


                if($staff_id){
                    $where = array();
                    $where[] = $QStaff->getAdapter()->quoteInto('id =?',$staff_id);
                    $where[] = $QStaff->getAdapter()->quoteInto('status =?',1);
                    $staff_info = $QStaff->fetchRow($where);

                    if($staff_info != ''){
                         // Insert Store Staff 
                        $data = array(
                            'staff_id' => $staff_id,
                            'store_id' => $id,
                            'is_leader' => 1,
                        );

                        $QStoreStaff->insert($data);

                // Insert Store Staff Log
                        $data = array(
                            'staff_id' => $staff_id,
                            'store_id' => $id,
                            'is_leader' => 1,
                            'joined_at' => time(),
                        );

                        $QStoreStaffLog->insert($data);
                    }
                }

                
            }

        }
        

        //remove cache
        $cache = Zend_Registry::get('cache');
        $cache->remove('store_cache');
        $cache->remove('store_staff_log_cache');
        $cache->remove('store_assigned_cache');

        $flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('Done!');

        $db->commit();

    } catch (Exception $e) {
        $db->rollBack();
        echo "Fail! : ".$e;
        //exit;
    }
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/store') ).'"</script>';