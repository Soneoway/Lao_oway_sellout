<?php
error_reporting(0);
ini_set('display_error', 0);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QTiming = new Application_Model_Timing();

if (!defined("IMEI_ACTIVATION_EXPIRE"))
    define("IMEI_ACTIVATION_EXPIRE", 7);

if ($this->getRequest()->getMethod() == 'POST'){
    set_time_limit(0);
    
	
	
	
    $id               = $this->getRequest()->getParam('id');
    $shift            = $this->getRequest()->getParam('shift');
    $date             = $this->getRequest()->getParam('date');
    $store            = $this->getRequest()->getParam('store');
    $start            = $this->getRequest()->getParam('timepicker_start');
    $end              = $this->getRequest()->getParam('timepicker_end');
    $note             = $this->getRequest()->getParam('note');
    $sumsung          = $this->getRequest()->getParam('sumsung');
    $vivo             = $this->getRequest()->getParam('vivo');
    $other            = $this->getRequest()->getParam('other');
    $products         = $this->getRequest()->getParam('product');
    $models           = $this->getRequest()->getParam('model');
    $imeis            = $this->getRequest()->getParam('imei');
    $customer_names   = $this->getRequest()->getParam('customer_name');
    $phone_numbers    = $this->getRequest()->getParam('phone_number');
    $emails           = $this->getRequest()->getParam('email');
    $addresses        = $this->getRequest()->getParam('address');
    $customer_ids     = $this->getRequest()->getParam('customer_ids');
    $timing_sales_ids = $this->getRequest()->getParam('timing_sales_ids');
    $photos           = $this->getRequest()->getParam('photo');
	
	

	



    $temp  = explode('/', $date);
    $temp2 = explode(':', $start);
    $temp3 = explode(':', $end);

    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    if (!$store){
        echo '<script>
                parent.alert("กรุณาเลือกร้านด้วยค่ะ.");
                parent.palert("กรุณาเลือกร้านด้วยค่ะ.");
            </script>';
        exit;
    }

    $QStore         = new Application_Model_Store();
    $store_cache    = $QStore->get_cache();

    if (!isset($store_cache[ $store ])) {
        echo '<script>
                parent.alert("Invalid Stores.");
                parent.palert("Invalid Stores.");
            </script>';
        exit;
    }

    // nếu ở TGDD thì set các IMEI là null -> như vậy thì vẫn chấm công được nhưng ếu có IMEI nào
    if (defined("PREVENT_TIMING_AT_TGDD") && PREVENT_TIMING_AT_TGDD && preg_match('/^TGDĐ[\s]?-/', $store_cache[ $store ]))
        $imeis = $models = $products = $customer_names = $phone_numbers = $emails = $customer_ids = $timing_sales_ids = $photos = null;

    if (    ! ( isset($temp[2]) and intval($temp[2]) >= 2013 and intval($temp[2]) <= 2020
        and isset($temp[1]) and intval($temp[1]) <= 12 and intval($temp[1]) >= 1
        and isset($temp[0]) and intval($temp[0]) <= 31 and intval($temp[0]) >= 1
        and isset($temp2[0]) and intval($temp2[0]) <= 23 and intval($temp2[0]) >= 0
        and isset($temp2[1]) and intval($temp2[1]) <= 59 and intval($temp2[1]) >= 0
        and isset($temp3[0]) and intval($temp3[0]) <= 23 and intval($temp3[0]) >= 0
        and isset($temp3[1]) and intval($temp3[1]) <= 59 and intval($temp3[1]) >= 0
    )
    ){
        echo '<script>
                parent.alert("กรุณาเลือกวันที่.");
                parent.palert("กรุณาเลือกวันที่.");
            </script>';
        exit;
    }

    //prevent report soon
    if ( strtotime($temp[2].'-'.$temp[1].'-'.$temp[0]) > strtotime( date('Y-m-d') ) ){
        echo '<script>
                parent.palert("คุณไม่สามารถทำรายการได้ โปรดตรวจสอบวัน.");
                parent.alert("คุณไม่สามารถทำรายการได้ โปรดตรวจสอบวัน.");
            </script>';
        exit;
    }

    // prevent report after 2 days
    if ( strtotime( date('Y-m-d') ) - strtotime($temp[2].'-'.$temp[1].'-'.$temp[0]) > TIME_LIMIT_TIMING ) {
        $day_limit = ceil( TIME_LIMIT_TIMING/(24*60*60) );

        echo '<script>
                parent.palert("คุณสามารถรายงานยอดวันนี้และไม่เกิน  '.$day_limit.' วัน.");
                parent.alert("คุณสามารถรายงานยอดวันนี้และไม่เกิน '.$day_limit.' วัน.");
            </script>';
        exit;
    }

    // if($imeis){
    //     $QTiming = new Application_Model_Timing();
    //     foreach ($imeis as $key => $imei) {
    //         $checkImei = $QTiming->checkImeiDealer($imei,$store);
    //         if($checkImei == false){
    //             echo '<script>
    //                 parent.palert("IMEI '.$imei.' ไม่ได้อยู่ในช่องทางร้าน กรุณาติดต่อ Admin.");
    //                 parent.alert("IMEI '.$imei.' ไม่ได้อยู่ในช่องทางร้าน กรุณาติดต่อ Admin.");
    //             </script>';
    //             exit;
    //         }
    //     }
    // }

// ປິດໄວ້ກ່ອນ
    // $formatedDate = (isset($temp[2]) ? $temp[2] : '1970').'-'.(isset($temp[1]) ? $temp[1] : '01').'-'.(isset($temp[0]) ? $temp[0] : '01');

    // // Kiểm tra xem staff này vào ngày chấm công có thuộc store tương ứng chưa.
    // // Nếu chưa thì chặn không cho chấm công
    // $QStoreStaffLog = new Application_Model_StoreStaffLog();

    // if ( ! $QStoreStaffLog->belong_to($userStorage->id, $store, $formatedDate) ) {
    //     echo '<script>
    //             parent.palert("เมื่อวันที่ '.$formatedDate.', ไม่ได้เป็นร้าน '
    //                 .( isset($store_cache[$store]) && $store_cache[$store] ? ('['.$store_cache[$store].']') : ' này')
    //                 .'. สำหรับการจับเวลาคุณต้องให้  PC หรือขายที่ร้านนี้ กรุณาติดต่อเราเพื่อขอความช่วยเหลือธุรการขาย.");
    //             parent.alert("เมื่อวันที่ '.$formatedDate.', ไม่ได้เป็นร้าน '
    //                 .( isset($store_cache[$store]) && $store_cache[$store] ? ('['.$store_cache[$store].']') : ' này')
    //                 .'. สำหรับการจับเวลาคุณต้องให้  PC หรือขายที่ร้านนี้ กรุณาติดต่อเราเพื่อขอความช่วยเหลือธุรการขาย.");
    //         </script>';
    //     exit;
    // }
    //

    //kiểm tra approved hay chưa khi edit
    if ($id) {

        $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);


        $_timing = $QTiming->fetchRow($where);

        if (!$_timing){
            echo '<script>
                    parent.palert("ไม่พบรายงานที่คุณต้องการปรับเปลี่ยน!");
                </script>';
            exit;
        }

        if ($_timing['approved_at']){
            echo '<script>
                    parent.palert("รายงานฉบับนี้ได้รับการยืนยันแล้ว, คุณไม่สามารถแก้ไข!");
                </script>';
            exit;
        }

        // Kiểm tra xem nó phải sales quản lý hay không,
        //      trường hợp edit mà người edit khác với người chấm công
        if ( $userStorage->id != $_timing['staff_id'] 
                && !empty($_timing['store']) && !empty($_timing['from'])
                && ! $QStoreStaffLog->belong_to($userStorage->id, $_timing['store'], $_timing['from'], true) ) {
            echo '<script>
                    parent.palert("เมื่อวันที่ '.date('Y-m-d', strtotime($_timing['from']))
                        .' คุณยังไม่ขายของร้านค้า '
                        .( isset($store_cache[$_timing['store']]) && $store_cache[$_timing['store']] ? ('['.$store_cache[$_timing['store']].']') : ' này')
                        .' คุณไม่สามารถแก้ไขได้.");
                </script>';
            exit;
        }
    }



    //check reported or not yet
    // check ngoại lệ region
    $QCasual = new Application_Model_CasualWorker();
    $casuals = $QCasual->get_cache();

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $where = array();
    $where[] = $QTiming->getAdapter()->quoteInto('shift = ?', $shift);
    $where[] = $QTiming->getAdapter()->quoteInto('date(`from`) = ?', $formatedDate);
    $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);

    if ($id)
        $where[] = $QTiming->getAdapter()->quoteInto('id <> ?', $id);

    if ( $userStorage->group_id == SALES_ID ||  $userStorage->group_id == LEADER_ID || 
            ( isset( $casuals[ $userStorage->id ] ) 
                && $casuals[ $userStorage->id ]['status'] == 1 ) 
            )
        $where[] = $QTiming->getAdapter()->quoteInto('store = ?', $store);

    $checked_existed = $QTiming->fetchRow($where);

	/*
     if ($checked_existed){
        echo '<script>
                    parent.palert("!");
                </script>';
        exit;
    } 
	*/
    
    $formatedFrom = $start.':00';
    $formatedTo = $end.':00';

    $data = array(
        'shift' => $shift,
        'from'  => $formatedDate . ' ' . $formatedFrom,
        'to'    => $formatedDate . ' ' . $formatedTo,
        'note'  => trim($note),
        'sumsung'  => trim($sumsung),
        'vivo'  => trim($vivo),
        'other'  => trim($other),
        'store' => $store,
    );

    if ($imeis) {
        $count_vl = array_count_values($imeis);
        foreach ($count_vl as $key => $value) {
            if ($value > 1) {
                echo '<script>
                            parent.palert("IMEI: '.$key.' ถูกป้อน 2 ครั้ง.");
                            parent.alert("IMEI: '.$key.'  ถูกป้อน 2 ครั้ง.");
                        </script>';
                exit;
            }
        }
    }

    if (isset($imeis) && $imeis)
        foreach ($imeis as $k=>$imei)
        	$imeis[$k] = trim($imei);

    //validate IMEI
    if (isset($imeis) && $imeis) {
        foreach ($imeis as $k=>$imei){
            $info = array();

            $return = $this->checkImei( $imei, (isset($timing_sales_ids[$k]) ? $timing_sales_ids[$k] : null), $info );

            if ($return==1){
                echo '<script>
                            parent.palert("คุณได้รายงาน IMEI: '.$imei.'     วันที่ ['. date('d/m/Y H:i:s', strtotime($info['date'])).'] ในร้าน ['.$info['store'].']");
                            parent.alert("คุณได้รายงาน IMEI: '.$imei.'  วันที่  ['. date('d/m/Y H:i:s', strtotime($info['date'])).'] ในร้าน ['.$info['store'].']");
                        </script>';
                exit;
            } elseif ($return==2){
                echo '<script>
                            parent.palert("IMEI '.$imei.' ไม่ได้อยู่ในระบบ โปรดตรวจสอบ IMEI หรือติดต่อ admin.");
                            parent.alert("IMEI '.$imei.' ไม่ได้อยู่ในระบบ โปรดตรวจสอบ IMEI หรือติดต่อ admin.");
                        </script>';
                exit;
            } elseif ($return==3||$return==5){
                echo '<script>
	                            parent.palert("IMEI '
                    .$imei.' นี่คือ ['.$info['staff'].'] รายงานในการจัดเก็บ ['
                    .$info['store'].'] ที่ ['
                    .date('d/m/Y H:i:s', strtotime($info['date']))
                    .'] <a href=\"#\" data-staff-first=\"'
                    .$info['staff_id'].'\" data-timing-sales-id=\"'
                    .$info['timing_sales_id'].'\" data-imei=\"'
                    .$imei.'\" data-case=\"IMEI '
                    .$imei.' นี่คือ ['.$info['staff'].'] รายงานในการจัดเก็บ ['
                    .$info['store'].'] ที่ ['
                    .date('d/m/Y H:i:s', strtotime($info['date'])).']\" data-checksum=\"'.sha1(md5($imei).$info['timing_sales_id']).'\" class=\"send_notify\">คลิกที่นี่</a>เพื่อขอการประมวลผล.");
	                            parent.alert("IMEI '.$imei.' เป็น ['.$info['staff'].'] รายงานในการจัดเก็บ ['.$info['store'].'] ที่ ['.date('d/m/Y H:i:s', strtotime($info['date'])).'] เลื่อนขึ้นไปด้านบนเพื่อเลือกฟังก์ชั่นที่จะส่งรายงานการเกิดขึ้น IMEI.");
	                        </script>';
                exit;
            } elseif ($return == 4) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' ได้เปิดใช้งานมากขึ้น '.IMEI_ACTIVATION_EXPIRE.' วัน บริษัท ฯ จะไม่นับรวมกับยอดขายคอมพิวเตอร์ได้เรียกเพิ่มเติม '.IMEI_ACTIVATION_EXPIRE.' วัน กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                            parent.alert("IMEI '.$imei.' ได้เปิดใช้งานมากขึ้น '.IMEI_ACTIVATION_EXPIRE.' วัน บริษัท ฯ จะไม่นับรวมกับยอดขายคอมพิวเตอร์ได้เรียกเพิ่มเติม '.IMEI_ACTIVATION_EXPIRE.' วัน กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                        </script>';
                exit;
            } elseif ($return == 6) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' ของขวัญของลูกค้าที่ยังไม่ได้คำนวณ KPI กรุณาติดต่อ ASM / ขายถ้าคุณมีคำถาม.");
                            parent.alert("IMEI '.$imei.' ของขวัญของลูกค้าที่ยังไม่ได้คำนวณ KPI กรุณาติดต่อ ASM / ขายถ้าคุณมีคำถาม.");
                        </script>';
                exit;
            }  elseif ($return == 7) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' ที่ยังไม่ได้คำนวณ KPI กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                            parent.alert("IMEI '.$imei.' ที่ยังไม่ได้คำนวณ KPI กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                        </script>';
                exit;
            } elseif ($return==5) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' ถูกเปิดใช้งาน '.IMEI_ACTIVATION_EXPIRE.' วัน โดย [' . $info['staff'].']  ที่ร้าน ['. $info['store'].'] ที่ร้าน['.date('d/m/Y H:i:s', strtotime($info['date'])).']. บริษัท ฯ จะไม่นับรวมกับยอดขาย '.IMEI_ACTIVATION_EXPIRE.' วัน กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                            parent.alert("IMEI '.$imei.' ถูกเปิดใช้งาน '.IMEI_ACTIVATION_EXPIRE.' วัน โดย [' . $info['staff'].'] ที่ร้าน ['. $info['store'].'] วันที่ ['.date('d/m/Y H:i:s', strtotime($info['date'])).']. บริษัท ฯ จะไม่นับรวมกับยอดขาย '.IMEI_ACTIVATION_EXPIRE.' วัน กรุณาติดต่อ ASM / ธุรการขายถ้าคุณมีคำถาม.");
                        </script>';
                exit;
            }
        }
    }

    // Kiểm tra product_id và model có hợp lệ hay không
    // Kiểm tra IMEI với product/model có khớp/thiếu hay không
    if (is_array($products) and is_array($models)) {
        $QWebImei = new Application_Model_WebImei();

        $QGood = new Application_Model_Good();
        $products_list = $QGood->get_cache();

        $QGoodColor = new Application_Model_GoodColor();
        $color_list = $QGoodColor->get_cache();

        foreach ($products as $k=>$product) {

            // Trường hợp product_id không hợp lệ
            if ( !is_numeric($product) || $product < 1 ) {
                echo '<script>
                            parent.palert("สินค้าเลขที่ '.($k+1).' โมฆะ กรุณาเลือกผลิตภัณฑ์จากรายการ.");
                            parent.alert("สินค้าเลขที่ '.($k+1).' โมฆะ กรุณาเลือกผลิตภัณฑ์จากรายการ.");
                    	</script>';
                exit;
            }

            // kiểm tra có product nào thiếu IMEI ko
            if  ( empty( $imeis[$k] ) ) {
                echo '<script>
                            parent.palert("สินค้าเลขที่ '.($k+1).' ไม่มี IMEI. กรุณากรอกใหม่.");
                            parent.alert("สินค้าเลขที่ '.($k+1).' ไม่มี IMEI. กรุณากรอกใหม่.");
                    	</script>';
                exit;
            }

            // Kiểm tra có product/model nào không khớp IMEI ko
            $where = $QWebImei->getAdapter()->quoteInto('imei_sn LIKE ?', $imeis[$k]);
            $imei = $QWebImei->fetchRow($where);
            // Product ko hợp lệ
            if ( $imei[ 'good_id' ] != $product) {
                echo '<script>
                            parent.palert("สินค้ารายการที่ '.($k+1).', IMEI ['.$imeis[$k].'] ไม่ได้เป็น ['.$products_list[$product].']. กรุณาตรวจสอบด้วยค่ะ.");
                            parent.alert("สินค้ารายการที่ '.($k+1).', IMEI ['.$imeis[$k].'] ไม่ได้เป็น ['.$products_list[$product].']. กรุณาตรวจสอบด้วยค่ะ.");
                        </script>';
                exit;
            }

            // kiểm tra model
            if ( !is_numeric($models[$k]) || $models[$k] < 1 ) {
                echo '<script>
                            parent.palert("สินค้ารายการที่ '.($k+1).'ไม่ถูกต้อง กรุณาตรวจสอบด้วยค่ะ.");
                            parent.alert("สินค้ารายการที่ '.($k+1).' ไม่ถูกต้อง กรุณาตรวจสอบด้วยค่ะ.");
                        </script>';
                exit;
            }

            // Kiểm tra màu
            if ( $imei[ 'good_color' ] != $models[$k]) {
                echo '<script>
                            parent.palert("สินค้ารายการที่ '.($k+1).', IMEI ['.$imeis[$k].'] ไม่ได้เป็น ['.$color_list[$models[$k]].']. กรุณาตรวจสอบด้วยค่ะ.");
                            parent.alert("สินค้ารายการที่ '.($k+1).', IMEI ['.$imeis[$k].'] ไม่ได้เป็น ['.$color_list[$models[$k]].']. กรุณาตรวจสอบด้วยค่ะ.");
                        </script>';
                exit;
            }
        }
    }

    $QLog = new Application_Model_Log();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');

    $ts = "";
    if (isset($timing_sales_ids)) {
        foreach ($timing_sales_ids as $k => $v) {
            $ts .= $v . ",";
        }

        $ts = trim($ts, ",");
    }

    $diff_ids = array();

    //insert sales_id
    if (!$id){ //nếu insert thì thêm sales_id
        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $sales_leader = $QStoreStaffLog->get_sales_man($store, $formatedDate);

        if ($sales_leader)
            $data['sales_id'] = $sales_leader;

        $QLeaderLog = new Application_Model_StoreLeaderLog();
        $leader_id = $QLeaderLog->get_leader($store, $formatedDate);

        if ($leader_id)
            $data['leader_id'] = $leader_id;
    }

    $QTimingSale = new Application_Model_TimingSale();

    if ($id){

        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $userStorage->id;

        $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);
        $QTiming->update($data, $where);

        //check old timing sale
        $where = $QTimingSale->getAdapter()->quoteInto('timing_id = ?', $id);
        $old_timing = $QTimingSale->fetchAll($where);


        foreach ($old_timing as $item){

            if ( !in_array($item->id, $timing_sales_ids) or !$timing_sales_ids ){

                $diff_ids[] = $item->id;

            }
        }

        $info = "TIMING - Update (".$id.") - Timing sale (".$ts.") - Info (".serialize($data).")" ;
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userStorage->id;
        $data['staff_id'] = $userStorage->id;
        $id = $QTiming->insert($data);

        $info = "TIMING - Add (".$id.") - Timing sale (".$ts.") - Info (".serialize($data).")";
    }

    $info .= " - IMEIs (".serialize($imeis).")";

    //todo log
    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );



    //insert timing sale
    if (is_array($products) and is_array($models)){



        foreach ($products as $k=>$product){
            if ($product and isset($models[$k]) and $models[$k]) {
                // get price
                $price = My_Good::getPrice($product, $models[$k], $formatedDate);

                //insert customer
                $QCustomer = new Application_Model_Customer();
                if (isset($customer_ids[$k]) and $customer_ids[$k]) {
                    $customer_id = $customer_ids[$k];
                    $where = $QCustomer->getAdapter()->quoteInto('id = ?', $customer_id);

                    $QCustomer->update(array(
                        'name' => ((isset($customer_names[$k]) and $customer_names[$k]) ? $customer_names[$k] : null),
                        'phone_number' => ((isset($phone_numbers[$k]) and $phone_numbers[$k]) ? $phone_numbers[$k] : null),
                        'email' => ((isset($emails[$k]) and $emails[$k]) ? $emails[$k] : null),
                        'address' => ((isset($addresses[$k]) and $addresses[$k]) ? $addresses[$k] : null),
                        'updated_at' => date('Y-m-d H:i:s')
                    ), $where);
                } else
                    if ( isset($customer_names[$k]) and $customer_names[$k] )
                        $customer_id = $QCustomer->insert(array(
                            'name' => ((isset($customer_names[$k]) and $customer_names[$k]) ? $customer_names[$k] : null),
                            'phone_number' => ((isset($phone_numbers[$k]) and $phone_numbers[$k]) ? $phone_numbers[$k] : null),
                            'email' => ((isset($emails[$k]) and $emails[$k]) ? $emails[$k] : null),
                            'address' => ((isset($addresses[$k]) and $addresses[$k]) ? $addresses[$k] : null),
                            'created_at' => date('Y-m-d H:i:s')
                        ));

                try {
                    //insert timing sale
                    if (isset($timing_sales_ids[$k]) and $timing_sales_ids[$k]) {
                        $timing_sales_id = $timing_sales_ids[$k];
                        $where = $QTimingSale->getAdapter()->quoteInto('id = ?', $timing_sales_id);

                        $QTimingSale->update(array(
                            'model_id' => $models[$k],
                            'product_id' => $product,
                            'timing_id' => $id,
                            'customer_id' => (isset($customer_id) ? $customer_id : null),
                            'imei' => (isset($imeis[$k]) ? $imeis[$k] : null),
                            'price' => $price,
                        ), $where);
                    } else
                        if ( isset($models[$k]) and $models[$k] )
                            $timing_sales_id = $QTimingSale->insert(array(
                                'model_id' => $models[$k],
                                'product_id' => $product,
                                'timing_id' => $id,
                                'customer_id' => (isset($customer_id) ? $customer_id : null),
                                'imei' => (isset($imeis[$k]) ? $imeis[$k] : null),
                                'price' => $price,
                            ));
                    
                } catch (Exception $e) {
                    $db->rollback();

                    if ($e->getCode() == 23000) {
                        echo '<script>
                                    parent.palert("สินค้าเลขที่ '.($k+1).', IMEI ['.$imeis[$k].'] ซ้ำ โปรดกลับมาตรวจสอบ.");
                                    parent.alert("สินค้าเลขที่ '.($k+1).', IMEI ['.$imeis[$k].'] ซ้ำ โปรดกลับมาตรวจสอบ.");
                                </script>';

                        // $writer = new Zend_Log_Writer_Stream(ini_get('error_log').DIRECTORY_SEPARATOR.'timing.log');
                        // $logger = new Zend_Log($writer);
                         
                        // $logger->info('23000 - Timing - IMEI '.$imeis[$k].' duplicated.');
                        exit;
                    } else {
                        echo '<script>
                                    parent.palert("'.$e->getMessage().'");
                                    parent.alert("'.$e->getMessage().'");
                                </script>';
                        exit;
                    }
                }



                if (isset($photos[$k]) and $photos[$k]
                    and $timing_sales_id
                ) {



                    try {
                        $month = substr($formatedDate, 0, -3);

                        //move from temp to folder
                        $located_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR.$month.DIRECTORY_SEPARATOR.$formatedDate.DIRECTORY_SEPARATOR.$timing_sales_id.DIRECTORY_SEPARATOR;
                        if (!is_dir($located_dir))
                            @mkdir($located_dir, 0777, true);

                        $tem = explode('_located_', $photos[$k]);

                        if (isset($tem[1]) and $tem[1]) {
                            $uniqid = $tem[0];
                            $file_name = $tem[1];

                            $uploaded_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR;
                            $uploaded_dir .= 'temp' . DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR . $file_name;


                            if (is_file($uploaded_dir)){

                                $info = pathinfo($uploaded_dir);

                                $new_file_name = md5($timing_sales_id.FILENAME_SALT).'.'.$info['extension'];

                                copy($uploaded_dir, $located_dir . $new_file_name);

                            }

                            //remove other file
                            $files = glob($located_dir.'*'); // get all file names
                            foreach($files as $file){ // iterate files
                                if(is_file($file) and !strstr($file, $new_file_name))
                                    unlink($file); // delete file
                            }

                            $data      = array('photo' => $new_file_name);

                            $where = $QTimingSale->getAdapter()->quoteInto('id = ?', $timing_sales_id);

                            $QTimingSale->update($data, $where);
                        }

                    } catch (Exception $e) {
                        echo '<script>
                                    parent.palert("Please input valid file.");
                                </script>';
                        exit;
                    }
                }
            }
        }

        //unlink temp folder
        try {
            $dirPath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR.'temp' . DIRECTORY_SEPARATOR . $userStorage->id . DIRECTORY_SEPARATOR;
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
            @rmdir($dirPath);
        } catch (Exception $e){}
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');

}

if ($diff_ids){
    $old_date = explode(' ', $old_timing_2['from']);
    $old_date = $old_date[0];
    $old_month = substr($old_date, 0, -3);

    //delete unused timing sales
    $where = $QTimingSale->getAdapter()->quoteInto('id IN (?)', $diff_ids);
    $QTimingSale->delete($where);

    //delete photo folder
    foreach ( $diff_ids as $un_timing_sales_id ){
        //unlink folder
        try {
            $dirPath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR. $old_month .DIRECTORY_SEPARATOR. $old_date .DIRECTORY_SEPARATOR. $un_timing_sales_id . DIRECTORY_SEPARATOR ;
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
            rmdir($dirPath);
        } catch (Exception $e){}
    }
}

$db->commit();

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'.( $back_url ? $back_url : '/timing' ).'"</script>';
exit;