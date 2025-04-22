<?php
class My_Report_Hr {

    // PC Commission

    public static function kpiPg($params) {
        
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);
        
        //print_r($params); die;

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            /*'Store Name',
            'ASM/Sale/Leader Name'*/
        );

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $good_list = $QGoodKpi->get_list($params);
        //print_r($good_list);die;

        for ($i=0;$i<count($good_list);$i++) {
            $d = explode('-', $good_list[$i]['from_date']);
            $from = $d[2].'/'.$d[1].'/'.$d[0];

            $d = explode('-', $good_list[$i]['to_date']);
            $to = $d[2].'/'.$d[1].'/'.$d[0];

            array_push($heads, $good_list[$i]['good_name']."_".$good_list[$i]['color_name']."|".$from." - ".$to);
        }
        
        array_push($heads,'Total Sell Out');
        array_push($heads,'Total Score');
        array_push($heads,'Commission Rate : '.COMMISSION_RATE.' Bath/Score');
        array_push($heads,'Total Price');
        array_push($heads,'Work Days');
        array_push($heads,'Threshold');

        //print_r($heads);die;

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        $chk_heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            /*'Store Name',
            'ASM/Sale/Leader Name',*/
            'Total Sell Out',
            'Total Score',
            'Commission Rate : '.COMMISSION_RATE.' Bath/Score',
            'Total Price',
            'Work Days',
            'Threshold'
        );

        $i = 1;
        foreach ($heads as $value) {

            if (!in_array($value, $chk_heads)) {
                $tmp = explode('|', $value);
                $sheet->setCellValue($alpha.$index, $tmp[0]);
                $sheet->setCellValue($alpha.'2', $tmp[1]);

/*
                // check model F1 for Commission Rule 
                $chk_f1_tmp = explode('_', $tmp[0]);
                if ($chk_f1_tmp[0] == 'F1f') { $col_f1[] = $i; }
*/
            } else {
                $sheet->setCellValue($alpha.$index, $value);
            }
            $alpha++;
            $i++;
        }

        $log = $QGoodKpi->report_kpiPC($good_list,$params);
        //print_r($log); die;

        // get Staff Grade
        $SG_List = array();
        $staff_grade = $QGoodKpi->getStaffGradeList($params);
        for ($i=0;$i<count($staff_grade);$i++) { 
            $SG_List[ $staff_grade[$i]['staff_code'] ] = $staff_grade[$i]['percent']; 
        }

        $index = 3;
        $n = 1;
        $bkk_list = array('BKK East-1','BKK East-2','BKK West-1','BKK West-2');

        // loop row
        $i = 0;
        //$cnt_log = count($log);
        foreach ($log as $value ) {
            $alpha = 'A';

            // skip last row
            //if ($i == $cnt_log - 1) { break; }

            $sheet->setCellValue($alpha++ . $index, $n++);

            // loop model
            $total_kpi = 0;
            $total_price = 0;
            $j = 0;
            $cnt_value = count($value);
/*
            if (!in_array($value['area_name'], $bkk_list)) {
                $f1_result = array();
                $f1_result = $QGoodKpi->compare_f1_sellout($value['staff_code'],$params);
            } 
            $style_f1 = array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'088A08')));
*/
            //print_r($value); die;
            foreach ($value as $row) {

                // prepare data on excel
                if ($j == $cnt_value - 1) { 
                    // last coloumn of Model
                    $temp = explode('|', $row);
                    $sheet->setCellValue($alpha . $index, $temp[0]);
                } else { 
                    // skip created_at 
                    if ($j == 2) { $staff_created = $row; } 
                    // skip off_date
                    else if ($j == 3) { $staff_offdate = $row; } 
                    // place data on excel
                    else if ($j > 3) {
                        $temp = explode('|', $row);

                        //$temp[0] = $row; 
/*
                        // Exclude BKK
                        if (!in_array($value['area_name'], $bkk_list)) { 
                            // if more than 30% of last month 
                            if (in_array($j, $col_f1)) {
                                if ($staff_created >= $params['from']." 00:00:00") {
                                    // New PC Need 6 item to get 10 point
                                    if ($f1_result['sellout_02'] >= 6) { 
                                        $temp[1] = 10; 
                                        $sheet->getStyle($alpha.$index)->applyFromArray($style_f1);
                                    }
                                } else {
                                    // Old PC Need at least 4 item to get 10 point
                                    if ($f1_result['sellout_02'] >= 6) {
                                        $f1_threshold = ceil( $f1_result['sellout_01'] * 1.3 );
                                        if ($f1_result['sellout_02'] >= $f1_threshold) { 
                                            $temp[1] = 10; 
                                            $sheet->getStyle($alpha.$index)->applyFromArray($style_f1);
                                        }
                                    } 
                                }
    
                                $sheet->setCellValue($alpha++ . $index, $temp[0]);
                                // debug set
                                //$sheet->setCellValue($alpha++ . $index, $temp[0]."|".$temp[1]."|".$f1_result['sellout_01']."|".$f1_result['sellout_02']."|".$f1_threshold);
                            } else {
                                $sheet->setCellValue($alpha++ . $index, $temp[0]);
                            }
                        } else {
                            $sheet->setCellValue($alpha++ . $index, $temp[0]);
                        }
*/

                        $sheet->setCellValue($alpha++ . $index, $temp[0]);

                        $total_kpi = $total_kpi + ( intval($temp[0]) * intval($temp[1]) ); 
                        $total_price = $total_price + (intval($temp[0]) * intval($temp[2]));
                    }
                    else {
                        $sheet->setCellValue($alpha++ . $index, $row);
                    }
                    
                }

                $j++;
            }

            $beta = $alpha;
            $alpha++;
            $sheet->setCellValue($alpha++ . $index, "=SUM(E".$index.":".$beta."".$index.")");

            //$beta = $alpha;
            //$alpha++;
            $sheet->setCellValue($alpha . $index, $total_kpi);

            $beta = $alpha;
            $alpha++;

            // add filter threshold
            if (!is_null($staff_offdate) && $staff_offdate <= $params['to']." 23:59:59") { 
                $now = strtotime($staff_offdate); 

                $tmp_off = explode("-", $staff_offdate);
                $created_at = strtotime(date($tmp_off[0]."-".$tmp_off[1]."-01"));

                if ( $created_at < strtotime($staff_created) ) { $created_at = strtotime($staff_created); }
            } else { 
                $now = strtotime($params['to']." 23:59:59"); 
                $created_at = strtotime($staff_created);
            }

            $diffdate = $now - $created_at;
            $work_day = floor($diffdate/(60*60*24)) + 1; // plus 1 day

            if ($work_day >= 30) { $threshold = 50; } 
            else { $threshold = $work_day * 1.67; }

            $sellout_result = array();
            $style_threshold = array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFFF00')));

            if ($total_kpi >= $threshold) { 
                if (in_array($value['staff_code'], array_keys($SG_List) )) {
                    $sheet->setCellValue($alpha++ . $index, "=(".$beta.$index."*".COMMISSION_RATE.")*".$SG_List[ $value['staff_code'] ]);
                } else {
                    $sheet->setCellValue($alpha++ . $index, "=".$beta.$index."*".COMMISSION_RATE);
                }
            } else { 
                // check total sellout of this staff (All Store and Area)
                $sellout_result = $QGoodKpi->getAllSelloutByStaff($params);
                if ($sellout_result['total_kpi'] >= $threshold) {

                    $sheet->getStyle($alpha.$index)->applyFromArray($style_threshold);

                    if (in_array($value['staff_code'], array_keys($SG_List) )) {
                        $sheet->setCellValue($alpha++ . $index, "=(".$beta.$index."*".COMMISSION_RATE.")*".$SG_List[ $value['staff_code'] ]);
                    } else {
                        $sheet->setCellValue($alpha++ . $index, "=".$beta.$index."*".COMMISSION_RATE);
                    }
                } else {
                    $sheet->setCellValue($alpha++ . $index, "0"); 
                }

            }

            //$sheet->setCellValue($alpha++ . $index, "=".$beta.$index."*".COMMISSION_RATE);

            $sheet->setCellValue($alpha++ . $index, $total_price);
            $sheet->setCellValue($alpha++ . $index, $work_day);
            $sheet->setCellValue($alpha++ . $index, $threshold);

            //$sheet->setCellValue($alpha++ . $index, $sellout_result['sellout']);
            //$sheet->setCellValue($alpha++ . $index, $sellout_result['total_kpi']);

            $PHPExcel->getActiveSheet()->getStyle('C'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

            $index++;
            $i++;

        }

        $filename = 'Sell out - KPI - PC - '.date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;

    }

    // KPI PC By Target [PC]
    public static function kpiTarget($params) {
        
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);
        
        // print_r($params); die;

        $last_02 = date('M-Y', strtotime("-2 Month", strtotime($params['from'])));
        $last_01 = date('M-Y', strtotime("-1 Month", strtotime($params['from'])));

        $filename = 'Target_KPI_PC_'.date('Y-m-d H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'No.',
            'Area',
            'Province',
            'District', 
            'Staff Code',
            'Staff Name',
            'PC Stand By',
            'Staff Created',
            'Staff Off Date',
            'Work Days [Months]',
            'Staff Join Store',
            'Staff Leave Store',
            'Store ID',
            'Store Name',
            'Store Type',
            'Market Type',
            'Market Name',
            // 'PCM Code',
            // 'PCM Name',
            'Current PC',
            'Current PC Stand By',
            'Store Target',
            'PC Target',
            $last_02." Unit",
            $last_02." Price",
            $last_01." Unit",
            $last_01." Price",
            'Unit Sellout',
            'Price Sellout',
            'Price Achieve (%)',
            'Sell Out [F11 Pro]',
        );

        fputcsv($output, $heads);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $result = $QGoodKpi->report_kpiTarget($params);

        $StoreStaff = new Application_Model_StoreStaff();
        // $StoreStaffLog = new Application_Model_StoreStaffLog();

        $no = 1;
        $date2 = new DateTime();

        foreach ($result as $data) {

            $result_pc_01 = $StoreStaff->getPC($data['store_id'], 0);
            $result_pc_02 = $StoreStaff->getPC($data['store_id'], 1);

            $cnt_pc_01 = count($result_pc_01);
            $cnt_pc_02 = count($result_pc_02);

            $achieve = round(( $data['sellout_price'] / $data['pc_target'] ) * 100, 2);

            // Get PCM Details
            // $pcm_list = $StoreStaffLog->get_pcm_name($data['store_id'], $params['from'], $params['to']);
            
            $date1 = new DateTime($data['staff_created']);
            
            // Have Off Date
            if( isset($data['staff_offdate']) ) { 
                $date3 = new DateTime($data['staff_offdate']);
                $interval = date_diff($date1, $date3); 
            } else { 
                $interval = date_diff($date1, $date2); 
            }

            $diff_month = $interval->m + ($interval->y * 12);
            $diff_day = $interval->d;

            $work_day = "";
            $work_day = $diff_month;

            $row = array();
            $row[] = $no++;
            $row[] = $data['area_name'];
            $row[] = $data['province_name'];
            $row[] = $data['district_name'];
            $row[] = $data['staff_code'];
            $row[] = $data['staff_name'];
            $row[] = $data['pc_stand_by'];
            $row[] = $data['staff_created'];
            $row[] = $data['staff_offdate'];
            $row[] = $work_day;

            $row[] = $data['joined_date'];
            $row[] = $data['released_date'];

            $row[] = $data['store_id'];
            $row[] = $data['store_name'];
            $row[] = $data['store_type'];
            $row[] = $store_market[ $data['store_id'] ]['market_type'];
            $row[] = $store_market[ $data['store_id'] ]['market_name'];

            // $row[] = $pcm_list['pcm_code'];
            // $row[] = $pcm_list['pcm_name'];
            $row[] = $cnt_pc_01;
            $row[] = $cnt_pc_02;
            $row[] = $data['store_target'];
            $row[] = $data['pc_target'];

            $row[] = $data['last_02'];
            $row[] = $data['last_02_price'];
            $row[] = $data['last_01'];
            $row[] = $data['last_01_price'];
            $row[] = $data['sellout'];
            $row[] = $data['sellout_price'];
            $row[] = $achieve;
            $row[] = $data['sellout_f11pro'];

            fputcsv($output, $row);
        }

        exit;

    }

    // KPI PC By Target Hero Product [F1s]
    public static function kpiTargetHeroProduct($params) {
        
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);
        
        //print_r($params); die;

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'Province',
            'District', 
            'Staff Code',
            'Staff Name',
            'Staff Off Date',
            'Store ID',
            'Store Name',
            'Store Type',
            'Number of PC',
            'Punish F1s',
            'Reward F1s',
            'Total Sell Out [NoAc]',
            'Total Sell Out',
            'Total Sell Out [F1s-NoAc]',
            'Total Sell Out [F1s]',
            'Sellout Punish',
            'Sellout Reward',
            'Comission Punish',
            'Comission Reward',
        );

        //print_r($heads);die;

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach ($heads as $value) {
            $sheet->setCellValue($alpha.$index, $value);
            $alpha++;
        }

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $StoreStaffLog = new Application_Model_StoreStaffLog();

        $log = $QGoodKpi->report_kpiTargetHeroProduct($params);
        //print_r($log); die;

        // get Staff Grade
        /*
        $SG_List = array();
        $staff_grade = $QGoodKpi->getStaffGradeList($params);
        for ($i=0;$i<count($staff_grade);$i++) { 
            $SG_List[ $staff_grade[$i]['staff_code'] ] = $staff_grade[$i]['percent']; 
        }*/

        $index = 2;
        $n = 1;

        //$cnt_log = count($log);
        foreach ($log as $value ) {
            $cnt_pc = 0;
            $result = array();
            $where_ss = array();

            $where_ss[] = $StoreStaffLog->getAdapter()->quoteInto('store_id = ?', $value['store_id']);
            $where_ss[] = $StoreStaffLog->getAdapter()->quoteInto('is_leader = 0');
            $where_ss[] = $StoreStaffLog->getAdapter()->quoteInto('? >= FROM_UNIXTIME(joined_at, \'%Y-%m-%d\')', $params['from']);
            $where_ss[] = $StoreStaffLog->getAdapter()->quoteInto('( ? < FROM_UNIXTIME(released_at, \'%Y-%m-%d\') OR released_at IS NULL OR released_at = 0 )', $params['to']);

            $result_ss = $StoreStaffLog->fetchAll($where_ss);

            $tmp_cnt_pc = array();
            foreach($result_ss as $item) { $tmp_cnt_pc[ $item['staff_id'] ] = $item; }

            $cnt_pc = count($tmp_cnt_pc);

            if(!isset($value['punish'])) { $value['punish'] = 0; } 
            if(!isset($value['reward'])) { $value['reward'] = 0; } 

            if($cnt_pc > 1) {
                $punish = ceil($value['punish'] / $cnt_pc);
                $reward = ceil($value['reward'] / $cnt_pc);
            } else {
                $punish = $value['punish'];
                $reward = $value['reward'];
            }

            if($value['total_sellout_hero_product'] > $punish) {
                $sellout_punish = 0;
            } else {
                $sellout_punish = $punish - $value['total_sellout_hero_product'];
            }

            if($value['total_sellout_hero_product'] < $reward) {
                $sellout_reward = 0;
            } else {
                
                if ($reward == 0) { $sellout_reward = 0; }
                else { $sellout_reward = $value['total_sellout_hero_product'] - $reward; }
                
                // for test 
                //$sellout_reward = $value['total_sellout_hero_product'] - $reward;
            }

            $com_punish = $sellout_punish * 200;
            
            if($sellout_reward > 0) {
                $params['staff_id'] = $value['staff_id'];
                $params['limit'] = $sellout_reward;

                $com_reward = $QGoodKpi->getTargetHeroProductReward($params);

                unset($params['staff_id']);
                unset($params['limit']);

            } else {
                $com_reward['total_kpi'] = 0;
            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $n++);            
            $sheet->setCellValue($alpha++ . $index, $value['area_name']);
            $sheet->setCellValue($alpha++ . $index, $value['province_name']);
            $sheet->setCellValue($alpha++ . $index, $value['district_name']);
            $sheet->setCellValue($alpha++ . $index, $value['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $value['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $value['staff_offdate']);
            $sheet->setCellValue($alpha++ . $index, $value['store_id']);
            $sheet->setCellValue($alpha++ . $index, $value['store_name']);
            $sheet->setCellValue($alpha++ . $index, $value['store_type']);
            $sheet->setCellValue($alpha++ . $index, $cnt_pc);
            $sheet->setCellValue($alpha++ . $index, $punish);
            $sheet->setCellValue($alpha++ . $index, $reward);
            $sheet->setCellValue($alpha++ . $index, $value['total_sellout_no_ac']);
            $sheet->setCellValue($alpha++ . $index, $value['total_sellout']);
            $sheet->setCellValue($alpha++ . $index, $value['total_sellout_hero_product_no_ac']);
            $sheet->setCellValue($alpha++ . $index, $value['total_sellout_hero_product']);
            $sheet->setCellValue($alpha++ . $index, $sellout_punish);
            $sheet->setCellValue($alpha++ . $index, $sellout_reward);
            $sheet->setCellValue($alpha++ . $index, $com_punish);
            $sheet->setCellValue($alpha++ . $index, "=".$com_reward['total_kpi']."*".COMMISSION_RATE);

            $index++;
        }

        $filename = 'Target - F1s - KPI - PC - '.date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;

    }


    // KPI PC By Target [Store]
    public static function kpiTargetByStore($params) {
        
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);
        
        //print_r($params); die;

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Store ID',
            'Store Name',
            'Store Type',
            'Market Type',
            'Market Name',
            'PC',
            'PC Stand By',
            'Store Target',
            'Unit Sellout',
            'Price Sellout',
            'Price Achieve',
        );

        //print_r($heads);die;

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach ($heads as $value) {
            $sheet->setCellValue($alpha.$index, $value);
            $alpha++;
        }

        $StoreStaff = new Application_Model_StoreStaff();
        
        $QStoreMarket = new Application_Model_StoreMarket();
        $market_info = $QStoreMarket->get_cache();

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $log = $QGoodKpi->report_kpiTargetByStore($params);
        // print_r($log); die;

        $index = 2;
        $n = 1;

        //$cnt_log = count($log);
        foreach ($log as $value ) {

            // Get PC Info 
            $result_pc_01 = $StoreStaff->getPC($value['store_id'], 0);
            $result_pc_02 = $StoreStaff->getPC($value['store_id'], 1);

            $cnt_pc_01 = count($result_pc_01);
            $cnt_pc_02 = count($result_pc_02);

            $achieve = round(( $value['sellout_price'] / $value['store_target'] ) * 100, 2);

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $n++);            
            $sheet->setCellValue($alpha++ . $index, $value['area_name']);
            $sheet->setCellValue($alpha++ . $index, $value['store_id']);
            $sheet->setCellValue($alpha++ . $index, $value['store_name']);
            $sheet->setCellValue($alpha++ . $index, $value['store_type']);

            $sheet->setCellValue($alpha++ . $index, $market_info[ $value['store_id'] ]['market_type']);
            $sheet->setCellValue($alpha++ . $index, $market_info[ $value['store_id'] ]['market_name']);

            $sheet->setCellValue($alpha++ . $index, $cnt_pc_01);
            $sheet->setCellValue($alpha++ . $index, $cnt_pc_02);

            $sheet->setCellValue($alpha++ . $index, $value['store_target']);
            $sheet->setCellValue($alpha++ . $index, $value['sellout']);
            $sheet->setCellValue($alpha++ . $index, $value['sellout_price']);
            $sheet->setCellValue($alpha++ . $index, number_format($achieve,2)."%");

            $index++;
        }

        $filename = 'Target By Store - KPI - PC - '.date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;

    }

    // PC Commission ALL
    public static function com_pc_export($data, $params) {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
/*
        $flag = 0;
        $area_list = array(81,82,83,85,86,90,91,92,94,95,97,98,99,100,101,103,104,105,106,107,108,110,115,116);

        if ( in_array($params['area_id'][0], $area_list) ) { $flag = 1; }

        $flag = 1;
*/
        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',

            'Total Sellout',
            'Commossion Rate',
            'Total Price',

            'Rate',
            // 'Incentive',
            'ค่าคอมมิชชั่นที่จ่ายจริง',
            'Work Day',
            'Remark'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Report Summary Commission for PC');
        $sheet->setCellValue('A2', "Report as of ".$params['from_date']." - ".$params['to_date']);

        $alpha = 'A';
        $index = 3;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle("A1:K3")->applyFromArray($style); 

        $index = 4;

        // Get Data 
        $QGoodKpi   = new Application_Model_GoodKpiLog();
        $QOIT       = new Application_Model_OppoIndividualTarget();
        $QPcActive  = new Application_Model_PcActive();
        $QPcChk     = new Application_Model_PcCheckInLog();
        $QPAE       = new Application_Model_PcAddonException();

        $EOL_List = $PCU_List_01 = $PCU_List_02 = array();

/*
        // Get Commission EOL
        $EOL_result = $QGoodKpi->comission_eol($params);
        for ($i=0;$i<count($EOL_result);$i++) { 

            if ( $EOL_result[$i]['total_unit'] >= 2 ) {
                $EOL_List[ $EOL_result[$i]['staff_code'] ] = $EOL_result[$i]['total_unit'] * 100; 
            } else {
                $EOL_List[ $EOL_result[$i]['staff_code'] ] = $EOL_result[$i]['total_unit'] * 0; 
            }

        }
*/

        // Get PC Un-Punish By Memo [Total Price < 200000]
        $pc_unpunish = $QGoodKpi->get_pc_unpunish($params);

        if ( !empty($pc_unpunish) ) { 

            $pc_unpunish_list = array();
            foreach ($pc_unpunish as $key => $value) {
                $pc_unpunish_list[$value['staff_code']] = $value['unpunish_type'];
            }

            for ($i=0;$i<count($pc_unpunish);$i++) { 
                            
                // Conditoin 01 : 200k
                if ( $pc_unpunish[$i]['con_01'] == 1 ) {
                    $PCU_List_01[ $pc_unpunish[$i]['staff_code'] ] = $pc_unpunish[$i]['staff_id'];
                }

                // Condition 02 : Index
                if ( $pc_unpunish[$i]['con_02'] == 1 ) {
                    $PCU_List_02[ $pc_unpunish[$i]['staff_code'] ] = $pc_unpunish[$i]['staff_id'];
                }
                 
            }

        }

        for ($i=0;$i<count($data);$i++) { 

            $flag = 1;

            $cnt = $i + 1;
            $remark = "";
            $com_rate = $data[$i]['com_pc']; 

            // Calculate Work Day
            $staff_created = $data[$i]['staff_joined'];
            $staff_offdate = $data[$i]['staff_offdate'];
            
            if (!is_null($staff_offdate) && $staff_offdate <= $params['to']." 23:59:59") { 
                $now = strtotime($staff_offdate); 
                $created_at = strtotime($staff_created);
            } else { 
                $now = strtotime($params['to']." 23:59:59"); 
                $created_at = strtotime($staff_created);
            }

            $diffdate = $now - $created_at;
            $work_day = floor($diffdate/(60*60*24)) + 1; // plus 1 day

            $store_level = $pc_target = $pc_level = $etest = $debug = '';
            $store_org_id = $store_type_id = '';

            $addon_rate = $addon_flag = 1;

            // Check PC Un-Punish Condition 2 : Index 
            if ( in_array($data[$i]['staff_code'], array_keys($PCU_List_02)) ) {
                $flag = 0;
                $remark = "ยกเว้นค่าคอมฯพิเศษ (Index) - ".$pc_unpunish_list[ $data[$i]['staff_code'] ];
            }

            if ($flag == 1) { 
                // Check Add-on 01 : Not PC Stand By
                if ( $data[$i]['pc_stand_by'] == 0 ) {
                    // Check Add-on 02 : Not New PC
                    if ( $work_day >= 30 ) {
                        // Check Add-on 03 : Check Number of Store 
                        if ( $data[$i]['cnt_store'] >= 1 ) {

                            $store_level = $data[$i]['st_level'];
                            $store_org_id = $data[$i]['st_org_id'];
                            $store_type_id = $data[$i]['st_type_id'];

                            // Check Add-on XX : PC Exception
                            if ( $data[$i]['cnt_store'] > 1 ) {

                                $params_addon = array(
                                    'staff_id'  => $data[$i]['staff_id'],
                                    'from'      => $params['from'],
                                    'to'        => $params['to'],
                                );

                                $result_pae = $QPAE->getPcExceptionStore($params_addon);

                                if ( !empty($result_pae) ) {
                                    $store_level = $result_pae['st_level'];
                                    $addon_flag = 0;
                                }
                                
                            } 

                            if ( $data[$i]['cnt_store'] == 1 ) { $addon_flag = 0; }
/*
                            $debug = 
                                "\r
                                    Staff ID : ".$data[$i]['staff_id']."\r
                                    PC Stand By : ".$data[$i]['pc_stand_by']."\r
                                    Cnt Store : ".$data[$i]['cnt_store']."\r
                                    Store Level : ".$store_level."\r
                                    Addon Flag : ".$addon_flag."\r
                                    Sellout : ".$data[$i]['total_sellout']."\r
                                    Price : ".$data[$i]['total_price']."\r
                                ";
*/
                            if ( $addon_flag == 0 ) {

                                $where = array();
                                $where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                $where[] = $QOIT->getAdapter()->quoteInto('from_date >= ?', $params['from']);
                                $where[] = $QOIT->getAdapter()->quoteInto('to_date <= ?', $params['to']);

                                $result_target = $QOIT->fetchRow($where);

                                // Check Add-on 04 : Check PC Target
                                if ( !empty($result_target) ) {

                                    $pc_target = $result_target['target_price'];
                                    // $debug .= "Target : ".$pc_target."\r"; 

                                    $where = array();
                                    $where[] = $QPcActive->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at >= ?', $params['from']." 00:00:00");
                                    $where[] = $QPcActive->getAdapter()->quoteInto('created_at <= ?', $params['to']." 23:59:59");

                                    $result_pc_grade = $QPcActive->fetchRow($where);

                                    // Check Add-on 05 : Check PC Grade
                                    if ( !empty($result_pc_grade) && $result_pc_grade['etest_count'] == 2 ) { 

                                        $work_point = $val_ach = $grade_point = $achive = $com_index = 0;

                                        // $pc_level = $result_pc_grade['grade'];
                                        $etest = $result_pc_grade['etest_result'];
                                        // $debug .= "PC Level : ".$pc_level."\r"; 

                                        // Check Add-on 06 : Check PC Work Hour
                                        $where = array();
                                        $where[] = $QPcChk->getAdapter()->quoteInto('status = ?', 'Y');
                                        $where[] = $QPcChk->getAdapter()->quoteInto('action_id = ?', 1);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('staff_id = ?', $data[$i]['staff_id']);
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in >= ?', $params['from']." 00:00:00");
                                        $where[] = $QPcChk->getAdapter()->quoteInto('check_in <= ?', $params['to']." 23:59:59");

                                        $result_pc_chk = $QPcChk->fetchAll($where);

                                        $work_hour = count($result_pc_chk);

                                        // Calculate Com Index 
                                        $work_point = -5;
                                        if ( $work_hour >= 25 ) { $work_point = 0; } 
                                        if ( $work_hour >= 28 ) { $work_point = 10; } 
/*
                                        switch ($pc_level) {
                                            case 'A': ($etest == 'Y') ? $grade_point = 20 : $grade_point = 10; break;
                                            case 'B': ($etest == 'Y') ? $grade_point = 10 : $grade_point = 5; break;
                                            case 'C': ($etest == 'Y') ? $grade_point = 5 : $grade_point = 0; break;
                                            default : $grade_point =  0; break;
                                        }
*/
                                        if ($etest == 'Y') { $grade_point = 20; } 
                                        else { $grade_point = 0; }

                                        $achive = round(( $data[$i]['total_price'] / $pc_target ) * 100, 2);
                                        $val_ach = round( ( ($data[$i]['total_price'] * $achive) / 100 ) / 10000 , 2);

                                        $com_index = $work_point + $grade_point + $val_ach;
/*
                                        $debug = 
                                            "\r
                                                Staff ID : ".$data[$i]['staff_id']."\r
                                                PC Stand By : ".$data[$i]['pc_stand_by']."\r
                                                Cnt Store : ".$data[$i]['cnt_store']."\r
                                                Store Level : ".$store_level."\r
                                                Addon Flag : ".$addon_flag."\r
                                                Work Hour : ".$work_hour." : ".$work_point."\r
                                                PC Level : ".$pc_level." : ".$grade_point."\r
                                                Sellout/Target : ".$data[$i]['total_sellout']." : ".$pc_target."\r
                                                Achieve : ".$achive."\r
                                                Price : ".$data[$i]['total_price']."\r
                                                Val_Ach : ".$val_ach."\r
                                                Index : ".$com_index."\r
                                            ";
*/
                                        if ( $store_level == 'S' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }
                                            if ($com_index > 70) { $addon_rate = 1.3; }
                                            
                                        }

                                        if ( $store_level == 'A' ) { 

                                            if ($com_index <= 60) { $addon_rate = 1; }
                                            if ($com_index > 60) { $addon_rate = 1.2; }

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

            if ( in_array($data[$i]['staff_code'], array('6005578','6101212','6202681','5801652','6204185','6006936')) ) {
                $addon_rate = 1;
            }

            // Commission EOL
            if (!empty($EOL_List)) {
                if (in_array($data[$i]['staff_code'], array_keys($EOL_List) )) {
                    $com_eol = $EOL_List[ $data[$i]['staff_code'] ];
                } else { $com_eol = 0; }
            }
            
            // Check Price Threshold
            $price_threshold = 200000; 

            if ( $data[$i]['total_price'] < $price_threshold ) {

                $params['staff_code'] = $data[$i]['staff_code'];
                // check total sellout of this staff (All Store and Area)
                $sellout_result = $QGoodKpi->getAllSelloutByStaff($params);

                if ( $sellout_result['total_price'] < $price_threshold ) {

                    if ($work_day < 30) {

                        $remark = "ยกเว้นเงื่อนไข - ทำงานไม่ถึง 30 วัน";

                    } else {

                        // Check PC Un-Punish Condition 1 : 200K 
                        if (!in_array($data[$i]['staff_code'], array_keys($PCU_List_01))) {

                            $addon_rate = 0;
                            $remark = "ยอดขายไม่ถึง ".number_format($price_threshold)." บาท ບໍ່ໄດ້ຮັບຄ່າຄອມ";

                        } else {
                            // $remark = "ยกเว้นเงื่อนไขจากแผนก Training";
                            $remark = "ยกเว้นเงื่อนไข 200K - ".$pc_unpunish_list[ $data[$i]['staff_code'] ];
                        }
                        
                    }
                    
                } else {
                    $addon_rate = 1;
                    $remark = "โอนย้ายเขต";
                }

            }

            $com_real = round( ($com_rate * $addon_rate) + $com_eol, 0);

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);

            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_sellout']);
            $sheet->setCellValue($alpha++ . $index, number_format($com_rate));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);
            // $sheet->setCellValue($alpha++ . $index, $debug);
            $sheet->setCellValue($alpha++ . $index, $addon_rate);
            // $sheet->setCellValue($alpha++ . $index, $com_eol);
            $sheet->setCellValue($alpha++ . $index, number_format($com_real));
            $sheet->setCellValue($alpha++ . $index, $work_day);
            $sheet->setCellValue($alpha++ . $index, $remark);
            $index++;

        }

        $filename = 'Commission PC - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // PC Commission Total : Export Excel
    public static function com_total_pc($rd_result,$data,$params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        if ($params['report_type'] == 'BKK') { 

            $filename = 'Commission PC Total - BKK - '.date('d-m-Y H:i:s');
            $report_name = 'PC BKK';

        } else {

            $filename = 'Commission PC Total - UPC - '.date('d-m-Y H:i:s');
            $report_name = 'PC Up Country';
        }

        $heads = array(
            'Item.',
            'Name RD.',
            'Area',
            $report_name,'','','',
            'Remark' 
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $heads2 = array('','','','Person', 'Unit', 'Commission', 'Total Price','');

        $alpha = 'A';
        $index = 2;
        foreach ($heads2 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('H1:H2');

        $sheet->mergeCells('D1:G1');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:H2")->applyFromArray($style);

        $index = 3;

        $sum_staff = $sum_sellout = $sum_com_real = $sum_price = 0;

        for ($i=0;$i<count($rd_result);$i++) { 

            $cnt = $i + 1;
        
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $rd_result[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $rd_result[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[ $rd_result[$i]['area_id'] ]['cnt_staff']);
            $sheet->setCellValue($alpha++ . $index, $data[ $rd_result[$i]['area_id'] ]['total_sellout']);
            $sheet->setCellValue($alpha++ . $index, $data[ $rd_result[$i]['area_id'] ]['com_real']);
            $sheet->setCellValue($alpha++ . $index, $data[ $rd_result[$i]['area_id'] ]['total_price']);
            $sheet->setCellValue($alpha++ . $index, '');

            $sum_staff    = $sum_staff + $data[ $rd_result[$i]['area_id'] ]['cnt_staff'];
            $sum_sellout  = $sum_sellout + $data[ $rd_result[$i]['area_id'] ]['total_sellout'];
            $sum_com_real = $sum_com_real + $data[ $rd_result[$i]['area_id'] ]['com_real'];
            $sum_price    = $sum_price + $data[ $rd_result[$i]['area_id'] ]['total_price'];

            $index++;
        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Grand Total');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, $sum_staff);
        $sheet->setCellValue($alpha++ . $index, $sum_sellout);
        $sheet->setCellValue($alpha++ . $index, $sum_com_real);
        $sheet->setCellValue($alpha++ . $index, $sum_price);
        $sheet->setCellValue($alpha++ . $index, '');

        $sheet->mergeCells("A".$index.":C".$index);
        $sheet->getStyle("A".$index.":C".$index)->applyFromArray($style);

        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // BM-Dealer Commission 
    public static function com_bm_dealer_export($data, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        $com_month = date('F Y', strtotime($params['from']) );

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Area',
            'Store ID',
            'Store Name',
            'Staff ID',
            'Staff Name',
            'Target',
            'Sellout',
            'Achieve',
            'Price [EX VAT]',
            '% Achieve',
            'Com. Unit',
            'Com. Achieve',
            'Total Com.',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION BRAND SHOP BY DEALER FOR ".$com_month);

        $alpha = 'A';
        $index = 3;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:N3")->applyFromArray($style);

        $index = 4;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $bm_exception = $QGoodKpi->get_bm_exception();
        $bm_exception = array_column($bm_exception, 'staff_id');

        $sum_bm_com = $sum_pc_com = $sum_total_bm_com = 0;

        for ($i=0;$i<count($data);$i++) { 

            $price = $achieve = $bm_rate = $bm_com = $pc_com = $total_bm_com = 0;

            if ( isset($data[$i]['price']) && $data[$i]['price'] ) { $price = $data[$i]['price']; } 

            $price_ex_vat = round(($data[$i]['price'] * 100 ) / 107, 2);

            if ( isset($data[$i]['st_target']) && $data[$i]['st_target'] != 0 ) { 

                $achieve = round(( $data[$i]['sellout'] / $data[$i]['st_target'] ) * 100, 2); 

                $bm_rate = $QGoodKpi->com_bm_rate($achieve);

                $bm_com = round(($price_ex_vat * $bm_rate) / 100, 2);

            } else {
                $data[$i]['st_target'] = '-';
            }

            // No PC Commission for BM 15K
            if ( isset($data[$i]['bm_id']) && $data[$i]['bm_id']) {
                if ( !in_array($data[$i]['bm_id'], $bm_exception) ) {

                    $params['bm_flag'] = 1;
                    $params['staff_code'] = $data[$i]['bm_code'];

                    $result = $QGoodKpi->report_kpiPC(null,$params);

                    // echo "<pre>"; print_r($result);

                    $pc_com = $result[0]['com_pc'];
                } 
            } else { $pc_com = $bm_com = 0; }

            // Exceptoin for Mar 2019
            if ( $data[$i]['bm_code'] == '6202168' && $com_month == 'March 2019' ) { $data[$i]['sellout'] = $price_ex_vat = $bm_com = 0; }
            if ( in_array($data[$i]['bm_code'], array('6104168','6100403') ) && $com_month == 'March 2019' ) { $pc_com = $bm_com = 0; }
            if ( $data[$i]['bm_code'] == '6000730' && $com_month == 'March 2019' ) { $bm_com = 0; }
            if ( $data[$i]['bm_code'] == '5901881' && $com_month == 'March 2019' ) { 
                $bm_com = round(($price_ex_vat * 0.6) / 100, 2);
            }

            // Exception for Apr 2019
            if ( $data[$i]['bm_code'] == '6202501' && $com_month == 'April 2019' ) { $bm_com = 0; }
            if ( $data[$i]['bm_code'] == '6201052' && $com_month == 'April 2019' ) { 
                $bm_com = round(($price_ex_vat * 0.6) / 100, 2);
            }

            // Exception for May 2019
            if ( $data[$i]['bm_code'] == '6200140' && $com_month == 'May 2019' ) { $bm_com = 3797; }
            if ( $data[$i]['bm_code'] == '6203126' && $com_month == 'May 2019' ) { $bm_com = 2390; }
            if ( $data[$i]['bm_code'] == '6202353' && $com_month == 'May 2019' ) { $bm_com = 2521; }
            if ( $data[$i]['bm_code'] == '6201383' && $com_month == 'May 2019' ) { $bm_com = 2518; }
            if ( $data[$i]['bm_code'] == '6203587' && $com_month == 'May 2019' ) { $bm_com = 0; }
            if ( $data[$i]['bm_code'] == '6005825' && $com_month == 'May 2019' ) { $bm_com = 5648; }

            // Exception for Jun 2019
            if ( $data[$i]['bm_code'] == '6011217' && $com_month == 'June 2019' ) { $bm_com = 5556; }
            if ( $data[$i]['bm_code'] == '6101932' && $com_month == 'June 2019' ) { $bm_com = 0; }

            $cnt = $i + 1;
            
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_name']);

            $sheet->setCellValue($alpha++ . $index, $data[$i]['bm_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['bm_name']);

            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_target']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++ . $index, number_format($achieve,2));
            $sheet->setCellValue($alpha++ . $index, number_format($price_ex_vat,2));
            $sheet->setCellValue($alpha++ . $index, number_format($bm_rate,2));

            $sheet->setCellValue($alpha++ . $index, number_format($pc_com));
            $sheet->setCellValue($alpha++ . $index, number_format($bm_com));

            // Total BM Commission
            $total_bm_com = $bm_com + $pc_com;
            
            // Exceptoin for Dec 2019
            if ( $data[$i]['bm_code'] == '6200140' && $com_month == 'December 2018' ) { $total_bm_com = 0; }
            // Exceptoin for Mar 2019
            if ( in_array($data[$i]['bm_code'], array('6201928') ) && $com_month == 'March 2019' ) { $total_bm_com = 0; }

            $sheet->setCellValue($alpha++ . $index, number_format($total_bm_com));

            $sum_bm_com = $sum_bm_com + $bm_com;
            $sum_pc_com = $sum_pc_com + $pc_com;
            $sum_total_bm_com = $sum_total_bm_com + $total_bm_com;

            $index++;

        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, "Total Commission on ".$com_month);

        for ($i=0;$i<10;$i++) { $sheet->setCellValue($alpha++.$index, ''); }
        
        $sheet->setCellValue($alpha++ . $index, number_format($sum_pc_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_bm_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_total_bm_com));

        $sheet->mergeCells("A".$index.":K".$index);

        $filename = 'Commission BM Dealer - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // BM-OPPO Commission 
    public static function com_bm_oppo_export($data, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        $com_month = date('F Y', strtotime($params['from']) );

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff ID',
            'Name',
            'Group',
            'Store ID',
            'Store Name',
            'Target',
            'Sellout',
            'Achieve',
            'Price [EX VAT] Mobile',
            'Price [EX VAT] ACC',
            '% Achieve',
            'Com. Unit',
            'Com. Achieve',
            'Com. ACC',
            'Com. FF+SKU',
            'Total Com.',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION BRAND SHOP BY OPPO FOR ".$com_month);

        $alpha = 'A';
        $index = 3;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:P3")->applyFromArray($style);

        $index = 4;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $bm_exception = $QGoodKpi->get_bm_exception();
        $bm_exception = array_column($bm_exception, 'staff_id');

        $sum_bm_com = $sum_pc_com = $sum_total_bm_com = 0;

        for ($i=0;$i<count($data);$i++) { 

            $price = $bm_rate = $pc_com = $total_bm_com = $acc_price = $acc_com = $acc_ff_com = 0;

            if ( isset($data[$i]['group_id']) && $data[$i]['group_id'] ) { 

                $bm_target = $bm_sellout = $bm_price = $bm_rate = $bm_com = $achieve = "";

                if ($data[$i]['pc_stand_by'] == 0) {
                    $acc_rate = $QGoodKpi->com_bm_acc_rate($data[$i]['group_id']);

                    if ( strpos($data[$i]['st_name'], 'TME') !== false ) {
                        $acc_com = 0;
                    } else {
                        $acc_com = round(($data[$i]['acc_price'] * $acc_rate) / 100);
                    }   

                }

                $acc_price = $data[$i]['acc_price'];

                $cnt_staff = $cnt_staff + 1;

                if ($data[$i]['group_id'] == PGPB_ID) { $bm_flag = 0; }

                // Check Case No BM
                if ($data[$i]['group_id'] != BM_ID && $bm_flag == 1) {

                    $params2 = array();
                    $params2['store_id'] = $data[$i]['st_id'];
                    $params2['from'] = $params['from'];
                    $params2['to'] = $params['to'];

                    $bm_com_info = $QGoodKpi->getComBMbyOppo($params2);
                    // echo "<pre>"; print_r($bm_com_info);

                    $bm_sellout = $bm_com_info['sellout'];
                    $bm_price = $bm_com_info['price'];

                    if ( isset($bm_com_info['st_target']) && $bm_com_info['st_target'] != 0 ) { 

                        $bm_target = $bm_com_info['st_target'];

                        $achieve = round(( $bm_sellout / $bm_target ) * 100, 2); 

                        $bm_rate = $QGoodKpi->com_bm_rate($achieve);

                        $bm_com = 0;
                        
                    }

                    $alpha = 'A';
                    $sheet->setCellValue($alpha++ . $index, "-");
                    $sheet->setCellValue($alpha++ . $index, "-");
                    $sheet->setCellValue($alpha++ . $index, "-");
                    $sheet->setCellValue($alpha++ . $index, "-");

                    $sheet->setCellValue($alpha++ . $index, $data[$i]['st_id']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['st_name']);

                    $sheet->setCellValue($alpha++ . $index, $bm_target);
                    $sheet->setCellValue($alpha++ . $index, $bm_sellout);
                    $sheet->setCellValue($alpha++ . $index, number_format($achieve,2));
                    $sheet->setCellValue($alpha++ . $index, number_format($bm_price,2));
                    $sheet->setCellValue($alpha++ . $index, number_format($acc_price,2));
                    $sheet->setCellValue($alpha++ . $index, number_format($bm_rate,2));

                    $sheet->setCellValue($alpha++ . $index, number_format($pc_com));
                    $sheet->setCellValue($alpha++ . $index, number_format($bm_com));
                    $sheet->setCellValue($alpha++ . $index, '0');
                    $sheet->setCellValue($alpha++ . $index, '0');

                    $sheet->setCellValue($alpha++ . $index, number_format($total_bm_com));

                    $index++;

                    $bm_target = $bm_sellout = $bm_price = $bm_rate = $bm_com = $achieve = "";

                } else {

                    if ( isset($data[$i]['group_id']) && $data[$i]['group_id'] == BM_ID ) {

                        $params2 = array();
                        $params2['bm_id'] = $data[$i]['staff_id'];
                        $params2['store_id'] = $data[$i]['st_id'];
                        $params2['from'] = $params['from'];
                        $params2['to'] = $params['to'];

                        $bm_com_info = $QGoodKpi->getComBMbyOppo($params2);
                        // echo "<pre>"; print_r($bm_com_info);

                        // if ( isset($bm_com_info['st_target']) && $bm_com_info['st_target'] != 0 ) { 

                        if ( !empty($bm_com_info) ) {  

                            $bm_target = $bm_com_info['st_target'];
                            $bm_sellout = $bm_com_info['sellout'];
                            $bm_price = $bm_com_info['price'];

                            if ( $bm_target == 0 ) { $achieve = $acc_com = 0; } 
                            else { $achieve = round(( $bm_sellout / $bm_target ) * 100, 2); }

                            $bm_rate = $QGoodKpi->com_bm_rate($achieve);

                            $bm_com = round(($bm_price * $bm_rate) / 100, 2);

                        }

                    }

                    // Get Com Acc Film Focus 
                    $params3 = array();
                    $params3['staff_code'] = $data[$i]['staff_code'];
                    $params3['from'] = $params['from'];
                    $params3['to'] = $params['to'];

                    $result_acc_ff = $QGoodKpi->getAccComFF($params3);

                    // print_r($result_acc_ff); echo "<br/>";

                    if ( !empty($result_acc_ff) ) { $acc_ff_com = $result_acc_ff['com_rate']; }

                    // Excepion for Mar 2019
                    if ( in_array($data[$i]['staff_code'], array('5900327','6105814') ) && $com_month == 'March 2019') { $bm_com = 0; }
                    if ( in_array($data[$i]['staff_code'], array('6201203') ) && $com_month == 'March 2019') { 
                        $acc_com = $bm_com = 0; 
                        $data[$i]['staff_id'] = '';
                        $data[$i]['staff_code'] = '-';
                        $data[$i]['staff_name'] = '-';
                        $data[$i]['group_name'] = '-';
                    }

                    // Excepion for Apr 2019
                    if ( in_array($data[$i]['staff_code'], array('6202279','6202700') ) && $com_month == 'April 2019') { $acc_com = 0; }
                    if ( in_array($data[$i]['staff_code'], array('5902516') ) && $com_month == 'April 2019') { $acc_com = $bm_com = 0; }

                    // Excepion for May 2019
                    if ( in_array($data[$i]['staff_code'], array('6100742') ) && $com_month == 'May 2019') { 
                        $acc_com = $bm_com = 0; 
                        $data[$i]['staff_id'] = '';
                        $data[$i]['staff_code'] = '-';
                        $data[$i]['staff_name'] = '-';
                        $data[$i]['group_name'] = '-';
                    }

                    // Excepion for Jun 2019
                    if ( in_array($data[$i]['staff_code'], array('5901199') ) && $com_month == 'June 2019') { $acc_com = $bm_com = 0; }

                    // Excepion for Jul 2019
                    if ( in_array($data[$i]['staff_code'], array('5901199') ) && $com_month == 'July 2019') { $acc_com = $bm_com = 0; }
                    if ( in_array($data[$i]['staff_code'], array('6011650','6204839') ) && $com_month == 'July 2019') { $acc_com = 0; }
                    if ( in_array($data[$i]['staff_code'], array('6200266','6204958') ) && $com_month == 'July 2019') { 
                        $acc_com = $bm_com = 0; 
                        $data[$i]['staff_id'] = '';
                        $data[$i]['staff_code'] = '-';
                        $data[$i]['staff_name'] = '-';
                        $data[$i]['group_name'] = '-';
                    }

                    // Excepion for Aug 2019
                    if ( in_array($data[$i]['staff_code'], array('6200266') ) && $com_month == 'August 2019') { 
                        $acc_com = $bm_com = 0; 
                        $data[$i]['staff_id'] = '';
                        $data[$i]['staff_code'] = '-';
                        $data[$i]['staff_name'] = '-';
                        $data[$i]['group_name'] = '-';
                    }

                }

                $bm_flag = 0;

                // No PC Commission for BM 15K
                if ( isset($data[$i]['staff_id']) && $data[$i]['staff_id']) {

                    if ( !in_array($data[$i]['staff_id'], $bm_exception) && $data[$i]['group_id'] != PGPB_ID) {

                        // if ( $data[$i]['group_id'] == PGPB_ID ) { $params['bm_flag'] = 2; }
                        // else { $params['bm_flag'] = 1; }
                        
                        $params['bm_flag'] = 1;
                        $params['staff_code'] = $data[$i]['staff_code'];
                        $params['store_id'] = $data[$i]['st_id'];

                        $result = $QGoodKpi->report_kpiPC(null,$params);

                        // echo "<pre>"; print_r($result);

                        $pc_com = $result[0]['com_pc'];
                    } 

                }

            }

            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);

            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_name']);

            $sheet->setCellValue($alpha++ . $index, $bm_target);
            $sheet->setCellValue($alpha++ . $index, $bm_sellout);
            $sheet->setCellValue($alpha++ . $index, number_format($achieve,2));
            $sheet->setCellValue($alpha++ . $index, number_format($bm_price,2));
            $sheet->setCellValue($alpha++ . $index, number_format($acc_price,2));
            $sheet->setCellValue($alpha++ . $index, number_format($bm_rate,2));

            $sheet->setCellValue($alpha++ . $index, number_format($pc_com));
            $sheet->setCellValue($alpha++ . $index, number_format($bm_com));
            $sheet->setCellValue($alpha++ . $index, number_format($acc_com));
            $sheet->setCellValue($alpha++ . $index, number_format($acc_ff_com));

            // Total BM Commission
            $total_bm_com = round($bm_com + $pc_com + $acc_com + $acc_ff_com, 0);

            $sheet->setCellValue($alpha++ . $index, number_format($total_bm_com));

            $sum_bm_com = $sum_bm_com + round($bm_com,0);
            $sum_pc_com = $sum_pc_com + $pc_com;
            $sum_acc_com = $sum_acc_com + $acc_com;
            $sum_acc_ff_com = $sum_acc_ff_com + $acc_ff_com;
            $sum_total_bm_com = $sum_total_bm_com + $total_bm_com;

            $sum_each = $sum_each + $total_bm_com;

            // Total Each 
            if ($data[$i]['st_id'] != $data[$i+1]['st_id'] && $i != 0 ) {

                $index++;

                $alpha = 'A';
                for ($j=0;$j<16;$j++) { $sheet->setCellValue($alpha++.$index, ''); }
                $sheet->setCellValue($alpha++ . $index, number_format($sum_each));

                $sum_each = 0;

                $bm_flag = 1;
            }

            $index++;

        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, "รวม ".$cnt_staff." คน | Total Commission on ".$com_month);

        for ($i=0;$i<11;$i++) { $sheet->setCellValue($alpha++.$index, ''); }
        
        $sheet->setCellValue($alpha++ . $index, number_format($sum_pc_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_bm_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_acc_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_acc_ff_com));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_total_bm_com));

        $sheet->mergeCells("A".$index.":L".$index);
        $sheet->getStyle("A".$index.":L".$index)->applyFromArray($style);

        $filename = 'Commission BM OPPO - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // Sale UPC Commission ALL
    public static function com_sale_upc_export($data, $params) {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'ID Staff',
            'NAME',
            'Position',
            'ZONE',
            'Target',
            'Unit',
            'Ratio',
            'Sale Amount',
            //'หัก Margin 20%',
            'Commission',
            'Remark',
        );

        $area_id = $data[0]['area_id'];
        $area_name = $data[0]['area_name'];

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION FOR ".$params['from_date']." - ".$params['to_date']);
        $sheet->setCellValue('A3', "ZONE : ");
        $sheet->setCellValue('B3', $area_name);

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('B3:K3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:K1")->applyFromArray($style);

        $index = 5;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $area_leader = array(ASM_ID, ASMSTANDBY_ID);

        $cnt_data = count($data);
        $cnt = 0;

        for ($i=0;$i<$cnt_data;$i++) { 

            $remark = "";

            $sale_target = $QGoodKpi->get_sale_com_target($area_id,$data[$i]['staff_id'],$params['from'], $params['to']);

            if ($data[$i]['group_id'] <> SALES_ID) { 
                $com_rate = 0; 
                $remark = "ไม่ใช่ตำแหน่ง Sale ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
            }
            else { $com_rate = $data[$i]['com_rate']; }

            if (!$sale_target) { 
                $sale_target = 0;
                $com_rate = 0;
                $remark = "ไม่มี Sale Target ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
            } 

            if ($data[$i]['group_id'] == RM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ RM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
            if ($data[$i]['group_id'] == ASM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
            if ($data[$i]['group_id'] == ASMSTANDBY_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }

            $ratio = ( $data[$i]['total_unit'] / $sale_target ) * 100; 

            //if ($data[$i]['group_id'] <> AM_ID) {
                $cnt = $cnt + 1;
                $alpha = 'A';

                $sheet->setCellValue($alpha++ . $index, $cnt);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
                $sheet->setCellValue($alpha++ . $index, $sale_target);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
                $sheet->setCellValue($alpha++ . $index, $ratio."%");
                $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);
                //$sheet->setCellValue($alpha++ . $index, "-");
                $sheet->setCellValue($alpha++ . $index, $com_rate);
                $sheet->setCellValue($alpha++ . $index, $remark);

                $sum_target = $sum_target + $sale_target;
                $sum_unit = $sum_unit + $data[$i]['total_unit'];
                $sum_price = $sum_price + $data[$i]['total_price'];

                $index++;
            //}

        }

        $margin = $sum_price - ( $sum_price * 0.2 );

        // Get Leader of Area
        $QAsm = new Application_Model_Asm();
        $result_asm = $QAsm->get_asm_for_com($area_id);
        $result_asm_cnt = count($result_asm);
        // echo "<br/>"; print_r($result_asm);

        for($i=0;$i<$result_asm_cnt;$i++) {

            $params['asm_code'] = $result_asm[$i]['staff_code'];

            $alpha = 'A';
            $leader_remark = "";
            $leader_com_rate = 0;
            $score_group = $result_asm[$i]['group_name'];

            $cnt2 = $cnt + $i + 1;
            $leader_ratio = ( $sum_unit / $sum_target ) * 100;

/*
            if ($result_asm[$i]['group_id'] == ASM_ID) {
                $leader_com_rate = 0;

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $leader_com_rate = ( $margin * $asm_score['gfk'] ) / 100;
            }

            if ($result_asm[$i]['group_id'] == ASMSTANDBY_ID) {
                $leader_com_rate = 0;

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $leader_com_rate = ( ( $margin * $asm_score['gfk'] ) / 100 ) / 2;
                $leader_remark = "ได้รับค่าคอมฯ 50% ของ ASM";
            }

            if ($result_asm[$i]['group_id'] == RM_ID) {
                $leader_com_rate = 0;

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 
                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                    $score_group = "ASM";
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                } else {
                    $asm_score = $rm_score;
                }

                $leader_com_rate = ( $margin * $asm_score['gfk'] ) / 100;

            }
            
            // Garantee Commission 2016 
            if ($result_asm[$i]['staff_code'] == '5702190') {
                if ($leader_com_rate < 70000) { $leader_com_rate = 70000; }
                $leader_remark = "ค่าคอมฯการันตี";
            }
*/

            if ($result_asm[$i]['group_id'] == ASM_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = ASM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = $result_by_area['com_rate'] * $asm_score['gfk'];

            }

            if ($result_asm[$i]['group_id'] == ASMSTANDBY_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = ASM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = ($result_by_area['com_rate'] * $asm_score['gfk']) / 2;
                $leader_remark = "ได้รับค่าคอมฯ 50% ของ ASM";
            }

            if ($result_asm[$i]['group_id'] == RM_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = RM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 
                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                    $score_group[$i] = "ASM";
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                } else {
                    $asm_score = $rm_score;
                }

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = $result_by_area['com_rate'] * $asm_score['gfk'];

            }

            /*
            // Garantee Commission 2017
            if ($result_asm[$i]['staff_code'] == '5700053') {
                if ($leader_com_rate < 70000) { $leader_com_rate = 70000; }
                $leader_remark = "ค่าคอมฯการันตี";
            }*/

            $sheet->setCellValue($alpha++ . $index, $cnt2);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['group_name']);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $sum_target);
            $sheet->setCellValue($alpha++ . $index, $sum_unit);
            $sheet->setCellValue($alpha++ . $index, $leader_ratio);
            $sheet->setCellValue($alpha++ . $index, $sum_price);
            //$sheet->setCellValue($alpha++ . $index, $margin);
            $sheet->setCellValue($alpha++ . $index, $leader_com_rate);
            $sheet->setCellValue($alpha++ . $index, $leader_remark);

            $index++;

        }

        $alpha = 'A';
        $total_merge_start = $index;

        for($i=0;$i<$result_asm_cnt;$i++) {

            if ($result_asm[$i]['group_id'] == ASM_ID) {

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; } 

            }

            if ($result_asm[$i]['group_id'] == ASMSTANDBY_ID) {

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; } 

            }

            if ($result_asm[$i]['group_id'] == RM_ID) {

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 

                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                    if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; }

                } else {

                    $asm_score = $rm_score;
                }

            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, "Total");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "Score ".$score_group." = ".$asm_score['score']);
            $sheet->setCellValue($alpha++ . $index, "GFK = ".$asm_score['gfk']."%");

            $sheet->mergeCells("H".$index.":K".$index);

            $index++;
        }

        $total_merge_end = $index - 1;

        if ( !empty($result_asm) ) {
            $sheet->mergeCells("A".$total_merge_start.":E".$total_merge_end);
            $sheet->getStyle("A".$total_merge_start.":E".$total_merge_end)->applyFromArray($style);
        }

        $filename = 'Commission Sale UPC - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // Sale UPC Commission ALL
    public static function com_sale_bkk_export($data, $params) {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'ID Staff',
            'NAME',
            'Position',
            'ZONE',
            'Total Unit',
            'Commission Sale',
            'Commission Store Share',
            'Total Commission',
            'Total Price',
        );

        $area_id = $data[0]['area_id'];
        $area_name = $data[0]['area_name'];

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION FOR ".$params['from_date']." - ".$params['to_date']);
        $sheet->setCellValue('A3', "NAME : ");
        $sheet->setCellValue('B3', $params['asm_name']);
        $sheet->setCellValue('A4', "ZONE : ");
        $sheet->setCellValue('B4', $area_name);

        $alpha = 'A';
        $index = 5;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('B3:J3');
        $sheet->mergeCells('B4:J4');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:J1")->applyFromArray($style);

        $index = 6;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $cnt_data = count($data);
        $cnt = 0;

        for ($i=0;$i<$cnt_data;$i++) { 

            $cnt_data = count($data);
            $cnt = 0;

            $sum_unit = 0;
            $sum_com_rate = 0;
            $sum_store_share = 0;
            $sum_total_com_rate = 0;
            $sum_total_price = 0;

            for ($i=0;$i<$cnt_data;$i++) { 

                if ($data[$i]['group_id'] <> AM_ID) { 

                    $alpha = 'A';
                    $cnt = $cnt + 1;
                    $total_com_rate = $data[$i]['com_rate'] + $data[$i]['store_share'];

                    $sheet->setCellValue($alpha++ . $index, $cnt);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['store_share']);
                    $sheet->setCellValue($alpha++ . $index, $total_com_rate);
                    $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);

                    $sum_unit = $sum_unit + $data[$i]['total_unit'];
                    $sum_com_rate = $sum_com_rate + $data[$i]['com_rate'];
                    $sum_store_share = $sum_store_share + $data[$i]['store_share'];
                    $sum_total_com_rate = $sum_total_com_rate + $total_com_rate;
                    $sum_total_price = $sum_total_price + $data[$i]['total_price'];

                    $index++;
                }

            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, "Total");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, $sum_unit);
            $sheet->setCellValue($alpha++ . $index, $sum_com_rate);
            $sheet->setCellValue($alpha++ . $index, $sum_store_share);
            $sheet->setCellValue($alpha++ . $index, $sum_total_com_rate);
            $sheet->setCellValue($alpha++ . $index, $sum_total_price);

            $sheet->mergeCells("A".$index.":E".$index);
            $sheet->getStyle("A".$index.":E".$index)->applyFromArray($style);
        }

        $filename = 'Commission Sale BKK - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // Sale BKK 2017 Commission ALL
    public static function com_sale_bkk_2017_export($data, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        // Get Total Day of Month
        $total_days = date('t', strtotime($params['from']));

        $flag = 0;
        $area_list = array(81,82,83,85,86,90,91,92,94,95,97,98,99,100,101,103,104,105,106,107,108,110,115,116);

        if ( in_array($params['area_id'][0], $area_list) ) { $flag = 1; }

        $heads_01 = array(
            'No.',
            'ID Staff',
            'NAME',
            'Position',
            'ZONE',
            'Target',
            'Unit',
            'Sale Amount',
            'Commission',
            'Achieve',
            'Com. x GFK',
        );

        $heads_02 = array();
        if ($flag == 1) {  $heads_02 = array('Price Target','Price Sellout','Price Achieve'); }

        $heads_03 = array(
            'Punish',
            'Actual Com.',
            'Remark',
        );

        $heads = array_merge($heads_01, $heads_02, $heads_03);

        $area_id = $data[0]['area_id'];
        $area_name = $data[0]['area_name'];

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION FOR ".$params['from_date']." - ".$params['to_date']);
        $sheet->setCellValue('A3', "ZONE : ");
        $sheet->setCellValue('B3', $area_name);

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:Q1');
        $sheet->mergeCells('A2:Q2');
        $sheet->mergeCells('B3:Q3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:Q1")->applyFromArray($style);

        $index = 5;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();
        $QSPT = new Application_Model_StorePriceTarget();

        $area_leader = array(ASM_ID, ASMSTANDBY_ID);

        $cnt_data = count($data);
        $cnt = 0;

        for ($i=0;$i<$cnt_data;$i++) { 

            $remark = "";
            // Case No Sale in Store
            if (is_null($data[$i]['staff_id'])) { 
                $data[$i]['staff_id'] = 0; 
                $data[$i]['staff_code'] = "N/A"; 
                $data[$i]['staff_name'] = "N/A"; 
                $data[$i]['group_name'] = "N/A"; 
                $data[$i]['com_rate'] = 0;
                $remark = "ຍອດຂາຍຂອງຮ້ານທີ່ບໍ່ມີ Sale ຮັບຜິດຊອບ";
            }

            $sale_target = $QGoodKpi->get_sale_com_target($area_id,$data[$i]['staff_id'],$params['from'], $params['to']);

            $com_rate = $data[$i]['com_rate'];

            if (!isset($sale_target['sale_target']) && $data[$i]['staff_id'] !== 0) { 
                $sale_target = 0;
                // $remark = "ไม่ได้ตั้ง Sale Target"; 
            } else {
/*
                if ($sale_target['com'] == 0) {
                    $sale_target = 0;
                    $com_rate = 0;
                    $remark = "ยกเว้น ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
                } else {
                    $sale_target = $sale_target['sale_target'];
                }
*/
                $sale_target = $sale_target['sale_target'];
            }

            if ($data[$i]['group_id'] <> SALES_ID && $data[$i]['staff_id'] !== 0) { 
                $com_rate = 0; 
                $remark = "ไม่ใช่ตำแหน่ง Sale ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
            }

            if ($data[$i]['group_id'] == RM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ RM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
            if ($data[$i]['group_id'] == RMSTANDBY_ID) { $remark = "5 ຮ້ານຄ້າທີ່ RM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
            if ($data[$i]['group_id'] == ASM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
            if ($data[$i]['group_id'] == ASMSTANDBY_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }

            $ratio = ( $data[$i]['total_unit'] / $sale_target ) * 100; 

            // Check Punish By Memo [Sale]
            $params['asm_id'] = $data[$i]['staff_id'];
            $result_puish = array();
            if (!in_array($data[$i]['group_id'], array(RM_ID,RMSTANDBY_ID,ASM_ID,ASMSTANDBY_ID))) {
                $result_puish = $QGoodKpi->get_punish_memo($params);
            }
            unset($params['asm_id']);
            //print_r($result_puish);

            $punish_price = "";
            $punish_remark = "";
            $sum_punish_price = 0;

            for ($j=0;$j<count($result_puish);$j++) {

                if ($j==0) { 
                    $punish_price = number_format($result_puish[$j]['price']); 
                    $punish_remark = $result_puish[$j]['p_name']; 
                } else { 
                    $punish_price = $punish_price."\n".number_format($result_puish[$j]['price']); 
                    $punish_remark = $punish_remark."\n".$result_puish[$j]['p_name']; 
                }

                $sum_punish_price = $sum_punish_price + $result_puish[$j]['price'];
            }

            if ($remark == "") { $remark = $punish_remark; }
            else { $remark = $remark.", ".$punish_remark; }

            $cnt = $cnt + 1;
            $alpha = 'A';

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $sale_target);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);
            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $ratio."%");
            $sheet->setCellValue($alpha++ . $index, $com_rate);

            $sum_com_net = $sum_com_net + $com_rate;
            $com_extra = 0;

            // Sale Add-on Commission
            if ($flag == 1) { 

                $params_sale = array(
                    'sale_id' => $data[$i]['staff_id'],
                    'area_id' => $params['area_id'],
                    'from'    => $params['from'],
                    'to'      => $params['to'],
                );

                $target_sale = $QSPT->getTargetByMonth($params_sale);

                $sellout_sale = array();
                $sellout_price = 0;
                $store_price_target = 0;
                $achieve_sale = 0;
                $rate = 1;

                if ( !empty($target_sale) ) {

                    $store_list = array();
                    for ($j=0;$j<count($target_sale);$j++) {

                        // Calculate Total Days
                        if ( $target_sale[$j]['st_joined_at'] <= $params['from']." 00:00:00" ) { $start = $params['from']." 00:00:00"; } 
                        else { $start = $target_sale[$j]['st_joined_at']; }

                        if ( !isset($target_sale[$j]['st_released_at']) || $target_sale[$j]['st_released_at'] >= $params['to']." 23:59:59" ) { $end = $params['to']." 23:59:59"; $date_flag = 1; } 
                        else { $end = $target_sale[$j]['st_released_at']; $date_flag = 0; }

                        $diffdate = strtotime($end) - strtotime($start);
                        $days = floor($diffdate/(60*60*24)) + $date_flag;

                        // Calculate Sale Target 
                        $sale_target = round( ($target_sale[$j]['st_target_price'] * $days) / $total_days, 0);

                        $store_price_target = $store_price_target + $sale_target;
                        // $store_list[$j] = $target_sale[$j]['st_id'];
                    }

                    $params2 = array(
                        'sale_id' => $data[$i]['staff_id'],
                        'area_id' => $params['area_id'],
                        'from'    => $params['from'],
                        'to'      => $params['to'],
                    );

                    $sellout_sale = $QGoodKpi->getSelloutBySale($params2);
                    $sellout_price = $sellout_sale['price'];

                    $achieve_sale = round(($sellout_price / $store_price_target) * 100, 2);

                    $rate = $QGoodKpi->getSaleAchieveRate($achieve_sale);
                }

                // No Taget 
                if ( $store_price_target == 0 ) { $rate = 0; }

                $sheet->setCellValue($alpha++ . $index, $store_price_target);
                $sheet->setCellValue($alpha++ . $index, $sellout_price);
                $sheet->setCellValue($alpha++ . $index, $achieve_sale);
/*
                // Sale Exception
                if ( $data[$i]['staff_code'] == '6201256' ) {
                    $rate = 1;
                    $remark = 'ยกเว้นเงื่อนไขตาม Memo';
                }
*/

                // Sellout Price <= 2,500,000 Reduct to Rate 1
                if ( $sellout_price < 2500000 ) { $rate = 1; } 

                // $com_rate = $com_rate * $rate;
                if ( $rate == 0 ) { $com_rate = 0; } 
                else if ( $rate == 1 ) { $com_rate = $com_rate; } 
                else { $com_rate = $com_rate + (round($sellout_price*$rate,0)); }
/*
                // Extra Commission By Unit 30 May - 02 Jun 2019
                $result_com_extra = $QGoodKpi->comission_extra($params_sale);

                if (!empty($result_com_extra)) { $com_extra = $result_com_extra['com_rate']; } 

                $sheet->setCellValue($alpha++ . $index, $com_extra);
*/
            }   

            $sale_ac_com_rate = max($com_rate - $sum_punish_price + $com_extra, 0);

            $sheet->setCellValue($alpha++ . $index, $punish_price);
            $sheet->setCellValue($alpha++ . $index, $sale_ac_com_rate);
            $sheet->setCellValue($alpha++ . $index, $remark);

            $sum_target = $sum_target + $sale_target;
            $sum_com = $sum_com + $com_rate;

            //$sum_unit = $sum_unit + $data[$i]['total_unit'];
            //$sum_price = $sum_price + $data[$i]['total_price'];

            $index++;
            
        }

        // Get Sale Leader Commission
        $sale_leader_data = $QGoodKpi->com_sale_leader_bkk_2017($params);
        //print_r($sale_leader_data);

        for ($i=0;$i<count($sale_leader_data);$i++) {

            $remark = "ค่าคอมฯ Sale Leader";
            $alpha = 'A';

            $sale_target = $QGoodKpi->get_sale_com_target($area_id,$sale_leader_data[$i]['staff_id'],$params['from'], $params['to']);

            // if ($sale_leader_data[$i]['group_id'] <> Leader_ID) { 
            //  $com_rate = 0; 
            //  $remark = "ไม่ใช่ตำแหน่ง Sale Leader ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
            // }
            // else { $com_rate = $sale_leader_data[$i]['com_rate']; }

            $com_rate = $sale_leader_data[$i]['com_rate'];

            if (!$sale_target['sale_target']) { 
                $sale_target = 0;
                //$com_rate = 0;
                //$remark = "ไม่มี Sale Leader Target ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
            } 

            $ratio = ( $sale_leader_data[$i]['total_unit'] / $sale_target['sale_target'] ) * 100; 

            $cnt2 = $cnt + $i + 1;

            $sheet->setCellValue($alpha++ . $index, $cnt2);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['group_name']);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $sale_target);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, $sale_leader_data[$i]['total_price']);
            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $ratio);

            if ($flag == 1) { 
                $sheet->setCellValue($alpha++ . $index, ""); 
                $sheet->setCellValue($alpha++ . $index, ""); 
                $sheet->setCellValue($alpha++ . $index, ""); 
                // $sheet->setCellValue($alpha++ . $index, ""); 
            } 

            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $remark);

            $index++;
        }

        // Get Leader of Area
        $QAsm = new Application_Model_Asm();
        $result_asm = $QAsm->get_asm_for_com($area_id);
        $result_asm_cnt = count($result_asm);
        // echo "<br/>"; print_r($result_asm); 

        for($i=0;$i<$result_asm_cnt;$i++) {

            $params['asm_id'] = $result_asm[$i]['staff_id'];
            $params['asm_code'] = $result_asm[$i]['staff_code'];

            $alpha = 'A';
            $leader_remark = "";
            $leader_com_rate = 0;
            $score_group[$i] = $result_asm[$i]['group_name'];

            $cnt3 = $cnt2 + $i + 1;
/*
            // Exception for ASM BKK-E5A 
            if ($result_asm[$i]['staff_code'] == '5801683') { 
                $result_asm[$i]['group_id'] = ASMSTANDBY_ID; 
                $leader_remark = 'ได้รับค่าคอมฯ Rate ASM Stand By';
            }
*/
            if ($result_asm[$i]['group_id'] == ASM_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = ASM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = round($result_by_area['com_rate'] * $asm_score['gfk'], 0);

            }

            if ($result_asm[$i]['group_id'] == ASMSTANDBY_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = ASM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = round(($result_by_area['com_rate'] * $asm_score['gfk']) / 2, 0);
                $leader_remark = "ได้รับค่าคอมฯ 50% ของ ASM";
            }

            if ($result_asm[$i]['group_id'] == RM_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = RM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 
                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                    $score_group[$i] = "ASM";
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                } else {
                    $asm_score = $rm_score;
                }

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = round($result_by_area['com_rate'] * $asm_score['gfk'], 0);

            }

            if ($result_asm[$i]['group_id'] == RMSTANDBY_ID) {
                $leader_com_rate = 0;

                // Get Sellout By Area 
                $params['com_leader'] = RM_ID;
                $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                unset($params['com_leader']);

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 
                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                    $score_group[$i] = "ASM";
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                } else {
                    $asm_score = $rm_score;
                }

                $sum_unit = $result_by_area['total_unit'];
                $sum_price = $result_by_area['total_price'];
                $leader_com_rate = round(($result_by_area['com_rate'] * $asm_score['gfk']) / 2, 0);
                $leader_remark = "ได้รับค่าคอมฯ 50% ของ RM";

            }

            // Check Punish By Memo
            $result_puish = $QGoodKpi->get_punish_memo($params);

            $punish_price = "";
            $punish_remark = "";
            $sum_punish_price = 0;

            for ($j=0;$j<count($result_puish);$j++) {

                if ($j==0) { 
                    $punish_price = number_format($result_puish[$j]['price']); 
                    $punish_remark = $result_puish[$j]['p_name']; 
                } else { 
                    $punish_price = $punish_price."\n".number_format($result_puish[$j]['price']); 
                    $punish_remark = $punish_remark."\n".$result_puish[$j]['p_name']; 
                }

                $sum_punish_price = $sum_punish_price + $result_puish[$j]['price'];
            }

            if ($leader_remark == "") { $leader_remark = $punish_remark; }
            else { $leader_remark = $leader_remark.", ".$punish_remark; }
/*
            $leader_ac_com_rate = max($leader_com_rate - $sum_punish_price - $abm_share + $abm_plus, 0);
*/
            $leader_ac_com_rate = max($leader_com_rate - $sum_punish_price, 0);

            $leader_ratio = ( $sum_unit / $sum_target ) * 100;

            $sheet->setCellValue($alpha++ . $index, $cnt3);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['group_name'].$abm_mark);
            $sheet->setCellValue($alpha++ . $index, $result_asm[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $sum_target);
            $sheet->setCellValue($alpha++ . $index, $sum_unit);
            $sheet->setCellValue($alpha++ . $index, $sum_price);
            $sheet->setCellValue($alpha++ . $index, $result_by_area['com_rate']);

            // $sheet->setCellValue($alpha++ . $index, $leader_ratio);
            $sheet->setCellValue($alpha++ . $index, "-");
            // $sheet->setCellValue($alpha++ . $index, $asm_score['gfk']);

            $sheet->setCellValue($alpha++ . $index, $leader_com_rate);

            if ($flag == 1) { 
                $sheet->setCellValue($alpha++ . $index, "-"); 
                $sheet->setCellValue($alpha++ . $index, "-"); 
                $sheet->setCellValue($alpha++ . $index, "-"); 
                // $sheet->setCellValue($alpha++ . $index, "-"); 
            } 

            $sheet->setCellValue($alpha++ . $index, $punish_price);
            $sheet->setCellValue($alpha++ . $index, $leader_ac_com_rate);
            $sheet->setCellValue($alpha++ . $index, $leader_remark);

            // $sheet->getStyle("L".$index)->getAlignment()->setWrapText(true);
            // $sheet->getStyle("N".$index)->getAlignment()->setWrapText(true);

            $index++;

        }

        $alpha = 'A';
        $total_merge_start = $index;

        for($i=0;$i<$result_asm_cnt;$i++) {

            if ($result_asm[$i]['group_id'] == ASM_ID) {

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; } 

            }

            if ($result_asm[$i]['group_id'] == ASMSTANDBY_ID) {

                $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; } 

            }

            if ( in_array($result_asm[$i]['group_id'], array(RM_ID, RMSTANDBY_ID)) ) {

                $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$i]['staff_id'],$area_id,$params['from'],$params['to']);
                if (!$rm_score['score']) { 

                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                    if (!$asm_score['score']) { $asm_score['score'] = 0; $asm_score['gfk'] = 0; }

                } else {

                    $asm_score = $rm_score;
                }

            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, "Total");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "Score ".$score_group[$i]." = ".$asm_score['score']);
            $sheet->setCellValue($alpha++ . $index, "GFK = ".$asm_score['gfk']."%");

            $sheet->mergeCells("H".$index.":Q".$index);

            $index++;
        }

        $total_merge_end = $index - 1;

        if ( !empty($result_asm) ) {
            $sheet->mergeCells("A".$total_merge_start.":E".$total_merge_end);
            $sheet->getStyle("A".$total_merge_start.":E".$total_merge_end)->applyFromArray($style);
        }

        $filename = 'Commission Sale BKK 2017 - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public function com_total_sale($params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        // Get Total Day of Month
        $total_days = date('t', strtotime($params['from']));

        $filename = 'Report_Total_COM_Sale'.$params['report_type'];
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'Area NO.',
            'Item',
            'Staff ID',
            'Name',
            'Position',
            'Zone',
            'Target',
            'Unit',

            'Sales Amount',
            'Commission',

            'Achieve',
            'Rate',
            'Commission x Rate',

            'Price Target',
            'Price Sellout',
            'Price Achieve',

            // 'TME Commission',

            'Punish Sales',
            'Actual Commission',
            'Remark',
        );

        fputcsv($output, $heads);

        $QGoodKpi = new Application_Model_GoodKpiLog();
        $QSPT = new Application_Model_StorePriceTarget();

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('a' => 'area'), array('a.*'));

        if ($params['report_type'] == 'UPC') {

            // Order Area By HR Request [Seperate by Thai's Region]
            $select->where('id NOT IN (48,49,72)', 1);
            //$select->where('id IN (?)', array(33,54));
            $select->where('id < 73', 1);
            $select->order("FIND_IN_SET(a.id, '33,54,55,57,56,13,58,24,25,39,50,51,69,45,41,53,37,52,63,62,40,44,38,64,61,59,46,60,47,66,18,42,65,14,67,20,68')");
        } else {
            $select->where('id >= 73', 1);
            $select->where('id <= 117', 1);
            $select->order('a.name ASC');
        }
            
        //echo $select;
        $result_area = $db->fetchAll($select);
        //print_r($result_area);

        $no = 1;
        $cnt = 2;
        $grand_sum_target   = 0;
        $grand_sum_unit     = 0;
        $grand_sum_ratio    = 0;
        $grand_sum_price    = 0;
        $grand_sum_com      = 0;

        $index = 2;
        $index_grand = array();
        $index_achieve = array();

        $index_grand_price_target = array();
        $index_grand_price_sellout = array();

        $index_com_extra = array();

        for ($i=0;$i<count($result_area);$i++) {

            $index_start = $index;

            $result = array();
            $params['area_id'] = $result_area[$i]['id'];

            $result = $QGoodKpi->com_sale_bkk_2017($params);

            //print_r($result);

            $flag = 0;
            $area_list = array(81,82,83,85,86,90,91,92,94,95,97,98,99,100,101,103,104,105,106,107,108,110,115,116);

            if ( in_array($params['area_id'], $area_list) ) { $flag = 1; }

            $no2 = 1;
            $sum_target = 0;
            $sum_unit   = 0;
            $sum_price  = 0;
            $sum_com    = 0;
            $total_punish_price = 0;
            $remark = '';

            for ($j=0;$j<count($result);$j++) {

                $remark = "";

                // Case No Sale in Store
                if (is_null($result[$j]['staff_id'])) { 
                    $result[$j]['staff_id'] = 0; 
                    $result[$j]['staff_code'] = "N/A"; 
                    $result[$j]['staff_name'] = "N/A"; 
                    $result[$j]['group_name'] = "N/A"; 
                    $result[$j]['com_rate'] = 0;
                    $remark = "ຍອດຂາຍຂອງຮ້ານທີ່ບໍ່ມີ Sale ຮັບຜິດຊອບ";
                }

                // Get Target
                $sale_target = $QGoodKpi->get_sale_com_target($params['area_id'],$result[$j]['staff_id'],$params['from'], $params['to']);

                if (!isset($sale_target['sale_target']) && $result[$j]['staff_id'] !== 0) { 
                    $sale_target = 0;
                    //$result[$j]['com_rate'] = 0;
                    // $remark = "ไม่ได้ตั้ง Sale Target"; 
                } else {
/*
                    if ($sale_target['com'] == 0) {
                        $sale_target = 0;
                        $result[$j]['com_rate'] = 0;
                        $remark = "ยกเว้น ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
                    } else {
                        $sale_target = $sale_target['sale_target'];
                    }
*/
                    $sale_target = $sale_target['sale_target'];
                }

                if ($result[$j]['group_id'] <> SALES_ID  && $result[$j]['staff_id'] !== 0) { 
                    $result[$j]['com_rate'] = 0; 
                    $remark = "ບໍ່ແມ່ນຕຳແໜ່ງ Sale ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
                }

                if ($result[$j]['group_id'] == RM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ RD ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
                if ($result[$j]['group_id'] == RMSTANDBY_ID) { $remark = "5 ຮ້ານຄ້າທີ່ RD ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
                if ($result[$j]['group_id'] == ASM_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }
                if ($result[$j]['group_id'] == ASMSTANDBY_ID) { $remark = "5 ຮ້ານຄ້າທີ່ ASM ຮັບຜິດຊອບ ບໍ່ໄດ້ຮັບຄ່າຄອມ"; }

                $ratio = ( $result[$j]['total_unit'] / $sale_target ) * 100; 

                // Check Punish By Memo [Sale]
                $old_area_id = $params['area_id'];
                $params['area_id'] = array($params['area_id']);
                $params['asm_id'] = $result[$j]['staff_id'];
                //print_r($params);
                $result_puish = array();
                if (!in_array($result[$j]['group_id'], array(RM_ID,RMSTANDBY_ID,ASM_ID,ASMSTANDBY_ID))) {
                    $result_puish = $QGoodKpi->get_punish_memo($params);
                }
                // reset parameter
                unset($params['asm_id']);
                $params['area_id'] = $old_area_id;
                //print_r($result_puish);

                $punish_price = "";
                $punish_remark = "";
                $sum_punish_price = 0;

                for ($k=0;$k<count($result_puish);$k++) {
                    $sum_punish_price = $sum_punish_price + $result_puish[$k]['price'];
                }

                if ($remark == "") { $remark = $punish_remark; }
                else { $remark = $remark.", ".$punish_remark; }

                $remark = rtrim($remark, ", ");

                // Start
                $row = array();

                if ($j == 0) { $row[] = $no++; } else { $row[] = ''; } 

                $row[] = $no2++;

                if ( is_null($result[$j]['staff_id']) ) {
                    $row[] = 'N/A';
                    $row[] = 'N/A';
                    $row[] = 'N/A';
                } else {
                    $row[] = $result[$j]['staff_code'];
                    $row[] = $result[$j]['staff_name'];
                    $row[] = $result[$j]['group_name'];
                }

                $row[] = $result_area[$i]['name'];

                $row[] = $sale_target;
                $row[] = $result[$j]['total_unit'];
                
                $row[] = $result[$j]['total_price'];
                $row[] = $result[$j]['com_rate'];

                $row[] = number_format($ratio,2);
                $row[] = 1;
                $row[] = '=MAX(0,J'.$cnt.'*L'.$cnt.')';

                $com_rate = $result[$j]['com_rate'];

                $com_extra = 0;

                // Sale Add-on Commission
                if ($flag == 1) { 

                    $params_sale = array(
                        'sale_id' => $result[$j]['staff_id'],
                        'area_id' => $params['area_id'],
                        'from'    => $params['from'],
                        'to'      => $params['to'],
                    );

                    $target_sale = $QSPT->getTargetByMonth($params_sale);

                    $sellout_sale = array();
                    $sellout_price = 0;
                    $store_price_target = 0;
                    $achieve_sale = 0;
                    $rate = 1;

                    if ( !empty($target_sale) ) {

                        $store_list = array();
                        for ($k=0;$k<count($target_sale);$k++) {

                            // Calculate Total Days
                            if ( $target_sale[$k]['st_joined_at'] <= $params['from']." 00:00:00" ) { $start = $params['from']." 00:00:00"; } 
                            else { $start = $target_sale[$k]['st_joined_at']; }

                            if ( !isset($target_sale[$k]['st_released_at']) || $target_sale[$k]['st_released_at'] >= $params['to']." 23:59:59" ) { $end = $params['to']." 23:59:59"; $date_flag = 1; } 
                            else { $end = $target_sale[$k]['st_released_at']; $date_flag = 0; }

                            $diffdate = strtotime($end) - strtotime($start);
                            $days = floor($diffdate/(60*60*24)) + $date_flag;

                            // Calculate Sale Target 
                            $sale_target = round( ($target_sale[$k]['st_target_price'] * $days) / $total_days, 0);

                            $store_price_target = $store_price_target + $sale_target;
                            // $store_list[$k] = $target_sale[$k]['st_id'];
                        }

                        $params_sale2 = array(
                            'sale_id' => $result[$j]['staff_id'],
                            'area_id' => $params['area_id'],
                            'from'    => $params['from'],
                            'to'      => $params['to'],
                        );

                        $sellout_sale = $QGoodKpi->getSelloutBySale($params_sale2);
                        $sellout_price = $sellout_sale['price'];

                        $achieve_sale = round(($sellout_price / $store_price_target) * 100, 2);

                        $rate = $QGoodKpi->getSaleAchieveRate($achieve_sale);
                    }

                    // No Taget 
                    if ( $store_price_target == 0 ) { $rate = 0; }

                    $row[] = $store_price_target;
                    $row[] = $sellout_price;
                    $row[] = $achieve_sale;
/*
                    // Sale Exception
                    if ( $result[$j]['staff_code'] == '6201256' ) {
                        $rate = 1;
                        $remark = 'ยกเว้นเงื่อนไขตาม Memo';
                    }
*/

                    // Sellout Price <= 2,500,000 Reduct to Rate 1
                    if ( $sellout_price < 2500000 ) { $rate = 1; } 

                    // $com_rate = $com_rate * $rate;
                    if ( $rate == 0 ) { $com_rate = 0; } 
                    else if ( $rate == 1 ) { $com_rate = $com_rate; } 
                    else { $com_rate = $com_rate + (round($sellout_price*$rate,0)); }
/*
                    // Extra Commission By Unit 30 May - 02 Jun 2019
                    $result_com_extra = $QGoodKpi->comission_extra($params_sale);

                    if (!empty($result_com_extra)) { $com_extra = $result_com_extra['com_rate']; } 

                    $row[] = $com_extra;
*/
                    $com_rate = max($com_rate - $sum_punish_price + $com_extra, 0);

                    $row[] = $sum_punish_price;
                    $row[] = $com_rate;
                    // $row[] = '=MAX(0,(M'.$cnt.'-Q'.$cnt.')*'.$rate.')';
                    
                } else {

                    $row[] = $sum_punish_price;
                    $row[] = '=MAX(0,M'.$cnt.'-N'.$cnt.')';
                }

                $row[] = $remark;

                $index++;

                $sum_target = $sum_target + $sale_target; 
                $sum_unit = $sum_unit + $result[$j]['total_unit']; 
                $sum_price = $sum_price + $result[$j]['total_price']; 
                $sum_com = $sum_com + $result[$j]['com_rate'];
                $total_punish_price = $total_punish_price + $sum_punish_price;

                // Last Row of Area
                if ( $j == count($result)-1 ) {

                    fputcsv($output, $row);

                    /*
                    // Sale Leader 
                    $sale_leader_data = $QGoodKpi->com_sale_leader_bkk_2017($params);

                    for ($k=0;$k<count($sale_leader_data);$k++) {

                        $remark = "ค่าคอมฯ Sale Leader";

                        $sale_target = $QGoodKpi->get_sale_com_target($params['area_id'],$sale_leader_data[$k]['staff_id'],$params['from'], $params['to']);

                        $com_rate = $sale_leader_data[$k]['com_rate'];

                        if (!$sale_target['sale_target']) { 
                            $sale_target = 0;
                            //$com_rate = 0;
                            //$remark = "ไม่มี Sale Leader Target ບໍ່ໄດ້ຮັບຄ່າຄອມ"; 
                        } 

                        $ratio = ( $sale_leader_data[$k]['total_unit'] / $sale_target ) * 100; 

                        $row = array();

                        $row[] = "";
                        $row[] = $no2++;
                        $row[] = $sale_leader_data[$k]['staff_code'];
                        $row[] = $sale_leader_data[$k]['staff_name'];
                        $row[] = $sale_leader_data[$k]['group_name'];
                        $row[] = $result_area[$i]['name'];
                        $row[] = $sale_target;
                        $row[] = $sale_leader_data[$k]['total_unit'];
                        $row[] = $ratio;
                        $row[] = $sale_leader_data[$k]['total_price'];
                        $row[] = $com_rate;
                        $row[] = '';
                        $row[] = '=MAX(0,K'.$cnt.'-L'.$cnt.')';
                        $row[] = $remark;
                        $row[] = $index++;

                        $sum_com = $sum_com + $com_rate;

                        fputcsv($output, $row);

                        $cnt++;

                    }*/

                    $cnt++;

                    

                    if ($flag == 1) {

                        array_push($index_achieve, "M".$index);
                        array_push($index_grand_price_target, "N".$index);
                        array_push($index_grand_price_sellout, "O".$index);
                        // array_push($index_com_extra, "Q".$index);
                        array_push($index_grand, "S".$index);

                    } else {

                        array_push($index_achieve, "M".$index);
                        array_push($index_grand, "O".$index);

                    }

                    $index_end = $index - 1;
                    $test = $index_start. ' | ' .$index_end;

                    $row = array();
                    $sum_ratio = ( $sum_unit / $sum_target ) * 100;

                    $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';

                    $row[] = 'Total';
                    $row[] = $sum_target;
                    $row[] = $sum_unit;
                    $row[] = $sum_price;

                    $row[] = $sum_com;
                    //$row[] = $sum_ratio;
                    $row[] = '=ROUND((H'.$index.'/G'.$index.')*100,2)';
                    $row[] = '';

                    if ($flag == 1) {

                        $row[] = '=SUM(M'.$index_start.':M'.$index_end.')';
                        $row[] = '=SUM(N'.$index_start.':N'.$index_end.')';
                        $row[] = '=SUM(O'.$index_start.':O'.$index_end.')';
                        $row[] = '';
                        // $row[] = '=SUM(Q'.$index_start.':Q'.$index_end.')';
                        $row[] = $total_punish_price;
                        $row[] = '=SUM(S'.$index_start.':R'.$index_end.')';

                    } else {
                        $row[] = '=SUM(M'.$index_start.':M'.$index_end.')';
                        $row[] = $total_punish_price;
                        $row[] = '=SUM(O'.$index_start.':O'.$index_end.')';
                    }

                    //$row[] = '=K'.$cnt.'-L'.$cnt;
                    $row[] = '';


                    $index++;

                    $grand_sum_target = $grand_sum_target + $sum_target;
                    $grand_sum_unit = $grand_sum_unit + $sum_unit;
                    $grand_sum_ratio = $grand_sum_ratio + $sum_ratio;
                    $grand_sum_price = $grand_sum_price + $sum_price;
                    $grand_sum_com = $grand_sum_com + $sum_com;
                    $grand_total_punish_price = $grand_total_punish_price + $total_punish_price;
                } 

                fputcsv($output, $row);

                $cnt++;
            }

            if ( $i == count($result_area)-1 ) { 

                $row = array();
                $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';

                $row[] = 'Grand Total';
                $row[] = $grand_sum_target;
                $row[] = $grand_sum_unit;
                $row[] = $grand_sum_price;

                $row[] = $grand_sum_com;
                //$row[] = $grand_sum_ratio;
                $row[] = '=ROUND((H'.$index.'/G'.$index.')*100,2)';
                $row[] = '';

                if ($flag == 1) { 

                    $row[] = '=SUM('.implode(',',$index_achieve).')';
                    $row[] = '=SUM('.implode(',',$index_grand_price_target).')';
                    $row[] = '=SUM('.implode(',',$index_grand_price_sellout).')';
                    $row[] = '';
                    // $row[] = '=SUM('.implode(',',$index_com_extra).')';
                    $row[] = $grand_total_punish_price;
                    $row[] = '=SUM('.implode(',',$index_grand).')';

                } else {

                    $row[] = '=SUM('.implode(',',$index_achieve).')';
                    $row[] = $grand_total_punish_price;
                    $row[] = '=SUM('.implode(',',$index_grand).')';

                }

                //$row[] = '=K'.$cnt.'-L'.$cnt;
                $row[] = '';

                fputcsv($output, $row);

            }

        } 

        exit;

    }

    public function com_total_asm($params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Report_Total_COM_ASM_'.$params['report_type'];
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'Item',
            'Area',
            'Employee ID',
            'Name',
            'Position',
            'Unit',
            'Sale Amount',
            'Commission',
            'Score (GFK.)',
            'COM. (GFK.)',
            'COM. x GFK.',
            // 'ABM COM.',
            // 'ABM Share',
            'Punish By Memo',
            'Actual Commission',
            'Remark',
        );

        fputcsv($output, $heads);

        $QGoodKpi = new Application_Model_GoodKpiLog();

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('a' => 'area'), array('a.*'));

        if ($params['report_type'] == 'UPC') {

            // Order Area By HR Request [Seperate by Thai's Region]
            $select->where('id NOT IN (48,49,72)', 1);
            $select->where('id < 73', 1);
            // $select->where('id IN (37,64)', 1);
            $select->order("FIND_IN_SET(a.id, '33,54,55,57,56,13,58,24,25,39,50,51,69,45,41,53,37,52,63,62,40,44,38,64,61,59,46,60,47,66,18,42,65,14,67,20,68')");
        } else {
            $select->where('id >= 73', 1);
            $select->where('id <= 117', 1);
            $select->order('a.name ASC');
        }
            
        //echo $select;
        $result_area = $db->fetchAll($select);
        //print_r($result_area);

        $no = 1;
        $cnt = 2;
        $grand_sum_unit         = 0;
        $grand_sum_price        = 0;
        $grand_sum_com          = 0;
        $grand_leader_com_rate  = 0;

        $QAsm = new Application_Model_Asm();
        
        for ($i=0;$i<count($result_area);$i++) {

            $area_id = $result_area[$i]['id'];
            $params['area_id'][0] = $area_id;

            // Get Leader of Area
            $result_asm = $QAsm->get_asm_for_com($area_id);
            $result_asm_cnt = count($result_asm);
            // echo "<br/>"; print_r($result_asm);

            for($j=0;$j<$result_asm_cnt;$j++) {

                // $abm_flag = 0;

                $params['asm_id'] = $result_asm[$j]['staff_id'];
                $params['asm_code'] = $result_asm[$j]['staff_code'];

                $leader_remark = "";
                $leader_com_rate = 0;
                $score_group = $result_asm[$j]['group_name'];

                //$leader_ratio = ( $sum_unit / $sum_target ) * 100;
/*
                // Exception for ASM BKK-E5A 
                if ($result_asm[$j]['staff_code'] == '5801683' && $area_id == 97) { 
                    $result_asm[$j]['group_id'] = ASMSTANDBY_ID; 
                    $leader_remark = 'ได้รับค่าคอมฯ Rate ASM Stand By';
                }
*/
                if ($result_asm[$j]['group_id'] == ASM_ID) {
                    $leader_com_rate = 0;

                    // Get Sellout By Area 
                    $params['com_leader'] = ASM_ID;
                    $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                    unset($params['com_leader']);

                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                    // $sum_unit = $result_by_area['total_unit'];
                    // $sum_price = $result_by_area['total_price'];
                    $leader_com_rate = round($result_by_area['com_rate'] * $asm_score['gfk'], 0);

                }

                if ($result_asm[$j]['group_id'] == ASMSTANDBY_ID) {
                    $leader_com_rate = 0;

                    // Get Sellout By Area 
                    $params['com_leader'] = ASM_ID;
                    $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                    unset($params['com_leader']);

                    $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);
                    if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                    // $sum_unit = $result_by_area['total_unit'];
                    // $sum_price = $result_by_area['total_price'];
                    $leader_com_rate = round(($result_by_area['com_rate'] * $asm_score['gfk']) / 2, 0);
                    $leader_remark = "ได้รับค่าคอมฯ 50% ของ ASM";
                }

                if ($result_asm[$j]['group_id'] == RM_ID) {
                    $leader_com_rate = 0;

                    // Get Sellout By Area 
                    $params['com_leader'] = RM_ID;
                    $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                    unset($params['com_leader']);

                    $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$j]['staff_id'],$area_id,$params['from'],$params['to']);
                    if (!$rm_score['score']) { 
                        $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                        $score_group[$j] = "ASM";
                        if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                    } else {
                        $asm_score = $rm_score;
                    }

                    // $sum_unit = $result_by_area['total_unit'];
                    // $sum_price = $result_by_area['total_price'];
                    $leader_com_rate = round($result_by_area['com_rate'] * $asm_score['gfk'], 0);

                }

                if ($result_asm[$j]['group_id'] == RMSTANDBY_ID) {
                    $leader_com_rate = 0;

                    // Get Sellout By Area 
                    $params['com_leader'] = RM_ID;
                    $result_by_area = $QGoodKpi->com_sale_bkk_2017($params);
                    unset($params['com_leader']);

                    $rm_score = $QGoodKpi->get_sale_com_rm($result_asm[$j]['staff_id'],$area_id,$params['from'],$params['to']);
                    if (!$rm_score['score']) { 
                        $asm_score = $QGoodKpi->get_sale_com_asm($area_id,$params['from'],$params['to']);

                        $score_group[$j] = "ASM";
                        if (!$asm_score['score']) { $asm_score['gfk'] = 0; } 

                    } else {
                        $asm_score = $rm_score;
                    }

                    // $sum_unit = $result_by_area['total_unit'];
                    // $sum_price = $result_by_area['total_price'];
                    $leader_com_rate = round(($result_by_area['com_rate'] * $asm_score['gfk']) / 2, 0);
                    $leader_remark = "ได้รับค่าคอมฯ 50% ของ RD";

                }

                // Check Punish By Memo
                $result_puish = $QGoodKpi->get_punish_memo($params);

                $punish_price = "";
                $punish_remark = "";
                $sum_punish_price = 0;

                for ($k=0;$k<count($result_puish);$k++) {

                    if ($k==0) { 
                        $punish_remark = "ຫັກຕາມ Memo ".number_format($result_puish[$k]['price']); 
                        //$punish_remark = $result_puish[$k]['p_name']; 
                    } else { 
                        $punish_remark = $punish_remark."+".number_format($result_puish[$k]['price']); 
                        //$punish_remark = $punish_remark."\n".$result_puish[$k]['p_name']; 
                    }

                    $sum_punish_price = $sum_punish_price + $result_puish[$k]['price'];
                }

                if ($punish_remark != "") { $punish_remark = $punish_remark." บาท"; }

                if ($leader_remark == "") { $leader_remark = $punish_remark; }
                else { $leader_remark = $leader_remark.", ".$punish_remark; }

                //$leader_ac_com_rate = max($leader_com_rate - $sum_punish_price, 0);

                // Start 
                $row = array();

                if ($j == 0) { 
                    $row[] = $no++; 
                    $row[] = $result_area[$i]['name'];
                } else { 
                    $row[] = ''; 
                    $row[] = ''; 
                } 

                $row[] = $result_asm[$j]['staff_code'];
                $row[] = $result_asm[$j]['staff_name'];
                $row[] = $result_asm[$j]['group_name'];

                if ($j == 0) { 
                    
                    unset($params['asm_code']);
                    $area_temp = array();
                    $sum_unit = $sum_price = 0;
                    $area_temp = $QGoodKpi->com_sale_bkk_2017($params);

                    foreach ($area_temp as $key => $value) {
                        $sum_unit += $value['total_unit'];
                        $sum_price += $value['total_price'];
                    }

                    $row[] = $sum_unit;
                    $row[] = $sum_price;

                } else { 
                    // Clear Duplicate Unit, Price
                    $sum_unit = 0;
                    $sum_price = 0;

                    $row[] = ''; 
                    $row[] = ''; 
                } 

               
                $row[] = $result_by_area['com_rate'];
                $row[] = $asm_score['score'];
                $row[] = $asm_score['gfk'];
                $row[] = $leader_com_rate;
/*
                $row[] = $abm_plus;
                $row[] = $abm_share;
*/
                $row[] = $sum_punish_price; 
                // $row[] = '=MAX(0,K'.$cnt.'+L'.$cnt.'-M'.$cnt.'-N'.$cnt.')';
                $row[] = '=MAX(0,K'.$cnt.'-L'.$cnt.')';
                $row[] = $leader_remark;

                $grand_sum_unit         = $grand_sum_unit + $sum_unit;
                $grand_sum_price        = $grand_sum_price + $sum_price;
                $grand_sum_com          = $grand_sum_com + $result_by_area['com_rate'];
                $grand_leader_com_rate  = $grand_leader_com_rate + $leader_com_rate;

/*
                $grand_sum_abm_com      = $grand_sum_abm_com + $abm_plus;
                $grand_sum_abm_share    = $grand_sum_abm_share + $abm_share;
*/
                $grand_sum_punish       = $grand_sum_punish + $sum_punish_price;

                fputcsv($output, $row);

                $cnt++;

            }

        } 

        // Last Row
        $row = array();
        $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; 
        $row[] = 'Total'; 
        $row[] = $grand_sum_unit; 
        $row[] = $grand_sum_price; 
        $row[] = $grand_sum_com; 
        $row[] = '';
        $row[] = '';
        $row[] = $grand_leader_com_rate; 
/*
        $row[] = $grand_sum_abm_com;
        $row[] = $grand_sum_abm_share;
*/
        $row[] = $grand_sum_punish;
        // $row[] = '=K'.$cnt.'+L'.$cnt.'-M'.$cnt.'-N'.$cnt;
        $row[] = '=K'.$cnt.'-L'.$cnt;

        fputcsv($output, $row);

        exit;

    }

    // Sale Commission
    public static function kpiSaleBKK_ORG($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'Target',
            'Total Unit',
            'Achieve Rate',
            'Commission' 
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $tmp_month = explode('-',$params['from']);
        $params['month'] = intval($tmp_month[1]);

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoodKpiLog->report_kpiSaleBKK_ORG($params);
        //print_r($data);die;

        for ($i=0;$i<count($data);$i++) { 

            $achieve_rate = 0;
            $com_rate = 0;
            $cnt = $i + 1;

            $achieve_rate = ($data[$i]['total_unit'] / $data[$i]['target']) * 100;

            // May ahead
            if ($params['month'] >= 5) {
                if ($achieve_rate >= 60) { $com_rate = 10000; }
            } 
            
            if ($achieve_rate >= 70) { $com_rate = 15000; }
            if ($achieve_rate >= 80) { $com_rate = 20000; }
            if ($achieve_rate >= 90) { $com_rate = 25000; }
            if ($achieve_rate >= 100) { $com_rate = 30000; }
            if ($achieve_rate >= 110) { $com_rate = 40000; }
        
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['target']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, number_format($achieve_rate,2)."%" );
            $sheet->setCellValue($alpha++ . $index, $com_rate);

            $index++;
        }

        $filename = 'Commission Sale - BKK ORG - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public static function kpiSaleBKK_Dealer($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'Store Name',
            'Store Type',
            'Total Unit',
            'Total Price',
            'Commission', 
            'Store Share',
            'Number of PC'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $tmp_month = explode('-',$params['from']);
        $params['month'] = intval($tmp_month[1]);

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        $data = $QGoodKpiLog->report_kpiSaleBKK_Dealer($params);
        //print_r($data);die;

        for ($i=0;$i<count($data);$i++) { 
            $com_rate = 0;
            $store_share = 0;
            $cnt = $i + 1;

            // check pc of thi month base on from filter
            $result_ss = array();
            $where = array();
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $data[$i]['store_id']);
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = 0');
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('FROM_UNIXTIME(joined_at, "%Y-%m-%d 00:00:00") <= ?', 
                date('Y-m-01 00:00:00', strtotime($params['from'])) );
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('( FROM_UNIXTIME(released_at, "%Y-%m-%d 23:59:59") >= ? 
                OR released_at IS NULL )', date('Y-m-t 23:59:59', strtotime($params['from'])));

            $result_ss = $QStoreStaffLog->fetchAll($where);

            /*
            if ($data[$i]['total_unit'] >= 10 && $params['month'] >= 5) {

                if (count($result_ss) > 0) {
                    $com_rate = $data[$i]['com_rate'];
                    $store_share = "0";
                } else {
                    $com_rate = $data[$i]['com_rate'] / 2;
                    $store_share = $data[$i]['com_rate'] / 2;
                }
                
            } else {
                $com_rate = $data[$i]['com_rate'];
                $store_share = "0";
            }*/

            if ($data[$i]['total_unit'] >= 1 ) {
                if (count($result_ss) > 0) {
                    $com_rate = $data[$i]['com_rate'];
                    $store_share = "0";
                } else {
                    $com_rate = $data[$i]['com_rate'] / 2;
                    $store_share = $data[$i]['com_rate'] / 2;
                }
            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['org_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit'] );
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price'] );
            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $store_share);
            $sheet->setCellValue($alpha++ . $index, count($result_ss) );

            $index++;
        }

        $filename = 'Commission Sale - BKK - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public static function kpiSaleScanBKK_Dealer($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'Store Name',
            'Store Type',
            'Total Unit',
            'Total Price',
            'Commission', 
            'Store Share',
            'Number of PC'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $tmp_month = explode('-',$params['from']);
        $params['month'] = intval($tmp_month[1]);

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        $data = $QGoodKpiLog->report_kpiSaleScanBKK_Dealer($params);
        //print_r($data);die;

        for ($i=0;$i<count($data);$i++) { 
            $com_rate = 0;
            $store_share = 0;
            $cnt = $i + 1;

            // check pc of thi month base on from filter
            $result_ss = array();
            $where = array();
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $data[$i]['store_id']);
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = 0');
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('FROM_UNIXTIME(joined_at, "%Y-%m-%d 00:00:00") <= ?', 
                date('Y-m-01 00:00:00', strtotime($params['from'])) );
            $where[] = $QStoreStaffLog->getAdapter()->quoteInto('( FROM_UNIXTIME(released_at, "%Y-%m-%d 23:59:59") >= ? 
                OR released_at IS NULL )', date('Y-m-t 23:59:59', strtotime($params['from'])));

            $result_ss = $QStoreStaffLog->fetchAll($where);

            /*
            if ($data[$i]['total_unit'] >= 10 && $params['month'] >= 5) {

                if (count($result_ss) > 0) {
                    $com_rate = $data[$i]['com_rate'];
                    $store_share = "0";
                } else {
                    $com_rate = $data[$i]['com_rate'] / 2;
                    $store_share = $data[$i]['com_rate'] / 2;
                }
                
            } else {
                $com_rate = $data[$i]['com_rate'];
                $store_share = "0";
            }*/

            if ($data[$i]['total_unit'] >= 1 ) {
                if (count($result_ss) > 0) {
                    $com_rate = $data[$i]['com_rate'];
                    $store_share = "0";
                } else {
                    $com_rate = $data[$i]['com_rate'] / 2;
                    $store_share = $data[$i]['com_rate'] / 2;
                }
            }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['org_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit'] );
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price'] );
            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $store_share);
            $sheet->setCellValue($alpha++ . $index, count($result_ss) );

            $index++;
        }

        $filename = 'Commission Sale Scan - BKK - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public static function kpiSale($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'Group',
            'Total Unit',
            'Total Price',
            'Commission'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoodKpiLog->report_kpiSale($params);

        for ($i=0;$i<count($data);$i++) {
            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);

            if ($data[$i]['gr_id'] != SALES_ID) { $sheet->setCellValue($alpha++ . $index, 0); } 
            else { 
                $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']);
                
                /*if ($data[$i]['total_unit'] < 150) { $sheet->setCellValue($alpha++ . $index, 0); } 
                else { $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']); } */
            }

            $index++;
        }

        $filename = 'Commission Sale - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public static function kpiSaleScan($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'Group',
            'Total Unit',
            'Total Price',
            'Commission'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoodKpiLog->report_kpiSaleScan($params);

        for ($i=0;$i<count($data);$i++) {
            $cnt = $i + 1;

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_price']);

            if ($data[$i]['gr_id'] != SALES_ID) { $sheet->setCellValue($alpha++ . $index, 0); } 
            else { 
                $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']);
                
                /*if ($data[$i]['total_unit'] < 150) { $sheet->setCellValue($alpha++ . $index, 0); } 
                else { $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']); } */
            }

            $index++;
        }

        $filename = 'Commission Sale Scan - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    public static function kpiEOL($params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'AREA',
            'Staff Code',
            'Staff Name',
            'EOL Sellout',
            'Commission'
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index = 2;

        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoodKpiLog->comission_eol($params);
        //print_r($data);die;

        for ($i=0;$i<count($data);$i++) { 

            $com_rate = 0;
            $cnt = $i + 1;

            $com_rate = $data[$i]['total_unit'] * 100;
            // May ahead
        
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt++);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['total_unit']);
            $sheet->setCellValue($alpha++ . $index, $com_rate);

            $index++;
        }

        $filename = 'Commission - EOL - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }


    public static function kpiLeader($from, $to)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Mã NV',
            'NV KV Phụ trách',
            'Ngày công',
            'Tên cửa hàng',
            'Tổng cộng',
        );

        // các model có đổi giá
        $QKpi = new Application_Model_GoodPriceLog();
        $list = $QKpi->get_list(date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to)), My_Kpi_Object::Pg);

        $QGood = new Application_Model_Good();
        $products = $QGood->get_cache();

        $QGoodColor = new Application_Model_GoodColor();
        $product_colors = $QGoodColor->get_cache();

        $product_col = chr(ord('A') + count($heads));
        $product_color_list = array();

        foreach ($products as $key => $value)
        {
            // các model có đổi giá, tách thành nhiều cột
            if (isset($list[$key]))
            {
                foreach ($list[$key] as $_color => $ranges)
                {
                    if ($_color)
                    {
                        $product_color_list[$key][] = $_color;

                        foreach ($ranges as $range)
                            $heads[] = $value . ' / ' . $product_colors[$_color] . ' / ' . (new DateTime($range['from']))->
                                format('d') . '->' . (new DateTime($range['to']))->format('d');
                    } else
                    {
                        foreach ($ranges as $range)
                            $heads[] = $value . ' / ' . (new DateTime($range['from']))->format('d') . '->' . (new
                                DateTime($range['to']))->format('d');
                    }
                }
            }
        }

        $heads[] = 'Cộng';
        $heads[] = 'Thưởng KPI';

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $index++;

        $QArea = new Application_Model_Area();
        $area_cache = $QArea->get_cache();
        $QRegion = new Application_Model_RegionalMarket();
        $region_cache = $QRegion->get_cache_all();

        $db = Zend_Registry::get('db');

        // danh sách tất cả NV PG được tính KPI
        // dựa vào danh sách và thứ tự trong danh sách này để điền chính xác vào file excel
        $sql = "SELECT DISTINCT s.id, s.`code`, s.regional_market, s.firstname, s.lastname
            FROM staff s INNER JOIN imei_kpi i ON s.id=i.leader_id
            WHERE DATE(i.timing_date) >= ? AND DATE(i.timing_date) <= ?
            AND s.regional_market <> 0 AND s.regional_market IS NOT NULL";

        $all_staff = $db->query($sql, array($from, $to));

        // dùng mảng này để lưu thứ tự các staff trong result set trên
        // vì duyệt xong thằng kia thì hết set rồi, con trỏ mà
        $staff_array = array();
        $staff_list = array();

        // điền danh sách staff ra file trước
        foreach ($all_staff as $key => $staff)
        {
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, isset($area_cache[$region_cache[$staff['regional_market']]['area_id']]) ?
                $area_cache[$region_cache[$staff['regional_market']]['area_id']] : '');
            $sheet->setCellValue($alpha++ . $index, $staff['code']);
            $sheet->setCellValue($alpha++ . $index, $staff['firstname'] . ' ' . $staff['lastname']);

            $staff_array[$staff['id']] = $index; // lưu dòng ứng với staff id
            $staff_list[] = $staff['id']; // dùng để fetchGrid
            $index++;
        }

        $QImeiKpi = new Application_Model_ImeiKpi();
        $params = array(
            'kpi'    => 1,
            'leader' => $staff_list,
            'from'   => $from,
            'to'     => $to,
        );

        $sell_out = $QImeiKpi->fetchGrid($params);
        $sell_out_list = array();

        // sắp xếp $sell_out theo staff, rồi theo good_id, color...
        foreach ($sell_out as $_key => $_so) {
            $sell_out_list[ $_so['staff_id'] ][ $_so['good_id'] ][ $_so['color_id'] ][ $_so['from_date'].'_'.$_so['to_date'] ] = array(
                'sell_out'      => $_so['quantity'],
                'activated'     => $_so['activated'],
                'non_activated' => $_so['non_activated'],
                'kpi'           => $_so['kpi'],
            );
        }

        // duyệt qua dãy model để tính KPI,
        // mỗi lần duyệt tính KPI của nguyên list NV, theo 1 model của vòng lặp hiện tại
        $alpha = $product_col;
        $kpi_list = array();
        $quantity_list = array();

        foreach ($products as $_product_id => $value)
        {
            // các model có đổi giá, tách thành 2 cột
            if (isset($list[$_product_id])) {
                foreach ($list[$_product_id] as $_color => $ranges) {
                    foreach ($ranges as $range) {
                        foreach ($sell_out_list as $_staff_id => $_data) {
                            // nếu có trong danh sách NV ở trên
                            // đưa vào đúng dòng luôn
                            if (isset($staff_array[$_staff_id])) {
                                $sheet->setCellValue(
                                    $alpha . $staff_array[$_staff_id],
                                    isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['activated'])
                                        ? $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['activated']
                                        : 0
                                );

                                if (!isset($kpi_list[ $_staff_id ]))
                                    $kpi_list[ $_staff_id ] = 0;

                                $kpi_list[ $_staff_id ] += isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['kpi'])
                                    ? $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['kpi']
                                    : 0;

                                if (!isset($quantity_list[ $_staff_id ]))
                                    $quantity_list[ $_staff_id ] = 0;

                                $quantity_list[ $_staff_id ] += isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['activated'])
                                    ? $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]['activated']
                                    : 0;

                            }
                        }

                        $alpha++;
                    }
                }
            }
        }

        foreach ($quantity_list as $_staff_id => $_qty) {
            if (isset($staff_array[$_staff_id])) {
                $sheet->setCellValue(
                    $alpha . $staff_array[$_staff_id],
                    $_qty
                );
            }
        }
        
        $alpha++;

        foreach ($kpi_list as $_staff_id => $_kpi) {
            if (isset($staff_array[$_staff_id])) {
                $sheet->setCellValue(
                    $alpha . $staff_array[$_staff_id],
                    $_kpi
                );
            }
        }


        $filename = 'Sell out - Leader - ' . date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    public function ExportKPIsetting($params) {
        
        error_reporting(E_ALL);
        date_default_timezone_set('Europe/London');
        require_once 'PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator('Catty System');
        //Setting a worksheet’s page orientation and size

        // Set LANDSCAPE 
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Set Paper Size
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //Page Setup: Scaling options
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        //Page margins 
        /*
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(1);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(1);
*/
        $heads = array(
            'No.',
            'Product',
            'Color',
            'Price',
            //'KPI',
            'Com Rate',
            'From', 
            'To'
        );

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($alpha)->setWidth(21); 
            $objPHPExcel->getActiveSheet()->getRowDimension($index)->setRowHeight(25);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha.$index, $key);

            $objPHPExcel->getActiveSheet()->getStyle($alpha.$index)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($alpha.$index)->getFill()->getStartColor()->setRGB('9F9F9F');
            
            $alpha++;
        }

        $QGoogKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoogKpiLog->fetchGoodKpi(null, null, $total, $params);
        //print_r($data); die;

        $index = 2;

        for ($i=0;$i<count($data);$i++) {
            $alpha = 'A';

            $d = explode('-', $data[$i]['from_date']);
            $from = $d[2].'/'.$d[1].'/'.$d[0];
            $d = explode('-', $data[$i]['to_date']);
            $to = $d[2].'/'.$d[1].'/'.$d[0];

            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha.$index, $i+1);

            $objPHPExcel->getActiveSheet()->getColumnDimension($alpha++)->setWidth(21); 
            $objPHPExcel->getActiveSheet()->getRowDimension($index)->setRowHeight(25);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['good_name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['price']);
            //$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['kpi']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['com_rate']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $from);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $to);


            $styleArray = array(
              'borders' => array(
                'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_DASHDOT                        
                )
              )
            );

            $objPHPExcel->getActiveSheet()->getStyle("A".$index.":G".$index)->applyFromArray($styleArray);
            unset($styleArray);

            $index++;
        }



        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Catty System');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = 'KPI Setting - List - ' . date('Y-m-d H-i-s');
        // Redirect output to a client’s web browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="'.$filename.'.pdf"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save('php://output');
        exit;
    }

    //PC Level Report
    public static function pcLevelReport($params) {
        
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);
        
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'Staff Code',
            'Staff Name',
            'Staff Created',
            'First Checkin',
            'Work Month',
            'Level',
            'Area',
            'Market Name',
            'Store ID',
            'Store Name',
            'Store Type',
            'PCM',
            'Target',
            'Sell OUT',
            'Achieve',
            'Total Sell OUT',
            'F9 Series',
            'F9 Series Share',
            'Target',
            'Sell OUT',
            'Achieve',
            'Total Sell OUT',
            'F9 Series',
            'F9 Series Share'        
        );

        $startdatecurrent = "";
        $enddatecurrent = "";

        $startdatebefore = "";
        $enddatebefore = "";

        $frommonth = (new DateTime($params['from']))->format('m');
        $fromyear = (new DateTime($params['from']))->format('Y');
        $tomonth = (new DateTime($params['to']))->format('m');
        $toyear = (new DateTime($params['to']))->format('Y');
  
        $diffcheck = (int)$tomonth - (int)$frommonth;

        if($diffcheck > 0){
            $startdatecurrent = $toyear."-".$tomonth."-01";           
            $enddatecurrent = $params["to"];
        }else{
            $startdatecurrent = $params["from"];           
            $enddatecurrent = $params["to"];
        }

        $tolast = (new DateTime($params['from']))->modify('-1 month')->format('Y-m-d');
        
        $lastMonth = (new DateTime($tolast))->format('m');
        $lastYear =(new DateTime($tolast))->format('Y');
        $countmonth = (new DateTime($tolast))->format('t') ;
           
        $startdatebefore = $lastYear."-".$lastMonth."-01";   
        $enddatebefore = $lastYear."-".$lastMonth."-".$countmonth;   

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;

        foreach ($heads as $value) {
            $PHPExcel->getActiveSheet()->getColumnDimension($alpha)->setWidth(21); 
            $PHPExcel->getActiveSheet()->getRowDimension($index)->setRowHeight(25);

            $PHPExcel->setActiveSheetIndex(0)->setCellValue($alpha.$index, $key);
            if($alpha < "L"){
                $sheet->mergeCells($alpha.'1:'.$alpha.'2');  
            }

            $styleArray = array(
              'borders' => array(
                'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_MEDIUM                        
                )
              )
            );

            $PHPExcel->getActiveSheet()->getStyle("A2:X2")->applyFromArray($styleArray);
            unset($styleArray);
            if($alpha > "K"){
                $sheet->mergeCells('M1:R1');
                $sheet->mergeCells('S1:X1');

                 $current_s = (new DateTime($startdatecurrent))->format('d M Y');
                 $current_e = (new DateTime($enddatecurrent))->format('d M Y');
                 $before_s = (new DateTime($startdatebefore))->format('d M Y');
                 $before_e = (new DateTime($enddatebefore))->format('d M Y');

                $sheet->setCellValue('M1', $before_s." - " .$before_e);
                $sheet->setCellValue('S1', $current_s." - " .$current_e);
                
                $sheet->setCellValue($alpha."2", $value);
            }else{
                $sheet->setCellValue($alpha.$index, $value);
            }
            
            $alpha++;
        }
        $sheet->setCellValue('B2', "");

        $QStaff = new Application_Model_Staff();
        $Qtiming = new Application_Model_Timing();
        $QStaffCheckInLog = new Application_Model_StaffCheckInLog();
        $QAsm = new Application_Model_Asm();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;

        // My_Staff_Permission_Area::view_all // kiểm tra xem người này có toàn quyền xem tất cả các khu vực hay không
        // viết xong rồi giờ nhìn vô éo nhớ nó là cái gì, phải comment lại cho chắc
        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        $dataStaff = $QStaff->getStaffPcLevel($params);
        $dataTiming = $Qtiming->getTimingPcLevelCurrent($params,$startdatecurrent,$enddatecurrent);
        $dataTimingBefore = $Qtiming->getTimingPcLevelCurrent($params,$startdatebefore,$enddatebefore);

        $index = 3;
        $n = 1;

        $itemcount = 0;
        foreach ($dataStaff as $key  ) {

            // Get First Action of Checkin
            $temp = array(
                'staff_code'    => $key["staff_code"],
                'group_id'      => $key["group_id"],
            );

            $result_staff = $QStaffCheckInLog->getStaffInfo($temp);

            //  $styleArray = array(
            //   'borders' => array(
            //     'outline' => array(
            //       'style' => PHPExcel_Style_Border::BORDER_DASHED                        
            //     )
            //   )
            // );
            // $PHPExcel->getActiveSheet()->getStyle("A".$index.":T".$index)->applyFromArray($styleArray);
            // unset($styleArray);

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $n++);            
            $sheet->setCellValue($alpha++ . $index, (int)$key["staff_code"]);
            $sheet->setCellValue($alpha++ . $index, $key["firstname"]." ". $key["lastname"]);
            $sheet->setCellValue($alpha++ . $index, $key["created_at"]);
            $sheet->setCellValue($alpha++ . $index, $result_staff['first_checkin']);
            
            $d1 = new DateTime();
            $d2 = new DateTime($key["created_at"]);
            // echo $d1->format('Y-m-d')."<br>".$d2->format('Y-m-d')."<br>";
            $diff = $d1->diff($d2);
            $workMonth = 12 * $diff->y + $diff->m;

            // $workMonth = ($diff->format('%m months'));

            $sellout = (int)$dataTiming[$key["staff_id"]][$key["store_id"]]["sellout"];
            $selloutall = (int)$dataTiming[$key["staff_id"]]["selloutall"];
            $target = (int)$dataTiming[$key["staff_id"]][$key["store_id"]]["target"];
            $r9sseries =  (int)$dataTiming[$key["staff_id"]][$key["store_id"]]["r9sseries"];

            $selloutbefore = (int)$dataTimingBefore[$key["staff_id"]][$key["store_id"]]["sellout"];
            // $selloutallbefore = (int)$dataTimingBefore[$key["staff_id"]][$key["store_id"]]["selloutall"];
            $selloutallbefore = (int)$dataTimingBefore[$key["staff_id"]]["selloutall"];

            $targetbefore = (int)$dataTimingBefore[$key["staff_id"]][$key["store_id"]]["target"];
            $r9sseriesbefore =  (int)$dataTimingBefore[$key["staff_id"]][$key["store_id"]]["r9sseries"];

            /*
            // Check Same Staff + More than 1 Store
            if($selloutall == 0){
                foreach ($dataStaff as $key2  ) {
                    if($selloutall == 0 && $key2['staff_id']==$key["staff_id"]){
                        $selloutall = (int)$dataTiming[$key["staff_id"]][$key2["store_id"]]["selloutall"];
                    }
                    
                }
             }
            if($selloutallbefore == 0){
                foreach ($dataStaff as $key2  ) {
                    if($selloutallbefore == 0 && $key2['staff_id']==$key["staff_id"]){
                        $selloutallbefore = (int)$dataTimingBefore[$key["staff_id"]][$key2["store_id"]]["selloutall"];
                    }
                    
                }
             }
            */
             
            $level = "Trainee";
            if($key["level_id"]){
                $level = $key["level_name"];
            }
            $sheet->setCellValue($alpha++ . $index, $workMonth);
            $sheet->setCellValue($alpha++ . $index, $level);
            $sheet->setCellValue($alpha++ . $index, $key["area_name"]);
            $sheet->setCellValue($alpha++ . $index, $key["market_name"]);
            $sheet->setCellValue($alpha++ . $index, $key["store_id"]);
            $sheet->setCellValue($alpha++ . $index, $key["store_name"]);
            $sheet->setCellValue($alpha++ . $index, $key["store_type"]);

            // Add PCM Name 
            $pcm_name = "";
            $pcm_list = $QAsm->getPCMByStoreID($key["store_id"]);

            $cnt = count($pcm_list);

            if ($cnt > 0 ) {
                for ($i=0;$i<$cnt;$i++) {
                    $pcm_name = $pcm_name.$pcm_list[$i]['staff_name'];
                    if ($i != $cnt-1) { $pcm_name = $pcm_name." / "; }
                }
            } 

            $sheet->setCellValue($alpha++ . $index, $pcm_name);

            $sheet->setCellValue($alpha++ . $index, $targetbefore);
            
            $sheet->setCellValue($alpha++ . $index, $selloutbefore);
             $achievebefore = 0;
            if(isset($targetbefore) && $targetbefore > 0){            
                $achievebefore = round(($selloutbefore/$targetbefore)*100,2);        
            }
            
            $sheet->setCellValue($alpha++ . $index, $achievebefore."%");
            $sheet->setCellValue($alpha++ . $index, $selloutallbefore);
            $sheet->setCellValue($alpha++ . $index, $r9sseriesbefore);
            $sheet->setCellValue($alpha++ . $index, round(($r9sseriesbefore/$selloutbefore)*100,2)."%");        
            $sheet->setCellValue($alpha++ . $index, $target);            
            $sheet->setCellValue($alpha++ . $index, $sellout);
             $achieve = 0;
            if($target > 0){                
                $achieve = round(($sellout/$target)*100,2);
            }
            
            $sheet->setCellValue($alpha++ . $index, $achieve."%");
            $sheet->setCellValue($alpha++ . $index, $selloutall);
            $sheet->setCellValue($alpha++ . $index, $r9sseries);
            $sheet->setCellValue($alpha++ . $index, round(($r9sseries/$sellout)*100,2)."%");
        
            $item++;
            $index++;
        }

        $filename = ' PC - Level - '.date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');
        exit;

    }

    // Sellin Commission for Sale 
    public function sellin_com_sale_export($data, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $heads = array(
            'No.',
            'ID Staff',
            'NAME',
            'Position',
            'ZONE',
            'Unit',
            'Sale Amount',
            'Commission',
            'Remark',
        );

        $area_id = $data[0]['area_id'];
        $area_name = $data[0]['area_name'];

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $sheet->setCellValue('A1', 'Possefy Group CO.,LTD');
        $sheet->setCellValue('A2', "COMMISSION FOR ".$params['from_date']." - ".$params['to_date']);
        $sheet->setCellValue('A3', "ZONE : ");
        $sheet->setCellValue('B3', $area_name);

        $alpha = 'A';
        $index = 4;
        foreach ($heads as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('B3:I3');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:I1")->applyFromArray($style);

        $index = 5;

        $cnt_data = count($data);
        $cnt = 0;
        $total_sum_sellin = $total_sum_m_total = $total_com_rate = 0;

        for ($i=0;$i<$cnt_data;$i++) { 

            $remark = "";

            $cnt = $cnt + 1;
            $alpha = 'A';

            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['sum_sellin']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['sum_m_total']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']);
            $sheet->setCellValue($alpha++ . $index, $remark);

            $total_sum_sellin = $total_sum_sellin + $data[$i]['sum_sellin'];
            $total_sum_m_total = $total_sum_m_total + $data[$i]['sum_m_total'];
            $total_com_rate = $total_com_rate + $data[$i]['com_rate'];

            $index++;
        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, "Total");
        $sheet->setCellValue($alpha++ . $index, "");
        $sheet->setCellValue($alpha++ . $index, "");
        $sheet->setCellValue($alpha++ . $index, "");
        $sheet->setCellValue($alpha++ . $index, "");
        $sheet->setCellValue($alpha++ . $index, $total_sum_sellin);
        $sheet->setCellValue($alpha++ . $index, $total_sum_m_total);
        $sheet->setCellValue($alpha++ . $index, $total_com_rate);

        $sheet->mergeCells("A".$index.":E".$index);
        $sheet->getStyle("A".$index.":E".$index)->applyFromArray($style);

        $filename = 'Sellin Commission Sale - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

    // Export Sellin Commission foe Sale [Total]
    public function sellin_com_total_sale($params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Report_Total_Sellin_COM_Sale'.$params['report_type'];
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'Area NO.',
            'Item',
            'Staff ID',
            'Name',
            'Position',
            'Zone',
            'Unit',
            'Sales Amount',
            'Commission',
            'Remark',
        );

        fputcsv($output, $heads);

        $QGoodKpi = new Application_Model_GoodKpiLog();

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('a' => 'area'), array('a.*'));

        if ($params['report_type'] == 'UPC') {

            // Order Area By HR Request [Seperate by Thai's Region]
            $select->where('id NOT IN (48,49,72)', 1);
            $select->where('id < 73', 1);
            $select->order("FIND_IN_SET(a.id, '33,54,55,57,56,13,58,24,25,39,50,51,69,45,41,53,37,52,63,62,40,44,38,64,61,59,46,60,47,66,18,42,65,14,67,20,68')");
        } else {
            $select->where('id >= 73', 1);
            $select->where('id <= 117', 1);
            $select->order('a.name ASC');
        }
            
        //echo $select;
        $result_area = $db->fetchAll($select);
        //print_r($result_area);

        $no = 1;
        $cnt = 2;
        $grand_sum_unit     = 0;
        $grand_sum_price    = 0;
        $grand_sum_com      = 0;

        for ($i=0;$i<count($result_area);$i++) {

            $result = array();
            $params['area_id'] = $result_area[$i]['id'];

            $result = $QGoodKpi->sellin_com_sale($params);

            //print_r($result);

            $no2 = 1;
            $sum_unit   = 0;
            $sum_price  = 0;
            $sum_com    = 0;
            $remark = '';

            for ($j=0;$j<count($result);$j++) {

                $remark = "";

                // Start
                $row = array();

                if ($j == 0) { $row[] = $no++; } else { $row[] = ''; } 

                $row[] = $no2++;

                $row[] = $result[$j]['staff_code'];
                $row[] = $result[$j]['staff_name'];
                $row[] = $result[$j]['staff_group'];

                $row[] = $result_area[$i]['name'];

                $row[] = $result[$j]['sum_sellin'];
                $row[] = $result[$j]['sum_m_total'];
                $row[] = $result[$j]['com_rate'];
                $row[] = $remark;

                $sum_unit = $sum_unit + $result[$j]['sum_sellin']; 
                $sum_price = $sum_price + $result[$j]['sum_m_total']; 
                $sum_com = $sum_com + $result[$j]['com_rate'];

                // Last Row of Area
                if ( $j == count($result)-1 ) {

                    fputcsv($output, $row);

                    $cnt++;

                    $row = array();

                    $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';

                    $row[] = 'Total';
                    $row[] = $sum_unit;
                    $row[] = $sum_price;

                    $row[] = $sum_com;
                    $row[] = '';

                    $grand_sum_unit = $grand_sum_unit + $sum_unit;
                    $grand_sum_price = $grand_sum_price + $sum_price;
                    $grand_sum_com = $grand_sum_com + $sum_com;

                } 

                fputcsv($output, $row);

                $cnt++;
            }

            if ( $i == count($result_area)-1 ) { 

                $row = array();
                $row[] = ''; $row[] = ''; $row[] = ''; $row[] = ''; $row[] = '';

                $row[] = 'Grand Total';
                $row[] = $grand_sum_unit;
                $row[] = $grand_sum_price;
                $row[] = $grand_sum_com;
                $row[] = '';

                fputcsv($output, $row);

            }

        } 

        exit;

    }

    // AM Commission 
    public static function com_am($data_tmp, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        $data = array();

        foreach ($data_tmp as $key => $value) { $data[ $value['base_kpi'] ][] = $value; }
        $data = array_values($data);

        $staff_code = $data[0]['staff_code'];
        $staff_name = $data[0]['staff_name'];
        $base_pay = $data[0]['base_kpi'];

        $com_month = date('F Y', strtotime($params['from']) );

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        // Row 1
        $heads_01 = array('ລາຍງານສະຫຼຸບຄ່າຄອມປະຈຳເດືອນ '.$com_month,'','','');
        $alpha = 'A';
        $index = 1;
        foreach ($heads_01 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:D1');
        $sheet->getStyle("A1:D1")->applyFromArray($style);

        // Table Loop
        $total_com_rate = 0;
        $grand_com_rate = $sum_com_rate = $bonus = $sum_bonus = 0;
        $com_rate_txt = "";

        for ($i=0;$i<count($data);$i++) {

            // Row 2
            $heads_02 = array('Commission of '.$com_month,'','',$data[$i][0]['base_kpi']);
            $alpha = 'A';
            $index++;
            foreach ($heads_02 as $key) {
                $sheet->setCellValue($alpha . $index, $key);
                $alpha++;
            }
            
            $sheet->mergeCells('B'.$index.':C'.$index);

            $heads_03 = array('Name : '.$data[$i][0]['staff_name'].' ('.$data[$i][0]['staff_code'].' )','','','');
            $alpha = 'A';
            $index++;
            foreach ($heads_03 as $key) {
                $sheet->setCellValue($alpha . $index, $key);
                $alpha++;
            }

            $sheet->mergeCells('A'.$index.':D'.$index);

            $heads_04 = array('Channel','Target (Unit)','Actual Commission (Unit)','Achieve Rate');
            $alpha = 'A';
            $index++;
            foreach ($heads_04 as $key) {
                $sheet->setCellValue($alpha . $index, $key);
                $alpha++;
            }

            $sheet->getStyle('A'.$index.':D'.$index)->applyFromArray($style);

            $sum_target = $sum_sellout = $achieve = $sum_achieve = 0;

            $QGoodKpi = new Application_Model_GoodKpiLog();

            $index++;
            for ($j=0;$j<count($data[$i]);$j++) { 

                $achieve = ( $data[$i][$j]['sellout'] / $data[$i][$j]['target'] ) * 100;

                $alpha = 'A';
                $sheet->setCellValue($alpha++ . $index, $data[$i][$j]['channel']);
                $sheet->setCellValue($alpha++ . $index, number_format($data[$i][$j]['target']));
                $sheet->setCellValue($alpha++ . $index, number_format($data[$i][$j]['sellout']));
                $sheet->setCellValue($alpha++ . $index, number_format($achieve,2)."%" );

                $sum_target = $sum_target + $data[$i][$j]['target'];
                $sum_sellout = $sum_sellout + $data[$i][$j]['sellout'];

                $index++;
            }

            $sum_achieve = ( $sum_sellout / $sum_target ) * 100;

            if (isset($params['export']) && $params['export'] == 1 && $sum_achieve >= 120) { $sum_achieve = 120; }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, 'Total');
            $sheet->setCellValue($alpha++ . $index, number_format($sum_target));
            $sheet->setCellValue($alpha++ . $index, number_format($sum_sellout));
            $sheet->setCellValue($alpha++ . $index, number_format($sum_achieve,2)."%" );

            $com_rate = ( $data[$i][0]['base_kpi'] * number_format($sum_achieve,2) ) / 100;
            
            if (isset($params['export']) && $params['export'] == 2) {
                $operator_rate = $QGoodKpi->am_operator_rate(number_format($sum_achieve,2));
                $total_com_rate = $com_rate * $operator_rate;
            } else {
                $total_com_rate = $com_rate;
            }

            if ( $total_com_rate == 0 ) { $com_rate = 0; }

            $grand_com_rate = $grand_com_rate + $total_com_rate;
            $bonus = $total_com_rate - $com_rate;

            $index++;

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, 'Commission');
            $sheet->setCellValue($alpha++ . $index, '');
            $sheet->setCellValue($alpha++ . $index, number_format($com_rate,2).' บาท');
            $sheet->setCellValue($alpha++ . $index, '');
            $sheet->mergeCells('A'.$index.':B'.$index);
            $sheet->mergeCells('C'.$index.':D'.$index);
            $sheet->getStyle('A'.$index.':D'.$index)->applyFromArray($style);

            $sum_com_rate = $sum_com_rate + $com_rate;
            $sum_bonus = $sum_bonus + $bonus;

            $index++;
        }

        $deduction = $final_com_rate = 0;
        if ($params['am_id'] == 35022 && $com_month == 'August 2018') { $deduction = 5000; } 

        $final_com_rate = $grand_com_rate - $deduction;

        if ( $final_com_rate == "0.00" ) { $com_rate_txt = "ສູນກີບ"; } 
        else { $com_rate_txt = My_Number::PriceToThai(number_format($final_com_rate,2)); }

        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Commission');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($sum_com_rate,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':B'.$index);
        $sheet->mergeCells('C'.$index.':D'.$index);
        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Bonus (Over 100%)');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($sum_bonus,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':B'.$index);
        $sheet->mergeCells('C'.$index.':D'.$index);
        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Total');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($grand_com_rate,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':B'.$index);
        $sheet->mergeCells('C'.$index.':D'.$index);
        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Deduction');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($deduction,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':B'.$index);
        $sheet->mergeCells('C'.$index.':D'.$index);
        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Total Commission');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($final_com_rate,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':B'.$index);
        $sheet->mergeCells('C'.$index.':D'.$index);
        $index++;

        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Summary '.$com_month.' Commission '.number_format($final_com_rate,2));
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':D'.$index);
        $sheet->getStyle('A'.$index.':D'.$index)->applyFromArray($style);
        $index++;

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, '( '.$com_rate_txt.' )');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->mergeCells('A'.$index.':D'.$index);
        $sheet->getStyle('A'.$index.':D'.$index)->applyFromArray($style);
        $index++;

        $filename = 'Commission AM - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }


    // AM Commission Total
    public static function com_total_am($data, $params) {

        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', 0);
        error_reporting(~E_ALL);

        $com_month = date('F Y', strtotime($params['from']) );

        $report_type = '';
        if ($params['export'] == 1) { $report_type = "Key Account"; }
        if ($params['export'] == 2) { $report_type = "Operator"; }

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        // Row 1
        $heads_01 = array('Commission for '.$report_type.' '.$com_month,'','','');
        $alpha = 'A';
        $index = 1;
        foreach ($heads_01 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        // Row 2
        $heads_02 = array('ID Staff','Full Name','Nick Name','Position','Channel','Base Commission','Commission');
        $alpha = 'A';
        $index = 2;
        foreach ($heads_02 as $key) {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $sheet->mergeCells('A1:G1');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $style2 = array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $sheet->getStyle("A1:G2")->applyFromArray($style);

        $QGoodKpi = new Application_Model_GoodKpiLog();

        $data2 = array();
        foreach ($data as $key => $value) { 

            $params['am_id'] = $key;
            $com_temp = $QGoodKpi->com_am($params);
            //echo "<pre>"; print_r($com_temp);

            foreach ($com_temp as $key2 => $value2) {
                $data2[ $value2['staff_id'] ][ $value2['base_kpi'] ][]  = $value2;
            }

        }

        $sum_kpi = $sum_com_rate = 0; 
        $index = 3;

        foreach ($data2 as $key3 => $value3) {

            $cnt_person = count($value3);
            $cnt = 0;

            foreach ($value3 as $key4 => $value4) {

                $channel = '';
                $achieve = $sum_target = $sum_sellout = $base_kpi = $com_rate = 0;

                foreach ($value4 as $key5 => $value5) {
                    $channel = $channel.$value5['channel'].", ";
                    $sum_target = $sum_target + $value5['target'];
                    $sum_sellout = $sum_sellout + $value5['sellout'];
                    $base_kpi = $value5['base_kpi'];
                }

                $channel = rtrim($channel, ", ");
                $achieve = ( $sum_sellout / $sum_target ) * 100;

                // KA or Operator
                if (isset($params['export']) && $params['export'] == 2) {
                    $operator_rate = $QGoodKpi->am_operator_rate(number_format($achieve,2));
                    $com_rate = (( $base_kpi * number_format($achieve,2) ) / 100) * $operator_rate;

                    $deduction = 0;
                    if ($value4[0]['staff_code'] == '6102149' && $com_month == 'August 2018') { $deduction = 5000; } 

                    $com_rate = $com_rate - $deduction;

                } else {

                    if ($achieve >= 120) { $achieve = 120; }
                    $com_rate = ( $base_kpi * number_format($achieve,2) ) / 100;
                }

                $alpha = 'A';
                $sheet->setCellValue($alpha++ . $index, $value4[0]['staff_code']);
                $sheet->setCellValue($alpha++ . $index, $value4[0]['staff_name']);
                $sheet->setCellValue($alpha++ . $index, '');
                $sheet->setCellValue($alpha++ . $index, $value4[0]['staff_group']);
                $sheet->setCellValue($alpha++ . $index, $channel);
                $sheet->setCellValue($alpha++ . $index, number_format($base_kpi));
                $sheet->setCellValue($alpha++ . $index, number_format($com_rate,2));

                if ($cnt == $cnt_person-1 && $cnt_person > 1) {
                    $sheet->mergeCells('A'.($index-$cnt).':A'.$index);
                    $sheet->mergeCells('B'.($index-$cnt).':B'.$index);
                    $sheet->mergeCells('C'.($index-$cnt).':C'.$index);
                    $sheet->mergeCells('D'.($index-$cnt).':D'.$index);
                    $sheet->getStyle('A'.($index-$cnt).':D'.$index)->applyFromArray($style2);
                }

                $sum_kpi = $sum_kpi + $base_kpi;
                $sum_com_rate = $sum_com_rate + $com_rate;

                $cnt++;
                $index++;
            }   

        }

        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, 'Total');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, '');
        $sheet->setCellValue($alpha++ . $index, number_format($sum_kpi));
        $sheet->setCellValue($alpha++ . $index, number_format($sum_com_rate,2));

        $sheet->mergeCells('A'.$index.':E'.$index);
        $sheet->getStyle('A'.$index.':E'.$index)->applyFromArray($style);

        $filename = 'Commission Total AM - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }

	public function ExportKPIsettingExcel($params) {
             error_reporting(E_ALL);
        date_default_timezone_set('Europe/London');
        require_once 'PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator('Catty System');
        //Setting a worksheet?s page orientation and size

        // Set LANDSCAPE 
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Set Paper Size
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //Page Setup: Scaling options
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        //Page margins 
        /*
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(1);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(1);
*/
        $heads = array(
            'No.',
            'Product',
            'Color',
            'Price',
            'Com Rate',
            'Date From', 
            'Date To'
        );

        $alpha    = 'A';
        $index    = 1;

        foreach($heads as $key) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($alpha)->setWidth(21); 
            $objPHPExcel->getActiveSheet()->getRowDimension($index)->setRowHeight(25);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha.$index, $key);

            $objPHPExcel->getActiveSheet()->getStyle($alpha.$index)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($alpha.$index)->getFill()->getStartColor()->setRGB('9F9F9F');
            
            $alpha++;
        }

        $QGoogKpiLog = new Application_Model_GoodKpiLog();
        $data = $QGoogKpiLog->fetchGoodKpi(null, null, $total, $params);
        //print_r($data); die;

        $index = 2;

        for ($i=0;$i<count($data);$i++) {
            $alpha = 'A';

            $d = explode('-', $data[$i]['from_date']);
            $from = $d[2].'/'.$d[1].'/'.$d[0];
            $d = explode('-', $data[$i]['to_date']);
            $to = $d[2].'/'.$d[1].'/'.$d[0];

            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha.$index, $i+1);

            $objPHPExcel->getActiveSheet()->getColumnDimension($alpha++)->setWidth(21); 
            $objPHPExcel->getActiveSheet()->getRowDimension($index)->setRowHeight(25);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['good_name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $data[$i]['color_name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['price']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($alpha++.$index, $data[$i]['com_rate']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $from);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alpha++.$index, $to);


            $styleArray = array(
              'borders' => array(
                'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_DASHDOT                        
                )
              )
            );

            $objPHPExcel->getActiveSheet()->getStyle("A".$index.":G".$index)->applyFromArray($styleArray);
            unset($styleArray);

            $index++;
        }



        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Catty System');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM

        $filename = 'KPI Setting - List - ' . date('Y-m-d H-i-s');
        // Redirect output to a client?s web browser
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->save('php://output');
        exit;
    }

}