<?php

/**
 * Bảng mã lỗi
 * 		các mã sau được trả về khi gọi function AJAX này
 * 		dùng để dịch ra ở client cho người ta dễ hiểu
 * @return  1 OK
 * @return  -100 Không phải truy vấn kiểu AJAX
 * @return  0 Thiếu thông tin
 * @return  -1 Không tồn tại thông báo trùng IMEI này
 * @return  -2 Thông báo này đã được xử lý
 * @return  -3 Bạn không có quyền sử dụng chức năng này
 * @return  -4 Không tìm thấy chấm công của người Bị báo cáo (chấm công không tồn tại hoặc đã bị xóa)
 * @return  -5 [description] chỗ này cần check lại, nhưng nếu dùng transaction thì ok, không lo bị nóng
 * @return  -6 Thiếu ID người được nhận
 * @return  -7 Không tìm thấy thông tin khách hàng của người Bị báo cáo
 * @return  -8 Không tìm thấy chấm công của người Bị báo cáo
 * @return  -9 Không tìm thấy thông tin người báo cáo
 * @return  -10 Không tìm thấy thông tin người Bị báo cáo
 * @return  -11 Không tìm thấy chấm công của người báo cáo
 * @return  -1000 Chặn ASM báo cáo
 * @return  -9000 Éo biết vì sao lỗi (try-catch-exception)
 */

$this->_helper->viewRenderer->setNoRender(true);
$this->_helper->layout->disableLayout();

if(! $this->getRequest()->isXmlHttpRequest()) {
    exit('-100'); // not
}

$id        = $this->getRequest()->getParam('id');
$staff_win = $this->getRequest()->getParam('staff_win');
$note      = $this->getRequest()->getParam('note');

if ($id) {
    // try {
    	$QDuplicatedImei = new Application_Model_DuplicatedImei();
        $QTiming = new Application_Model_Timing();
        $QTimingSale = new Application_Model_TimingSale();
    
        // check $id
        $di = $QDuplicatedImei->find($id);
        $di = $di->current();
    
        if (! $di ) { // không tồn tại dòng này
            echo '-1';
            exit;
        }
    
        if ($di['solved'] == 1) { // đã xử lý rồi mà -_-
            echo '-2';
            exit;
        }
    
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    
        $group_id = $userStorage->group_id;
    
        // $db = Zend_Registry::get('db');
    	// $db->beginTransaction();
        // Vì không dùng transaction nữa nên dùng 2 biến này
        // làm biến tạm để rollback lại khi cần thiết
        $to_insert_timing_sales = null;
        $to_delete_timing_sales = null;
        $flag = false;
    
        // chia thao tác: tùy sếp hay asm mà xử lý
        if ($group_id == BOARD_ID || $group_id == SALES_EXT_ID || $userStorage->id == SUPERADMIN_ID) {
        	if (!$staff_win) { // thiếu thông tin
        		// $db->rollback();
    	        echo '-6';
    	        exit;
    	    }
    
    	    $timing_sales_first = $QTimingSale->find($di['timing_sales_first']);
    		$timing_sales_first = $timing_sales_first->current();
    
    		if (!$timing_sales_first) {
    			// $db->rollback();
    			// echo '-7';
    			// exit;
    		}
    
    		$timing_first = $QTiming->find($timing_sales_first['timing_id']);
    		$timing_first = $timing_first->current();
    
    		if (!$timing_first) {
    			// $db->rollback();
    			// echo '-8';
    			// exit;
    		}
    
        	/**
        	 * trường hợp thằng báo cáo sau được tính - xử hơi mệt à
        	 */
        	if ($staff_win == $di['staff_id']) {
        		// copy timing sales qua bảng trash
        		$ts = $QTimingSale->find($di['timing_sales_first']);
        		$ts = $ts->current();
    
        		if ($ts) {
        			$ts = $ts->toArray();
    				$QTimingSaleTrash = new Application_Model_TimingSaleTrash();
    				$tst = $QTimingSaleTrash->find($ts['id']);
    				$tst = $tst->current();
    
    				if ($tst) { // đã move dữ liệu
    					// $db->rollback();
    					// echo '-5';
    					// exit;
    				} else { // chưa move -> move nè
                        $to_insert_timing_sales = $ts;
        				$is_insert = $QTimingSaleTrash->insert($ts);
    		    		
                        if ($is_insert) { // insert được mới xử tiếp, không thì thôi
        		    		// xóa timing sales gốc
        		    		$where = $QTimingSale->getAdapter()->quoteInto('id = ?', $ts['id']);
                            $to_delete_timing_sales = $QTimingSale->fetchRow($where)->toArray();
        		    		$QTimingSale->delete($where);
                        }
                    }
                } else { // timing sale không tồn tại
                    // $db->rollback();
                    // echo '-4';
                    // exit;
                }

	    		// chèn customer mới vào, lấy ID mới
    			$QCustomer = new Application_Model_Customer();
	    		$data = array(
					'name'         => $di['customer_name'],
					'phone_number' => $di['customer_phone'],
					'address'      => $di['customer_address'],
	    			);

				$to_insert_customer_id = $customer_id = $QCustomer->insert($data);

				// Lấy thông tin model
				$model = $QDuplicatedImei->get_model($di['imei']);

				// tìm xem nó chấm công cho ngày nào, lấy cái id đó ra
                $where   = array();
                $where[] = $QTiming->getAdapter()->quoteInto('DATE(`from`) = ?', date('Y-m-d', strtotime($di['date'])));
                $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $staff_win);
                $timing  = $QTiming->fetchRow($where);

				// chèn timing sales mới, theo customer id và timing id vừa có
	    		if ($timing) {
		    		$data = array(
						'product_id'  => $model['product_id'],
						'model_id'    => $model['color_id'],
						'customer_id' => $customer_id,
						'imei'        => $di['imei'],
						'timing_id'   => $timing['id'],
		    			);

	    			$timing_sale_id = $QTimingSale->insert($data);

                    $flag = true;
	    		} else {
                    $QTimingSale->insert($to_delete_timing_sales);
                    
                    $where = $QCustomer->getAdapter()->quoteInto('id = ?', $to_insert_customer_id);
                    $QCustomer->delete($where);

                    $where = $QTimingSaleTrash->getAdapter()->quoteInto('id = ?', $is_insert);
                    $QTimingSaleTrash->delete($where);
	    			// $db->rollback();
	    			echo '-11';
	    			exit;
	    		}
    
    	        $data = array_filter(array(
    	        	'timing_sales'  => $timing_sale_id,
    				'staff_win'     => $staff_win,
    				'director_note' => $note,
    				'solved'        => 1,
    				'solved_at'     => date('Y-m-d H:i:s'),
    				'solved_by'     => $userStorage->id,
    	        ));
    
    		/**
    		 * trường hợp thằng báo cáo trước được tính,
    		 * không có update gì nhiều,
    		 * chỉnh trong dòng duplicate_imei là xong
    		 */
        	} else {
                $flag = true;
    
    	        $data = array_filter(array(
    				'staff_win'     => $staff_win,
    				'director_note' => $note,
    				'solved'        => 1,
    				'solved_at'     => date('Y-m-d H:i:s'),
    				'solved_by'     => $userStorage->id,
    	        ));
        	}
        
            if ($flag) {
                
        		$QNotify            = new Application_Model_Notification();
        		$QStaff             = new Application_Model_Staff();
        		$QRegional_market   = new Application_Model_RegionalMarket();
        		$QStore             = new Application_Model_Store();
        		
        		$region = $QRegional_market->get_cache();
        		$store  = $QStore->get_cache();
        		
        		$staff  = $QStaff->find($di['staff_id']);
        		$staff  = $staff->current();
        
        		if (!$staff) {
        			// $db->rollback();
        			echo '-9';
        			exit;
        		}
        		
        		$staff_first = $QStaff->find($di['staff_id_first']);
        		$staff_first = $staff_first->current();
        
        		if (!$staff_first) {
        			// $db->rollback();
        			echo '-10';
        			exit;
        		}
        		
        		// $name     = 'Thông báo về việc chuyển doanh số bán';
        		// $ids      = $di['staff_id'].','.$di['staff_id_first'];
        		
        		// $staff_1  = $staff_first['firstname'] . ' ' . $staff_first['lastname'];
        		// $staff_2  = $staff['firstname'] . ' ' . $staff['lastname'];
        		// $region_1 = isset($region[ $staff_first['regional_market'] ]) ? $region[ $staff_first['regional_market'] ] : '';
        		// $region_2 = isset($region[ $staff['regional_market'] ]) ? $region[ $staff['regional_market'] ] : '';
        		// $date_0   = date('d/m/Y', strtotime($di['created_at']));
        
        		// $date_1  = date('d/m/Y', strtotime($timing_first['from']));
        		// $date_2  = date('d/m/Y', strtotime($di['date']));
        		
        		// $store_1 = isset( $store[ $timing_first['store'] ] ) ? $store[ $timing_first['store'] ] : '';
        		// $store_2 = isset( $store[ $di['store_id'] ] ) ? $store[ $di['store_id'] ] : '';
        		
        		// $imei_0  = $di['imei'];
        
        		// if ($staff_win == $di['staff_id']) {
        		// 	$content   = "<p>Gửi các PG/PB/Sales: ".$staff_1." (".$region_1.") và ".$staff_2." (".$region_2.")</p>
        		// 		<p>Dựa theo đơn xin xem xét tranh chấp doanh số giữa ".$staff_1." (".$region_1.") và ".$staff_2." (".$region_2.") gửi ngày ".$date_0.". 
        		// 		Giám đốc bộ phận Sale sau khi kiểm tra và xác minh, quyết định chuyển doanh số bán của 
        		// 		IMEI: ".$imei_0." từ PG/PB ".$staff_1." (bán ngày: ".$date_1." tại cửa hàng ".$store_1.") 
        		// 		sang cho PG/PB ".$staff_2." (bán ngày:".$date_2." tại cửa hàng ".$store_2.").</p>
        		// 		<p>Đây là quyết định cuối cùng.</p>
        		// 		<p>Trân trọng,</p>";
        		// } else {
        		// 	$content   = "<p>Gửi các PG/PB/Sales: ".$staff_1." (".$region_1.") và ".$staff_2." (".$region_2.")</p>
        		// 		<p>Dựa theo đơn xin xem xét tranh chấp doanh số giữa ".$staff_1." (".$region_1.") và ".$staff_2." (".$region_2.") gửi ngày ".$date_0.". 
        		// 		Giám đốc bộ phận Sale sau khi kiểm tra và xác minh, quyết định giữ nguyên doanh số bán của 
        		// 		IMEI: ".$imei_0." cho ".$staff_1." (bán ngày: ".$date_1." tại cửa hàng ".$store_1.").</p>
        		// 		<p>Đây là quyết định cuối cùng.</p>
        		// 		<p>Trân trọng,</p>";
        		// }
        
        	 //    $notify = array(
        		// 	'name'       => $name,
        		// 	'content'    => trim($content),
        		// 	'ids'        => $ids,
        		// 	'created_at' => date('Y-m-d H:i:s'),
        		// 	'created_by' => $userStorage->id
        	 //    );
        
        	 //    $QNotify->insert($notify);
            }    
    
        } elseif (in_array( $group_id, array(ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID) )) {
        	// exit('-1000');
        	
            $data = array_filter(array(
    			'asm_check'   => 1,
    			'asm_at'      => date('Y-m-d H:i:s'),
    			'asm_id'      => $userStorage->id,
            ));
        } else {
        	// $db->rollback();
            exit('-3');
        }
    
        $where = $QDuplicatedImei->getAdapter()->quoteInto('id = ?', $id);
    
        $QDuplicatedImei->update($data, $where);
        // $db->commit();
    
        echo '1'; // update thành công
    
        exit;
    // } catch (Exception $e) {
    	// $db->rollback();
        // PC::db('aaaaaaaa');
        // PC::db($e->getMessage()); 
    	echo '-9000';
    	exit;
    // }
}

echo '0'; // không có id lấy gì mà check

exit;