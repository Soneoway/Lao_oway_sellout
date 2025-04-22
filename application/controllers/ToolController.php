<?php

class ToolController extends Zend_Controller_Action
{

    function exportAction(){

        $QGood = new Application_Model_Good();

        $where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
        $goods = $QGood->fetchAll($where, 'desc');
        $this->view->goods = $goods;

        $date = array();

        $QTiming = new Application_Model_Timing();
        foreach ($goods as $good){
            for ($i=1; $i<31; $i++){
                $tm = ($i<10 ? '0' : '').$i;
                $sel = $QTiming->getSellOut('2014-06-'.$tm, '2014-06-'.$tm, $good->id);
                $date[$i][$good->id] = $sel;
            }
        }
        //var_dump($date);exit;
        $this->view->date = $date;

        set_time_limit(0);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            '',
        );

        for ($i=1; $i<31; $i++){
            $tm = ($i<10 ? '0' : '').$i;
            $heads[] = '2014-06-'.$tm;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;
        foreach($heads as $key)
        {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }
        $index    = 2;


        foreach($goods as $item){
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $item['desc']);


            for ($i=1; $i<31; $i++){

                if (isset($date[$i][$item['id']]) and $date[$i][$item['id']])
                    $sheet->setCellValue($alpha++.$index, $date[$i][$item['id']]);
                else
                    $sheet->setCellValue($alpha++.$index, 0);

            }

            $index++;
        }

        $filename = 'TIMING_REPORT_BY_STORE_'.date('d/m/Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public function getProductAction() {

        $brand_id = $this->getRequest()->getParam('brand_id');
        $QBrand = new Application_Model_Brand();
        $brands = $QBrand->getProduct($brand_id);

        echo json_encode(array('brands' => $brands),true); exit;
        exit;

        // $QGood = new Application_Model_Good();
        // $where[] = $QGood->getAdapter()->quoteInto('brand_id = ?', $brand_id);
        // $where[] = $QGood->getAdapter()->quoteInto('product_status IN (?)',array('1','2'));

        // echo json_encode($QGood->fetchAll($where, 'name')->toArray());
        // exit;
    }

    function export2Action(){

        $QGood = new Application_Model_Good();

        $where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
        $goods = $QGood->fetchAll($where, 'desc');
        $this->view->goods = $goods;

        $date = $data = array();

        $start = '2014-04-01';

        while (strtotime($start)<strtotime('2014-06-30')){
            $date[] = array(
                $start, date('Y-m-d', strtotime('+6 days', strtotime($start)))
            );
            $start = date('Y-m-d', strtotime('+1 week', strtotime($start)));
        }

        $QTiming = new Application_Model_Timing();
        foreach ($goods as $good){

            foreach ($date as $d){
                $sel = $QTiming->getSellOut($d[0], $d[1], $good->id);
                $data[$d[0]][$good->id] = $sel;
            }
        }

        set_time_limit(0);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            '',
        );

        foreach ($date as $d){
            $heads[] = $d[0] . ' - '. $d[1];
        }

        $PHPExcel->setActiveSheetIndex(0);
        $sheet    = $PHPExcel->getActiveSheet();

        $alpha    = 'A';
        $index    = 1;
        foreach($heads as $key)
        {
            $sheet->setCellValue($alpha.$index, $key);
            $alpha++;
        }
        $index    = 2;


        foreach($goods as $item){
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $item['desc']);


            foreach ($date as $d){

                if (isset($data[$d[0]][$item['id']]) and $data[$d[0]][$item['id']])
                    $sheet->setCellValue($alpha++.$index, $data[$d[0]][$item['id']]);
                else
                    $sheet->setCellValue($alpha++.$index, 0);

            }

            $index++;
        }

        $filename = 'SELL_OUT_WEEK_'.date('d/m/Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public function tgddAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $this->view->uid = $userStorage->id;
    }

    public function tgddSaveAction()
    {
        $this->_helper->layout->disableLayout();

        if ( $this->getRequest()->getMethod() != 'POST' ) { // Big IF

        } else {
            define("TIMING_ROW_START", 2);
            define("MODEL_COL", 0);
            define("IMEI_COL", 1);
            define("DATE_COL", 3);
            define("STORE_COL", 5);
            define("TITLE_ROW", 1);
            define("STAFF_ID", 3025);

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $upload = new Zend_File_Transfer();

            $uniqid = uniqid('', true);
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'
                    . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files'
                    . DIRECTORY_SEPARATOR . 'timing'
                    . DIRECTORY_SEPARATOR  . $userStorage->id
                    . DIRECTORY_SEPARATOR . $uniqid;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            $upload->setValidators(array(
                'Size'  => array('min' => 50, 'max' => 5000000),
                'Count' => array('min' => 1, 'max' => 1),
                'Extension' => array('xlsx', 'xls'),
            ));

            if (!$upload->isValid()){ // validate IF
                $errors = $upload->getErrors();
                $sError = null;

                if ($errors and isset($errors[0]))
                    switch ($errors[0]) {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                            $sError = 'The file you selected weren\'t the type we were expecting';
                            break;
                        case 'fileExtensionFalse':
                            $sError = 'Please choose a file in XLS or XLSX format.';
                            break;
                        case 'fileCountTooFew':
                            $sError = 'Please choose a PO file (in XLS or XLSX format)';
                            break;
                        case 'fileUploadErrorNoFile':
                            $sError = 'Please choose a PO file (in XLS or XLSX format)';
                            break;
                        case 'fileSizeTooBig':
                            $sError = 'File size is too big';
                            break;
                    }

                $this->view->error = $sError;

            } else {
                try {
                    $upload->receive();

                    $path_info = pathinfo($upload->getFileName());
                    $filename = $path_info['filename'];
                    $extension = $path_info['extension'];

                    $old_name = $filename . '.'.$extension;
                    $new_name = 'UPLOAD-'.md5($filename . uniqid('', true)) . '.'.$extension;

                    if (is_file($uploaded_dir . DIRECTORY_SEPARATOR . $old_name)){
                        rename($uploaded_dir . DIRECTORY_SEPARATOR . $old_name, $uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                    } else {
                        $new_name = $old_name;
                    }

                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                    $QFileLog = new Application_Model_FileUploadLog();

                    $data = array(
                        'staff_id'       => $userStorage->id,
                        'folder'         => $uniqid,
                        'filename'       => $new_name,
                        'type'           => 'mass timing tgdd',
                        'real_file_name' => $filename . '.'.$extension,
                        'uploaded_at'    => time(),
                        );

                    $log_id = $QFileLog->insert($data);

                    // action
                    $error_list = array();
                    $success_list = array();
                    $number_of_order = 0;
                    $total_order_row = 0;

                    // mapping products
                    $QStore = new Application_Model_Store();
                    $QTiming = new Application_Model_Timing();
                    $QWebImei = new Application_Model_WebImei();

                    require_once 'PHPExcel.php';
                    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
                    $cacheSettings = array( 'memoryCacheSize' => '32MB');
                    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

                    switch ($extension) {
                        case 'xls':
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                            break;
                        case 'xlsx':
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                            break;
                        default:
                            throw new Exception("Invalid file extension");
                            break;
                    }

                    $objReader->setReadDataOnly(true);

                    $objPHPExcel = $objReader->load($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

                    $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
                    $total_order_row = $highestRow - TIMING_ROW_START + 1;

                    $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
                    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

                    // get title
                    $title = array();
                    for ($i = 1; ( $title_tmp
                                            = trim($objWorksheet
                                                    ->getCellByColumnAndRow($i, TITLE_ROW)
                                                    ->getValue())
                                            ) != ''; $i++) {
                        $title[] = $title_tmp;
                    }

                    // $i là dòng
                    for ($i = TIMING_ROW_START; $i <= $highestRow; $i++) {
                        try {
                            $flag = true;
                            $number_of_order++;
                            $percent = round($number_of_order*100/$total_order_row, 1);
                            // file_put_contents(APPLICATION_PATH.'/../public/files/timing/'.$userStorage->id.'/status.txt', $percent);

                            // validate Store ID
                            $store_id_tmp = trim($objWorksheet
                                                ->getCellByColumnAndRow(STORE_COL, $i)
                                                ->getValue());
                            if (intval($store_id_tmp) == 0) {
                                $error_list[] = array(
                                    'reason' => 'Invalid Store ID',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            // validate store in DB
                            $store_tmp = $QStore->find($store_id_tmp);
                            $store_tmp = $store_tmp->current();
                            if (!$store_tmp) {
                                $error_list[] = array(
                                    'reason' => 'Invalid Store ID',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            // validate IMEI
                            $imei_tmp = trim($objWorksheet
                                                ->getCellByColumnAndRow(IMEI_COL, $i)
                                                ->getValue());

                            if (!preg_match('/^[0-9]{15}$/', $imei_tmp)) {
                                $error_list[] = array(
                                    'reason' => 'Invalid IMEI format',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            // check IMEI as real timing action
                            $info = array();
                            $return = $this->checkImei($imei_tmp, null, $info);
                            $reason = '';

                            if ($return==1){
                                $reason = "Bạn đã báo cáo IMEI này rồi, vào lúc [" . date('d/m/Y H:i:s', strtotime($info['date'])) . "] Tại cửa hàng [".$info['store']."]";

                            } else if ($return==2){
                                $reason = "IMEI không tồn tại.";

                            } else if ($return==3 || $return==5){
                                $reason = 'IMEI '.$imei_tmp.' này đã được ['.$info['staff'].'] báo cáo ở cửa hàng ['.$info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).']';

                            } elseif ($return==5) {
                                $reason = "IMEI này đã được bán cách đây hơn 01 tháng. Bởi [" . $info['staff'].'] ở cửa hàng ['. $info['store'].'] vào lúc ['.date('d/m/Y H:i:s', strtotime($info['date'])).'].';

                            } /*elseif ($return == 4) {
                                $reason = 'IMEI này đã được bán cách đây hơn 01 tháng. Công ty không tính doanh số đối với các máy đã bán ra thị trường hơn 01 tháng. Vui lòng liên hệ ASM nếu có thắc mắc.';

                            } */ elseif ($return == 6) {
                                $reason = 'IMEI này của máy tặng khách hàng, thuộc diện không tính KPI.';

                            } elseif ($return == 7) {
                                $reason = 'IMEI này của máy demo, thuộc diện không tính KPI.';

                            }  elseif ($return == 8) {
                                $reason = 'IMEI này của máy xuất cho nhân viên, thuộc diện không tính KPI.';

                            } elseif ($return == 9) {
                                $reason = 'IMEI này của máy mượn, thuộc diện không tính KPI.';

                            }

                            if (!in_array($return, array(0, 4))) {
                                $error_list[] = array(
                                    'reason' => in_array($return, array(3, 5)) ? $info : $reason,
                                    'row'    => $i,
                                    );
                                $flag = false;
                                continue;
                            }

                            // get product id and color id
                            if (!isset($info['good_id']) || !$info['good_id']) {
                                $error_list[] = array(
                                    'reason' => 'Invalid product ID',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            if (!isset($info['good_color']) || !$info['good_color']) {
                                $error_list[] = array(
                                    'reason' => 'Invalid color ID',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            $product_id_tmp = $info['good_id'];
                            $product_color_tmp = $info['good_color'];

                            // validate sell out date
                            $date_tmp = trim($objWorksheet
                                                ->getCellByColumnAndRow(DATE_COL, $i)
                                                ->getCalculatedValue());
                            $date_tmp = strtotime($date_tmp);
                            if ( ! ( $date_tmp >= strtotime('2015-01-01 00:00:00') && $date_tmp <= strtotime('2015-12-31 23:59:59') ) ) {
                                $error_list[] = array(
                                    'reason' => 'Wrong sell out date',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            $date_tmp = date('Y-m-d', $date_tmp);

                            // insert timing
                            $timing_id = $this->timing($date_tmp, $store_id_tmp, STAFF_ID);

                            if (!$timing_id) {
                                $error_list[] = array(
                                    'reason' => 'Insert failed',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            // insert timing sales
                            $timing_sale_id = $this->timing_sale($timing_id, $product_id_tmp, $product_color_tmp, $imei_tmp);

                            if (!$timing_sale_id) {
                                $where = $QTiming->getAdapter()->quoteInto('id = ?', $timing_id);
                                $QTiming->delete($where);

                                $error_list[] = array(
                                    'reason' => 'Insert failed',
                                    'row'    => $i,
                                    );
                                continue;
                            }

                            $success_list[] = array(
                                'timing_id'      => $timing_id,
                                'timing_sale_id' => $timing_sale_id,
                                'model_name'     => $product_name_tmp,
                                'product_id'     => $product_id_tmp,
                                'product_color'  => $product_color_tmp,
                                'imei'           => $imei_tmp,
                                'date'           => $date_tmp,
                                'store_id'       => $store_id_tmp,
                                );
                        } catch (Exception $e) {

                            $error_list[] = array(
                                'reason' => 'Unknown Error',
                                'row'    => $i,
                                );
                        } // try-catch
                    } // END big FOR

                    $data = array(
                        'total'   => $number_of_order,
                        'failed'  => count($error_list),
                        'succeed' => $number_of_order - count($error_list),
                        'value'   => 0,
                        );

                    // xuất file excel các order lỗi
                    if (is_array($error_list) && count($error_list) > 0) {
                        $data['error_file_name'] = 'FAILED-'.md5(microtime(true) . uniqid('', true)) . '.'.$extension;
                        // xuất excel @@
                        //
                        $objPHPExcel_out = new PHPExcel();
                        $objPHPExcel_out->createSheet();
                        $objWorksheet_out = $objPHPExcel_out->getActiveSheet();
                        //
                        $objWorksheet = $objPHPExcel->getActiveSheet();

                        $alpha = 'A';
                        $i = 1;
                        foreach ($title as $key => $value)
                            $objWorksheet_out->setCellValue($alpha++.$i, $value);

                        $i++;
                        // các dòng lỗi
                        $objWorksheet = $objPHPExcel->getActiveSheet();
                        foreach ($error_list as $key => $row) {
                            for ($j=0; $j <= $highestColumnIndex; $j++) {
                                if ($j == IMEI_COL)
                                    $objWorksheet_out->getCellByColumnAndRow($j, $i)->setValueExplicit($objWorksheet->getCellByColumnAndRow($j, $row['row'])->getValue(), PHPExcel_Cell_DataType::TYPE_STRING);

                                else
                                    $objWorksheet_out->setCellValueByColumnAndRow(
                                        $j, $i, $objWorksheet->getCellByColumnAndRow($j, $row['row'])->getValue()
                                        );
                            }

                            if (is_array($row['reason']) && count($row['reason']) > 0) {

                                $objWorksheet_out->setCellValueByColumnAndRow($j++, $i, $row['reason']['staff']);
                                $objWorksheet_out->setCellValueByColumnAndRow($j++, $i, $row['reason']['store']);
                                $objWorksheet_out->setCellValueByColumnAndRow($j++, $i, $row['reason']['date']);

                            } else {
                                $objWorksheet_out->setCellValueByColumnAndRow($j, $i, $row['reason']);
                            }

                            $i++;
                        }
                        //
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

                        $new_file_dir = $uploaded_dir . DIRECTORY_SEPARATOR . $data['error_file_name'];

                        $objWriter->save($new_file_dir);
                    }
                    // END IF // xuất file excel các order lỗi

                    // xuất file excel các order thành công
                    if (is_array($success_list) && count($success_list) > 0) {
                        $data['success_file_name'] = 'SUCCESS-'.md5(microtime(true) . uniqid('', true)) . '.'.$extension;
                        // xuất excel @@
                        //
                        $objPHPExcel_out = new PHPExcel();
                        $objPHPExcel_out->createSheet();
                        $objWorksheet_out = $objPHPExcel_out->getActiveSheet();

                        $all_store   = $QStore->get_cache();
                        $QGood = new Application_Model_Good();
                        $QGoodColor = new Application_Model_GoodColor();
                        $all_product = $QGood->get_cache();
                        $all_color   = $QGoodColor->get_cache();
                        //
                        $headers = array(
                            'No.',
                            // 'Model Name',
                            "Product",
                            'Color',
                            'IMEI',
                            'Store',
                            'Date',
                            );

                        $sxx_list = array();
                        $stt = 1;
                        $objWorksheet_out->fromArray($headers, NULL, 'A1');
                        $index = 2;
                        foreach ($success_list as $key => $value) {
                            $col = "A";
                            $objWorksheet_out->setCellValue($col++.$index, $stt++);
                            // $objWorksheet_out->setCellValue($col++.$index, $value['model_name']);
                            $objWorksheet_out->setCellValue($col++.$index, isset($all_product[ $value['product_id'] ]) ? $all_product[ $value['product_id'] ] : '');
                            $objWorksheet_out->setCellValue($col++.$index, isset($all_color[ $value['product_color'] ]) ? $all_color[ $value['product_color'] ] : '');
                            $objWorksheet_out->getCell($col++.$index)->setValueExplicit($value['imei'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $objWorksheet_out->setCellValue($col++.$index, isset($all_store[ $value['store_id'] ]) ? $all_store[ $value['store_id'] ] : '');
                            $objWorksheet_out->getCell($col++.$index)->setValueExplicit($value['date'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $index++;
                        }

                        $objWorksheet_out->fromArray($sxx_list, NULL, 'A2');

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

                        $new_file_dir = $uploaded_dir . DIRECTORY_SEPARATOR . $data['success_file_name'];

                        $objWriter->save($new_file_dir);
                    }
                    // END IF // xuất file excel các order thành công

                    $where = $QFileLog->getAdapter()->quoteInto('id = ?', $log_id);
                    $QFileLog->update($data, $where);

                    $this->view->error_list = $error_list;
                    $this->view->objWorksheet = $objWorksheet;
                    $this->view->number_of_order = $number_of_order;

                    $this->view->success_file = isset($data['success_file_name']) ? (HOST
                            . 'files'
                            . DIRECTORY_SEPARATOR . 'timing'
                            . DIRECTORY_SEPARATOR  . $userStorage->id
                            . DIRECTORY_SEPARATOR . $uniqid
                            . DIRECTORY_SEPARATOR . $data['success_file_name']):false;
                    $this->view->error_file = isset($data['error_file_name']) ? (HOST
                            . 'files'
                            . DIRECTORY_SEPARATOR . 'timing'
                            . DIRECTORY_SEPARATOR  . $userStorage->id
                            . DIRECTORY_SEPARATOR . $uniqid
                            . DIRECTORY_SEPARATOR . $data['error_file_name']):false;

                } catch (Zend_File_Transfer_Exception $e) {
                    $this->view->error =  $e->getMessage();
                } catch (Exception $e) {
                    $this->view->error =  $e->getMessage();
                }
            } // END validate IF

            // file_put_contents(APPLICATION_PATH.'/../public/files/timing/'.$userStorage->id.'/status.txt', '0');
        } // END Big IF

    }

    private function timing($date, $store_id, $staff_id, $shift = 1, $note = 'auto insert')
    {
        $QTiming = new Application_Model_Timing();
        $data = array(
            'staff_id'    => $staff_id,
            'shift'       => $shift,
            'from'        => $date . ' 09:00:00',
            'to'          => $date . ' 15:00:00',
            'store'       => $store_id,
            'note'        => $note,
            'created_at'  => date('Y-m-d H:i:s'),
            'created_by'  => $staff_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $staff_id,
            'status'      => 1,
            );

        return $QTiming->insert($data);
    }

    private function timing_sale($timing_id, $product_id, $product_color, $imei, $quantity = 1)
    {
        $QTimingSale = new Application_Model_TimingSale();
        $data = array(
            'timing_id'  => $timing_id,
            'product_id' => $product_id,
            'model_id'   => $product_color,
            'imei'       => $imei,
            'quantity'   => $quantity,
            );

        return $QTimingSale->insert($data);
    }

    private function checkImei($imei, $timing_sales_id, &$info, $tool = null){
        /**
         * Note: by phamquocbuu
         * Check IMEI theo bảng imei_exception
         *      co trong bang exception thi uu tien lay trong do de check co fai imei demo hay khong
         * Check IMEI theo bảng imei
         *      Không tồn tại -> thông báo
         *      Tồn tại -> bước sau
         * Check trong bảng acti
         *      Ngày acti trước 30 ngày -> từ chối chấm công
         *      Ngược lại, check trong phần timing
         *          Tồn tại
         *              So sánh thời gian chấm công
         *                  Chấm hơn 30 ngày -> từ chối
         *                  Ngược lại -> thông báo
         *          Không tồn tại -> chấm công
         */

        // Check IMEI theo bảng imei
        $QWebImei = new Application_Model_WebImei();
        $where = array();
        // $where[] = $QWebImei->getAdapter()->quoteInto('out_date > ?', 0);
        $where[] = $QWebImei->getAdapter()->quoteInto('into_date > ?', 0);
        $where[] = $QWebImei->getAdapter()->quoteInto('imei_sn = ?', $imei);

        $result = $QWebImei->fetchRow($where);
        if (!$result || !preg_match('/^[0-9]{15}$/', $imei)){
            //not existed in list sales out
            return 2;
        }

        $info['good_id'] = $result['good_id'];
        $info['good_color'] = $result['good_color'];

        $info['activated_at'] = $result['activated_date'];
        $info['out_date'] = $result['out_date'];
        //

        //check trong bảng lock_imei
        $QLockedImei = new Application_Model_LockedImei();
        $where = $QLockedImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $locked = $QLockedImei->fetchRow($where);

        if ($locked) {
            return 6; // hàng cấm, éo tính
        }

        //check trong bảng imei_demo_fpt
        $QDemo = new Application_Model_ImeiDemoFpt();
        $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $locked = $QDemo->fetchRow($where);

        if ($locked) {
            return 7; // hàng demo của fpt, éo tính
        }

        //check hàng demo, for staff, for lending
        // check trong bang imei_exception
        $QImeiException = new Application_Model_ImeiException();
        $where = $QImeiException->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $imei_exception = $QImeiException->fetchRow($where);
        //check hàng demo
        if ($imei_exception){ // neu co trong exception thi uu tien lay trong do

            // kiem tra type cua exception: 1: xuat cho retailer; 2: xuat demo ...
            if ($imei_exception['type'] == 2) {
                return 7; // hàng demo, éo tính
            }

            if ($imei_exception['type'] == 3) {
                return 8; // hàng staff, éo tính
            }

            if ($imei_exception['type'] == 4) {
                return 9; // hàng lending, éo tính
            }

        } else {
            //check trong bảng imei_demo_fpt
            $QDemo = new Application_Model_ImeiDemoFpt();
            $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
            $locked = $QDemo->fetchRow($where);

            if ($locked) {
                return 7; // hàng demo, éo tính
            }

            //check hàng demo
            $QMarket = new Application_Model_Market();
            $where = array();
            $where[] = $QMarket->getAdapter()->quoteInto('sn = ?', $result['sales_sn']);
            $where[] = $QMarket->getAdapter()->quoteInto('outmysql_time >= ?', '2014-06-01 00:00:00');
            $market = $QMarket->fetchRow($where);

            if ($market) {
                if ($market['type'] == 2) {
                    return 7; // hàng demo, éo tính
                }

                if ($market['type'] == 3) {
                    return 8; // hàng staff, éo tính
                }

                if ($market['type'] == 4) {
                    return 9; // hàng lending, éo tính
                }
            }
        }

        //check trong phần timing
        $QTimingSale = new Application_Model_TimingSale();
        $where = array();
        $where[] = $QTimingSale->getAdapter()->quoteInto('id <> ?', $timing_sales_id);
        $where[] = $QTimingSale->getAdapter()->quoteInto('imei = ?', $imei);

        $ts = $QTimingSale->fetchRow($where);

        // IMEI đã chấm công
        if ($ts){
            $QTiming = new Application_Model_Timing();
            $where = $QTiming->getAdapter()->quoteInto('id = ?', $ts['timing_id']);
            $result = $QTiming->fetchRow($where);

            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $user_id = $userStorage->id;

            if ($result && $result['staff_id'] == $user_id) {
                $info['date'] = $result['from'];

                $QStore = new Application_Model_Store();
                $store_rs = $QStore->find($result['store']);
                $store = $store_rs->current();

                $info['store'] = $store['name'];

                return 1; // nó chấm rồi
            } else {
                $QStaff = new Application_Model_Staff();
                $staff_rs = $QStaff->find($result['staff_id']);
                $staff = $staff_rs->current();

                $QStore = new Application_Model_Store();
                $store_rs = $QStore->find($result['store']);
                $store = $store_rs->current();

                if ($staff) {
                    $info['staff'] = $staff['firstname'] . ' ' . $staff['lastname'] . ' | ' . preg_replace('/oppomobile.vn/', '', $staff['email']) ;
                    $info['staff_id'] = $staff['id'];
                }

                if ($store)
                   $info['store'] = $store['name'];

                $info['date'] = $result['from'];
                $info['timing_sales_id'] = $ts['id'];

                // kiểm tra phòng trường hợp bảng imei acti ko có imei này
                // bán hơn 1 tháng
                if ( strtotime(date('Y-m-d')) - strtotime($result['from']) > 3*24*3600 ) return 5;

                return 3; // thằng khác chấm
            }
        }

        // IMEI chưa chấm công
        return 0;
    }

    public function reimportSaveAction()
    {
        $this->_helper->layout->disableLayout();

        if ( $this->getRequest()->getMethod() != 'POST' ) { // Big IF

        } else {
            define("TITLE_ROW", 1);
            define("ROW_START", 2);
            define("IMEI_COL", 0);
            define("DATE_COL", 1);
            define("TYPE_COL", 2);
            define("REASON_COL", 3);

            set_time_limit(0);
            ini_set('memory_limit', -1);

            $upload = new Zend_File_Transfer();

            $uniqid = uniqid('', true);
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'
                    . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files'
                    . DIRECTORY_SEPARATOR . 'timing'
                    . DIRECTORY_SEPARATOR . 'kpi'
                    . DIRECTORY_SEPARATOR  . $userStorage->id
                    . DIRECTORY_SEPARATOR . $uniqid;

            if (!is_dir($uploaded_dir))
                @mkdir($uploaded_dir, 0777, true);

            $upload->setDestination($uploaded_dir);

            $upload->setValidators(array(
                'Size'  => array('min' => 50, 'max' => 5000000),
                'Count' => array('min' => 1, 'max' => 1),
                'Extension' => array('xlsx', 'xls'),
            ));

            if (!$upload->isValid()){ // validate IF
                $errors = $upload->getErrors();
                $sError = null;

                if ($errors and isset($errors[0]))
                    switch ($errors[0]) {
                        case 'fileUploadErrorIniSize':
                            $sError = 'File size is too large';
                            break;
                        case 'fileMimeTypeFalse':
                            $sError = 'The file you selected weren\'t the type we were expecting';
                            break;
                        case 'fileExtensionFalse':
                            $sError = 'Please choose a file in XLS or XLSX format.';
                            break;
                        case 'fileCountTooFew':
                            $sError = 'Please choose a PO file (in XLS or XLSX format)';
                            break;
                        case 'fileUploadErrorNoFile':
                            $sError = 'Please choose a PO file (in XLS or XLSX format)';
                            break;
                        case 'fileSizeTooBig':
                            $sError = 'File size is too big';
                            break;
                    }

                $this->view->error = $sError;

            } else {
                try {
                    $upload->receive();

                    $path_info = pathinfo($upload->getFileName());
                    $filename = $path_info['filename'];
                    $extension = $path_info['extension'];

                    $old_name = $filename . '.'.$extension;
                    $new_name = 'UPLOAD-'.md5($filename . uniqid('', true)) . '.'.$extension;

                    if (is_file($uploaded_dir . DIRECTORY_SEPARATOR . $old_name)){
                        rename($uploaded_dir . DIRECTORY_SEPARATOR . $old_name, $uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                    } else {
                        $new_name = $old_name;
                    }

                    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                    $QFileLog = new Application_Model_FileUploadLog();

                    $data = array(
                        'staff_id'       => $userStorage->id,
                        'folder'         => $uniqid,
                        'filename'       => $new_name,
                        'type'           => 'reimport kpi',
                        'real_file_name' => $filename . '.'.$extension,
                        'uploaded_at'    => time(),
                        );

                    $log_id = $QFileLog->insert($data);

                    // action
                    $error_list = array();
                    $success_list = array();
                    $number_of_order = 0;
                    $total_order_row = 0;

                    require_once 'PHPExcel.php';
                    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
                    $cacheSettings = array( 'memoryCacheSize' => '32MB');
                    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

                    switch ($extension) {
                        case 'xls':
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                            break;
                        case 'xlsx':
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                            break;
                        default:
                            throw new Exception("Invalid file extension");
                            break;
                    }

                    $objReader->setReadDataOnly(true);

                    $objPHPExcel = $objReader->load($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

                    $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
                    $total_order_row = $highestRow - ROW_START + 1;

                    $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
                    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

                    // get title
                    $file_title = array();
                    for ($i = 1; ( $title_tmp = trim($objWorksheet
                            ->getCellByColumnAndRow($i, TITLE_ROW)
                            ->getValue()) ) != ''; $i++) {
                        $file_title[] = $title_tmp;
                    }

                    $staff_list = array();
                    $QImeiKpi = new Application_Model_ImeiKpi();

                    $QStaff = new Application_Model_Staff();
                    $staff_cache = $QStaff->get_cache();

                    $QGood = new Application_Model_Good();
                    $good_cache = $QGood->get_cache();

                    $QGoodColor = new Application_Model_GoodColor();
                    $color_cache = $QGoodColor->get_cache();

                    $QStore = new Application_Model_Store();
                    $store_cache = $QStore->get_cache();

                    // $db = Zend_Registry::get('db');
                    // $db->beginTransaction();

                    // $i là dòng
                    for ($i = ROW_START; $i <= $highestRow; $i++) {
                        try {
                            $flag = true;
                            $number_of_order++;

                            $percent = round($number_of_order*100/$total_order_row, 1);
                            // file_put_contents(APPLICATION_PATH.'/../public/files/timing/'.$userStorage->id.'/status.txt', $percent);

                            // get IMEI
                            $imei_tmp = trim($objWorksheet
                                ->getCellByColumnAndRow(IMEI_COL, $i)
                                ->getValue()
                            );
                            // get date
                            $date_tmp = trim($objWorksheet
                                ->getCellByColumnAndRow(DATE_COL, $i)
                                ->getValue()
                            );
                            // get type
                            $type_tmp = trim($objWorksheet
                                ->getCellByColumnAndRow(TYPE_COL, $i)
                                ->getValue()
                            );
                            // get reason
                            $reason_tmp = trim($objWorksheet
                                ->getCellByColumnAndRow(REASON_COL, $i)
                                ->getValue()
                            );

                            if (!isset($imei_tmp) || empty($imei_tmp)) continue;

                            $where = $QImeiKpi->getAdapter()->quoteInto('imei_sn = ?', $imei_tmp);
                            $imei_kpi = $QImeiKpi->fetchRow($where);

                            if (!$imei_kpi) {
                                $error_list[] = array(
                                    'reason' => 'IMEI not found: '.$imei_tmp,
                                    'row'    => $i,
                                );
                                continue;
                            }

                            if (!isset($imei_kpi['status']) || $imei_kpi['status'] != 0) {
                                $error_list[] = array(
                                    'reason' => 'Status not for reimporting: '.$imei_tmp,
                                    'row'    => $i,
                                );
                                continue;
                            }

                            if (!isset($imei_kpi['timing_date']) || $imei_kpi['timing_date'] != $date_tmp) {
                                $error_list[] = array(
                                    'reason' => 'Timing date not match: '.$imei_tmp,
                                    'row'    => $i,
                                );
                                continue;
                            }

                            $kpi = false;

                            // update
                            if (isset($type_tmp) && $type_tmp == 1) {
                                $imei_old_date = $imei_kpi['timing_date'];
                                $imei_old_day = date('d', strtotime($imei_kpi['timing_date']));
                                $next_month = date_add(
                                    date_create_from_format(
                                        'Y-m-d',
                                        date("Y-m-01", strtotime($imei_kpi['timing_date']))
                                    ),
                                    new DateInterval("P1M")
                                );

                                $next_month_last_day = $next_month->format('t');

                                // cập nhật lại ngày
                                // nếu ngày imei lớn hơn ngày cuối cùng của tháng tiếp theo thì lấy là ngày cuối tháng
                                if ($imei_old_day > $next_month_last_day) $imei_old_day = $next_month_last_day;

                                $imei_new_date = date('Y-m-', strtotime($imei_old_date)).$imei_old_day;
                                $next_month = date_add(date_create_from_format('Y-m-d', $imei_new_date), new DateInterval("P1M"));

                                $data = $imei_kpi->toArray();
                                $data['timing_date'] = $next_month->format("Y-m-d 00:00:00");
                                $data['status'] = 2;
                                $where = $QImeiKpi->getAdapter()->quoteInto('imei_sn = ?', $imei_tmp);

                                $QImeiKpi->update($data, $where);
                                $kpi = true;
                            }

                            $staff_list[ $imei_kpi['pg_id'] ][ $kpi ? "kpi" : ($type_tmp == 0 ? "no_kpi" : "will_kpi") ][] = array(
                                'imei'     => $imei_kpi['imei_sn'],
                                'good_id'  => $imei_kpi['good_id'],
                                'color_id' => $imei_kpi['color_id'],
                                'store_id' => $imei_kpi['store_id'],
                                'old_date' => date_create_from_format("Y-m-d H:i:s", $imei_kpi['timing_date'])->format('d/m/Y'),
                                'new_date' => $kpi ? $next_month->format("d/m/Y") : null,
                                'reason'   => $kpi ? null : $reason_tmp,
                            );

                            $success_list[] = array('imei' => $imei_tmp, 'date' => $imei_kpi['timing_date']);
                        } catch (Exception $e) {
                            // $db->rollback();
                            $error_list[] = array(
                                'reason' => 'Unknown Error',
                                'row'    => $i,
                            );
                            continue;
                        } // try-catch
                    } // END big FOR

                    // $db->commit();

                    // gửi thông báo:
                    $title = sprintf("Danh sách IMEI được tính KPI bổ sung");
                    $category_id = 4;

                    if (isset($staff_list) && count($staff_list)) {
                        foreach ($staff_list as $_staff_id => $_timings) {
                            if (! count($_timings) ) continue;

                            $content = sprintf("<p>Chào bạn %s,</p>
                                <p>Sau khi Công ty thực hiện kiểm tra, xác nhận thông tin khách hàng với các IMEI chưa được tính KPI ở tháng trước do chưa active, Công ty quyết định:</p>",
                                $staff_cache[ $_staff_id ],
                                date("d/m/Y")
                            );

                            // danh sách IMEI bổ sung
                            if (isset($_timings['kpi']) && count($_timings['kpi'])) {
                                $content .= "<p>1. Bổ sung  KPI cho các IMEI sau trong kỳ tính KPI tháng 6/2015:</p>
                                    <table class=\"table\">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>IMEI</th>
                                                <th>Model</th>
                                                <th>Màu sắc</th>
                                                <th>Ngày bán</th>
                                                <th>Ngày được tính KPI</th>
                                                <th>Cửa hàng</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                $i = 1;
                                foreach ($_timings['kpi'] as $_key => $_timing) {
                                    $content .= sprintf("<tr>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>",
                                        $i++,
                                        $_timing['imei'],
                                        isset( $good_cache[ $_timing['good_id'] ] ) ? $good_cache[ $_timing['good_id'] ] : '#',
                                        isset( $color_cache[ $_timing['color_id'] ] ) ? $color_cache[ $_timing['color_id'] ] : '#',
                                        $_timing['old_date'],
                                        $_timing['new_date'],
                                        isset( $store_cache[ $_timing['store_id'] ] ) ? $store_cache[ $_timing['store_id'] ] : '#'
                                    );
                                } // END foreach: danh sách IMEI bổ sung
                                $content .= "</tbody>
                                    </table>";
                            }

                            ///////////////////////////////

                            // danh sách IMEI không bổ sung
                            if (isset($_timings['no_kpi']) && count($_timings['no_kpi'])) {
                                $i = 1;
                                $content .= "<p></p><p>2. Không tính KPI cho các IMEI sau:</p>";
                                $content .= "<p></p><table class=\"table\">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>IMEI</th>
                                            <th>Model</th>
                                            <th>Màu sắc</th>
                                            <th>Ngày bán</th>
                                            <th>Cửa hàng</th>
                                            <th>Lý do</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                foreach ($_timings['no_kpi'] as $_key => $_timing) {
                                    $content .= sprintf("<tr>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>",
                                        $i++,
                                        $_timing['imei'],
                                        isset( $good_cache[ $_timing['good_id'] ] ) ? $good_cache[ $_timing['good_id'] ] : '#',
                                        isset( $color_cache[ $_timing['color_id'] ] ) ? $color_cache[ $_timing['color_id'] ] : '#',
                                        $_timing['old_date'],
                                        isset( $store_cache[ $_timing['store_id'] ] ) ? $store_cache[ $_timing['store_id'] ] : '#',
                                        $_timing['reason']
                                    );
                                } // END foreach: danh sách IMEI không bổ sung
                                $content .= "</tbody>
                                    </table><p></p>";
                            }

                            // danh sách IMEI sẽ xem xét bổ sung
                            if (isset($_timings['will_kpi']) && count($_timings['will_kpi'])) {
                                $i = 1;
                                $content .= "<p></p><p>3. IMEI xem xét thưởng KPI trong kỳ tính KPI tháng 7:</p>";
                                $content .= "<p></p><table class=\"table\">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>IMEI</th>
                                            <th>Model</th>
                                            <th>Màu sắc</th>
                                            <th>Ngày bán</th>
                                            <th>Cửa hàng</th>
                                            <th>Lý do</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                foreach ($_timings['will_kpi'] as $_key => $_timing) {
                                    $content .= sprintf("<tr>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>",
                                        $i++,
                                        $_timing['imei'],
                                        isset( $good_cache[ $_timing['good_id'] ] ) ? $good_cache[ $_timing['good_id'] ] : '#',
                                        isset( $color_cache[ $_timing['color_id'] ] ) ? $color_cache[ $_timing['color_id'] ] : '#',
                                        $_timing['old_date'],
                                        isset( $store_cache[ $_timing['store_id'] ] ) ? $store_cache[ $_timing['store_id'] ] : '#',
                                        $_timing['reason']
                                    );
                                } // END foreach: danh sách IMEI không bổ sung
                                $content .= "</tbody>
                                    </table><p></p>";
                            }

                            My_Notification::add(
                                $title,
                                $content,
                                $category_id,
                                array(
                                    'staff' => $_staff_id,
                                    'from'  => date('d/m/Y H:i:s'),
                                    'to'    => (new DateTime('now'))
                                        ->add(new DateInterval('P20D'))
                                        ->format('d/m/Y H:i:s'),
                                ),
                                1,
                                My_Notification_Type::SystemCreate
                            );

                            unset($content);
                        } // END foreach: tạo notification
                    }

                    unset($staff_list);
                    $data = array(
                        'total'   => $number_of_order,
                        'failed'  => count($error_list),
                        'succeed' => $number_of_order - count($error_list),
                        'value'   => 0,
                    );

                    // xuất file excel các order lỗi
                    if (is_array($error_list) && count($error_list) > 0) {
                        $data['error_file_name'] = 'FAILED-'.md5(microtime(true) . uniqid('', true)) . '.'.$extension;
                        // xuất excel @@
                        //
                        $objPHPExcel_out = new PHPExcel();
                        $objPHPExcel_out->createSheet();
                        $objWorksheet_out = $objPHPExcel_out->getActiveSheet();
                        //
                        $objWorksheet = $objPHPExcel->getActiveSheet();

                        $alpha = 'A';
                        $i = 1;
                        foreach ($file_title as $key => $value)
                            $objWorksheet_out->setCellValue($alpha++.$i, $value);

                        $i++;
                        // các dòng lỗi
                        $objWorksheet = $objPHPExcel->getActiveSheet();
                        foreach ($error_list as $key => $row) {
                            for ($j=0; $j <= $highestColumnIndex; $j++) {
                                if ($j == IMEI_COL)
                                    $objWorksheet_out->getCellByColumnAndRow($j, $i)->setValueExplicit($objWorksheet->getCellByColumnAndRow($j, $row['row'])->getValue(), PHPExcel_Cell_DataType::TYPE_STRING);

                                else
                                    $objWorksheet_out->setCellValueByColumnAndRow(
                                        $j, $i, $objWorksheet->getCellByColumnAndRow($j, $row['row'])->getValue()
                                    );
                            }

                            $objWorksheet_out->setCellValueByColumnAndRow($j, $i, $row['reason']);

                            $i++;
                        }
                        //
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

                        $new_file_dir = $uploaded_dir . DIRECTORY_SEPARATOR . $data['error_file_name'];

                        $objWriter->save($new_file_dir);
                    }
                    // END IF // xuất file excel các order lỗi

                    // xuất file excel các order thành công
                    if (is_array($success_list) && count($success_list) > 0) {
                        $data['success_file_name'] = 'SUCCESS-'.md5(microtime(true) . uniqid('', true)) . '.'.$extension;
                        // xuất excel @@
                        //
                        $objPHPExcel_out = new PHPExcel();
                        $objPHPExcel_out->createSheet();
                        $objWorksheet_out = $objPHPExcel_out->getActiveSheet();
                        //
                        $headers = array(
                            'No.',
                            'IMEI',
                            'Date',
                            );

                        $sxx_list = array();
                        $stt = 1;
                        $objWorksheet_out->fromArray($headers, NULL, 'A1');
                        $index = 2;
                        foreach ($success_list as $key => $value) {
                            $col = "A";
                            $objWorksheet_out->setCellValue($col++.$index, $stt++);
                            $objWorksheet_out->getCell($col++.$index)->setValueExplicit($value['imei'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $objWorksheet_out->getCell($col++.$index)->setValueExplicit($value['date'], PHPExcel_Cell_DataType::TYPE_STRING);
                            $index++;
                        }

                        $objWorksheet_out->fromArray($sxx_list, NULL, 'A2');

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

                        $new_file_dir = $uploaded_dir . DIRECTORY_SEPARATOR . $data['success_file_name'];

                        $objWriter->save($new_file_dir);
                    }
                    // END IF // xuất file excel các order thành công

                    $where = $QFileLog->getAdapter()->quoteInto('id = ?', $log_id);
                    $QFileLog->update($data, $where);

                    $this->view->error_list = $error_list;
                    $this->view->objWorksheet = $objWorksheet;
                    $this->view->number_of_order = $number_of_order;

                    $this->view->success_file = isset($data['success_file_name']) ? (HOST
                            . 'files'
                            . DIRECTORY_SEPARATOR . 'timing'
                            . DIRECTORY_SEPARATOR . 'kpi'
                            . DIRECTORY_SEPARATOR  . $userStorage->id
                            . DIRECTORY_SEPARATOR . $uniqid
                            . DIRECTORY_SEPARATOR . $data['success_file_name']):false;
                    $this->view->error_file = isset($data['error_file_name']) ? (HOST
                            . 'files'
                            . DIRECTORY_SEPARATOR . 'timing'
                            . DIRECTORY_SEPARATOR . 'kpi'
                            . DIRECTORY_SEPARATOR  . $userStorage->id
                            . DIRECTORY_SEPARATOR . $uniqid
                            . DIRECTORY_SEPARATOR . $data['error_file_name']):false;

                } catch (Zend_File_Transfer_Exception $e) {
                    $this->view->error =  $e->getMessage();
                } catch (Exception $e) {
                    $this->view->error =  $e->getMessage();
                }
            } // END validate IF

            // file_put_contents(APPLICATION_PATH.'/../public/files/timing/'.$userStorage->id.'/status.txt', '0');
        } // END Big IF
    }
}

