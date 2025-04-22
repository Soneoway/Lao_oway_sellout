<?php
error_reporting(0);
ini_set('display_error', 0);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QTiming = new Application_Model_Timing();


if ($this->getRequest()->getMethod() == 'POST'){
    set_time_limit(0);
    
    $id               = $this->getRequest()->getParam('id');
    $shift            = $this->getRequest()->getParam('shift');
    $date             = $this->getRequest()->getParam('date');
    $store            = $this->getRequest()->getParam('store');
    $start            = $this->getRequest()->getParam('timepicker_start');
    $end              = $this->getRequest()->getParam('timepicker_end');
    $note             = $this->getRequest()->getParam('note');
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
                parent.alert("Vui lòng nhập cửa hàng.");
                parent.palert("Vui lòng nhập cửa hàng.");
            </script>';
        exit;
    }

    $QStore         = new Application_Model_Store();
    $store_cache    = $QStore->get_cache();

    if (!isset($store_cache[ $store ])) {
        echo '<script>
                parent.alert("Cửa hàng không hợp lệ.");
                parent.palert("Cửa hàng không hợp lệ.");
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
                parent.alert("Vui lòng chọn đúng định dạng ngày báo cáo.");
                parent.palert("Vui lòng chọn đúng định dạng ngày báo cáo.");
            </script>';
        exit;
    }



    if($imeis){
        $QTiming = new Application_Model_Timing();
        foreach ($imeis as $key => $imei) {
            $checkImei = $QTiming->checkImeiDealer($imei,$store);
            if($checkImei == false){
                echo '<script>
                    parent.palert("IMEI '.$imei.' không thuộc cửa hàng này. Có thể đây là máy mượn/chuyển giữa các cửa hàng. Công ty không tính doanh số với các máy được báo cáo sai cửa hàng. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
                    parent.alert("IMEI '.$imei.' không thuộc cửa hàng này. Có thể đây là máy mượn/chuyển giữa các cửa hàng. Công ty không tính doanh số với các máy được báo cáo sai cửa hàng. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
                </script>';
                exit;
            }
        }
    }

    $formatedDate = (isset($temp[2]) ? $temp[2] : '1970').'-'.(isset($temp[1]) ? $temp[1] : '01').'-'.(isset($temp[0]) ? $temp[0] : '01');

    // Kiểm tra xem staff này vào ngày chấm công có thuộc store tương ứng chưa.
    // Nếu chưa thì chặn không cho chấm công
    $QStoreStaffLog = new Application_Model_StoreStaffLog();

    if ( ! $QStoreStaffLog->belong_to($userStorage->id, $store, $formatedDate) ) {
        echo '<script>
                parent.palert("Vào ngày '.$formatedDate.', Bạn không thuộc Store '
                    .( isset($store_cache[$store]) && $store_cache[$store] ? ('['.$store_cache[$store].']') : ' này')
                    .'. Để chấm công, bạn phải là PG/PB hoặc Sales ở Store này. Vui lòng liên hệ Sales Admin để được hỗ trợ.");
                parent.alert("Vào ngày '.$formatedDate.', Bạn không thuộc Store '
                    .( isset($store_cache[$store]) && $store_cache[$store] ? ('['.$store_cache[$store].']') : ' này')
                    .'. Để chấm công, bạn phải là PG/PB hoặc Sales ở Store này. Vui lòng liên hệ Sales Admin để được hỗ trợ.");
            </script>';
        exit;
    }
    //

    //kiểm tra approved hay chưa khi edit
    if ($id) {

        $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);


        $_timing = $QTiming->fetchRow($where);

        if (!$_timing){
            echo '<script>
                    parent.palert("Không tìm thấy báo cáo bạn muốn sửa!");
                </script>';
            exit;
        }

        if ($_timing['approved_at']){
            echo '<script>
                    parent.palert("Báo cáo này đã được xác nhận, bạn không thể sửa!");
                </script>';
            exit;
        }

        // Kiểm tra xem nó phải sales quản lý hay không,
        //      trường hợp edit mà người edit khác với người chấm công
        if ( $userStorage->id != $_timing['staff_id'] 
                && !empty($_timing['store']) && !empty($_timing['from'])
                && ! $QStoreStaffLog->belong_to($userStorage->id, $_timing['store'], $_timing['from'], true) ) {
            echo '<script>
                    parent.palert("Vào ngày '.date('Y-m-d', strtotime($_timing['from']))
                        .' bạn không phải là Sales của Store '
                        .( isset($store_cache[$_timing['store']]) && $store_cache[$_timing['store']] ? ('['.$store_cache[$_timing['store']].']') : ' này')
                        .' Bạn không có quyền chỉnh sửa chấm công này.");
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

    if ($checked_existed){
        echo '<script>
                    parent.palert("Bạn đã báo cáo cho ca làm việc này rồi. Bạn có thể chỉnh sửa báo cáo cũ, đừng tạo mới!");
                </script>';
        exit;
    }
    
    $formatedFrom = $start.':00';
    $formatedTo = $end.':00';

    $data = array(
        'shift' => $shift,
        'from'  => $formatedDate . ' ' . $formatedFrom,
        'to'    => $formatedDate . ' ' . $formatedTo,
        'note'  => trim($note),
        'store' => $store,
    );

    if (intval($temp[1]) != 4 ) {


        echo '<script>
                parent.palert("Bạn chỉ có thể báo cáo cho tháng 4");
                parent.alert("Bạn chỉ có thể báo cáo cho tháng 4 ");
            </script>';
        exit;
    }

    if ($imeis) {
        $count_vl = array_count_values($imeis);
        foreach ($count_vl as $key => $value) {
            if ($value > 1) {
                echo '<script>
                            parent.palert("IMEI: '.$key.' bị nhập 2 lần trong cùng một ca.");
                            parent.alert("IMEI: '.$key.' bị nhập 2 lần trong cùng một ca.");
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

            $QWebImei = new Application_Model_WebImei();
            $where = array();
            $where[] = $QWebImei->getAdapter()->quoteInto('into_date > ?', 0);
            $where[] = $QWebImei->getAdapter()->quoteInto('imei_sn = ?', $imei);

            $result = $QWebImei->fetchRow($where);

            $info['activated_at'] = $result['activated_date'];
            $info['out_date'] = $result['out_date'];
            //

            if(empty($info['activated_at']))
            {
                echo '<script>
                            parent.palert("Imei chưa activated!");
                            parent.alert("Imei chưa activated");
                        </script>';
                exit;
            }

            if($info['activated_at'])
            {
                $month = date('m', strtotime($info['activated_at']));
                if(intval($month) != 4)
                {
                    echo '<script>
                            parent.palert("Imei này có ngày activated không phải của tháng 4");
                            parent.alert("Imei này có ngày activated không phải của tháng 4");
                        </script>';
                    exit;
                }
            }

            if ($return==1){
                echo '<script>
                            parent.palert("Bạn đã báo cáo IMEI: '.$imei.' rồi, vào lúc ['. date('d/m/Y H:i:s', strtotime($info['date'])).'] Tại cửa hàng ['.$info['store'].']");
                            parent.alert("Bạn đã báo cáo IMEI: '.$imei.' rồi, vào lúc ['. date('d/m/Y H:i:s', strtotime($info['date'])).'] Tại cửa hàng ['.$info['store'].']");
                        </script>';
                exit;
            } elseif ($return==2){
                echo '<script>
                            parent.palert("IMEI '.$imei.' không tồn tại. Vui lòng xem kỹ lại IMEI hoặc liên hệ bộ phận Kỹ thuật.");
                            parent.alert("IMEI '.$imei.' không tồn tại. Vui lòng xem kỹ lại IMEI hoặc liên hệ bộ phận Kỹ thuật.");
                        </script>';
                exit;
            } elseif ($return==3||$return==5){
                echo '<script>
	                            parent.palert("IMEI '
                    .$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['
                    .$info['store'].'] vào lúc ['
                    .date('d/m/Y H:i:s', strtotime($info['date']))
                    .'] <a href=\"#\" data-staff-first=\"'
                    .$info['staff_id'].'\" data-timing-sales-id=\"'
                    .$info['timing_sales_id'].'\" data-imei=\"'
                    .$imei.'\" data-case=\"IMEI '
                    .$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['
                    .$info['store'].'] vào lúc ['
                    .date('d/m/Y H:i:s', strtotime($info['date'])).']\" data-checksum=\"'.sha1(md5($imei).$info['timing_sales_id']).'\" class=\"send_notify\">Bấm vào đây</a> để gửi yêu cầu xử lý.");
	                            parent.alert("IMEI '.$imei.' đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).'] Cuộn lên đầu trang để chọn chức năng gửi báo cáo trùng IMEI.");
	                        </script>';
                exit;
            } elseif ($return == 6) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' của máy tặng khách hàng, thuộc diện không tính KPI. Vui lòng liên hệ ASM/Sales nếu có thắc mắc.");
                            parent.alert("IMEI '.$imei.' của máy tặng khách hàng, thuộc diện không tính KPI. Vui lòng liên hệ ASM/Sales nếu có thắc mắc.");
                        </script>';
                exit;
            }  elseif ($return == 7) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' thuộc diện không tính KPI. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
                            parent.alert("IMEI '.$imei.' thuộc diện không tính KPI. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
                        </script>';
                exit;
            } elseif ($return==5) {
                echo '<script>
                            parent.palert("IMEI '.$imei.' đã kích hoạt hơn '.IMEI_ACTIVATION_EXPIRE.' ngày. Bởi [' . $info['staff'].'] ở cửa hàng ['. $info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']. Công ty không tính doanh số đối với các máy đã kích hoạt hơn '.IMEI_ACTIVATION_EXPIRE.' ngày. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
                            parent.alert("IMEI '.$imei.' đã kích hoạt hơn '.IMEI_ACTIVATION_EXPIRE.' ngày. Bởi [' . $info['staff'].'] ở cửa hàng ['. $info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']. Công ty không tính doanh số đối với các máy đã kích hoạt hơn '.IMEI_ACTIVATION_EXPIRE.' ngày. Vui lòng liên hệ ASM/Sales Admin nếu có thắc mắc.");
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
                            parent.palert("Sản phẩm thứ '.($k+1).' không hợp lệ. Vui lòng chọn lại sản phẩm từ danh sách.");
                            parent.alert("Sản phẩm thứ '.($k+1).' không hợp lệ. Vui lòng chọn lại sản phẩm từ danh sách.");
                    	</script>';
                exit;
            }

            // kiểm tra có product nào thiếu IMEI ko
            if  ( empty( $imeis[$k] ) ) {
                echo '<script>
                            parent.palert("Sản phẩm thứ '.($k+1).' thiếu IMEI. Vui lòng bổ sung.");
                            parent.alert("Sản phẩm thứ '.($k+1).' thiếu IMEI. Vui lòng bổ sung.");
                    	</script>';
                exit;
            }

            // Kiểm tra có product/model nào không khớp IMEI ko
            $where = $QWebImei->getAdapter()->quoteInto('imei_sn LIKE ?', $imeis[$k]);
            $imei = $QWebImei->fetchRow($where);
            // Product ko hợp lệ
            if ( $imei[ 'good_id' ] != $product) {
                echo '<script>
                            parent.palert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] không phải của máy ['.$products_list[$product].']. Vui lòng chọn lại sản phẩm từ danh sách.");
                            parent.alert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] không phải của máy ['.$products_list[$product].']. Vui lòng chọn lại sản phẩm từ danh sách.");
                        </script>';
                exit;
            }

            // kiểm tra model
            if ( !is_numeric($models[$k]) || $models[$k] < 1 ) {
                echo '<script>
                            parent.palert("Sản phẩm thứ '.($k+1).' màu sắc không hợp lệ. Vui lòng chọn lại màu sắc từ danh sách.");
                            parent.alert("Sản phẩm thứ '.($k+1).' màu sắc không hợp lệ. Vui lòng chọn lại màu sắc từ danh sách.");
                        </script>';
                exit;
            }

            // Kiểm tra màu
            if ( $imei[ 'good_color' ] != $models[$k]) {
                echo '<script>
                            parent.palert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] không phải màu ['.$color_list[$models[$k]].']. Vui lòng chọn lại màu từ danh sách.");
                            parent.alert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] không phải màu ['.$color_list[$models[$k]].']. Vui lòng chọn lại màu từ danh sách.");
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
                                    parent.palert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] bị trùng. Vui lòng kiểm tra lại.");
                                    parent.alert("Sản phẩm thứ '.($k+1).', IMEI ['.$imeis[$k].'] bị trùng. Vui lòng kiểm tra lại.");
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