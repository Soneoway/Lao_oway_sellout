<?php
/**
 * Chứa các AJAX action dùng để xử lý
 */
class AjaxController extends My_Controller_Action
{
	/**
     * AJAX function
     * dismiss a staff from a specific dashboard for current user
     * @return int 1 if success
     */
    public function dashboardDismissAction()
    {
    	$this->_helper->layout->disableLayout();

    	if ( ! $this->getRequest()->isXmlHttpRequest() ) {
    		exit('0');
    	}

    	$staff_id = $this->getRequest()->getParam('staff_id');
    	$dashboard_id = $this->getRequest()->getParam('dashboard_id');
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();

    	if ( intval($staff_id) > 0 && intval($dashboard_id) > 0 && $userStorage->id > 0 ) {
    		$QDismiss = new Application_Model_DashboardDismiss();

    		$where = array();
    		$where[] = $QDismiss->getAdapter()->quoteInto('staff_id = ?', $staff_id);
    		$where[] = $QDismiss->getAdapter()->quoteInto('dashboard_id = ?', $dashboard_id);

    		$dismiss = $QDismiss->fetchRow($where);

    		if ($dismiss) {

    		} else {
    			$data = array(
					'staff_id'     => $staff_id,
					'dashboard_id' => $dashboard_id,
					'user_id'      => $userStorage->id,
    				);

    			$QDismiss->insert($data);
    		}

    		echo '1';
    	} else {
    		echo '-1';
    	}

    	exit;
    }

    /**
     * Get Staff note
     * @return [type] [description]
     */
    public function staffNoteAction()
    {
    	$this->_helper->layout->disableLayout();

    	if ( ! $this->getRequest()->isXmlHttpRequest() ) {
    		exit('0');
    	}

    	$id = $this->getRequest()->getParam('id');

    	if ($id) {
    		$QStaff = new Application_Model_Staff();
    		$staff = $QStaff->find($id);
    		$staff = $staff->current();

    		if ($staff) {
				$this->view->name = $staff['firstname'] . ' ' . $staff['lastname'];
				$this->view->code = $staff['code'];
				$this->view->note = trim($staff['note']);
				$this->view->id   = $id;
    		} else {
    			exit('-1');
    		}
    	} else {
    		exit('-1');
    	}
    }

    public function staffNoteSaveAction()
    {
    	$this->_helper->layout->disableLayout();

    	if ( ! $this->getRequest()->isXmlHttpRequest() ) {
    		exit('0');
    	}

    	$id = $this->getRequest()->getParam('id');
    	$note = trim( $this->getRequest()->getParam('note') );

    	if ($id) {
    		$QStaff = new Application_Model_Staff();
    		$staff = $QStaff->find($id);
    		$staff = $staff->current();

    		if ($staff) {
    			$data = array(
    				'note' => $note
    				);
    			$where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
    			$QStaff->update($data, $where);

    			exit('1');
    		} else {
    			exit('-1');
    		}
    	} else {
    		exit('-1');
    	}
    }

    public function staffEmailSaveAction()
    {
    	$this->_helper->layout->disableLayout();

    	if ( ! $this->getRequest()->isXmlHttpRequest() ) {
    		exit('0');
    	}

    	$id = $this->getRequest()->getParam('id');
    	$email = trim( $this->getRequest()->getParam('email') ) . EMAIL_SUFFIX;

    	if ($id && intval($id) > 0 && $email && strlen($email) > 0) {
    		$QStaff = new Application_Model_Staff();

    		$where = $QStaff->getAdapter()->quoteInto('email = ?', $email);
    		$staff = $QStaff->fetchRow($where);

    		if ($staff) {
    			exit('-2');
    		}

    		$staff = $QStaff->find($id);
    		$staff = $staff->current();

    		if ($staff) {
    			$data = array(
    				'email' => $email,
    				);

    			$where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
    			$QStaff->update($data, $where);

    			$staff_after = $QStaff->find($id);
    			$staff_after = $staff_after->current();

    			Log::w($staff->toArray(), $staff_after->toArray(), $id, LogGroup::Staff, LogType::Update);
    			exit('1');
    		} else {
    			exit('-1');
    		}
    	} else {
    		exit('-1');
    	}
    }

    /**
     * Danh sách các mã lỗi return khi gọi hàm AJAX
     * @return 1 OK
     * @return  -100 Không phải truy vấn kiểu AJAX
     * @return  -1 Không tìm thấy dữ liệu báo trùng IMEI
     * @return  -21 Không tìm thấy thông tin khách hàng
     * @return  -22 Không tìm thấy chấm công của người bị báo cáo
     * @return  -23 Không tìm thấy dữ liệu để phục hồi
     * @return  -24 Không tìm thấy thông tin khách hàng
     * @return  -25 Không tìm thấy dữ liệu để phục hồi
     * @return  -3 Không tìm thấy chấm công của người Báo cáo
     * @return  -4 Không có ID
     * @return  -5 Không tìm thấy model
     * @return  -6 Không chèn được thông tin khách hàng
     */
    public function imeiReportReverseAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	if ( ! $this->getRequest()->isXmlHttpRequest() ) {
    		echo '-100';
    		exit;
    	}

    	$id = $this->getRequest()->getParam('id');

    	if ($id && intval($id) > 0) {
    		// tìm xem có dòng nào id này không
    		$QDuplicatedImei = new Application_Model_DuplicatedImei();
    		$di = $QDuplicatedImei->find($id);
    		$di = $di->current();

    		if ($di) { // ơ-rê-ka
				$QTimingSale      = new Application_Model_TimingSale();
				$QTimingSaleTrash = new Application_Model_TimingSaleTrash();


    			/**
    			 * trường hợp này là
    			 * 		thằng hiện tại được nhận là thằng đi report lên
    			 *   	đồng nghĩa có sẵn timing id cho nó rồi
    			 */
    			if (!empty($di['staff_win']) && $di['staff_win'] == $di['staff_id']) {
					// dựa vào timing sale có sẵn, move dòng timing sale với id tương ứng trong bảng timing sale qua trash
					// >> thằng second bị bỏ
					$ts = $QTimingSale->find($di['timing_sales']);
					$ts = $ts->current();

					if ($ts) {
						$QTimingSaleTrash->insert($ts->toArray());

						$where = $QTimingSale->getAdapter()->quoteInto('id = ?', $ts['id']);
						$QTimingSale->delete($where);
					} else {
						echo '-24'; // làm khỉ gì có cái id này
						exit;
					}

					// dựa vào timing sale first, vào trash move dòng với id tương ứng qua bảng timing sale
					// >> thằng first được nhận lại
					$tst = $QTimingSaleTrash->find($di['timing_sales_first']);
					$tst = $tst->current();

					if ($tst) {
						$QTimingSale->insert($tst->toArray());

						$where = $QTimingSaleTrash->getAdapter()->quoteInto('id = ?', $tst['id']);
						$QTimingSaleTrash->delete($where);
					} else {
						echo '-25'; // chửi như trên
						exit;
					}

					$where = $QDuplicatedImei->getAdapter()->quoteInto('id = ?', $di['id']);
					$data = array('staff_win' => $di['staff_id_first']);
					$QDuplicatedImei->update($data, $where);

    			/**
    			 * trường hợp này là
    			 * 		thằng đầu tiên đang được tính imei đó >> cần chuyển qua thằng thứ 2
    			 * thao tác xử lý:
    			 * 		- nếu chưa có timing sales id trong dòng này, xử lý tương tự khi chấm cho thằng report được hưởng
    			 * 		- nếu có rồi
    			 */
    			} else {
    				//	làm như bên imei-report-confirm
    				if (empty($di['timing_sales'])) {
    					$ts = $QTimingSale->find($di['timing_sales_first']);
    					$ts = $ts->current();

    					if ($ts) {
	    					$QTimingSaleTrash->insert($ts->toArray());

				    		// xóa timing sales gốc
				    		$where = $QTimingSale->getAdapter()->quoteInto('id = ?', $ts['id']);
				    		$QTimingSale->delete($where);

				    		// chèn customer mới vào, lấy ID mới
			    			$QCustomer = new Application_Model_Customer();
				    		$data = array(
								'name'         => $di['customer_name'],
								'phone_number' => $di['customer_phone'],
								'address'      => $di['customer_address'],
				    			);

							$customer_id = $QCustomer->insert($data);

							if (!$customer_id) {
								echo '-6';
								exit;
							}

							// Lấy thông tin model
							$model = $QDuplicatedImei->get_model($di['imei']);

							if (!$model) {
								echo '-5';
								exit;
							}

							// tìm xem nó chấm công cho ngày nào, lấy cái id đó ra
							$QTiming = new Application_Model_Timing();
							$where   = array();
							$where[] = $QTiming->getAdapter()->quoteInto('DATE(`from`) = ?', date('Y-m-d', strtotime($di['date'])));
							$where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', $di['staff_id']);
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

				    			$data = array_filter(array(
						        	'timing_sales'  => $timing_sale_id,
									'staff_win'     => $di['staff_id'],
						        ));

						        $where = $QDuplicatedImei->getAdapter()->quoteInto('id = ?', $di['id']);

    							$QDuplicatedImei->update($data, $where);
				    		} else {
				    			echo '-3';
				    			exit;
				    		}

    					} else {
    						echo '-21';
    						exit;
    					}

    				} else {
    					// dựa vào timing sale first, vào move dòng với id tương ứng qua bảng timing sale trash
						// >> thằng first bị bỏ
						$ts = $QTimingSale->find($di['timing_sales_first']);
						$ts = $ts->current();

						if ($ts) {
							$QTimingSaleTrash->insert($ts->toArray());

							$where = $QTimingSale->getAdapter()->quoteInto('id = ?', $ts['id']);
							$QTimingSale->delete($where);
						} else {
							echo '-22'; // chửi như trên
							exit;
						}

    					// dựa vào timing sale, move dòng với id tương ứng trong bảng trash qua timing sale
						// >> thằng second được nhận
						$tst = $QTimingSaleTrash->find($di['timing_sales']);
						$tst = $tst->current();

						if ($tst) {
							$QTimingSale->insert($tst->toArray());

							$where = $QTimingSaleTrash->getAdapter()->quoteInto('id = ?', $tst['id']);
							$QTimingSaleTrash->delete($where);
						} else {
							echo '-23'; // làm khỉ gì có cái id này
							exit;
						}

						$where = $QDuplicatedImei->getAdapter()->quoteInto('id = ?', $di['id']);
						$data = array('staff_win' => $di['staff_id']);
						$QDuplicatedImei->update($data, $where);
    				}
    			}

    		} else { // tìm éo thấy, dẹp đê
    			echo '-1';
    			exit;
    		}
    	} else {
    		echo '-4';
    		exit;
    	}

    	echo '1';

    	exit;
    }

    /**
     * Dành cho PG update số tồn kho ở các hệ thống cửa hàng lớn
     * @return 1 Cập nhật thành công
     * @return -1 Dữ liệu không hợp lệ
     * @return -2 Hôm nay, bạn đã báo cáo cho store này
     * @return -3 Cập nhật thất bại
     */
    public function stockUpdateAction()
    {
    	// bỏ layout
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	if (! $this->getRequest()->isXmlHttpRequest() ) {
    		$this->_redirect(HOST);
    	}

    	// Lấy tham số
		$product_id = $this->getRequest()->getParam('product_id');
		$num        = $this->getRequest()->getParam('num');
		$store      = $this->getRequest()->getParam('store');
		$model      = $this->getRequest()->getParam('model');

    	// kiểm tra tham số
    	if ( ! ( $product_id && intval($product_id) > 0
	    		&& intval( trim($num) ) >= 0
	    		&& intval($num) < 50
	    		&& $store && intval($store) > 0
	    		&& $model && intval($model) > 0 ) ) {
    		echo '-1'; // Dữ liệu không hợp lệ
    		exit;
    	}

    	// định dạng tham số
    	$num = intval( trim($num) );

    	// lấy id user
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$user_id = $userStorage->id;

		// lấy ngày hiện tại. mỗi khi pg báo cáo stock thì luôn lấy ngày hiện tại để lưu trữ
    	$date = date('Y-m-d H:i:s');

    	$QStock = new Application_Model_Stock();
    	$QStockCurrent = new Application_Model_StockCurrent();

    	// điều kiện tìm kiếm
    	// lọc các báo cáo trong ngày hiện tại của pg đang đăng nhập, và cửa hàng pg đang báo cáo, ứng với từng model cụ thể
    	// $where = array();
    	// $where[] = $QStock->getAdapter()->quoteInto('DATE(date) = DATE(?)', $date);
    	// $where[] = $QStock->getAdapter()->quoteInto('staff_id = ?', $user_id);
    	// $where[] = $QStock->getAdapter()->quoteInto('store_id = ?', $store);
    	// $where[] = $QStock->getAdapter()->quoteInto('product_id = ?', $product_id);
    	// $where[] = $QStock->getAdapter()->quoteInto('model = ?', $model);

    	// $stock = $QStock->fetchRow($where);

    	// if ($stock) {
    	// 	echo '-2'; // Hôm nay, bạn đã báo cáo cho store/model này
    	// 	exit;
    	// }

    	// nếu chưa báo thì chèn vô DB
    	$data = array(
			'staff_id'   => $user_id,
			'store_id'   => $store,
			'product_id' => $product_id,
			'model'      => $model,
			'number'     => $num,
			'date'       => $date,
    		);

    	$id = $QStock->insert($data);

    	$where = array();
    	$where[] = $QStockCurrent->getAdapter()->quoteInto('DATE(date) = DATE(?)', $date);
    	$where[] = $QStockCurrent->getAdapter()->quoteInto('store_id = ?', $store);
    	$where[] = $QStockCurrent->getAdapter()->quoteInto('product_id = ?', $product_id);
    	$where[] = $QStockCurrent->getAdapter()->quoteInto('model = ?', $model);

    	$stockcurrent = $QStockCurrent->fetchRow($where);

    	if ($stockcurrent) {
    		$where = $QStockCurrent->getAdapter()->quoteInto('id = ?', $stockcurrent['id']);

    		$data = array(
    			'number' => $num,
    			'date' => $date,
    			'staff_id' => $user_id,
    			);

    		$QStockCurrent->update($data, $where);
    	} else {
    		$QStockCurrent->insert($data);
    	}

    	if ($id && $id > 0) {
    		echo '1'; // chèn thành công
    		exit;
    	}

    	echo '-3'; // chèn thất bại
    	exit;
    }

    public function storeSearchAction()
    {
    	$this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isXmlHttpRequest() ) {
    		echo '-100';
    		exit;
    	}

    	$store_name = $this->getRequest()->getParam('store_name');

    	if (!$store_name) {
    		echo '-1';
    		exit;
    	}

    	$QStore = new Application_Model_Store();
    	$where = $QStore->getAdapter()->quoteInto('name LIKE ?', '%'.$store_name.'%');

    	$this->view->stores = $QStore->fetchAll($where);
    }

    public function getRegionWhAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo '-100';
            exit;
        }

        $area_id = $this->getRequest()->getParam('area_id');

        if (!$area_id) {
            echo '-1';
            exit;
        }

        $QRegion = new Application_Model_WhRegion();
        $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $area_id);

        $regions = $QRegion->fetchAll($where);

        $region_arr = array();

        foreach ($regions as $region) {
            $region_arr[] = array(
                'id' => $region['id'],
                'name' => $region['name'],
                );
        }

        echo json_encode($region_arr);
        exit;
    }

	// Dùng để PG tự xem doanh số trong tháng và số lượng từng loại máy
    public function pgShortReportAction()
    {
		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$this->view->user_name = $userStorage->firstname . ' ' . $userStorage->lastname;

		$staff_id = $userStorage->id;
		$from     = $this->getRequest()->getParam('from');
		$to       = $this->getRequest()->getParam('to');

		if (!$from || trim($from) == "") {
			$from = date('01/m/Y');
		}

		if (!$to || trim($to) == "") {
			$to = date('d/m/Y');
		}

		$params = array(
			'from'     => $from,
			'to'       => $to,
			'staff_id' => $staff_id,
		);

		$analytics = array();
		$total = 0;

		$QTiming = new Application_Model_Timing();
		$sales   = $QTiming->report_by_staff($params, $analytics, $total);
		$this->view->sales = $sales;
		$this->view->total = $total;
		$this->view->analytics = $analytics;

		$this->view->params = $params;

		$QStore = new Application_Model_Store();
		$this->view->store_cached = $QStore->get_cache();

		$QProduct = new Application_Model_Good();
		$this->view->products = $QProduct->get_cache();

		$this->_helper->layout->disableLayout();
    }

    /**
     * Kiểm tra xem staff hiện tại có thuộc...
     *     ...cửa hàng được chọn trong thời gian chấm công hay không
     * @param  int $store_id    id cửa hàng
     * @param  datetime $timing_time thời gian chấm công
     * @return int              1 - thuộc
     * @return int              -100 - không phải ajax request
     * @return int              -1 - không đủ dữ liệu
     * @return int              -2 - không thuộc
     */
    public function belongToAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo '-100';
            exit;
        }

        $store_id = $this->getRequest()->getParam('store_id');
        $timing_time = $this->getRequest()->getParam('timing_time');

        $formatedDate = '';

        if (isset($timing_time) && $timing_time) {
            $temp = explode('/', $timing_time);
            $formatedDate = (isset($temp[2]) ? $temp[2] : '1970').'-'.(isset($temp[1]) ? $temp[1] : '01').'-'.(isset($temp[0]) ? $temp[0] : '01');
        }

        if ( ! ( $store_id && intval($store_id) > 0 && strtotime($formatedDate) > 0 ) ) {
            echo '-1';
            exit;
        }

        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ( ! $QStoreStaffLog->belong_to($userStorage->id, $store_id, $formatedDate) && !in_array($userStorage->group_id, array(ADMINISTRATOR_ID)) ) {
            echo '-2';
            exit;
        }

        echo '1';
        exit;
    }

    /**
     * Lấy danh sách các store mà Sales/PG được gán vào
     * SAU KHI NÓ CHỌN THỜI GIAN CHẤM CÔNG
     * @return view/html mảng các thẻ option
     */
    public function getStoreAction()
    {
        $this->_helper->layout->disableLayout();

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo '-100';
            exit;
        }

        $timing_time = $this->getRequest()->getParam('timing_time');
        $formatedDate = '';

        if (isset($timing_time) && $timing_time) {
            $temp = explode('/', $timing_time);
            $formatedDate = (isset($temp[2]) ? $temp[2] : '1970').'-'.(isset($temp[1]) ? $temp[1] : '01').'-'.(isset($temp[0]) ? $temp[0] : '01');
        }

        if ( ! ( strtotime($formatedDate) > 0 ) ) {
            echo '-1';
            exit;
        }

        $QStore = new Application_Model_Store();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QAsm = new Application_Model_Asm();

        if(in_array($userStorage->group_id,array(5))){

        	$where = array();
        	$where[] = $QAsm->getAdapter()->quoteInto('staff_id =?',$userStorage->id);
        	$where[] = $QAsm->getAdapter()->quoteInto('type =?',2);
        	$area = $QAsm->fetchAll($where);

        	$area_list = array();
        	foreach($area as $value) {
        		$area_list[] = $value['area_id'];
        	}

        	$where2 = array();
        	$where2[] = $QStore->getAdapter()->quoteInto('regional IN (?)',$area_list);
        	$where2[] = $QStore->getAdapter()->quoteInto('status = ?',1);
        	$store_list = $QStore->fetchAll($where2);

        	$store_id_list = array();
        	foreach($store_list as $value){
        		$store_id_list[] = $value['id'];
        	}

        }else{

        $store_id_list = $QStoreStaffLog->get_stores($userStorage->id, $formatedDate);

	}


        $where = array();
        $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);

        if(is_array($store_id_list) && count($store_id_list) > 0)
            $where[] = $QStore->getAdapter()->quoteInto('id IN (?)', $store_id_list);
        elseif( ! in_array($userStorage->group_id, array(ADMINISTRATOR_ID)) )
            $where[] = $QStore->getAdapter()->quoteInto('1=0', 1);

        $this->view->stores = $QStore->fetchAll($where, 'name');
    }

    /**
     * Kiểm tra timing đó có thuộc danh sách cần xử lý báo cáo gian dối hay không
     * Nếu có thì bên giao diện sẽ hiện ra nút Remove màu đỏ
     *
     * @return int -100 - Không phải AJAX request
     * @return int -1 - Không đủ tham số
     * @return int 1 - Thuộc diện cần xử lý
     */
    public function checkFakeReportAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo '-100';
            exit;
        }

        $timing_id = $this->getRequest()->getParam('timing_id');

        if (!$timing_id) {
            echo '-1';
            exit;
        }

        echo '1';
        exit;

    }

    /**
     * Hiển thị thông tin chi tiết của chấm công
     * @return int -100 - Không phải AJAX request
     * @return int -1 - Không đủ tham số
     * @return view/html - Phần thân của Bootstrap modal
     *                     Chứa thông tin staff, ngày, ca làm, giờ làm, cửa hàng
     *                     thông tin IMEI/model và khách hàng
     */
    public function showTimingAction()
    {
        $this->_helper->layout->disableLayout();

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo '-100';
            exit;
        }

        // lấy tham số
        $timing_id = $this->getRequest()->getParam('timing_id');

        //
        if (!$timing_id) {
            echo '-1';
            exit;
        }

        // Kiểm tra xem ID nhận được có phải là chấm công đang tồn tại hay không
        $QTiming = new Application_Model_Timing();
        $timing = $QTiming->find($timing_id);
        $timing = $timing->current();

        if ($timing) {
            // Lấy thông tin chi tiết về doanh số của chấm công trên
            $QTimingSale = new Application_Model_TimingSale();
            $where = $QTimingSale->getAdapter()->quoteInto('timing_id = ?', $timing_id);
            $timing_sales = $QTimingSale->fetchAll($where);

            // mảng chứa các ID thông tin chi tiết chấm công
            // mảng này dùng để kiểm tra lại timing_sales có thuộc
            // danh sách cần xử lý hay không
            $ts_ids = array();

            // mảng chứa ID thông tin khách hàng
            // tạo mảng này để lấy thông tin các khách hàng tương ứng
            // sau đó sắp xếp vào 1 mảng với index là id khách hàng (customer_id)
            // để tiện cho việc hiển thị bên tầng view
            $c_ids = array();

            // duyệt qua các timing sales (từ timing trên) để lấy các ID vào mảng
            foreach ($timing_sales as $key => $value) {
                $c_ids[] = $value['customer_id'];
                $ts_ids[] = $value['id'];
            }

            // Dùng cái này để check lại timing sales này có thuộc
            // danh sách cần xử lý hay không
            $QTimingNotActivated = new Application_Model_TimingNotActivated();
            $where = $QTimingNotActivated->getAdapter()->quoteInto('timing_sale_id IN (?)', $ts_ids);

            $ina = $QTimingNotActivated->fetchAll($where);
            $ina_list = array();

            if ($ina)
                foreach ($ina as $item){
                    $ina_list[$item['timing_sale_id']] = 1;
                }

            // list các timing cần xử lý
            $this->view->ina_list = $ina_list;

            // sắp xếp lại thông tin khách hàng vào mảng với index là ID của nó
            if (isset($c_ids) && count($c_ids) > 0) {
                $QCustomer = new Application_Model_Customer();
                $where = $QCustomer->getAdapter()->quoteInto('id IN (?)', $c_ids);
                $customers = $QCustomer->fetchAll($where);

                $customer_arr = array();

                foreach ($customers as $key => $value) {
                    $customer_arr[$value['id']] = $value;
                }

                $this->view->customers = $customer_arr;
            }

            $this->view->timing = $timing;
            $this->view->timing_sales = $timing_sales;

            $QShift = new Application_Model_Shift();
            $this->view->shift = $QShift->get_cache();

            $QStaff = new Application_Model_Staff();
            $this->view->staffs = $QStaff->get_cache();

            $QStore = new Application_Model_Store();
            $this->view->stores = $QStore->get_cache();

            $QGood = new Application_Model_Good();
            $this->view->goods = $QGood->get_cache();

            $QModel = new Application_Model_Model();
            $this->view->models = $QModel->get_cache();

        } else {
            echo '-2';
            exit;
        }
    }

    /**
     * Sales/ASM xóa timing sales có IMEI thuộc danh sách IMEI chưa active
     * Cập nhật ngày giờ và người xóa trong bảng imei_not_activated
     * @return [type] [description]
     */
    public function removeTimingSalesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Chỉ chấp nhận AJAX request
        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            echo json_encode(array(
                'code' => -100,
                'message' => 'Require an AJAX request',
                ));
            exit;
        }

        // Lấy tham số
        $timing_sales_id = $this->getRequest()->getParam('timing_sales_id');

        // Validate tham số
        if (!$timing_sales_id || ! intval($timing_sales_id) > 0) {
            echo json_encode(array(
                'code' => -1,
                'message' => 'Invalid ID',
                ));
            exit;
        }

        // Kiểm tra xem timing sales id có tồn tại không
        $QTimingSale = new Application_Model_TimingSale();
        $timing_sales = $QTimingSale->find($timing_sales_id);
        $timing_sales = $timing_sales->current();

        if (!$timing_sales) {
            echo json_encode(array(
                'code' => -2,
                'message' => 'Sales not Exists',
                ));
            exit;

        } else {
            // Kiểm tra xem timing parent của timing sales trên
            // có tồn tại hay không
            $QTiming = new Application_Model_Timing();
            $timing = $QTiming->find($timing_sales['timing_id']);
            $timing = $timing->current();

            if (! $timing) {
                echo json_encode(array(
                    'code' => -3,
                    'message' => 'Timing not Exists',
                    ));
                exit;

            } else {
                // Kiểm tra staff đi xóa có phải SALES TEAM leader
                // tại thời gian chấm công hay không
                $QSalesTeamStaff = new Application_Model_SalesTeamStaff();
                $leaders = $QSalesTeamStaff->get_leader_by_date(date('Y-m-d 23:59:59', strtotime($timing['from'])), $timing['staff_id']);

                if (isset($leaders[0])) {
                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

                    if ($userStorage->id != $leaders[0]) {
                        echo json_encode(array(
                            'code' => -4,
                            'message' => 'You have no role here',
                            ));
                        exit;

                    } else {
                        // Sau khi thỏa các điều kiện trên
                        // xóa timing sales tương ứng
                        $where = $QTimingSale->getAdapter()->quoteInto('id = ?', $timing_sales_id);
                        $QTimingSale->delete($where);

                        // Log lại ngày giờ xóa và người xóa vào dòng tương ứng
                        $QTimingNotActivated = new Application_Model_TimingNotActivated();
                        $where = $QTimingNotActivated->getAdapter()->quoteInto('timing_sale_id IN (?)', $timing_sales_id);

                        $QTimingNotActivated->update(array(
                            'removed_at' => date('Y-m-d H:i:s'),
                            'removed_by' => $userStorage->id,
                        ), $where);

                        echo json_encode(array(
                            'code' => 1,
                            'message' => 'Removed Successfully',
                            ));
                        exit;
                    }

                } else {
                    echo json_encode(array(
                        'code' => -4,
                        'message' => 'You have no role here',
                        ));
                    exit;
                }
            }
        }

        exit;
    }

    public function assignLeaderStoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $store_id = $this->getRequest()->getParam('store_id');

        if ( ! ( $store_id && intval($store_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        if (!$this->checkLeader($store_id))
            exit( json_encode( array(
                'code' => '-4',
                'error' => 'Invalid store',
                ) ) );

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QStoreLeader = new Application_Model_StoreLeader();
        $where = array();
        $where[] = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
        $where[] = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $store_id);

        $store_leader = $QStoreLeader->fetchRow($where);

        if ($store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'You have been already assigned to this store',
                ) ) );
        }

        $data = array(
            'staff_id' => $userStorage->id,
            'store_id' => $store_id,
            'status' => 0
            );

        $id = $QStoreLeader->insert($data);

        if ($id > 0) {
            exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
        } else {
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Insert error',
                ) ) );
        }
    }

    public function approveLeaderStoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $store_id = $this->getRequest()->getParam('store_id');

        if ( ! ( $store_id && intval($store_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        // check store
        if (!$this->checkASM($store_id))
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Invalid store',
                ) ) );


        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $store_id);

        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $store_leader['id']);
        $data = array('status' => 1);
        $QStoreLeader->update($data, $where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('store_leader_log_cache');

        // log
        $this->logLeader($store_leader['staff_id'], $store_id, 1);

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }

    public function rejectLeaderStoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $store_id = $this->getRequest()->getParam('store_id');

        if ( ! ( $store_id && intval($store_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $store_id);

        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $store_leader['id']);
        $QStoreLeader->delete($where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('store_leader_log_cache');

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }

    public function removeLeaderStoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $store_id = $this->getRequest()->getParam('store_id');

        if ( ! ( $store_id && intval($store_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        // check store
        if (!$this->checkASM($store_id))
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Invalid store',
                ) ) );

        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $store_id);

        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $store_leader['id']);
        $QStoreLeader->delete($where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('store_leader_log_cache');

        $this->logLeader($store_leader['staff_id'], $store_id, 0);

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }

    private function checkLeader($store_id) {
        // check store
        $QStore = new Application_Model_Store();
        $store = $QStore->find($store_id);
        $store = $store->current();

        if (!$store) {
            return false;
        }

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QRegion = new Application_Model_RegionalMarket();

        $region = $QRegion->find($userStorage->regional_market);
        $region = $region->current();

        if ($region) {
            $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $region['area_id']);
            $regions = $QRegion->fetchAll($where);

            $region_list = array();

            foreach ($regions as $reg)
                $region_list[] = $reg['id'];

            if (is_array($region_list) && count($region_list) > 0) {

                if (!in_array($store['regional_market'], $region_list))
                    return false;

            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    private function checkASM($store_id) {
        $QStore = new Application_Model_Store();
        $store = $QStore->find($store_id);
        $store = $store->current();

        if (!$store)
            return false;

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (in_array( $userStorage->group_id, My_Staff_Group::$allow_in_area_view )) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache();
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) && in_array($store['district'], $list_regions)) {
                return true;
            }
        }

        return false;
    }

    private function logLeader($staff_id, $store_id, $status) // 1: add; 0: remove
    {
        $QLog = new Application_Model_StoreLeaderLog();

        if ($status == 0) {
            $where = array();
            $where[] = $QLog->getAdapter()->quoteInto('store_id = ?', $store_id);
            $where[] = $QLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
            $where[] = $QLog->getAdapter()->quoteInto('released_at IS NULL', 1);

            $data = array('released_at' => time());
            $QLog->update($data, $where);
        } elseif ($status == 1) {
            $data = array(
                'store_id' => $store_id,
                'staff_id' => $staff_id,
                'joined_at' => time(),
                );

            $QLog->insert($data);
        }

    }

    public function updateStoreJoinTimeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $id = $this->getRequest()->getParam('id');
        $value = $this->getRequest()->getParam('value');

        if (! ( $id && intval($id) > 0 && $value && strtotime($value) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $log = $QStoreStaffLog->find($id);
        $log = $log->current();

        if (!$log) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Invalid ID',
                ) ) );
        }

        $where = $QStoreStaffLog->getAdapter()->quoteInto('id = ?', $id);
        $data = array(
            'joined_at' => strtotime($value),
            );

        $QStoreStaffLog->update($data, $where);

        // log
        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'STORE STAFF - Update('.$id.') - Joined At - Old('.date('Y-m-d', $log['joined_at']).') - New('.$value.')';

        $QLog->insert( array (
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        exit( json_encode( array(
                'code' => '1',
                'message' => 'Success',
                ) ) );
    }

    public function updateLeaderStoreJoinTimeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $id = $this->getRequest()->getParam('id');
        $value = $this->getRequest()->getParam('value');

        if (! ( $id && intval($id) > 0 && $value && strtotime($value) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreStaffLog = new Application_Model_StoreLeaderLog();
        $log = $QStoreStaffLog->find($id);
        $log = $log->current();

        if (!$log) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Invalid ID',
                ) ) );
        }

        $where = $QStoreStaffLog->getAdapter()->quoteInto('id = ?', $id);
        $data = array(
            'joined_at' => strtotime($value),
            );

        $QStoreStaffLog->update($data, $where);

        // log
        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'STORE LEADER - Update('.$id.') - Joined At - Old('.date('Y-m-d', $log['joined_at']).') - New('.$value.')';

        $QLog->insert( array (
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        exit( json_encode( array(
                'code' => '1',
                'message' => 'Success',
                ) ) );
    }

    public function getTagsAction(){
        // disable layouts and renderers
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $tag_name = $this->getRequest()->getParam('term');

        $QTag = new Application_Model_Tag();
        $where = $QTag->getAdapter()->quoteInto('name LIKE ?', $tag_name . '%');
        $tags = $QTag->fetchAll($where, 'name');

        $aTags = array();
        if ($tags)
            foreach ($tags as $t){
                $aTags[] = array(
                    'id' => $t['id'],
                    'value' => $t['name'],
                );
            }

        echo json_encode($aTags, true);
    }

    public function storeSearchNewAction()
    {
        $this->_helper->layout->disableLayout();

        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit;
        }

        $store_name = $this->getRequest()->getParam('store_name');

        if (!$store_name) {
            exit;
        }

        $QStore = new Application_Model_Store();
        $where = array();
        $where[] = $QStore->getAdapter()->quoteInto('name LIKE ?', '%'.$store_name.'%');
        $where[] = $QStore->getAdapter()->quoteInto('del IS NULL OR del = 0', 1);
        $this->view->stores = $QStore->fetchAll($where);

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();
        $QRegion = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegion->get_cache_all();
    }

    public function getStoreForLeaderAction()
    {
        $this->_helper->layout->disableLayout();

        if (! $this->getRequest()->isXmlHttpRequest() ) {
            exit('-100');
        }

        $area_id     = $this->getRequest()->getParam('area_id');
        $region_id   = $this->getRequest()->getParam('region_id');
        $district_id = $this->getRequest()->getParam('district_id');
        $store_name  = $this->getRequest()->getParam('store_name');
        $search_type  = $this->getRequest()->getParam('search_type');

        $QStore = new Application_Model_Store();
        $where = array();

        if (!$area_id && !$region_id && !$store_name) {
            $where[] = $QStore->getAdapter()->quoteInto('1=0', 1);
            //exit('-1');
        }

        if ($area_id) {
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where_1 = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
            $regional_markets = $QRegionalMarket->fetchAll($where_1);

            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $where[] = $QStore->getAdapter()->quoteInto('regional_market IN (?)', $tem);
            else
                $where[] = $QStore->getAdapter()->quoteInto('1=0', 1);

        }

        if ($region_id) {
            $where[] = $QStore->getAdapter()->quoteInto('regional_market = ?', $region_id);
        }

        if ($district_id) {
            $where[] = $QStore->getAdapter()->quoteInto('district = ?', $district_id);
        }

        if ($store_name && strlen($store_name) > 0) {
            $where[] = $QStore->getAdapter()->quoteInto('name LIKE ?', '%' .$store_name. '%');
        }

        $where[] = $QStore->getAdapter()->quoteInto('del IS NULL OR del = 0', 1);

        $QRegion = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegion->get_cache_all();
        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();
        $QOrg = new Application_Model_Org();
        $this->view->org = $QOrg->get_cache();

        $store_list = $QStore->fetchAll($where, 'name');
        $this->view->stores = $store_list;

        // Search for Store Focus
        if ($search_type == 1) { 

            foreach ($store_list as $key => $value) { $store_id_list[] = $value['id']; }

            $QStoreFocus = new Application_Model_StoreFocus();

            $where_sf = $QStoreFocus->getAdapter()->quoteInto('store_id IN (?)', $store_id_list);
            $data = $QStoreFocus->fetchAll($where_sf);

            $result = array();
            for ($i=0;$i<count($data);$i++) { $result[ $data[$i]['store_id'] ] = $data[$i]['id']; }

            $this->view->store_focus = $result;

        } else {

            $store_control = array();
            $store_id_list = array();
            if ( !empty($store_list) ) {

                foreach ($store_list as $key => $value) { $store_id_list[] = $value['id']; }
                // print_r($store_id_list);

                $Qscm = new Application_Model_StoreControlMap();
                $store_control = $Qscm->getStoreControl($store_id_list);
            }

            $this->view->store_control = $store_control;

        } 

    }



    public function approveLeaderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $pid = $this->getRequest()->getParam('pid');
        $log_id = $this->getRequest()->getParam('log_id');

        if ( ! ( $pid && intval($pid) > 0 && $log_id && intval($log_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
        $where = $QStoreLeaderLog->getAdapter()->quoteInto('id = ?', $log_id);
        $store_leader_log = $QStoreLeaderLog->fetchRow($where);

        if (!$store_leader_log) {
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        if ($store_leader['store_id'] != $store_leader_log['store_id']
            || $store_leader['staff_id'] != $store_leader_log['staff_id']
            || $store_leader_log['parent'] != $pid) {
            exit( json_encode( array(
                'code' => '-4',
                'error' => 'Invalid Params',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $data = array('status' => 1);
        $QStoreLeader->update($data, $where);

        // log
        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'LEADER - Approved('.$pid.')  - Leader('.$store_leader['staff_id'].') - Stores('.$store_leader['store_id'].')';

        //todo log
        $QLog->insert( array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }


    public function getStaffForTimeAction()
    {
        $this->_helper->layout->disableLayout();

        if (! $this->getRequest()->isXmlHttpRequest() ) {
            exit('-100');
        }

        $area_id     = $this->getRequest()->getParam('area_id');
        $team   = $this->getRequest()->getParam('team_id');
        $district_id = $this->getRequest()->getParam('district_id');
        $staff_name  = $this->getRequest()->getParam('staff_name');
        $asm = $this->getRequest()->getParam('asm');
        if(!isset($asm))
            if (!$area_id && !$team && !$staff_name) {
                exit('-1');
            }

        $QStaff = new Application_Model_Staff();
        $where = array();

        if ($area_id) {
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where_1 = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
            $regional_markets = $QRegionalMarket->fetchAll($where_1);

            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $where[] = $QStaff->getAdapter()->quoteInto('regional_market IN (?)', $tem);
            else
                $where[] = $QStaff->getAdapter()->quoteInto('1=0', 1);

        }

        if ($team) {
            if($team == PGPB_TITLE)
                $where[] = $QStaff->getAdapter()->quoteInto('title = ?', $team);
            else
                $where[] = $QStaff->getAdapter()->quoteInto('team = ?', $team);
        }

        if ($staff_name) {
            $where[] = $QStaff->getAdapter()->quoteInto('CONCAT(firstname, " ",lastname) LIKE ?', '%'.$staff_name.'%');
        }

        if($asm)
        {
            $where[] = $QStaff->getAdapter()->quoteInto('team in (?)', array(75,119));
        }

        $where[] = $QStaff->getAdapter()->quoteInto('status = 1', '');

        $QRegion = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegion->get_cache_all();
        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();
        $QTeam = new Application_Model_Team();
        $this->view->team = $QTeam->get_cache();
        $this->view->staff = $QStaff->fetchAll($where);

    }

    public function rejectLeaderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $pid = $this->getRequest()->getParam('pid');
        $log_id = $this->getRequest()->getParam('log_id');

        if ( ! ( $pid && intval($pid) > 0 && $log_id && intval($log_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
        $where = $QStoreLeaderLog->getAdapter()->quoteInto('id = ?', $log_id);
        $store_leader_log = $QStoreLeaderLog->fetchRow($where);

        if (!$store_leader_log) {
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        if ($store_leader['store_id'] != $store_leader_log['store_id']
            || $store_leader['staff_id'] != $store_leader_log['staff_id']
            || $store_leader_log['parent'] != $pid) {
            exit( json_encode( array(
                'code' => '-4',
                'error' => 'Invalid Params',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $QStoreLeader->delete($where);

        $where = $QStoreLeaderLog->getAdapter()->quoteInto('id = ?', $log_id);
        $QStoreLeaderLog->delete($where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('store_leader_log_cache');

        // log
        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'LEADER - Reject('.$pid.')  - Leader('.$store_leader['staff_id'].') - Stores('.$store_leader['store_id'].')';

        //todo log
        $QLog->insert( array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }

    public function removeLeaderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $pid = $this->getRequest()->getParam('pid');
        $log_id = $this->getRequest()->getParam('log_id');

        if ( ! ( $pid && intval($pid) > 0 && $log_id && intval($log_id) > 0 ) ) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid params',
                ) ) );
        }

        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $store_leader = $QStoreLeader->fetchRow($where);

        if (!$store_leader) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
        $where = $QStoreLeaderLog->getAdapter()->quoteInto('id = ?', $log_id);
        $store_leader_log = $QStoreLeaderLog->fetchRow($where);

        if (!$store_leader_log) {
            exit( json_encode( array(
                'code' => '-3',
                'error' => 'Nobody has been assigned to this store',
                ) ) );
        }

        if ($store_leader['store_id'] != $store_leader_log['store_id']
            || $store_leader['staff_id'] != $store_leader_log['staff_id']
            || $store_leader_log['parent'] != $pid) {
            exit( json_encode( array(
                'code' => '-4',
                'error' => 'Invalid Params',
                ) ) );
        }

        $where = $QStoreLeader->getAdapter()->quoteInto('id = ?', $pid);
        $QStoreLeader->delete($where);

        $where = $QStoreLeaderLog->getAdapter()->quoteInto('id = ?', $log_id);
        $data = array('released_at' => time());
        $QStoreLeaderLog->update($data, $where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('store_leader_log_cache');

        // log
        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'LEADER - Remove('.$pid.')  - Leader('.$store_leader['staff_id'].') - Stores('.$store_leader['store_id'].')';

        //todo log
        $QLog->insert( array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );

        exit( json_encode( array(
                'code' => '1',
                'error' => 'Success',
                ) ) );
    }

    public function moveStaffToCompanyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit( json_encode( array(
                'code' => '-100',
                'error' => 'Only accept AJAX requests',
                ) ) );
        }

        $company_id = $this->getRequest()->getParam('company_id');
        $ids = $this->getRequest()->getParam('ids');

        if (!$company_id || !$ids) {
            exit( json_encode( array(
                'code' => '-1',
                'error' => 'Invalid ID',
                ) ) );
        }

        $QCompany = new Application_Model_Company();
        $company_check = $QCompany->find($company_id);
        $company_check = $company_check->current();

        if (!$company_check) {
            exit( json_encode( array(
                'code' => '-2',
                'error' => 'Invalid Company',
                ) ) );
        }

        $QStaff = new Application_Model_Staff();

        foreach ($ids as $id) {
            $staff_check = $QStaff->find($id);
            $staff_check = $staff_check->current();

            if (!$staff_check) {
                exit( json_encode( array(
                    'code' => '-2',
                    'error' => 'Invalid Staff ['.$id.']',
                    ) ) );
            }
        }

        foreach ($ids as $id) {
            $data = array('company_id' => $company_id);
            $where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
            $QStaff->update($data, $where);
        }

        exit( json_encode( array(
            'code' => '1',
            'message' => 'Success',
            'company_name' => $company_check['name'],
            ) ) );
    }

    public function loadTeamAction()
    {
        $department_id    = $this->getRequest()->getParam('department_id');

        $QTeam = new Application_Model_Team();
        if (is_array($department_id) && count($department_id) > 0)
            $where = $QTeam->getAdapter()->quoteInto('parent_id IN (?)', $department_id);
        else
            $where = $QTeam->getAdapter()->quoteInto('1=0', 1);

        echo json_encode( $QTeam->fetchAll($where, 'name')->toArray() );
        exit;
    }

    public function loadTitleAction()
    {
        $team_id    = $this->getRequest()->getParam('team_id');

        $QTeam = new Application_Model_Team();
        if (is_array($team_id) && count($team_id) > 0)
            $where = $QTeam->getAdapter()->quoteInto('parent_id IN (?)', $team_id);
        else
            $where = $QTeam->getAdapter()->quoteInto('1=0', 1);

        echo json_encode( $QTeam->fetchAll($where, 'name')->toArray() );
        exit;
    }

    public function getDistrictAction()
    {
        $this->_helper->layout->disableLayout();

        if (!$this->getRequest()->isXmlHttpRequest()) exit;

        $name = $this->getRequest()->getParam('name');

        if (!$name) return;

        $QRegion = new Application_Model_RegionalMarket();
        $where = array();
        $where[] = $QRegion->getAdapter()->quoteInto('parent <> 0 OR parent IS NULL', 1);
        $where[] = $QRegion->getAdapter()->quoteInto('name LIKE ?', '%'.$name.'%')
        . " OR " . $QRegion->getAdapter()->quoteInto('name LIKE ?', '%'.My_String::khongdau($name).'%');

        $this->view->regions = $QRegion->fetchAll($where, 'name');
    }

    public function getDistrictCbbAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (!$this->getRequest()->isXmlHttpRequest()) exit;

        $region = $this->getRequest()->getParam('region');

        if (!$region) {
            echo json_encode(array());
            exit;
        }

        $QRegion = new Application_Model_RegionalMarket();
        $where = array();
        $where[] = $QRegion->getAdapter()->quoteInto('parent <> 0 OR parent IS NULL', 1);

        if (is_array($region) && count($region))
            $where[] = $QRegion->getAdapter()->quoteInto('parent IN (?)', $region);
        else
            $where[] = $QRegion->getAdapter()->quoteInto('parent = ?', $region);

        echo json_encode( $QRegion->fetchAll($where, 'name')->toArray() );
        exit;
    }

    public function getSubDistrictCbbAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if (!$this->getRequest()->isXmlHttpRequest()) exit;

        $region = $this->getRequest()->getParam('region');

        if (!$region) {
            echo json_encode(array());
            exit;
        }

        $QSubDistrict = new Application_Model_SubDistrict();
        $where = array();
        //$where[] = $QSubDistrict->getAdapter()->quoteInto('parent <> 0 OR parent IS NULL', 1);

        if (is_array($region) && count($region))
            $where[] = $QSubDistrict->getAdapter()->quoteInto('district IN (?)', $region);
        else
            $where[] = $QSubDistrict->getAdapter()->quoteInto('district = ?', $region);

        echo json_encode( $QSubDistrict->fetchAll($where, 'name')->toArray() );
        exit;
    }

    public function searchStaffByKeywordAction()
    {
        $this->_helper->layout->disableLayout();

        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit;
        }

        $keyword = $this->getRequest()->getParam('keyword');

        if (!$keyword) {
            return;
        }

        $keyword = trim($keyword);

        $QStaff = new Application_Model_Staff();
        $where = $QStaff->getAdapter()->quoteInto('code = ?', $keyword)
                . " OR "
                . $QStaff->getAdapter()->quoteInto('email LIKE ?', str_replace(EMAIL_SUFFIX, '', $keyword).EMAIL_SUFFIX);

        $this->view->staffs = $QStaff->fetchAll($where, 'firstname');
    }

    public function assignStaffAsCasualAction()
    {
        $this->_helper->layout->disableLayout();

        if (!$this->getRequest()->isXmlHttpRequest())
            exit(json_encode(array(
                'result' => -100,
                'message' => 'Only accept AJAX request.',
                )));

        $staff_id = $this->getRequest()->getParam('staff_id');
        $area_id = $this->getRequest()->getParam('area_id');
        $note = $this->getRequest()->getParam('note');

        if (!$staff_id || !$area_id)
            exit(json_encode(array(
                'result' => -1,
                'message' => 'Invalid params.',
                )));

        $QStaff = new Application_Model_Staff();
        $staff = $QStaff->find($staff_id);
        $staff = $staff->current();

        if (!$staff) {
            exit(json_encode(array(
                'result' => -2,
                'message' => 'Invalid staff.',
                )));
        }

        $QArea = new Application_Model_Area();
        $area = $QArea->find($area_id);
        $area = $area->current();

        if (!$area) {
            exit(json_encode(array(
                'result' => -2,
                'message' => 'Invalid area.',
                )));
        }

        $QCasual = new Application_Model_CasualWorker();
        $where = $QCasual->getAdapter()->quoteInto('staff_id = ?', $staff_id);

        $casual = $QCasual->fetchRow($where);

        if ($casual) {
            $areas = $QArea->get_cache();

            exit(json_encode(array(
                'result' => -3,
                'message' => 'This staff has already assigned as casual worker for area ['.(isset($areas[$casual['area_id']]) ? $areas[$casual['area_id']] : '').']',
                )));
        }

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (!$userStorage) {
            exit(json_encode(array(
                'result' => -10,
                'message' => 'Invalid user.',
                )));
        }

        $data = array(
            'staff_id'    => $staff_id,
            'area_id'     => $area_id,
            'assigned_at' => time(),
            'assigned_by' => $userStorage->id,
            'note'        => trim($note),
            'status'      => 1,
            );

        $id = $QCasual->insert($data);

        if ($id) {
            $cache = Zend_Registry::get('cache');
            $cache->remove('casual_worker_cache');

            exit(json_encode(array(
                'result' => 1,
                'message' => 'Assigned successfully.',
                )));
        } else {
            exit(json_encode(array(
                'result' => -99,
                'message' => 'Insert failed.',
                )));
        }
    }

    public function switchCasualStatusAction()
    {
        $this->_helper->layout->disableLayout();

        if (!$this->getRequest()->isXmlHttpRequest())
            exit(json_encode(array(
                'result' => -100,
                'message' => 'Only accept AJAX request.',
                )));

        $id = $this->getRequest()->getParam('id');

        if (!$id)
            exit(json_encode(array(
                'result' => -1,
                'message' => 'Invalid params.',
                )));

        $QCasual = new Application_Model_CasualWorker();
        $casual = $QCasual->find($id);
        $casual = $casual->current();

        if (!$casual)
            exit(json_encode(array(
                'result' => -2,
                'message' => 'Invalid ID.',
                )));

        $where = $QCasual->getAdapter()->quoteInto('id = ?', $id);
        $data = array('status' => ($casual['status'] == 1 ? 0 : 1));
        $QCasual->update($data, $where);

        $cache = Zend_Registry::get('cache');
        $cache->remove('casual_worker_cache');

        exit(json_encode(array(
                'result' => 1,
                'status' => !$casual['status'],
                'message' => 'Success.',
                )));
    }

    public function checkImeiTimingAction()
    {
        $timing_sales_id = $this->getRequest()->getParam('timing_sales_id');
        $value = $this->getRequest()->getParam('value');
        $cat_id = $this->getRequest()->getParam('cat_id');

        try {
            if (!$value) throw new Exception("Invalid IMEI");

            if (!$cat_id) throw new Exception("Invalid Category");

            $value = trim($value);

            if (!strlen($value))  throw new Exception("Invalid IMEI");

        } catch (Exception $e) {

            exit ( json_encode( array(
                'valid' => false,
                'message' => $e->getMessage(),
             ) ) );
        }

        $valid = false;
        $message = 'IMEI không tồn tại';

        try {
            switch ($cat_id) {
                case DIGITAL_CAT_ID:
                    $return = $this->check_digital_imei($value, $timing_sales_id, $valid, $message);
                    break;
                case PHONE_CAT_ID:
                    $return = $this->check_phone_imei($value, $timing_sales_id, $valid, $message);
                    break;
                case ACCESS_CAT_ID:
                    $return = $this->check_access_imei($value, $timing_sales_id, $valid, $message);
                    break;

                default:
                    $message = 'Invalid Category';
                    $return = 0;
                    break;
            }

            if ($cat_id && $value) {
                $QCheckImeiLog = new Application_Model_CheckImeiLog();
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                //
                //todo log
                $QCheckImeiLog->insert( array(
                    'result'     => $return,
                    'imei'       => $value,
                    'user_id'    => $userStorage->id,
                    'ip_address' => $ip,
                    'time'       => date('Y-m-d H:i:s'),
                ) );
            }

        } catch (Exception $e) {
            $message = 'Lỗi không xác định.';
        }

        exit ( json_encode( array(
            'valid' => $valid,
            'message' => $message,
         ) ) );
    }

    private function check_digital_imei($imei, $timing_sales_id, &$valid, &$message)
    {
        $valid = false;
        $message = 'Wrong Category';
        $return = 0;
        $info = array();

        $QImei = new Application_Model_GoodSn();
        $return = $QImei->checkImei($imei, $timing_sales_id, $info);


        return $return;
    }

    private function check_phone_imei($imei, $timing_sales_id, &$valid, &$message)
    {
        $valid = false;
        $info = array();

        $QImei = new Application_Model_WebImei();
        $return = $QImei->checkImei($imei, $timing_sales_id, $info);

        if ($return==1){
            $message = "Bạn đã báo cáo IMEI này rồi, vào lúc [" . date('d/m/Y H:i:s', strtotime($info['date'])) . "] Tại cửa hàng [".$info['store']."]";

        } else if ($return==2){
            $message = "IMEI không tồn tại. Vui lòng xem kỹ lại IMEI hoặc liên hệ phòng Technology.";

        } else if ($return==3 || $return==5){
            $message = 'IMEI '.$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']
                                <a href="#" data-timing-sales-id="'.$info['timing_sales_id'].'" data-imei="'.$imei.'"
                                data-case="IMEI '.$imei.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']"
                                data-checksum="'.sha1(md5($imei).$info['timing_sales_id']).'"
                                data-staff-first="'.$info['staff_id'].'"
                                class="send_notify">Bấm vào đây</a> để gửi yêu cầu xử lý.';

        } elseif ($return == 4) {
            $message = 'IMEI này đã được bán cách đây hơn 01 tháng. Công ty không tính doanh số đối với các máy đã bán ra thị trường hơn 01 tháng. Vui lòng liên hệ ASM nếu có thắc mắc.';

        } elseif ($return == 6) {
            $message = 'IMEI này của máy tặng khách hàng, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.';

        } elseif ($return == 7) {
            $message = 'IMEI này của máy demo, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.';

        }  elseif ($return == 8) {
            $message = 'IMEI này của máy xuất cho nhân viên, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.';

        } elseif ($return == 9) {
            $message = 'IMEI này của máy mượn, thuộc diện không tính KPI. Vui lòng liên hệ ASM nếu có thắc mắc.';

        } else {
            $valid = true;
            $message = '';
        }

        return $return;
    }

    private function check_access_imei($imei, $timing_sales_id, &$valid, &$message)
    {
        $valid = false;

        return $return;
    }

    public function staffEduSaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (! $this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id             = $this->getRequest()->getParam('id');
            $staff_id       = $this->getRequest()->getParam('staff_id');
            $level          = $this->getRequest()->getParam('level');
            $school         = $this->getRequest()->getParam('school');
            $graduated_year = $this->getRequest()->getParam('graduated_year');
            $grade          = $this->getRequest()->getParam('grade');
            $field_of_study = $this->getRequest()->getParam('field_of_study');
            $mode_of_study  = $this->getRequest()->getParam('mode_of_study');

            if (intval($staff_id)) {
                $QStaffEducation = new Application_Model_StaffEducation();
                $data = array(
                    'staff_id'       => intval( $staff_id ),
                    'level'          => My_String::trim( $level ),
                    'school'         => My_String::trim( $school ),
                    'graduated_year' => intval( $graduated_year ),
                    'grade'          => My_String::trim( $grade ),
                    'field_of_study' => My_String::trim( $field_of_study ),
                    'mode_of_study'  => My_String::trim( $mode_of_study ),
                    );

                if ($id) {
                    $where = $QStaffEducation->getAdapter()->quoteInto('id = ?', $id);
                    $QStaffEducation->update($data, $where);
                } else {
                    $id = $QStaffEducation->insert($data);
                }

                if ($id)
                    exit(json_encode(array(
                        'result' => 1,
                        'id' => $id,
                        'message' => 'Success',
                        )));
                else
                    throw new Exception("Insert failed");

            } else {
                throw new Exception("All fields are required");
            }
        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffExpSaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (! $this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id                 = $this->getRequest()->getParam('id');
            $staff_id           = $this->getRequest()->getParam('staff_id');
            $company_name       = $this->getRequest()->getParam('company_name');
            $job_position       = $this->getRequest()->getParam('job_position');
            $from_date          = $this->getRequest()->getParam('from_date');
            $to_date            = $this->getRequest()->getParam('to_date');
            $reason_for_leaving = $this->getRequest()->getParam('reason_for_leaving');

            if (intval( $staff_id )) {
                $QStaffExperience = new Application_Model_StaffExperience();
                $from = explode('/', trim( $from_date ) );
                $to = explode('/', trim( $to_date ) );

                if ( !is_array($from) || count($from) != 3 || !is_array($to) || count($to) != 3 )
                    throw new Exception("Invalid date");

                $from = $from[2] .'-'.$from[1].'-'.$from[0];
                $to = $to[2] .'-'.$to[1].'-'.$to[0];

                if ( ! strtotime($from) || ! strtotime($to) )
                    throw new Exception("Invalid date");

                $data = array(
                    'staff_id'           => intval( $staff_id ),
                    'company_name'       => My_String::trim( $company_name ),
                    'job_position'       => My_String::trim( $job_position ),
                    'from_date'          => $from,
                    'to_date'            => $to,
                    'reason_for_leaving' => My_String::trim( $reason_for_leaving ),
                    );

                if ($id) {
                    $where = $QStaffExperience->getAdapter()->quoteInto('id = ?', $id);
                    $QStaffExperience->update($data, $where);
                } else {
                    $id = $QStaffExperience->insert($data);
                }

                if ($id)
                    exit(json_encode(array(
                        'result' => 1,
                        'id' => $id,
                        'message' => 'Success',
                        )));
                else
                    throw new Exception("Insert failed");

            } else {
                throw new Exception("All fields are required");
            }
        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffRelativeSaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (! $this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id            = $this->getRequest()->getParam('id');
            $staff_id      = $this->getRequest()->getParam('staff_id');
            $relative_type = $this->getRequest()->getParam('relative_type');
            $gender        = $this->getRequest()->getParam('gender');
            $full_name     = $this->getRequest()->getParam('full_name');
            $birth_year    = $this->getRequest()->getParam('birth_year');
            $job           = $this->getRequest()->getParam('job');
            $work_place    = $this->getRequest()->getParam('work_place');

            if (intval( $staff_id )) {
                $QStaffRelative = new Application_Model_StaffRelative();

                $data = array(
                    'staff_id'      => intval( $staff_id ),
                    'relative_type' => intval( $relative_type ),
                    'gender'        => intval($gender),
                    'full_name'     => My_String::trim( $full_name ),
                    'birth_year'    => intval($birth_year),
                    'job'           => My_String::trim( $job ),
                    'work_place'    => My_String::trim( $work_place ),
                    );

                if ($id) {
                    $where = $QStaffRelative->getAdapter()->quoteInto('id = ?', $id);
                    $QStaffRelative->update($data, $where);
                } else {
                    $id = $QStaffRelative->insert($data);
                }

                if ($id)
                    exit(json_encode(array(
                        'result' => 1,
                        'id' => $id,
                        'message' => 'Success',
                        )));
                else
                    throw new Exception("Insert failed");

            } else {
                throw new Exception("All fields are required");
            }
        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffRelativeDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (!$this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id = $this->getRequest()->getParam('id');

            $QStaffRelative = new Application_Model_StaffRelative();
            $where = $QStaffRelative->getAdapter()->quoteInto('id = ?', $id);
            $QStaffRelative->delete($where);

            exit(json_encode(array(
                    'result' => 1,
                    'message' => 'Success',
                    )));

        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffEducationDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (!$this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id = $this->getRequest()->getParam('id');

            $QStaffEducation = new Application_Model_StaffEducation();
            $where = $QStaffEducation->getAdapter()->quoteInto('id = ?', $id);
            $QStaffEducation->delete($where);

            exit(json_encode(array(
                    'result' => 1,
                    'message' => 'Success',
                    )));

        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffExperienceDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (!$this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id = $this->getRequest()->getParam('id');

            $QStaffExperience = new Application_Model_StaffExperience();
            $where = $QStaffExperience->getAdapter()->quoteInto('id = ?', $id);
            $QStaffExperience->delete($where);

            exit(json_encode(array(
                    'result' => 1,
                    'message' => 'Success',
                    )));

        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function staffTransferSaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        try {
            if (!$this->getRequest()->isXmlHttpRequest())
                throw new Exception("Only accept AJAX requests");

            $id = $this->getRequest()->getParam('id');

            $staff_id  = $this->getRequest()->getParam('staff_id');
            $type      = $this->getRequest()->getParam('type');
            $current   = $this->getRequest()->getParam('current');
            $from_date = $this->getRequest()->getParam('from_date');
            $to_date   = $this->getRequest()->getParam('to_date');

            if ($staff_id && $type) {
                $QStaff = new Application_Model_Staff();
                $where = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
                $staff_check = $QStaff->fetchRow($where);

                if (!$staff_check) throw new Exception("Invalid staff");

                // validate date format
                $from = explode('/', $from_date);

                if (!is_array($from) || count($from) != 3)
                    throw new Exception("Invalid From date");

                $from = $from[2].'-'.$from[1].'-'.$from[0];

                if (!strtotime($from))
                    throw new Exception("Invalid From date");

                $data = array();

                switch ($type) {
                    case My_Staff_Info_Type::Region:
                        $data['regional_market'] = $current;
                        break;
                    case My_Staff_Info_Type::Department:
                        $data['department'] = $current;
                        break;
                    case My_Staff_Info_Type::Team:
                        $data['team'] = $current;
                        break;
                    case My_Staff_Info_Type::Group:
                        $data['group_id'] = $current;
                        break;
                    case My_Staff_Info_Type::Status:
                        $data['status'] = $current;
                        break;
                    case My_Staff_Info_Type::Company:
                        $data['company_id'] = $current;
                        break;

                    default:
                        throw new Exception("Invalid type");
                        break;
                }

                // validate date range
                if (!Log::check_date_range($staff_id, $type, strtotime($from)))
                    throw new Exception("Invalid Date range");

                $QStaff->update($data, $where);

                $staff_after = $QStaff->fetchRow($where);

                Log::w($staff_check->toArray(), $staff_after->toArray(), $staff_id,
                        LogGroup::Staff, LogType::Update, $from);
            } else {
                throw new Exception("Missing params");
            }

            exit(json_encode(array(
                    'result' => 1,
                    'message' => 'Success',
                    )));

        } catch (Exception $e) {
            exit(json_encode(array(
                'result' => 0,
                'error' => $e->getMessage(),
                )));
        }
    }

    public function checkStoreNameAction()
    {
        $this->_helper->layout->disableLayout();

        $this->view->id = $this->getRequest()->getParam('id');
        $store_name = $this->getRequest()->getParam('store_name');
        $QStore = new Application_Model_Store();
        $this->view->result = $QStore->compare($store_name);
    }

    public function checkIdNumberAction()
    {
        $this->_helper->layout->disableLayout();

        $id_number = $this->getRequest()->getParam('id_number');
        $id = $this->getRequest()->getParam('id');

        if (!$id_number || empty($id_number)) return;

        $QStaff = new Application_Model_Staff();
        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('TRIM(ID_number) LIKE ?', trim($id_number));

        if ($id && intval($id))
            $where[] = $QStaff->getAdapter()->quoteInto('id <> ?', intval($id));

        $staff = $QStaff->fetchRow($where);

        if (!$staff) return;

        $this->view->id = $staff['id'];
        $this->view->firstname = $staff['firstname'];
        $this->view->lastname = $staff['lastname'];
        $this->view->email = $staff['email'];
        $this->view->staff_code = $staff['code'];
    }

    public function getTransferInfoAction(){
        /**
         * @hac.tran
         * @description: get transfer and staff log detail
         */
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);
        $transfer_id = $this->getRequest()->getParam('transfer_id');
        $db          = Zend_Registry::get('db');
        $arrCols = array(
            'from_date'     =>'p.from_date',
            'info_type'     =>'sl.info_type',
            'current_value' =>'sl.current_value',
        );
        $select = $db->select()
                ->from(array('p'=>'staff_transfer'),$arrCols)
                ->join(array('sl'=>'staff_log_detail'),'p.id = sl.transfer_id',array())
                ->where('p.id = ?',$transfer_id)
        ;

        $result = $db->fetchAll($select);
        if(!$result){
            exit(json_encode(array(
                'code'=>1,
                'message'=>'load data fail'
            )));
        }

        $data = array();
        foreach($result as $item){
            if($item['info_type'] == My_Staff_Info_Type::Company){
                $data['company'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Area){
                $data['area'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Region){
                $data['region'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Department){
                $data['department'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Team){
                $data['team'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Group){
                $data['group'] = $item['current_value'];
            }elseif($item['info_type'] == My_Staff_Info_Type::Title){
                $data['title'] = $item['current_value'];
            }
            $data['from_date'] = date('Y-m-d',strtotime($item['from_date']));
        }

        exit(json_encode(array(
            'code'  => 2,
            'result'=> $data
        ))) ;
    }

    public function loadTableTransferAction(){
        /**
         * @hac.tran
         */
        $this->_helper->layout()->disableLayout(true);

        // get Transfer
        $staff_id = $this->getRequest()->getParam('staff_id');
        $db = Zend_Registry::get('db');
        $arrCols = array(
            'note'=>'s.note',
            'sl.transfer_id',
            'staff_id'=> 's.staff_id',
            'sl.object',
            's.from_date',
            'info_types'     => new Zend_Db_Expr('GROUP_CONCAT(sl.info_type)'),
            'current_values' => new Zend_Db_Expr('GROUP_CONCAT(sl.current_value)'),
        );
        $select = $db->select()
            ->from(array('s'=>'staff_transfer'),$arrCols)
            ->joinLeft(array('sl'=>'staff_log_detail'),'s.id = sl.transfer_id',array())
            ->where("s.staff_id = ?",$staff_id)
            ->group('sl.transfer_id')
            ->order('s.created_at DESC')
        ;

        $result = $db->fetchAll($select);
        $this->view->resultStaffTransfer = $result;
        $this->view->staff_id = $staff_id;
    }

    public function loadPrintTransferAction()
    {
        $this->_helper->layout()->disableLayout(true);

        $db = Zend_Registry::get('db');
        $col_needed = array(
            'id_transfer' => 's.id',
            'note'=>'s.note',
            'sl.transfer_id',
            'staff_id'=> 's.staff_id',
            'sl.object',
            's.from_date',
            'info_types'     => new Zend_Db_Expr('GROUP_CONCAT(sl.info_type)'),
            'current_values' => new Zend_Db_Expr('GROUP_CONCAT(sl.current_value)'),
        );
        $selectStaffTransfer = $db->select()
            ->from(array('s'=>'staff_transfer'),$col_needed)
            ->joinLeft(array('sl'=>'staff_log_detail'),'s.id = sl.transfer_id',array())
            ->joinLeft(array('st'=>'staff'),'s.staff_id = st.id' , array('st.id' , 'st.contract_term' , 'st.joined_at' , 'staff_name' => 'CONCAT(st.firstname,  " " , st.lastname)'))
            ->where("s.from_date >=  ?", date('2015-05-01'))
            ->where("s.staff_id <= ?",date('Y-m-d'))
            ->where("s.printed_at is null", null)
            ->group('sl.transfer_id')
            ->order('s.created_at DESC')
        ;


        $result = $db->fetchAll($selectStaffTransfer);
        $this->view->data = $result;

    }

    public static function getOwnStores($staff_id)
    {
        $this->_helper->layout->disableLayout();

        $QLog     = new Application_Model_StoreStaffLog();
        $page     = $this->getRequest()->getParam('page', 1);
        $limit    = LIMITATION;
        $total    = 0;
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        
        $staff_id = $this->getRequest()->getParam('staff_id', $userStorage->id);
        $from     = $this->getRequest()->getParam('from');
        $to       = $this->getRequest()->getParam('to');

        $params = array(
            'staff_id' => $staff_id,
            'email'    => $email,
            'from'     => $from,
            'to'       => $to,
        );

        $this->view->logs = $QLog->fetchPagination($page, $limit, $total, $params);
    }

    /**
     * Dành cho insurance
     * [getHospitalAction description]
     * @return [type] [description]
     */
    public function getHospitalAction(){
        $province_id = $this->getRequest()->getParam('province_id');
        $QHospital   = new Application_Model_Hospital();
        $result      = $QHospital->get_all(array('province_id'=>$province_id));
        $this->_helper->json->sendJson($result);
    }


    public function loadRetailerInfoAction() {

        $d_id = $this->getRequest()->getParam('d_id');

        $QDistributor = new Application_Model_Distributor();
        $where = $QDistributor->getAdapter()->quoteInto('id = ?', $d_id);

        echo json_encode($QDistributor->fetchAll($where)->toArray());
        exit;

    }

    public function loadCompetitorProductAction()
    {
        $brand_id = $this->getRequest()->getParam('brand_id');

        $QCompetitorProduct = new Application_Model_CompetitorProduct();
        if (is_array($brand_id) && $brand_id)
            $where = $QCompetitorProduct->getAdapter()->quoteInto('brand_id IN (?)', $brand_id);
        else
            $where = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', $brand_id);

        echo json_encode($QCompetitorProduct->fetchAll($where, 'name')->toArray());
        exit;
    }

    public function getStaffForUpdateAction() {
    
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('s' => 'staff'),array('s.code','s.firstname','s.lastname','s.phone_number','s.group_id'))
            ->join(array('rm'=> 'regional_market'), 's.regional_market = rm.id', array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array('area_id' => 'a.id', 'area_name' => 'a.name'))
            ->where("s.group_id IN (?)",[9,5,16,28,35,37,38])
            ->where('s.off_date is NULL')
            ->where('s.status = ?',1);
            
        // echo $select;
        $stock_shop_list = $db->fetchAll($select);
        print_r(json_encode($stock_shop_list));
    }

    public function apiGetAllAreaGrandBkkAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = array();
    
        $QArea = new Application_Model_Area();
        $result = $QArea->getAllAreaGrandBKK($params);

        echo json_encode($result);
        exit;
    }

}