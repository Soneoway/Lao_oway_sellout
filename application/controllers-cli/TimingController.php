<?php
/**
*
*/
class TimingController extends My_Application_Controller_Cli
{
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
        set_time_limit(0);
        ini_set('memory_limit', '-1');

	}

    public function timingExpired()
    {
        $db = Zend_Registry::get('db');

        $QTime = new Application_Model_Time();
        $limited_time_as_today = $QTime->getLimitedTime();
        $QStaff = new Application_Model_Staff();
        $staff_cache = $QStaff->get_cache();
        $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
        $QLog = new Application_Model_Log();
        $category_id = 4;
        // tìm các chấm công chưa chấm
        $sql = sprintf("SELECT
                            p.staff_id,
                            CONCAT(s.`lastname`, s.`firstname` ,  ' '),
                            s.title,
                            select_limited_timing(s.id) AS limited
                        FROM
                            time AS p
                        INNER JOIN staff AS s ON p.staff_id = s.id
                        WHERE
                            (
                                p.created_at BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01')
                                AND NOW()
                            )
                        AND(
                            p.created_at BETWEEN DATE_FORMAT(NOW(), '%Y-%m-%d')
                            AND NOW()
                        )
				");

        $data = $db->fetchAll($sql);

        foreach($data as $k=>$v)
        {
            if($v['limited'] <= 22)
            {

                $where = array();
                $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ? ' , $v['staff_id']);
                $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
                $result = $QTimeStaffExpired->fetchRow($where);

                if($result)
                {
                    break;
                }

                $title = sprintf("Tài khoản chấm công của bạn %s đã bị khóa", $staff_cache[ $v['staff_id'] ]);
                $content = sprintf("<p>Chào bạn %s,</p>
                    <p>Theo chính sách của Công ty, Bạn đã không chấm công ba ngày , hệ thống sẽ khóa tài khoản của bạn.</p>
                    <p>Để sử dụng lại tài khoản vui lòng liên hệ bộ phận nhân sự: </p> " , $staff_cache[ $v['staff_id'] ] );

                My_Notification::add(
                    $title,
                    $content,
                    $category_id,
                    array(
                        'staff' => $v['staff_id'],
                        'from'  => date('d/m/Y H:i:s'),
                        'to'    => (new DateTime('now'))
                            ->add(new DateInterval('P10D'))
                            ->format('d/m/Y H:i:s'),
                    ),
                    1,
                    My_Notification_Type::SystemCreate
                );
                //them nhan vien do vo bang LOG
                $data = array(
                    'staff_id' => $v['staff_id'],
                    'locked_at' => date('Y-m-d h:i:s')
                );

                $QTimeStaffExpired->insert($data);
            }
        }
    }

	/**
	 * Xóa các IMEI trong bảng chấm công
	 * 		3 ngày sau khi chấm công chưa active
	 * 		hoặc active trước khi chấm công hơn 3 ngày
	 * @return
	 */
	public function removeExpiredImeiAction()
	{
		$delete_from = "2015-04-07 00:00:00";
		$category_id = 4;
		$timing_day = 7;
		// $dead_day = 15;

		$QStore      = new Application_Model_Store();
		$QStaff      = new Application_Model_Staff();
		$QGood       = new Application_Model_Good();
		$QGoodColor  = new Application_Model_GoodColor();

		$store_cache = $QStore->get_cache();
		$staff_cache = $QStaff->get_cache();
		$good_cache  = $QGood->get_cache();
		$color_cache = $QGoodColor->get_cache();

		$db = Zend_Registry::get('db');

		// tìm các chấm công sẽ xóa
		$sql = sprintf("SELECT
					timing.staff_id,
					timing.`from`,
					timing.store,
					timing_sale.product_id,
					timing_sale.model_id,
					timing_sale.imei,
					warehouse_new.imei.activated_date
				FROM
					timing,
					timing_sale,
					warehouse_new.imei
				WHERE
					timing.id = timing_sale.timing_id
				AND imei.imei_sn = timing_sale.imei
				AND (
						(
							imei.activated_date IS NULL
							OR imei.activated_date = 0
							OR imei.activated_date LIKE ''
					) -- chưa active
					AND DATEDIFF(NOW(), timing.`from`) > %d -- từ ngày kiểm tra về ngày chấm công hơn $timing_day ngày
				)
				AND timing.`from` >= '%s' -- chỉ xóa IMEI của các báo cáo sau ngày ra thông báo
				AND (timing_sale.reimport = 0 OR timing_sale.reimport IS NULL); -- không xóa IMEI đã tái nhập
				", $timing_day, $delete_from);

		$timings = $db->query($sql);
		$timing_arr = array();

		// gộp các IMEI theo staff
		foreach ($timings as $_key => $_timing) {
			if (! isset( $staff_cache[ $_timing['staff_id'] ] ) ) continue;

			if (!isset($timing_arr[ $_timing['staff_id'] ]))
				$timing_arr[ $_timing['staff_id'] ] = array();

			$timing_arr[ $_timing['staff_id'] ][] = array(
				'imei'       => $_timing['imei'],
				'good_id'    => $_timing['product_id'],
				'good_color' => $_timing['model_id'],
				'store_id'   => $_timing['store'],
				'activated_date' => isset($_timing['activated_date']) ? date('d/m/Y', strtotime($_timing['activated_date'])) : 0,
				'date'       => date( 'd/m/Y', strtotime( $_timing['from'] ) ),
			);
		} // END foreach: gộp các IMEI theo staff

		unset($sql);
		unset($timings);

		// tạo notification
		$title = sprintf("Danh sách IMEI bị xóa ngày %s", date("d/m/Y"));

		foreach ($timing_arr as $_staff_id => $_timings) {
			if (! count($_timings) ) continue;

			$content = sprintf("<p>Chào bạn %s,</p>
<p>Theo chính sách của Công ty, từ ngày 07/04/2015, những IMEI đã chấm công mà sau 07 ngày chưa active đều không được tính KPI và sẽ được gỡ khỏi danh sách các máy chấm công.</p>
<p>Sau đây là danh sách các IMEI bạn đã chấm công bị gỡ bỏ trong ngày %s:</p>
<ul>",
				$staff_cache[ $_staff_id ],
				date("d/m/Y")
			);

			// danh sách IMEI bị xóa
			foreach ($_timings as $_key => $_timing) {
				$content .= sprintf("<li>IMEI: <strong>%s</strong>;
<br />Model: %s/%s;
<br />Ngày bán: %s;
<br />Ngày active: %s;
<br />Cửa hàng: %s;</li>",
					$_timing['imei'],
					isset( $good_cache[ $_timing['good_id'] ] ) ? $good_cache[ $_timing['good_id'] ] : '#',
					isset( $color_cache[ $_timing['good_color'] ] ) ? $color_cache[ $_timing['good_color'] ] : '#',
					$_timing['date'],
					$_timing['activated_date'] ? $_timing['activated_date'] : 'Chưa active',
					isset( $store_cache[ $_timing['store_id'] ] ) ? $store_cache[ $_timing['store_id'] ] : '#'
				);
			} // END foreach: danh sách IMEI bị xóa

			$content .= "</ul>";

			My_Notification::add(
			    $title,
			    $content,
			    $category_id,
			    array(
					'staff' => $_staff_id,
					'from'  => date('d/m/Y H:i:s'),
					'to'    => (new DateTime('now'))
		                ->add(new DateInterval('P10D'))
		                ->format('d/m/Y H:i:s'),
			    ),
			    1,
			    My_Notification_Type::SystemCreate
			);

			unset($content);
		} // END foreach: tạo notification

		unset($timing_arr);

		$sql = sprintf("REPLACE INTO timing_sale_expired
				SELECT
					timing_sale.*
				FROM
					timing,
					timing_sale,
					warehouse_new.imei
				WHERE
					timing.id = timing_sale.timing_id
				AND imei.imei_sn = timing_sale.imei
				AND (
						(
							imei.activated_date IS NULL
							OR imei.activated_date = 0
							OR imei.activated_date LIKE ''
					) -- chưa active
					AND DATEDIFF(NOW(), timing.`from`) > %d -- từ ngày kiểm tra về ngày chấm công hơn $timing_day ngày
				)
				AND timing.`from` >= '%s' -- chỉ xóa IMEI của các báo cáo sau ngày ra thông báo
				AND (timing_sale.reimport = 0 OR timing_sale.reimport IS NULL); -- không xóa IMEI đã tái nhập
				", $timing_day, $delete_from);

		try {
			$res = $db->query($sql);
			$n = $res->rowCount();
			echo sprintf("%s\t- Moved %d IMEIs to trash\r\n", date('Y-m-d H:i:s'), $n);

			unset($n);
			unset($res);
			unset($sql);
		} catch (Exception $e) {}

		$sql = sprintf("DELETE e
				FROM
					timing_sale e
				INNER JOIN timing ON e.timing_id = timing.id
				INNER JOIN warehouse_new.imei ON warehouse_new.imei.imei_sn = e.imei
				WHERE (
					(
						imei.activated_date IS NULL
						OR imei.activated_date = 0
						OR imei.activated_date LIKE ''
					)
					AND DATEDIFF(NOW(), timing.`from`) > %d
				)
				AND timing.`from` >= '%s'
				AND (e.reimport = 0 OR e.reimport IS NULL); -- không xóa IMEI đã tái nhập
				", $timing_day, $delete_from);

		try {
			$res = $db->query($sql);
			$n = $res->rowCount();
			echo sprintf("\t\t\t- Deleted %d IMEIs from timing list\r\n", $n);

			unset($n);
			unset($res);
			unset($sql);
		} catch (Exception $e) {}

		unset($db);

		exit;
	}

	public function importAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		////////////////////////////////////////////////

		$file_path = $this->getRequest()->getParam('file');

		if (!$file_path) exit("File path is required.\r\n");

		$file_path = trim($file_path);

		if (!file_exists($file_path)) exit("File not exists.\r\n");

		//read file
		include 'PHPExcel/IOFactory.php';

		//  Read your Excel workbook
		try {
		    $inputFileType = PHPExcel_IOFactory::identify($file_path);
		    $path_info = pathinfo($file_path);
		    $extension = $path_info['extension'];
		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		    $objPHPExcel = $objReader->load($file_path);
		} catch (Exception $e) {
		    exit($e->getMessage()."\r\n");
		}

		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();

		/**
		 * Danh sách các đơn hàng lỗi
		 * @var array
		 */
		define("START_ROW", 2);
		define("IMEI_COL", 3);
		$error_list = array();
		$success_list = array();
		$order_rows = $highestRow - START_ROW;

		$header = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
		            NULL, TRUE, FALSE);
		$header = isset($header[0]) ? $header[0] : array();

		$db = Zend_Registry::get('db');
		$QTimingSale = new Application_Model_TimingSale();
		$QTimingSaleExpired = new Application_Model_TimingSaleExpired();

		for ($row = START_ROW; $row <= $highestRow; $row++) {

		    My_Cli_Util::show_status($row, $order_rows);

		    try {
		        // 
		        $range = 'A' . $row . ':' . $highestColumn . $row;
		        $sheet
		            ->getStyle($range)
		            ->getNumberFormat()
		            ->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );

	            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
		            NULL, TRUE, FALSE);

		        // validate data
		        $rowData = isset($rowData[0]) ? $rowData[0] : array();

		        $rowData[IMEI_COL] = preg_replace( '/[^0-9]/', '', trim($rowData[IMEI_COL]) );

		        if (empty($rowData[IMEI_COL])) throw new Exception("Empty IMEI, row: ".$row);

		        // kiểm tra IMEI chưa nhập nảy giờ
		        if (in_array($rowData[IMEI_COL], $success_list))
		        	throw new Exception("Duplicated IMEI, row: ".$row);
		        $where = $QTimingSale->getAdapter()->quoteInto('imei = ?', $rowData[IMEI_COL]);
		        $timing_sale = $QTimingSale->fetchRow($where);
		        if ($timing_sale) throw new Exception("IMEI exists, row: ". $row);

		        $where = $QTimingSaleExpired->getAdapter()->quoteInto('imei = ?', $rowData[IMEI_COL]);
		        $timing_sale_expired = $QTimingSaleExpired->fetchRow($where);
		        if (!$timing_sale_expired) throw new Exception("IMEI ".$rowData[IMEI_COL]." not exists, row: ".$row);
		        
		        try {
		        	$sql = sprintf("REPLACE INTO timing_sale SELECT * FROM timing_sale_expired WHERE imei=%s", $rowData[IMEI_COL]);
		        	$db->query($sql);

		        	$sql = sprintf("DELETE FROM timing_sale_expired WHERE imei=%s", $rowData[IMEI_COL]);
		        	$db->query($sql);

		        	// đánh dấu IMEI đã nhập
		        	$success_list[] = $rowData[IMEI_COL];
		        } catch(Exception $e) {
		        	throw new Exception($e->getMessage().", row: ". $row." -- ".$sql);
		        }
		    } catch (Exception $e) {
		    	$rowData[IMEI_COL] = "'".$rowData[IMEI_COL];
		        $error_list[] = array_merge($rowData, array($e->getMessage()));
		    }
		    // nothing here
		} // END loop through order rows

		// xuất file excel các order lỗi
		if (is_array($error_list) && count($error_list) > 0) {
		    $error_file_name = 'FAILED-'.date('Y-m-d H-i-s') . '.'.$extension;
		    // xuất excel @@
		    //
		    $objPHPExcel_out = new PHPExcel();
		    $objPHPExcel_out->createSheet();
		    $objWorksheet_out = $objPHPExcel_out->getActiveSheet();
		    //
		    // Title
		    $index = '1';
		    $row = $header;
		    $objWorksheet_out->fromArray($row, NULL, 'A'.$index++);

		    // các dòng lỗi
		    foreach ($error_list as $key => $row) {
		        $objWorksheet_out->fromArray($row, NULL, 'A'.$index++);
		    }
		    //

		    try {
		        switch ($extension) {
		            case 'xls':
		                $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel_out);
		                break;
		            case 'xlsx':
		                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel_out);
		                break;
		            default:
		                throw new Exception("Invalid file extension");
		                break;
		        }

		        $new_file_dir = dirname($file_path) . DIRECTORY_SEPARATOR . $error_file_name;

		        $objWriter->save($new_file_dir);
		    } catch (Exception $e) {
		        echo $e->getMessage()."\r\n";
		    }
		}
		// END IF // xuất file excel các order lỗi

		printf("Success: %d\r\n", count($success_list));
		printf("Failed: %d\r\n", count($error_list));
	}

	// Excecute OPPO Club Result 
	public function oppoclubresultAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $quater = $this->getRequest()->getParam('quater');
        $year = $this->getRequest()->getParam('year');


        // set Quater Date Range
        switch ($quater) {
        	case "Quater_01" : 
        		$params['from'] = "01/04/".$year;
        		$params['to'] 	= "30/06/".$year;
        		$month1 		= "04";
        		$month2 		= "05";
        		$month3 		= "06";
        		break;
        	case "Quater_02" : 
        		$params['from'] = "01/07/".$year;
        		$params['to'] 	= "30/09/".$year;
        		$month1 		= "07";
        		$month2 		= "08";
        		$month3 		= "09";
        		break;
        	case "Quater_03" : 
        		$params['from']	= "01/10/".$year;
        		$params['to'] 	= "31/12/".$year;
        		$month1 		= "10";
        		$month2 		= "11";
        		$month3 		= "12";
        		break;
        	case "Quater_04" : 
        		$params['from'] = "01/01/".$year;
        		$params['to'] 	= "31/03/".$year;
        		$month1 		= "01";
        		$month2 		= "02";
        		$month3 		= "03";
        		break;
        	default : exit;
        		break;
        }


        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Sellin By Distributor Data..."."\r\n";

        $QTiming = new Application_Model_Timing();
        $total = 0;
        $result = $QTiming->report_by_dealer_oppoclub(null,null,$total,$params);

        try {       

			echo "Start Transaction..."."\r\n";
			// delete data 
			$where = "quater_no = '".$quater."' AND quater_year = '".$year."' ";
			$db->delete('oppoclub_reward', $where);       

			// insert data
			$arr = array();
			$first 	= "QCnt_".$month1."_".$year;
			$second = "QCnt_".$month2."_".$year;
           	$third 	= "QCnt_".$month3."_".$year;
			$cnt_result = count($result);
			$cnt_insert = 0;

			for ($i=0;$i<$cnt_result;$i++) {

                $level_id = 0;
                if ($result[$i][$first] >= 50 && $result[$i][$second] >= 50 && $result[$i][$third] >= 50) { $level_id = 3; }
                if ($result[$i][$first] >= 100 && $result[$i][$second] >= 100 && $result[$i][$third] >= 100) { $level_id = 2; }
                if ($result[$i][$first] >= 200 && $result[$i][$second] >= 200 && $result[$i][$third] >= 200) { $level_id = 1; }

				$arr = array( 
					'quater_no'	=> $quater,
					'quater_year'	=> $year,
					'd_id'			=> $result[$i]['d_id'],
					'level_id'      => $level_id,
					'from_date'     => '2016-04-01 00:00:00',
					'to_date'       => '2017-12-31 23:59:59',
					'q_status'      => 1 
				);

				// Escape HUB Type = 8
				if ($result[$i]['d_rank'] == 7 && $level_id != 0) { 
					$db->insert('oppoclub_reward', $arr); 
					$cnt_insert = $cnt_insert + 1;
				}
				
			}
    
            $db->commit();
            echo "End Transaction..."."\r\n";
            echo "Result : Insert Data ".$cnt_insert." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // Excecute OPPO Club Season 2 Result 
	public function oppoclubs2resultAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $quater = $this->getRequest()->getParam('quater');
        $year = $this->getRequest()->getParam('year');


        // set Quater Date Range
        switch ($quater) {
        	case "Quater_01" : 
        		$params['from'] = "01/03/".$year;
        		$params['to'] 	= "31/05/".$year;
        		$month1 		= "03";
        		$month2 		= "04";
        		$month3 		= "05";
        		break;
        	case "Quater_02" : 
        		$params['from'] = "01/07/".$year;
        		$params['to'] 	= "30/09/".$year;
        		$month1 		= "07";
        		$month2 		= "08";
        		$month3 		= "09";
        		break;
        	case "Quater_03" : 
        		$params['from']	= "01/10/".$year;
        		$params['to'] 	= "31/12/".$year;
        		$month1 		= "10";
        		$month2 		= "11";
        		$month3 		= "12";
        		break;
        	case "Quater_04" : 
        		$params['from'] = "01/01/".$year;
        		$params['to'] 	= "31/03/".$year;
        		$month1 		= "01";
        		$month2 		= "02";
        		$month3 		= "03";
        		break;
        	default : exit;
        		break;
        }


        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Sellin By Distributor Data..."."\r\n";

        $QTiming = new Application_Model_Timing();
        $total = 0;
        $result = $QTiming->report_by_dealer_oppoclub(null,null,$total,$params);

        try {       

			echo "Start Transaction..."."\r\n";
			// delete data 
			$where = "quater_no = '".$quater."' AND quater_year = '".$year."' ";
			$db->delete('oppoclub_reward', $where);       

			// insert data
			$arr = array();
			$first 	= "QCnt_".$month1."_".$year;
			$second = "QCnt_".$month2."_".$year;
           	$third 	= "QCnt_".$month3."_".$year;
			$cnt_result = count($result);
			$cnt_insert = 0;

			for ($i=0;$i<$cnt_result;$i++) {

                $level_id = 0;
                if ($result[$i][$first] >= 50 && $result[$i][$second] >= 50 && $result[$i][$third] >= 50) { $level_id = 3; }
                if ($result[$i][$first] >= 100 && $result[$i][$second] >= 100 && $result[$i][$third] >= 100) { $level_id = 2; }
                if ($result[$i][$first] >= 200 && $result[$i][$second] >= 200 && $result[$i][$third] >= 200) { $level_id = 1; }

				$arr = array( 
					'quater_no'	=> $quater,
					'quater_year'	=> $year,
					'd_id'			=> $result[$i]['d_id'],
					'level_id'      => $level_id,
					'from_date'     => '2018-03-01 00:00:00',
					'to_date'       => '2019-03-31 23:59:59',
					'q_status'      => 1 
				);

				// Escape HUB Type = 8
				if ($result[$i]['d_rank'] == 7 && $level_id != 0) { 
					$db->insert('oppoclub_reward', $arr); 
					$cnt_insert = $cnt_insert + 1;
				}
				
			}
    
            $db->commit();
            echo "End Transaction..."."\r\n";
            echo "Result : Insert Data ".$cnt_insert." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // Excecute OPPO Club Result 
	public function stockshopexpiredAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Stock Shop Data..."."\r\n";

        $QImei = new Application_Model_WebImei();

        try {       

			echo "Start Transaction..."."\r\n";
  
			$db = Zend_Registry::get('db');

        	$select = $db->select()
	            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('i.*'))
	            ->where("stock_shop_status = 1")
	            ->where("stock_shop_date <= DATE_FORMAT( NOW() - INTERVAL 3 DAY, '%Y-%m-%d 23:59:59')");

	        $stock_shop_list = $db->fetchAll($select);

        	$cnt_update = count($stock_shop_list);
        	
        	print_r($stock_shop_list);
        	echo "count : ".$cnt_update."\r\n";
			// update data
        	if ($stock_shop_list) {

        		for($i=0;$i<$cnt_update;$i++) {
        			$update_data = array('stock_shop_status' => 2);
	        		$update_where = $QImei->getAdapter()->quoteInto('id = ?', $stock_shop_list[$i]['id']);
	        		$QImei->update($update_data, $update_where);
        		}
        	}
    
            $db->commit();
            echo "End Transaction..."."\r\n";
            echo "Result : Total Store Expired : ".$cnt_update." Store!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // PC Not Check in 3 Day Disable User.
    public function pcnotcheckinAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Stock Shop Data..."."\r\n";

        $QStaff = new Application_Model_Staff();
        $QStoreStaff = new Application_Model_StoreStaff();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        try {

            echo "Start Transaction..."."\r\n";

            $get = array(
                'staff_id'        => 'AAA.staff_id',
            );

            $check_in = array(
                'staff_id'          => 'pl.staff_id',
                'date_check_in'     => new Zend_Db_Expr("MAX(pl.created_at)"),
            );

            $leave = array(
                'staff_id'               => 'pll.staff_id',
                'date_check_leave'       => new Zend_Db_Expr("MAX(pll.to_date)"),
            );

            $AAA = $db
                ->select()
                ->from(array('pl' => 'pc_check_in_log'), $check_in)
                ->where("pl.action_id = ?", 1)
                ->where("pl.new_check_in <> ?", "Y")
                ->group('pl.staff_id');

            $BBB = $db
                ->select()
                ->from(array('pll' => 'pc_leave_log'), $leave)
                ->group('pll.staff_id');

            $select = $db->select()
                ->from(array('s'    => 'staff'), $get)
                ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
                ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
                ->where("s.group_id = (?)", 4)
                ->where("s.status = (?)", 1)
                ->where(new Zend_Db_Expr("s.off_date IS NULL"))
                ->where(new Zend_Db_Expr("AAA.staff_id IS NOT NULL"))
                ->where(new Zend_Db_Expr("AAA.staff_id = BBB.staff_id"))
                ->where(new Zend_Db_Expr("AAA.date_check_in <= DATE_FORMAT( NOW() - INTERVAL 4 DAY, '%Y-%m-%d 23:59:59')"))
                ->where(new Zend_Db_Expr("BBB.date_check_leave <= DATE_FORMAT( NOW() - INTERVAL 4 DAY, '%Y-%m-%d 23:59:59')"))
                ->where(new Zend_Db_Expr("MID(s.code,1,2) >= 50"))
                ->order(array('s.id ASC'));

            $pc_not_check_in = $db->fetchAll($select);

            $cnt_update = count($pc_not_check_in);

            echo "count : ".$cnt_update."\r\n";
            // update data
            if ($pc_not_check_in) {
                for($i=0;$i<$cnt_update;$i++) {

                	// Update Status
                    $update_data = array(
                    		'status' => 0, 
                    		//'off_date' => date('Y-m-d H:i:s', strtotime('-2 day')), 
                    		'off_date' => date('Y-m-d H:i:s'), 
                    		'email'  => null,
                    		'additional_info' => 'off date by system at '.date('Y-m-d H:i:s'),
                    	);
                    $update_where = $QStaff->getAdapter()->quoteInto('id = ?', $pc_not_check_in[$i]['staff_id']);
                    $QStaff->update($update_data, $update_where);

                    // Delete Store Staff 
                    $where = array();
                    $where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 0);
                	$where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $pc_not_check_in[$i]['staff_id']);
                    $QStoreStaff->delete($where);


                    // Update Released At on StoreStaffLog
                    $ssl_data = array( 'released_at' => time() );

                    $where = array();
                    $where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = ?', 0);
                	$where[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', $pc_not_check_in[$i]['staff_id']);
                	$where[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);
                    $QStoreStaffLog->update($ssl_data, $where);

                }
            }

            $db->commit();
            echo "End Transaction..."."\r\n";
            echo "Result : Total Staff Expired : ".$cnt_update." Staff!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // All Staff Not Check in 3 Day Off Date User [Except : PC, ASM]
    public function staffnotcheckinAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        $QStaff = new Application_Model_Staff();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        try {

            echo "Start Transaction..."."\r\n";

            $position_table = array(SALES_ID, BM_ID, TRAINING_TEAM_ID, 37, SALES_ADMIN_ID, AM_ID);

            for ($i=0;$i<count($position_table);$i++) {

            	$table_result = $QStaffCheckInLog->getTableNameByGroup($position_table[$i]);

            	echo "Start Transaction [".$table_result['table_group']."] ..."."\r\n";

            	$staff_exeption = array(4943,3155,29009,1970);

            	switch ($position_table[$i]) {
            		case SALES_ID: 			$group_id = array(9,31,33,34); 	break;
            		case BM_ID: 			$group_id = array(30,32); 		break;
            		case TRAINING_TEAM_ID: 	$group_id = array(17,36); 		break;
            		case 37: 				$group_id = array(37,38); 		break;
            		case SALES_ADMIN_ID: 	$group_id = array(12); 			break;
            		case AM_ID: 			$group_id = array(27); 			break;
            		default: 				$group_id = array(0); 			break;
            	}

            	$get = array(
	                'staff_id'        => 'AAA.staff_id',
	            );

	            $check_in = array(
	                'staff_id'          => 'chk.staff_id',
	                'date_check_in'     => new Zend_Db_Expr("MAX(chk.created_at)"),
	            );

	            $leave = array(
	                'staff_id'               => 'lea.staff_id',
	                'date_check_leave'       => new Zend_Db_Expr("MAX(lea.to_date)"),
	            );

	            $AAA = $db
	                ->select()
	                ->from(array('chk' => $table_result['check_in']), $check_in)
	                ->where("chk.action_id = ?", 1)
	                ->where("chk.new_check_in <> ?", "Y")
	                ->group('chk.staff_id');

	            $BBB = $db
	                ->select()
	                ->from(array('lea' => $table_result['leave']), $leave)
	                ->group('lea.staff_id');

	            $select = $db->select()
	                ->from(array('s'    => 'staff'), $get)
	                ->joinLeft(array('AAA' => $AAA), 'AAA.staff_id = s.id ')
	                ->joinLeft(array('BBB' => $BBB), 'BBB.staff_id = s.id ')
	                ->where("s.group_id IN (?)", $group_id)
	                ->where('s.id NOT IN (?)', $staff_exeption)
	                ->where('s.status = ?', 1)
	                ->where(new Zend_Db_Expr("s.off_date IS NULL"))
	                ->where(new Zend_Db_Expr("AAA.staff_id IS NOT NULL"))
	                ->where(new Zend_Db_Expr("AAA.staff_id = BBB.staff_id"))
	                ->where(new Zend_Db_Expr("AAA.date_check_in <= DATE_FORMAT( NOW() - INTERVAL 4 DAY, '%Y-%m-%d 23:59:59')"))
	                ->where(new Zend_Db_Expr("BBB.date_check_leave <= DATE_FORMAT( NOW() - INTERVAL 4 DAY, '%Y-%m-%d 23:59:59')"))
	                ->order(array('s.id ASC'));

	            $staff_not_check_in = $db->fetchAll($select);

	            //print_r($staff_not_check_in); echo "\r\n";

	            $cnt_update = count($staff_not_check_in);

	            echo "count : ".$cnt_update."\r\n";
	            // // update data
	            if ($staff_not_check_in) {
	                for($j=0;$j<$cnt_update;$j++) {

	                	// Update Off Date Account
	                    $update_data = array(
	                    		'status' => 0, 
	                    		// 'off_date' => date('Y-m-d H:i:s', strtotime('-2 day')), 
	                    		'off_date' => date('Y-m-d H:i:s'),
	                    		'email'  => null,
	                    		'additional_info' => 'off date by system at '.date('Y-m-d H:i:s'),
	                    	);
	                    $update_where = $QStaff->getAdapter()->quoteInto('id = ?', $staff_not_check_in[$j]['staff_id']);
	                    $QStaff->update($update_data, $update_where);

	                }
	            }

	            echo "Result : Total Staff Expired : ".$cnt_update." Staff!"."\r\n";

            }

            $db->commit();
            echo "End Transaction..."."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // Excecute ASM GFK
	public function gfkcalculationAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $from = $this->getRequest()->getParam('from_date');
        $to = $this->getRequest()->getParam('to_date');


        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        //echo "Getting Sellin By Distributor Data..."."\r\n";

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $total = 0;
        $result = $QGoodKpiLog->gfk_calculation($from, $to);

        try {       

			echo "Start Transaction..."."\r\n";      
			// delete data 
			$where = "from_date >= '".$from." 00:00:00' AND to_date <= '".$to." 23:59:59' ";
			$db->delete('sale_com_asm', $where);

			// insert data
			$arr = array();
			$now = date('Y-m-d H:i:s');
			$cnt_result = count($result);
			$cnt_insert = 0;

			$last_row = $cnt_result - 1;

			for ($i=0;$i<$cnt_result;$i++) {

				if ( is_null($result[$i]['gfk']) ) { 
					$score = 99;
				} else {
					$score = ( $result[$i]['sellout'] / ( ( $result[$last_row]['sellout'] * $result[$i]['gfk'] ) / 100 ) ) * 60 ;
				}
				
				$arr = array( 
					'area_id'		=> $result[$i]['area_id'],
					'score'			=> number_format($score, 3),
					'from_date'		=> $from." 00:00:00",
					'to_date'      	=> $to." 23:59:59",
					'created_by'	=> 57,
					'created_at'	=> $now,
				);

				echo "ADD GFK Area : [".$arr['area_id']."] ".$result[$i]['area_name']." | Score : ".$arr['score']."\r\n";

				if ( $i != $last_row ) {
					$db->insert('sale_com_asm', $arr); 
					$cnt_insert = $cnt_insert + 1;
				}
				
			}
    
            $db->commit();
            echo "End Transaction..."."\r\n";
            echo "Result : Insert Data ".$cnt_insert." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // Excecute Auto TMS Holiday : Run Every Morning
	public function autodayoffholidayAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Staff List [TMS, TMS Leader, Sale Admin, AM]..."."\r\n";

        $QHoliday = new Application_Model_Holiday();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $am_exception = array(6000425 ,6002972, 17019322, 6000321, 5900128, 5802315, 5902869);

        // Get Staff List
		$select_staff_list = $db->select()
			->from(array('s'  => 'staff'), array('s.*'))
			->join(array('rm' => 'regional_market'), 's.regional_market = rm.id', array())
			->join(array('a'  => 'area'), 'rm.area_id = a.id', array())
			// TMS, TMS Leader
			->where('s.group_id IN (?)', array(37,38)) 
			->where('s.off_date IS NULL', 1)
			->where('a.id NOT IN (?)', array(48,49,72))
			// Sale Admin
			->orWhere('(s.group_id = ?', 12) 
			->where('s.off_date IS NULL', 1)
			->where('s.id <> ?', 64) // Exception for Adminbkk3
			->where('a.id NOT IN (?))', array(48,49,72))
			// AM 
			->orWhere('(s.group_id = ?', 27) 
			->where('s.off_date IS NULL', 1)
			//->where('a.id NOT IN (?)', array(48,49,72))
			->where('s.code NOT IN (?))', $am_exception);

		//echo $select_staff_list; die;
		$staff_list = $db->fetchAll($select_staff_list);

        // $where = array();
        // $where[] = $QStaff->getAdapter()->quoteInto('id <> 3155'); // TMS Test User 37,38
        // $where[] = $QStaff->getAdapter()->quoteInto('group_id IN (?)', array(12) );
        // $where[] = $QStaff->getAdapter()->quoteInto('off_date IS NULL');
        // $staff_list = $QStaff->fetchAll($where);

        $where = array();
        $where[] = $QHoliday->getAdapter()->quoteInto('holiday_status = ?', 0);
        $holiday_list = $QHoliday->fetchAll($where);

        $current_date = date('Y-m-d', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));
        $current_date_name = date('D', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));

        //$current_date = '2017-11-05';
        //$current_date_name = 'Sat';

        echo "Date : [".$current_date_name."] ".$current_date."\r\n";

		$staff_list2 = array();
		foreach ($staff_list as $key => $value) { $staff_list2[] = $value; }
		
		$flag = 0;
		// check Is Holiday
		foreach ($holiday_list as $key2 => $value2) {
        	if ($current_date == $value2['date']) { $flag = 1; break; } 
        }

        if ( $current_date_name == "Sun" ) { $flag = 1; }

        // Exceptin for Sale Admin BKK
        if ( $current_date_name == "Sat" ) { 

        	// Get Staff List
			$select_staff_list = $db->select()
				->from(array('s'  => 'staff'), array('s.*'))
				->join(array('rm' => 'regional_market'), 's.regional_market = rm.id', array())
				->join(array('a'  => 'area'), 'rm.area_id = a.id', array())
				->where('s.group_id = ?', 12) 
				->where('s.off_date IS NULL', 1)
				->where('a.id NOT IN (?)', array(48,49,72))
				->where('a.name LIKE ?', 'BKK%')
				->where('s.id <> ?', 64); // Exception for Adminbkk3

			//echo $select_staff_list; die;
			$staff_list = $db->fetchAll($select_staff_list);

			$staff_list2 = array();
			foreach ($staff_list as $key => $value) { $staff_list2[] = $value; }

        	$flag = 1; 
        }

        try {       

        	if ($flag == 1) {

				// Remove Staff that already Check In on Sunday or Holiday
				foreach ($staff_list as $key => $value) {

					$table_result = $QStaffCheckInLog->getTableNameByGroup($value['group_id']);

					// Check Staff Check In
					$select_staff = $db->select()
						->from(array('chk' => $table_result['check_in']), array('staff_id' => 'chk.staff_id' ))
						->where('chk.staff_id = ?', $value['id'])
						->where('DATE(chk.created_at) = ?', $current_date);

					//echo $select_staff;
					$staff_checkin = $db->fetchRow($select_staff);

					// Check Staff Leave
					$select_staff2 = $db->select()
						->from(array('lea' => $table_result['leave']), array('staff_id' => 'lea.staff_id' ))
						->where('lea.staff_id = ?', $value['id'])
						->where('DATE(lea.from_date) <= ?', $current_date)
						->where('DATE(lea.to_date) >= ?', $current_date);

					//echo $select_staff2;
					$staff_leave = $db->fetchRow($select_staff2);

					if ( isset($staff_checkin['staff_id']) ) { unset($staff_list2[$key]); }
					if ( isset($staff_leave['staff_id']) ) { unset($staff_list2[$key]); }

				}

				// Debug : Check Staff List //
				// $cnt = 0;
				// foreach ($staff_list2 as $key3 => $value3) {
				// 	echo $value3['code']." / ".$value3['firstname']." / ".$value3['lastname']." / ".$value3['group_id']."\r\n";
				// 	$cnt++;
				// }
				// echo $cnt."\r\n"; die;

        		echo "Start Transaction..."."\r\n";  
        		$cnt_insert = 0; 

        		foreach ($staff_list2 as $key2 => $value2) {

        			$table_result2 = $QStaffCheckInLog->getTableNameByGroup($value2['group_id']);

        			$data = array(
        				'staff_id' 		=> $value2['id'],
        				'action_id'		=> 4,
        				'leave_remark'	=> 'Auto Day Off By System',
        				'from_date'		=> $current_date." 09:00:00",
        				'to_date'		=> $current_date." 18:00:00",
        				'status'		=> 'Y',
        				'approve_by'	=> 18,
        				'created_at'	=> $current_date." 09:00:00",
        				'updated_at'	=> $current_date." 18:00:00",

        			);

        			// print_r($data); echo "\r\n";
        			$db->insert($table_result2['leave'], $data);
        			$cnt_insert++;
        		}

        		$db->commit();
		        echo "End Transaction..."."\r\n";
		        echo "Result : Insert Data ".$cnt_insert." rows Complete!"."\r\n";

        	} else {

        		echo "Fail! : Current Date is not Sunday or Holiday...[or Saturday Only Sale Admin]..."."\r\n";
        	}

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }


        exit;
    }


    // Excecute Auto Approve Check In All Position : Run Every Morning
	public function autoapprovecheckinAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $current_date = date('Y-m-d', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));
        $position_table = array(PGPB_ID, SALES_ID, BM_ID, TRAINING_TEAM_ID, 37, SALES_ADMIN_ID, AM_ID);

        try {

        	echo "Start Transaction..."."\r\n";  

        	for ($i=0;$i<count($position_table);$i++) {

        		$table_result = $QStaffCheckInLog->getTableNameByGroup($position_table[$i]);

        		$data = array(
        			'status' 		=> 'Y',
        			'approve_by' 	=> 18,
        			'updated_at'	=> $current_date." 23:59:59",
        			'remark'		=> 'Approve by System',
        		);

        		$where = array();
        		$where['staff_id NOT IN (?)'] = array(471,2679);
        		$where['status = ?'] = 'W';
        		$where['created_at >= ?'] = $current_date." 00:00:00";
        		$where['created_at <= ?'] = $current_date." 23:59:59";
        		$where['approve_by IS NULL'] = 1;

        		if ($position_table[$i] == PGPB_ID) { $where['store_id <> ?'] = 0; }

        		$db->update($table_result['check_in'], $data, $where);

        	}

        	$db->commit();
	        echo "End Transaction..."."\r\n";
	        //echo "Result : Update Data ".$result." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }


    // Excecute Auto Approve First Leave All Position / Month : Run Every Morning
	public function autoapproveleaveAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $now = date('d', strtotime(date('Y-m-d')));
        $current_date = date('Y-m-d', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));

        if ($now >= 21) {
        	$period_start = date('Y-m-21', strtotime(date('Y-m-d')) );
        	$period_end = date('Y-m-20', strtotime("+1 Day" , strtotime(date('Y-m-t')) ));
        } else {
        	$period_start = date('Y-m-21', strtotime("-1 Day" , strtotime(date('Y-m-01')) ));
        	$period_end = date('Y-m-20', strtotime(date('Y-m-d')) );
        }

        $position_table = array(PGPB_ID, SALES_ID, TRAINING_TEAM_ID, 37, SALES_ADMIN_ID, AM_ID, BM_ID);
        //$position_table = array(SALES_ID);

        try {

        	echo "Start Transaction..."."\r\n";  

        	for ($i=0;$i<count($position_table);$i++) {

        		$table_result = $QStaffCheckInLog->getTableNameByGroup($position_table[$i]);

        		// Check Staff 
        		$select_staff = $db->select()
		            ->from(array('lea' => $table_result['leave']), array('staff_id' => 'lea.staff_id' ))
		            ->where('lea.approve_by IS NULL', 1)
		            ->where('lea.status = ?', 'W')
		            ->where('lea.created_at >= ?', $current_date.' 00:00:00')
		            ->where('lea.created_at <= ?', $current_date.' 23:59:59')
		            ->group('lea.staff_id');

		        //echo $select;
		        $result_staff = $db->fetchAll($select_staff);

		        //echo "<pre>"; print_r($result);

		        // Check Leave (on Period) of each Staff
		        for ($j=0;$j<count($result_staff);$j++) {

		        	$select_chk = $db->select()
			            ->from(array('lea' => $table_result['leave']), array('cnt' => new Zend_Db_Expr("COUNT(lea.id)") ))
			            ->where('lea.remark = ?', 'Approve By System')
			            ->where('lea.status = ?', 'Y')
			            ->where('lea.created_at >= ?', $period_start.' 00:00:00')
			            ->where('lea.created_at <= ?', $period_end.' 23:59:59')
			            ->where('lea.staff_id = ?', $result_staff[$j]['staff_id']);

					//echo $select;
					$result_chk = $db->fetchOne($select_chk);

					$data = array(
	        			'approve_by' 	=> 18,
	        			'updated_at'	=> $current_date." 23:59:59",
	        		);

					$where = array();
	        		$where['status = ?'] = 'W';
	        		$where['created_at >= ?'] = $current_date." 00:00:00";
	        		$where['created_at <= ?'] = $current_date." 23:59:59";
	        		$where['approve_by IS NULL'] = 1;
	        		$where['staff_id = ?'] = $result_staff[$j]['staff_id'];

	        		// Add Exception for Project Manager (Training Dept.)
		        	if ($result_chk == 0 || in_array($result_staff[$j]['staff_id'], array(3874,5379))) {
		        		$data['status'] = 'Y';
		        		$data['remark'] = 'Approve By System';
		        	} else {
		        		$data['status'] = 'N';
		        		$data['remark'] = 'Reject By System';
		        	}

		        	$db->update($table_result['leave'], $data, $where);

		        }

        	}

        	$db->commit();
	        echo "End Transaction..."."\r\n";
	        //echo "Result : Update Data ".$result." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }

    // Excecute Auto Check Out 3 Time Then Bussiness Leave Half Day All Position / Month : Run Every Morning
	public function autocheckoutAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();

        $now = date('d', strtotime(date('Y-m-d')));
        $current_date = date('Y-m-d', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));

        if ($now >= 22) {
        	$period_start = date('Y-m-21', strtotime(date('Y-m-d')) );
        	$period_end = date('Y-m-20', strtotime("+1 Day" , strtotime(date('Y-m-t')) ));
        } else {
        	$period_start = date('Y-m-21', strtotime("-1 Day" , strtotime(date('Y-m-01')) ));
        	$period_end = date('Y-m-20', strtotime(date('Y-m-d')) );
        }

        $position_table = array(PGPB_ID, SALES_ID, BM_ID, TRAINING_TEAM_ID, 37, SALES_ADMIN_ID, AM_ID);
        //$position_table = array(TRAINING_TEAM_ID);

        try {

        	echo "Start Transaction..."."\r\n";  

        	for ($i=0;$i<count($position_table);$i++) {

        		$table_result = $QStaffCheckInLog->getTableNameByGroup($position_table[$i]);

        		// Check Staff 
        		$select_staff = $db->select()
		            ->from(array('chk' => $table_result['check_in']), array('staff_id' => 'chk.staff_id' ))
		            ->where('chk.action_id = ?', 1)
		            ->where('chk.new_check_in = ?', 'N')
		            ->where('chk.check_out IS NULL', 1)
		            ->where('chk.created_at >= ?', $current_date.' 00:00:00')
		            ->where('chk.created_at <= ?', $current_date.' 23:59:59')
		            ->group('chk.staff_id');

		        //echo $select_staff."\r\n";
		        $result_staff = $db->fetchAll($select_staff);

		        //print_r($result_staff);

		        // Check Check Out By System (on Period) of each Staff
		        for ($j=0;$j<count($result_staff);$j++) {

		        	$select_chk = $db->select()
			            ->from(array('chk' => $table_result['check_in']), array('cnt' => new Zend_Db_Expr("COUNT(chk.id)") ))
			            ->where('chk.work_performance = ?', 'Adjusted By System #01')
			            ->where('chk.check_out IS NOT NULL', 1)
			            ->where('chk.new_check_in = ?', 'N')
			            ->where('chk.created_at >= ?', $period_start.' 00:00:00')
			            ->where('chk.created_at <= ?', $period_end.' 23:59:59')
			            ->where('chk.staff_id = ?', $result_staff[$j]['staff_id']);

					//echo $select;
					$result_chk = $db->fetchOne($select_chk);

					//echo $result_chk."\r\n";
					//echo $position_table[$i]."\r\n";

		        	if ($result_chk >= 3) {

		        		/*
		        		// Update Auto Check Out
		        		$data = array(
		        			'work_performance'	=> 'Adjusted By System #02',
		        			'check_out'			=> $current_date." 12:00:00",
		        		);

		        		$where['check_out IS NULL'] = 1;
		        		$where['action_id = ?'] = 1;
		        		$where['created_at >= ?'] = $current_date." 00:00:00";
		        		$where['created_at <= ?'] = $current_date." 23:59:59";
		        		$where['staff_id = ?'] = $result_staff[$j]['staff_id'];

		        		$db->update($table_result['check_in'], $data, $where);

		        		// Insert Bussiness Leave Half Day
		        		$data2 = array(
		        			'staff_id' 		=> $result_staff[$j]['staff_id'],
		        			'action_id' 	=> 2, 
		        			'leave_remark'	=> 'Adjusted By System #02',
		        			'from_date'		=> $current_date." 13:00:00",
		        			'to_date'		=> $current_date." 18:00:00",
		        			'status'		=> 'Y',
		        			'approve_by'	=> 18,
		        			'created_at'	=> $current_date." 13:00:00",
		        			'updated_at'	=> $current_date." 13:00:00",
		        		);
		        		
		        		$db->insert($table_result['leave'], $data2);
		        		*/
		        	} else {
		        		
		        		// Update Auto Check Out
		        		$data = array(
		        			'work_performance'	=> 'Adjusted By System #01',
		        			'check_out'			=> $current_date." 23:59:59",
		        		);
		        		
		        		$where = array();
		        		$where['check_out IS NULL'] = 1;
		        		$where['action_id = ?'] = 1;
		        		$where['new_check_in = ?'] = 'N';
		        		$where['created_at >= ?'] = $current_date." 00:00:00";
		        		$where['created_at <= ?'] = $current_date." 23:59:59";
		        		$where['staff_id = ?'] = $result_staff[$j]['staff_id'];

		        		$db->update($table_result['check_in'], $data, $where);
		        	}

		        	
		        }

        	}

        	$db->commit();
	        echo "End Transaction..."."\r\n";
	        //echo "Result : Update Data ".$result." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }

    // Excecute Head Count Daily 
	public function getheadcountlogAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        //echo "Getting Sellin By Distributor Data..."."\r\n";

        $selected_date = date('Y-m-d H:i:s');
        $params = array('selected_date' => $selected_date);

        $QTiming = new Application_Model_Timing();
        $result = $QTiming->getHeadCountByRD($params);
        $result_unique = $QTiming->getHeadCountUnique($params);

        //print_r($result);
        

        try {       

			echo "Start Transaction..."."\r\n";      
			// delete data 
			$where = "DATE(created_at) = DATE('".$selected_date."')";
			$db->delete('headcount_log', $where);

			// insert data
			$arr = array();
			$arr2 = array();

			for ($i=0;$i<count($result);$i++) {

				$arr = array( 
					'created_at'	=> $selected_date,
					'zone'			=> $result[$i]['zone'],
					'rd_id'			=> $result[$i]['rd_id'],
					'group_id'		=> $result[$i]['rd_group_id'],
					'cnt_pc'		=> $result[$i]['cnt_pc'],
					'cnt_sale'		=> $result[$i]['cnt_sale'],
					'cnt_sale_event'=> $result[$i]['cnt_sale_event'],
					'cnt_asm'		=> $result[$i]['cnt_asm'],
					'cnt_rd'		=> $result[$i]['cnt_rd'],
					'cnt_tms_leader'=> $result[$i]['cnt_tms_leader'],
					'cnt_tms'		=> $result[$i]['cnt_tms'],
					'cnt_pcm_leader'=> $result[$i]['cnt_pcm_leader'],
					'cnt_pcm'		=> $result[$i]['cnt_pcm'],
				);
				
				$db->insert('headcount_log', $arr); 
			}

			$arr2 = array(
				'created_at'	=> $selected_date,
				'zone'			=> 'Unique Headcount',
				'rd_id'			=> 0,
				'group_id'		=> 0,
				'cnt_pc'		=> $result_unique['cnt_pc'],
				'cnt_sale'		=> $result_unique['cnt_sale'],
				'cnt_sale_event'=> $result_unique['cnt_sale_event'],
				'cnt_asm'		=> $result_unique['cnt_asm'],
				'cnt_rd'		=> $result_unique['cnt_rd'],
				'cnt_tms_leader'=> $result_unique['cnt_tms_leader'],
				'cnt_tms'		=> $result_unique['cnt_tms'],
				'cnt_pcm_leader'=> $result_unique['cnt_pcm_leader'],
				'cnt_pcm'		=> $result_unique['cnt_pcm'],
			);

    		$db->insert('headcount_log', $arr2); 

            $db->commit();
            echo "End Transaction..."."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }
        exit;
    }

    // Excecute Auto Reject Check In Service : Run Every Morning
	public function autorejectcheckinAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $current_date = date('Y-m-d', strtotime("-1 Day" , strtotime(date('Y-m-d')) ));

        try {

        	echo "Start Transaction..."."\r\n";  

    		$data = array(
    			'status' 		=> 'N',
    			'approve_by' 	=> 94,
    			'updated_at'	=> $current_date." 23:59:59",
    			'remark'		=> 'Reject by System',
    		);

    		$where = array();
    		$where['status = ?'] = 'W';
    		$where['created_at >= ?'] = $current_date." 00:00:00";
    		$where['created_at <= ?'] = $current_date." 23:59:59";
    		$where['approve_by IS NULL'] = 1;

    		$db->update(HR_DB.'.service_check_in', $data, $where);

        	$db->commit();
	        echo "End Transaction..."."\r\n";
	        //echo "Result : Update Data ".$result." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }

	public function getimeiactivatedAction() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        // $from = $this->getRequest()->getParam('from_date');
        // $to = $this->getRequest()->getParam('to_date');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $from = date('Y-m-d', strtotime("-6 Day" , strtotime(date('Y-m-d')) ));
        $to = date('Y-m-d');

        try {

        	echo "Start Transaction..."."\r\n";  

        	// Get IMEI Not Activated
    		$select_imei = $db->select()
	            ->from(array('t' => 'timing'), array('imei' => 'ts.imei'))
	            ->join(array('ts'=> 'timing_sale')			, 't.id = ts.timing_id', array())
	            ->join(array('i' => WAREHOUSE_DB.'.imei')	, 'ts.imei = i.imei_sn', array())
	            ->where('t.created_at >= ?', $from." 00:00:00")
	            ->where('t.created_at <= ?', $to." 23:59:59")
	            ->where('i.activated_date IS NULL', 1)
	            ->order('t.created_at ASC');

	        //echo $select_imei."\r\n";
	        $result_imei = $db->fetchAll($select_imei);
	        
	        $imei_temp = array();
	        foreach ($result_imei as $key => $value) { $imei_temp[] = $value['imei']; }
	        //print_r($imei_temp);

	        $imei_temp2 = array_chunk($imei_temp, 100);

	        //$imei_temp2 = array(0 => array(0=>'869711035770397',1=>'869603038747797',2=>'869631030284493'));

	        //print_r($imei_temp2); exit;
	        $cnt = 1;

	        for ($i=0;$i<count($imei_temp2);$i++) {

	        	$imei_list = implode(",", $imei_temp2[$i]);

		        $timestamp  = time();
				$secret_key = '60eb46745ba9ce84582728aca7ed92dd';
				$appid      = 'ESA';

				$str = "appidESA_imeis".$imei_list."_timestamp".$timestamp."_sercetkey".$secret_key;
				$sign = strtoupper(md5($str));

				//$client = new SoapClient("http://warranty.oppo.com:8080/EsaService/ThailandGetImeiRegTime.asmx?wsdl",
				$client = new SoapClient("https://esa.myoppo.com/ESAService/ThailandGetImeiRegTime.asmx?wsdl",
					array(
						"trace"      => 1,	// enable trace to view what is happening
						"exceptions" => 0,	// disable exceptions
						"cache_wsdl" => 0	// disable any caching on the wsdl, encase you alter the wsdl server
					)    
				);

		        $params = array(
					'timestamp' => $timestamp,
					'appid' 	=> $appid,
					'imeis' 	=> $imei_list,
					'sign' 		=> $sign
		        );

		        $data = $client->GetImeiRegTime($params);
		        $result_string =  $data->GetImeiRegTimeResult;
		        $devided = simplexml_load_string($result_string);

				if($devided->flag == 1){
					foreach($devided->DataRow as $item){
						echo "No.".$cnt." | IMEI : ".$item->IMEI." | REG DATE : ".$item->REGDATE."\r\n"; $cnt++;

						// Update Auto Check Out
		        		$data = array(
		        			'activated_date'	=> $item->REGDATE,
		        		);
		        		
		        		$where = array();
		        		$where['imei_sn = ?'] = $item->IMEI;
		        		$where['activated_date IS NULL'] = 1;

		        		$db->update(WAREHOUSE_DB.'.imei', $data, $where);
					}
				}

	        }

        	$db->commit();
	        echo "End Transaction..."."\r\n";
	        //echo "Result : Update Data ".$result." rows Complete!"."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }

}