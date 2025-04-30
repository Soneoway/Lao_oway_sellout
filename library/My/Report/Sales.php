<?php
/**
*
*/
class My_Report_Sales
{
    public static function timingDetail($data)
    {
        //print_r($data);die;
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $created_report_by = "[".$userStorage->code."] ".$userStorage->firstname." ".$userStorage->lastname;
        $created_report_at = date("Y-m-d H:i:s");

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Staff Code',
            "Reporter's Title",
            'Reporter (PC)',
            'Staff Status',
            'User Status',
            'Leader (ASM/Leader/Sale)',
            'Leader Title',
            'Area',
            'Province',
            'District',
            /*'Commission Zone',
            'Commission Zone Rate',*/
            'Store Name',
            'Shop ID',
            'Store Type',
            'Market Type',
            'Market Name',
            'Model',
            'Color',
            'Imei',
            'Unit',
            'Score',
            'Price',
            'Commission Rate [2016]',
            'Commission Rate [2017]',
            'ASM Rate [2017]',
            'RM Rate [2017]',
            'Timing Date [Date]',
            'Timing Date [DateTime]',
            'Activated Date',
            'Date Diff',
            'Out Date',
            'Last Scan Date',
            'Scan Diff',
            'Sale Commission [Scan]',
            'Sale Commission [No Scan]',
            'Created Report By '.$created_report_by." AT ".$created_report_at,
            /*'Number of PC',*/
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $index = 2;

        //$db = Zend_Registry::get('db');
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

        // Hero product 2017
        $bkk_list = array(73,74,75,76,77,78,79,80);
        $hero_series = array('X9009','A1601','CPH1607');

        // Hero product 2016
        $f1_series = array('F1f','X9009','A1601');

        // Get StoreMarket List
        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        // Big Team List [Do not Get Commission on May]
        $big_team = array(
            '5901088', '5901090', '5901091', '5901093', '5901094', '5901095', 
            '5901096', '5901098', '5901099', '5901100', '5901102', '5901103', 
            '5901108', '5901110', '5901111', '5901113', '5901115', '5901116', 
            '5901117', '5901118', '5901119', '5901121', '5901169', '5901170', 
            '5901334');

        $n = 1;
        
        for ($i=0;$i<count($data);$i++) {

            // Comission Rate for pc [only for Feb. 2016]
            /*
            $com_zone = "";
            if ($data[$i]['com_zone'] == 1) { 
                $com_zone = "City"; 

                if ($data[$i]['price'] > 8000) { $com_zone_rate = 30; }
                else { $com_zone_rate = 20; }
            } 
            else { 
                $com_zone = "Country";

                if ($data[$i]['price'] > 8000) { $com_zone_rate = 80; }
                else {

                    // check rate for bkk1 - bkk7
                    if(in_array($data[$i]['province'],$bkk_list)) {
                        $com_zone_rate = 60;
                    } else {
                        $com_zone_rate = 50;    
                    }
                    
                }
            }*/

            // Comission Rate for pc [for March 2016 -> now]
            
            // check price of product from kpi setting
            
            $QGoodKpiLog = new Application_Model_GoodKpiLog();
            $where = array();
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto('good_id = ?', $data[$i]['good_id']);
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto('color_id = ?', $data[$i]['color_id']);
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto("CONCAT(from_date, ' 00:00:00') <= ?", $data[$i]['time_add']);
            $where[] = $QGoodKpiLog->getAdapter()->quoteInto("CONCAT(to_date, ' 23:59:59') >= ?", $data[$i]['time_add']);

            $result_price = $QGoodKpiLog->fetchAll($where);

            if(count($result_price) > 0) { 
                $kpi_score = $result_price[0]['kpi'];
                $kpi_price = $result_price[0]['price']; 
            } else { 
                $kpi_score = 0;
                $kpi_price = 0; 
            }
            
            // Check Big Team on May
            if  (   
                $data[$i]['time_add'] >= '2016-05-01 00:00:00' && 
                $data[$i]['time_add'] <= '2016-05-31 23:59:59' &&  
                in_array($data[$i]['staff_code'] , $big_team)
            ) 
            {
                
                $com_rate = 0;

            } else {
/*
                if(in_array($data[$i]['province'],$bkk_list)) {

                    if ($kpi_price > 8000) { $com_rate = 80; }
                    else { $com_rate = 60; }   
                       
                } else {
*/
                    // check if the staff is sale 
                    if ($data[$i]['staff_id'] == $data[$i]['leader_id']) {

                        // check pc in store_staff_log by store, timing_date 
                        $result_ss = array();
                        $where = array();
                        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $data[$i]['store_id']);
                        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = 0');
                        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('FROM_UNIXTIME(joined_at, "%Y-%m-%d 00:00:00") <= ?', $data[$i]['time_add']);
                        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('( FROM_UNIXTIME(released_at, "%Y-%m-%d 23:59:59") >= ? OR released_at IS NULL )', $data[$i]['time_add']);

                        $result_ss = $QStoreStaffLog->fetchAll($where);

                        // chceck if have pc in store
                        if (count($result_ss) > 0) {
                            $com_rate = 20;
                            if (in_array($data[$i]['good_name'], $f1_series)) { $com_rate = 30; }
                            //if (($data[$i]['good_name'] == "F1f") || ($data[$i]['good_name'] == "X9009")) { $com_rate = 30; }
                        } else {
                            $com_rate = 80;
                            if (in_array($data[$i]['good_name'], $f1_series)) { $com_rate = 120; }
                            //if (($data[$i]['good_name'] == "F1f") || ($data[$i]['good_name'] == "X9009")) { $com_rate = 120; }
                        }
                    } else {
                        $com_rate = 20;
                        if (in_array($data[$i]['good_name'], $f1_series)) { $com_rate = 30; }
                        //if (($data[$i]['good_name'] == "F1f") || ($data[$i]['good_name'] == "X9009")) { $com_rate = 30; }
                    }
                //}

                }

                $com_rate_2017 = 0;
                if ( in_array($data[$i]['area_id'], $bkk_list) ) {
                // BKK
                    $com_rate_2017 = 10;
                    if (in_array($data[$i]['good_name'], $hero_series)) { $com_rate_2017 = 40; }
                } else {
                // UPC 
                    $ac_result = $QGoodKpiLog->getComRateBySubArea($data[$i]['sub_district']);

                // Shopping Mall
                    if ($ac_result['com_rate'] == 1) { 
                        $com_rate_2017 = 10;
                        if (in_array($data[$i]['good_name'], $hero_series)) { $com_rate_2017 = 40; }
                    }
                // Stand Alone
                    if ($ac_result['com_rate'] == 2) { 
                        $com_rate_2017 = 20;
                        if (in_array($data[$i]['good_name'], $hero_series)) { $com_rate_2017 = 80; }
                    }
                }

            // ASM Rate
                if ( in_array($data[$i]['area_id'], $bkk_list) ) {
                // BKK
                    $asm_rate_2017 = 10;
                    if (in_array($data[$i]['good_name'], $hero_series)) { $asm_rate_2017 = 30; }

                } else {
                // UPC
                    $asm_rate_2017 = 15;
                    if (in_array($data[$i]['good_name'], $hero_series)) { $asm_rate_2017 = 60; }
                }

            // RM Rate 
                $rm_rate_2017 = 0;
                if ( in_array($data[$i]['area_id'], $bkk_list) ) {
                // BKK
                    $rm_rate_2017 = 6;
                    if (in_array($data[$i]['good_name'], $hero_series)) { $rm_rate_2017 = 20; }
                } else {

                    $rm_rate_2017 = 10;
                    if (in_array($data[$i]['good_name'], $hero_series)) { $rm_rate_2017 = 40; }
                }




                $alpha = 'A';
                $sheet->setCellValue($alpha++ . $index, $n++);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['group_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['reporter']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_status']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['user_status']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['leader']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['group_leader_name']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['area']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['province']);
                $sheet->setCellValue($alpha++ . $index, $data[$i]['district']);
            /*
            $sheet->setCellValue($alpha++ . $index, $com_zone);
            $sheet->setCellValue($alpha++ . $index, $com_zone_rate);*/
            $sheet->setCellValue($alpha++ . $index, $data[$i]['store_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['shop_id']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['org_name']);
            $sheet->setCellValue($alpha++ . $index, $store_market[ $data[$i]['store_id'] ]['market_type']);
            $sheet->setCellValue($alpha++ . $index, $store_market[ $data[$i]['store_id'] ]['market_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['good_color']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['imei']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['unit']);
            $sheet->setCellValue($alpha++ . $index, $kpi_score);
            $sheet->setCellValue($alpha++ . $index, $kpi_price);

            $sheet->setCellValue($alpha++ . $index, $com_rate);
            $sheet->setCellValue($alpha++ . $index, $com_rate_2017);
            $sheet->setCellValue($alpha++ . $index, $asm_rate_2017);
            $sheet->setCellValue($alpha++ . $index, $rm_rate_2017);
            $sheet->setCellValue($alpha++ . $index, strtok($data[$i]['time_add']," ") );
            $sheet->setCellValue($alpha++ . $index, $data[$i]['time_add']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['activated_date']);

/*
        $date11 = "2016-09-04 19:45:36";
        $date1 = strtok($date11, " ");
        //$date1 = date("Y-m-d", strtotime($date11));
        echo $date1;

        $date22 = "2016-09-11 12:56:27";
        $date2 = strtok($date22, " ");
        //$date2 = date("Y-m-d", strtotime($date22));
        echo $date2;
*/

            // clear parameter 
        $scan_diff = "";
        $act_diff = "";
        $date_01 = date_create(strtok($data[$i]['time_add']," "));

            // check diff date of timing date and actvated date
        if ($data[$i]['activated_date']) {
            $date_02 = date_create(strtok($data[$i]['activated_date']," "));
            $act_diff = date_diff($date_01, $date_02)->format("%R%a");
        } else {
            $act_diff = "";
        }
        
        $sheet->setCellValue($alpha++ . $index, $act_diff);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['out_date']);

             //$sheet->setCellValue($alpha++ . $index, $data[$i]['last_scan']);


            // check diff date of timing date and scan date
        if ($data[$i]['last_scan']) {
            $lastscan = $data[$i]['last_scan'];
            $date_03 = date_create(strtok($lastscan," "));
            $scan_diff = date_diff($date_01, $date_03)->format("%R%a");
            
        } else {
            $lastscan = "-";
            
            if (isset($data[$i]['out_date']) && $data[$i]['out_date'] != "") { 
                $outdate = $data[$i]['out_date'];
                $date_04 = date_create(strtok($outdate," ")); 
                $scan_diff = date_diff($date_01, $date_04)->format("%R%a");
            } else {
                $scan_diff = "-9999";
            }
            
        }

        $sheet->setCellValue($alpha++ . $index, $lastscan);
        $sheet->setCellValue($alpha++ . $index, $scan_diff);

        $group_leader_id = $data[$i]['group_leader_id'];
        $com_sale_result = "No";
        $com_sale_result_no_scan = "No";
        $threshold_sale = 3;
        $threshold_asm = 10;

        if ($group_leader_id == SALES_ID) { $threshold_rate = $threshold_sale; }
        if ($group_leader_id == ASM_ID) { $threshold_rate = $threshold_asm; }

        if ($act_diff !== "") {

            if ( (strpos($act_diff, '+') !== FALSE || $act_diff == 0) && $act_diff !== "" ) { $tmp_mark = "+"; }
            if ( strpos($act_diff, '-') !== FALSE && $act_diff !== "" ) { $tmp_mark = "-"; }

            $tmp_act = intval(str_replace($tmp_mark, "", $act_diff));

            if ($group_leader_id == AM_ID) { $threshold_rate = $tmp_act; }

            if ( ($tmp_mark == "+" && $tmp_act <= 7) || ($tmp_mark == "-" && $tmp_act <= 3) ) { 
                $com_sale_result_no_scan = "Yes";
            }

            if ( ($tmp_mark == "+" && $tmp_act <= 7) || ($tmp_mark == "-" && $tmp_act <= $threshold_rate) ) { 

                if ($data[$i]['store_type'] == "ORG" || $data[$i]['org_id'] == 19) { $com_sale_result = "Yes"; } 
                else {

                        // check scan imei & out date 
                    if (strpos($scan_diff, '-') !== FALSE) { 
                            // HAVE -
                        $tmp_scan = intval(str_replace("-", "", $scan_diff));

                        if ($tmp_scan <= $threshold_rate) { $com_sale_result = "Yes"; }
                    } else {
                            // HAVE + and only zero allowed
                        $tmp_scan = intval(str_replace("+", "", $scan_diff));

                        if ($tmp_scan == 0) { if ($tmp_scan <= $threshold_rate) { $com_sale_result = "Yes"; } }
                    }

                }

            }

        }
        

        $sheet->setCellValue($alpha++ . $index, $com_sale_result);
        $sheet->setCellValue($alpha++ . $index, $com_sale_result_no_scan);

            //$sheet->setCellValue($alpha++ . $index, count($result_ss));
            //$sheet->setCellValue($alpha++ . $index, $data[$i]['store_id']);

        $PHPExcel->getActiveSheet()->getStyle('B'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $PHPExcel->getActiveSheet()->getStyle('M'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $PHPExcel->getActiveSheet()->getStyle('O'.$index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

        $index++;
    }

    $filename = 'Sell Out - By PC - ' . date('d-m-Y H:i:s');
    $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');

    $objWriter->save('php://output');

    exit;
}

public function export_by_imei($result) {

    set_time_limit(0);
    error_reporting(0);
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);

    $filename = 'Report_By_Imei_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'NO.',
            'Timing ID.',
            'Company',
            'Reporter Code',
            'Reporter Name',
            'Position',
            // 'Store ID',
            'Store Code',
            'Store Name',
            'Store Status',
            // 'OPPO ID',
            // 'Store ID [ oppo ]',
            // 'Store Name [ oppo ]',
            // 'Grand Area',
            'Area',
            'Province',
            //'District',
            'Sub Area',
            // 'Sub District',
            // 'Geography',
            // 'TH_Province',
            // 'Store Rank',
            // 'Store Type',
            // 'Store Operation',
            // 'Store Level',
            // 'Shop ID',
            // 'Shop Code',
            // 'Market Type',
            // 'Market Name',
            //'Distributor ID',
            //'Distributor Name',
            'Sale Leader Code', 
            'Sale Leader Name',
            // 'SALE ID',
            'Sale Code', 
            'Sale Name',
            //'Group',
            'Tainer Code',
            'Trainer Name',
            // 'Stock Name',
            // 'PC Stand By',
            // 'User Created',
            // 'Joined At',
            //'Off Date',
            // 'Work Days [Months]',
            //'Model Code',
            'Category',
            'Model Name',
            'Color',
            'IMEI',
	    'Model Type',
            'Retail Price',
            'Wholesale Price',
            'Sale_Off_Percent',
            'Distributor ID',
	    'Distributor Code',
            'Distributor Name',
	    'Superior Distributor Name',
            'Warehouse Name',
            'CustomerName',
            'CustomerPhone',
            'Reporter Remark',
            // 'Distributor KA Type',
            //'Unit',
            //'PC Rate',
            // 'PC AEC Rate',
            //'Price',
            // 'Pre-Order',
            // 'VIP Card Number',
            // 'Sim Locked',
            // 'Sim Activated',
            // 'Sale Rate',
            // 'ASM Rate',
            // 'RM Rate',
            'Timing Date [Date]',
            'Timing Date [DateTime]',
            'Activated Date',
            'Pre-Order',
            'DateDiff',
            'PC Commission',
            'Sale Commission',
        );

        fputcsv($output, $heads);

        $QStoreMarket = new Application_Model_StoreMarket();
        $store_market = $QStoreMarket->get_cache();

        $QPackedSim = new Application_Model_PackedSim();
        $packed_sim = $QPackedSim->get_cache();

        $no = 1;
        $date2 = new DateTime();
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);

        foreach ($result as $data) {

            $date1 = new DateTime($data['reporter_created_date']);
            
            // Have Off Date
            if( isset($data['reporter_off_date']) ) { 
                $date3 = new DateTime($data['reporter_off_date']);
                $interval = date_diff($date1, $date3); 
            } else { 
                $interval = date_diff($date1, $date2); 
            }

            $diff_month = $interval->m + ($interval->y * 12);
            $diff_day = $interval->d;

            $work_day = "";
            // if ($diff_month == 0) { $work_day = $diff_day." Days"; }
            // else { $work_day = $diff_month." Months ".$diff_day." Days"; }
            $work_day = $diff_month;

            if ( in_array($data['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data['area_name']; }

            $pc_stand_by = 'No';
            if ($data['reporter_group_id'] == PGPB_ID) { if ( $data['reporter_pc_stand_by'] == 1 ) { $pc_stand_by = 'Yes'; } }

            $sim_locked = 'No';
            $sim_locked_activated = '';
            if ( isset($packed_sim[ $data['imei'] ]) ) { 

                if ($packed_sim[ $data['imei'] ] != '') {

                    switch ($data['model_id']) {
                        case 323: $data['com_rate_pc'] = $data['com_rate_pc'] + 20; break;
                        case 338: $data['com_rate_pc'] = $data['com_rate_pc'] + 20; break;
                        default: break;
                    }

                    $sim_locked_activated = $packed_sim[ $data['imei'] ];
                }
                
                $sim_locked = 'Yes'; 

            }

            if($data['company'] == 1) {
                $commpany_name = "OPPO";
            }else{
                $commpany_name = "Realme";
            }

            $row = array();
            $row[] = $no++;
            $row[] = $data['timing_id'];
            $row[] = $commpany_name;
            $row[] = $data['reporter_code'];
            $row[] = $data['reporter_name'];
            $row[] = $data['reporter_group'];
            // $row[] = $grand_area;

            $row[] = $data['store_code'];
            // $row[] = $data['st_id'];

            // $row[] = $data['st_name'];
	    // if($data['st_id'] == '50200'){
        //        $row[] = '[OppoShop] '.$data['opposhop'];
        //        }else{
            $row[] = $data['st_name'];
            // }
            $row[] = $data['st_del'];
            // $row[] = $data['oppo_id'];
            // $row[] = $data['oppo_shop_id'];
            // $row[] = $data['oppo_shop'];
            
             // $row[] = $data['province_name'];

            if($data['reporter_id'] == 6535){
                $row[] = $data['oppo_pc_area'];
                $row[] = $data['oppo_pc_provience'];
            }else{
                // $row[] = '[sellin] '.$data['province'];
                $row[] = $data['area_name'];
                $row[] = $data['province_name'];
            }

            //$row[] = $data['district_name'];
            $row[] = $data['sub_area_name'];
            // $row[] = $data['sub_district_name'];
            // $row[] = $data['geo_name'];
            // $row[] = $data['th_province_name'];
            // $row[] = $data['st_rank'];
            // $row[] = $data['st_type'];
            // $row[] = $data['st_operation'];
            // $row[] = $data['st_level'];
            // $row[] = $data['st_shop_id'];
            // $row[] = $data['st_shop_code'];
            // $row[] = $store_market[ $data['st_id'] ]['market_type'];
            // $row[] = $store_market[ $data['st_id'] ]['market_name'];
            //$row[] = $data['st_d_id'];
            //$row[] = $data['st_d_name'];
            $row[] = $data['asm_code'];
            $row[] = $data['asm_name'];

            // $row[] = $data['sale_id'];
            $row[] = $data['leader_code'];
            $row[] = $data['leader_name'];
            // $row[] = $data['leader_group'];
            $row[] = $data['pcm_code'];
            $row[] = $data['pcm_name'];
            // $row[] = $data['stock_code'].' '.$data['stock_name'];
            // $row[] = $pc_stand_by;
            // $row[] = $data['reporter_created_date'];
            // $row[] = $data['reporter_joined_at'];
            //$row[] = $data['reporter_off_date'];
            // $row[] = $work_day;

            //$row[] = $data['model'];
            $row[] = $data['cat_name'];
            $row[] = $data['brand_name']." ".$data['model_desc'];
            $row[] = $data['color'];
            $row[] = $data['imei'];

            if($data['model_type'] == 1) {
                $model_type = "Normal";
            }else if($data['model_type'] == 5){
                $model_type = "DEMO";
            }else{
                $model_type = "Staff";
            }

            $row[] = $model_type;

            $row[] = $data['out_price'];
            $row[] = $data['sales_price'];
            if ($data['sale_off_percent'] == '10') {
                $sale_off='APK '.$data['sale_off_percent'].'% (No Commission)';
            }else if($data['sale_off_percent'] > '11'){
                $sale_off='For Staff '.$data['sale_off_percent'].'% (No Commission)';
            }else{
                $sale_off='Normal';
            }

            $row[] = $sale_off;            
            $row[] = $data['imei_d_id'];
	    $row[] = $data['imei_d_code'];
            $row[] = $data['imei_d_name'];
	    $row[] = $data['superior_d_name'];
            $row[] = $data['warehouse_name'];

            $row[] = $data['cus_name'];
            $row[] = $data['cus_phone'];
            $row[] = $data['remark_reporter'];
            // $row[] = $data['imei_d_ka_type'];
            // $row[] = 1;

            // $row[] = $data['com_rate_pc'];
            // $row[] = $data['com_rate_pc_aec'];
            // $row[] = $data['price'];

            // pre-order status
            // $row[] = $data['pre_order'];
            // $row[] = $data['warrant_no'];
            // $row[] = $sim_locked;
            // $row[] = $sim_locked_activated;

            // $row[] = $data['com_rate_sale'];
            // $row[] = $data['com_rate_asm'];
            // $row[] = $data['com_rate_rm'];

            $row[] = strtok($data['timing_date'], " ");
            $row[] = $data['timing_date'];
            $row[] = $data['activated_date'];
            if ($data['imei_cpo'] == $data['imei']) {
                $row[] = 'Yes';
            }else{
                $row[] = 'No';
            }

            // Date Diff
            $act_diff = "";
            $date_01 = date_create(strtok($data['timing_date']," "));

            // check diff date of timing date and actvated date
            if ($data['activated_date']) {
                $date_02 = date_create(strtok($data['activated_date']," "));
                $act_diff = date_diff($date_01, $date_02)->format("%R%a");
            } else {
                $act_diff = "";
            }

            $row[] = $act_diff;

            $com_pc_result = "Yes";
            $com_sale_result = "Yes";

            if ($act_diff !== "") { 

                if ( (strpos($act_diff, '+') !== FALSE || $act_diff == 0) && $act_diff !== "" ) { $tmp_mark = "+"; }
                if ( strpos($act_diff, '-') !== FALSE && $act_diff !== "" ) { $tmp_mark = "-"; }

                $tmp_act = intval(str_replace($tmp_mark, "", $act_diff));

                if ( ($tmp_mark == "+" && $tmp_act >= 61) || ($tmp_mark == "-" && $tmp_act >= 61) ) { // KPI Date diff
                    $com_sale_result = "No";
                }

                if ( ($tmp_mark == "+" && $tmp_act >= 61) || ($tmp_mark == "-" && $tmp_act >= 61) ) { // KPI Date diff
                    $com_pc_result = "No";
                }
            }

            $row[] = $com_pc_result;
            $row[] = $com_sale_result;

            fputcsv($output, $row);
        }

        exit;

    }

    public function export_by_imei_short($result) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Report_By_Imei_Short_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'NO.',
            'Grand Area',
            'Area',
            // 'Province',
            // 'District',
            // 'Sub Area',
            // 'Sub District',
            // 'Geography',
            // 'TH_Province',
            'Store ID',
            'Store Name',
            // 'Store Status',
            // 'Store Rank',
            'Store Type',
            // 'Store Operation',
            // 'Shop ID',
            // 'Shop Code',
            // 'Market Type',
            // 'Market Name',
            // 'Distributor ID (Chain 1)',
            // 'Distributor Name (Chain 1)',
            // 'Leader Code', 
            // 'Leader Name (ASM/Sale)',
            // 'Group',
            'Reporter Code',
            'Reporter Name',
            'Group',
            'PC Stand By',
            // 'User Created',
            // 'Joined At',
            // 'Off Date',
            // 'Work Days [Months]',
            'Model Code',
            'Model Name',
            'Color',
            'IMEI',
            // 'Distributor ID (IMEI)',
            // 'Distributor Name (IMEI)',
            // 'Distributor KA Type',
            'Unit',
            // 'PC Rate',
            // 'PC AEC Rate',
            // 'Price',
            // 'Pre-Order',
            // 'VIP Card Number',
            // 'Sim Locked',
            // 'Sale Rate',
            // 'ASM Rate',
            // 'RM Rate',
            // 'Timing Date [Date]',
            'Timing Date [DateTime]',
            'Activated Date',
            // 'DateDiff',
            // 'PC Commission',
            // 'Sale Commission',
        );

        fputcsv($output, $heads);

        // $QStoreMarket = new Application_Model_StoreMarket();
        // $store_market = $QStoreMarket->get_cache();

        // $QPackedSim = new Application_Model_PackedSim();
        // $packed_sim = $QPackedSim->get_cache();

        $no = 1;
        $date2 = new DateTime();

        // Set Grand Area of BKK
        $grand_e1 = array(81,82,83,110,111,112);
        $grand_e2 = array(85,86,87,115,88,89,116,117);
        $grand_e3 = array(90,91,92,93,113);
        $grand_e4 = array(94,95,96);
        $grand_e5 = array(97,109);
        $grand_w1 = array(98,99,100,101,102,114);
        $grand_w2 = array(103,104,105);
        $grand_w3 = array(106,107,108);

        $grand_area = "";

        foreach ($result as $data) {

            /*
            $date1 = new DateTime($data['reporter_created_date']);
            
            // Have Off Date
            if( isset($data['reporter_off_date']) ) { 
                $date3 = new DateTime($data['reporter_off_date']);
                $interval = date_diff($date1, $date3); 
            } else { 
                $interval = date_diff($date1, $date2); 
            }

            $diff_month = $interval->m + ($interval->y * 12);
            $diff_day = $interval->d;

            $work_day = "";
            // if ($diff_month == 0) { $work_day = $diff_day." Days"; }
            // else { $work_day = $diff_month." Months ".$diff_day." Days"; }
            $work_day = $diff_month;
            */

            if ( in_array($data['area_id'], $grand_e1) ) { $grand_area = 'BKK East-1'; } 
            else if ( in_array($data['area_id'], $grand_e2) ) { $grand_area = 'BKK East-2'; }
            else if ( in_array($data['area_id'], $grand_e3) ) { $grand_area = 'BKK East-3'; }
            else if ( in_array($data['area_id'], $grand_e4) ) { $grand_area = 'BKK East-4'; }
            else if ( in_array($data['area_id'], $grand_e5) ) { $grand_area = 'BKK East-5'; }
            else if ( in_array($data['area_id'], $grand_w1) ) { $grand_area = 'BKK West-1'; }
            else if ( in_array($data['area_id'], $grand_w2) ) { $grand_area = 'BKK West-2'; }
            else if ( in_array($data['area_id'], $grand_w3) ) { $grand_area = 'BKK West-3'; }
            else { $grand_area = $data['area_name']; }

            $pc_stand_by = 'No';
            if ($data['reporter_group_id'] == PGPB_ID) { if ( $data['reporter_pc_stand_by'] == 1 ) { $pc_stand_by = 'Yes'; } }

            /*
            $sim_locked = 'No';
            if ( isset($packed_sim[ $data['imei'] ]) && $packed_sim[ $data['imei'] ] ) { $sim_locked = 'Yes'; }
            */

            $row = array();
            $row[] = $no++;
            $row[] = $grand_area;
            $row[] = $data['area_name'];
            // $row[] = $data['province_name'];
            // $row[] = $data['district_name'];
            // $row[] = $data['sub_area_name'];
            // $row[] = $data['sub_district_name'];
            // $row[] = $data['geo_name'];
            // $row[] = $data['th_province_name'];
            $row[] = $data['st_id'];
            $row[] = $data['st_name'];
            // $row[] = $data['st_del'];
            // $row[] = $data['st_rank'];
            $row[] = $data['st_type'];
            // $row[] = $data['st_operation'];
            // $row[] = $data['st_shop_id'];
            // $row[] = $data['st_shop_code'];
            // $row[] = $store_market[ $data['st_id'] ]['market_type'];
            // $row[] = $store_market[ $data['st_id'] ]['market_name'];
            // $row[] = $data['st_d_id'];
            // $row[] = $data['st_d_name'];

            // $row[] = $data['leader_code'];
            // $row[] = $data['leader_name'];
            // $row[] = $data['leader_group'];

            $row[] = $data['reporter_code'];
            $row[] = $data['reporter_name'];
            $row[] = $data['reporter_group'];
            $row[] = $pc_stand_by;
            // $row[] = $data['reporter_created_date'];
            // $row[] = $data['reporter_joined_at'];
            // $row[] = $data['reporter_off_date'];
            // $row[] = $work_day;

            $row[] = $data['model'];
            $row[] = $data['model_desc'];
            $row[] = $data['color'];
            $row[] = $data['imei'];
            // $row[] = $data['imei_d_id'];
            // $row[] = $data['imei_d_name'];
            // $row[] = $data['imei_d_ka_type'];
            $row[] = 1;

            // $row[] = $data['com_rate_pc'];
            // $row[] = $data['com_rate_pc_aec'];
            // $row[] = $data['price'];

            // pre-order status
            // $row[] = $data['pre_order'];
            // $row[] = $data['warrant_no'];
            // $row[] = $sim_locked;

            // $row[] = $data['com_rate_sale'];
            // $row[] = $data['com_rate_asm'];
            // $row[] = $data['com_rate_rm'];

            // $row[] = strtok($data['timing_date'], " ");
            $row[] = $data['timing_date'];
            $row[] = $data['activated_date'];

/*
            // Date Diff
            $act_diff = "";
            $date_01 = date_create(strtok($data['timing_date']," "));

            // check diff date of timing date and actvated date
            if ($data['activated_date']) {
                $date_02 = date_create(strtok($data['activated_date']," "));
                $act_diff = date_diff($date_01, $date_02)->format("%R%a");
            } else {
                $act_diff = "";
            }

            $row[] = $act_diff;

            $com_pc_result = "No";
            $com_sale_result = "No";

            if ($act_diff !== "") { 

                if ( (strpos($act_diff, '+') !== FALSE || $act_diff == 0) && $act_diff !== "" ) { $tmp_mark = "+"; }
                if ( strpos($act_diff, '-') !== FALSE && $act_diff !== "" ) { $tmp_mark = "-"; }

                $tmp_act = intval(str_replace($tmp_mark, "", $act_diff));

                if ( ($tmp_mark == "+" && $tmp_act <= 7) || ($tmp_mark == "-" && $tmp_act <= 3) ) { 
                    $com_sale_result = "Yes";
                }

                if ( ($tmp_mark == "+" && $tmp_act <= 7) || ($tmp_mark == "-" && $tmp_act <= 0) ) { 
                    $com_pc_result = "Yes";
                }
            }

            $row[] = $com_pc_result;
            $row[] = $com_sale_result;
*/
            fputcsv($output, $row);
        }

        exit;

    }

    public function export_focus_pc($result, $params) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        // Range of Last 1 Month
        $tmp_start_01 = new DateTime( $from );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );

        $tmp_end_01 = new DateTime( $from );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        // Range of Last 2 Month
        $tmp_start_02 = new DateTime( $last_start_01 );
        $tmp_start_02->modify( 'first day of previous month' );
        $last_start_02 = $tmp_start_02->format( 'Y-m-d' );
        
        $tmp_end_02 = new DateTime( $last_end_01 );
        $tmp_end_02->modify( 'last day of previous month' );
        $last_end_02 = $tmp_end_02->format( 'Y-m-d' );

        $QTiming = new Application_Model_Timing();

        $filename = 'Report_Focus_PC_'.date('d/m/Y');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'NO.',
            'Area',
            'Staff Code',
            'Staff Name',
            'Created At',
            'Joined At',
            'Store ID',
            'Store Name',
            'Store Type',
            'Sellout',
            'Last 01',
            'Last 02',
        );

        fputcsv($output, $heads);

        $no = 1;

        foreach ($result as $data) {

            $last_02 = $QTiming->getSelloutByPC($data['staff_id'], $last_start_02, $last_end_02);
            $last_01 = $QTiming->getSelloutByPC($data['staff_id'], $last_start_01, $last_end_01);

            $row = array();
            $row[] = $no++;
            $row[] = $data['area_name'];
            $row[] = $data['staff_code'];
            $row[] = $data['staff_name'];
            $row[] = $data['staff_created'];
            $row[] = $data['staff_joined'];
            $row[] = $data['st_id'];
            $row[] = $data['st_name'];
            $row[] = $data['st_type'];
            $row[] = $data['sellout'];
            $row[] = $last_01['sellout'];
            $row[] = $last_02['sellout'];
            
            fputcsv($output, $row);
        }

        exit;

    }

    // ABM Commission
    public static function export_abm_com($data, $params) {
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
            'Group',
            'Area',
            'Province',
            'Store ID',
            'Store Name',
            'Store Type',
            'Status',
            'Model',
            'Sellout',
            'ASM Set',
            'Com Rate',
            'Actual Com',
        );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
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

        $sheet->getStyle("A1:K1")->applyFromArray($style);

        $index = 2;

        // Get Data 
        $QGoodKpi = new Application_Model_GoodKpiLog();

        $cnt_data = count($data);
        $cnt = 0;

        for ($i=0;$i<$cnt_data;$i++) { 

            $cnt = $i + 1;

            if ($data[$i]['flag'] == 'NO') { $com_rate = $data[$i]['com_rate'] / 2; }
            else { $com_rate = 0; }

            if ( !in_array($data[$i]['staff_group_id'], array(ASM_ID, ASMSTANDBY_ID)) ) { $com_rate = 0; }

            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $cnt);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_group']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['province_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_id']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_type']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['st_status']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['good_name']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['sellout']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['flag']);
            $sheet->setCellValue($alpha++ . $index, $data[$i]['com_rate']);
            $sheet->setCellValue($alpha++ . $index, $com_rate);

            $index++;

            // $sheet->mergeCells("A".$index.":E".$index);
            // $sheet->getStyle("A".$index.":E".$index)->applyFromArray($style);
        }

        $filename = 'Commission ABM - Export - '.date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;

    }


    // public static function timingDetail($data)
    // {

    //     set_time_limit(0);
    //     error_reporting(E_ALL);
    //     ini_set('display_error', 1);
    //     ini_set('memory_limit', -1);

    //     $filename = 'Sell Out - Details - ' . date('d-m-Y H-i-s');

    //     // output headers so that the file is downloaded rather than displayed
    //     header('Content-Type: text/csv; charset=utf-8');
    //     header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    //     echo "\xEF\xBB\xBF"; // UTF-8 BOM

    //     // create a file pointer connected to the output stream
    //     $output = fopen('php://output', 'w');

    //     $heads = array(
    //         'Date',
    //         'Province',
    //         'Store',
    //         'Address',
    //         'Title',
    //         'PG',
    //         'Sale',
    //         'Product',
    //         'Model',
    //         'IMEI',
    //         'Customer name',
    //         'Phone number',
    //         'Email',
    //         'Address',
    //         );

    //     fputcsv($output, $heads);

    //     $QGood = new Application_Model_Good();
    //     $products_cached = $QGood->get_cache();

    //     $QGoodColor = new Application_Model_GoodColor();
    //     $models_cached = $QGoodColor->get_cache();

    //     $QTeam = new Application_Model_Team();
    //     $teams = $QTeam->get_cache();

    //     $data = preg_replace("/^(SELECT)(\s)+(SQL_CALC_FOUND_ROWS)?/", ' ', $data);
    //     $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . $data;

    //     $db = Zend_Registry::get('db');
    //     $result = $db->query($sql);
    //     $total = $db->fetchOne("select FOUND_ROWS()");
    //     $limit = LIMITATION*1000;
    //     $page = 0;

    //     do {
    //         $page++;
    //         $limit_string = sprintf(" LIMIT %d OFFSET %d ", $limit, $limit*($page-1));
    //         $sql = " SELECT " . $data . $limit_string;

    //         $result = $db->query($sql);

    //         foreach($result as $item)
    //         {
    //             $row = array();

    //             $row[] = $item['from'];
    //             $row[] = $item['regional_market'];
    //             $row[] = $item['store_name'];
    //             $row[] = $item['company_address'];
    //             $row[] = isset($teams[ $item['title'] ]) ? $teams[ $item['title'] ] :  '';
    //             $row[] = $item['firstname'] . ' ' . $item['lastname'];
    //             $row[] = '';
    //             $row[] = (isset($products_cached[$item['product_id']]) ? $products_cached[$item['product_id']] :
    //                 '');
    //             $row[] = (isset($models_cached[$item['model_id']]) ? $models_cached[$item['model_id']] :
    //                 '');
    //             $row[] = "=\"" . $item['imei'] . "\"";
    //             $row[] = $item['customer_name'];
    //             $row[] = "=\"" . $item['phone_number'] . "\"";
    //             $row[] = $item['email'];
    //             $row[] = $item['address'];

    //             fputcsv($output, $row);
    //             unset($item);
    //             unset($row);
    //         }

    //         unset($sql);
    //         unset($result);
    //     } while ($page * $limit < $total);

    //     exit;
    // }

    public static function store($from, $to, $result)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'Store ID',
            'Store Name',
            'Store Address',
            'Area',
            'Province',
            'District',
            'Sales Man',
        );

        $from = date_create_from_format("d/m/Y", $from)->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $to)->format("Y-m-d");

        // các model có đổi giá
        $QKpi = new Application_Model_GoodPriceLog();
        $list = $QKpi->get_list($from, $to);

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

        $heads[] = 'Total';

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
                        format('d') . '->' . (new DateTime($range['to']))->format('d') . ' Activated';
                    } else
                    {
                        foreach ($ranges as $range)
                            $heads[] = $value . ' / ' . (new DateTime($range['from']))->format('d') . '->' . (new
                                DateTime($range['to']))->format('d') . ' Activated';
                    }
                }
            }
        }

        $heads = array_merge($heads, array(
            'Total Activated',
            'Value',
            'Value Activated',
        ));

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

        $alpha = $product_col;
        foreach ($products as $key => $value)
            // các model có đổi giá, tách thành nhiều cột
            if (isset($list[$key]))
                foreach ($list[$key] as $_color => $ranges)
                    foreach ($ranges as $range)
                        $sheet->setCellValue($alpha++ . $index, $range['price']);

                    $alpha++;
                    foreach ($products as $key => $value)
            // các model có đổi giá, tách thành nhiều cột
                        if (isset($list[$key]))
                            foreach ($list[$key] as $_color => $ranges)
                                foreach ($ranges as $range)
                                    $sheet->setCellValue($alpha++ . $index, $range['price']);

                                $index++;

                                $all_store = array();

                                foreach ($result as $_key => $_so) {
                                    $sell_out_list[ $_so['store_id'] ][ $_so['good_id'] ][ $_so['color_id'] ][ $_so['from_date'].'_'.$_so['to_date'] ] = array(
                                        'total_quantity'        => $_so['total_quantity'],
                                        'total_activated'       => $_so['total_activated'],
                                        'total_value'           => $_so['total_value'],
                                        'total_value_activated' => $_so['total_value_activated'],
                                    );

                                    if (!isset($all_store[ $_so['store_id'] ]))
                                        $all_store[ $_so['store_id'] ] = array(
                                            'store_id'        => $_so['store_id'],
                                            'store_name'      => $_so['store_name'],
                                            'company_address' => $_so['company_address'],
                                            'store_district'  => $_so['store_district'],
                                        );
                                }

        // dùng mảng này để lưu thứ tự các staff trong result set trên
        // vì duyệt xong thằng kia thì hết set rồi, con trỏ mà
                                $store_array = array();

        // điền danh sách staff ra file trước
                                foreach ($all_store as $key => $store)
                                {
                                    $alpha = 'A';
                                    $sheet->setCellValue($alpha++ . $index, $store['store_id']);
                                    $sheet->setCellValue($alpha++ . $index, $store['store_name']);
                                    $sheet->setCellValue($alpha++ . $index, $store['company_address']);
                                    $sheet->setCellValue($alpha++ . $index, isset($store['store_district']) ? My_Region::getValue($store['store_district'], My_Region::Area) : '#');
                                    $sheet->setCellValue($alpha++ . $index, isset($store['store_district']) ? My_Region::getValue($store['store_district'], My_Region::Province) : '#');
                                    $sheet->setCellValue($alpha++ . $index, isset($store['store_district']) ? My_Region::getValue($store['store_district'], My_Region::District) : '#');
                                    $alpha++;

            $store_array[$store['store_id']] = $index; // lưu dòng ứng với store id
            $index++;
        }

        // duyệt qua dãy model để tính KPI,
        // mỗi lần duyệt tính KPI của nguyên list NV, theo 1 model của vòng lặp hiện tại
        $alpha = $product_col;

        $total_by_store = array();
        $total_activated_by_store = array();
        $value_by_store = array();
        $value_activated_by_store = array();

        unset($index);

        foreach ($products as $_product_id => $value) {
            // các model có đổi giá, tách thành 2 cột
            if (isset($list[$_product_id])) {
                foreach ($list[$_product_id] as $_color => $ranges) {
                    foreach ($ranges as $range) {
                        foreach ($sell_out_list as $_store_id => $_data) {
                            // nếu có trong danh sách NV ở trên
                            if (isset($store_array[$_store_id])) { // đưa vào đúng dòng luôn
                                if (isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ])) {
                                    $tmp = $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ];

                                    $sheet->setCellValue($alpha . $store_array[$_store_id],
                                        isset($tmp['total_quantity'])
                                        ? $tmp['total_quantity']
                                        : 0
                                    );

                                    if (!isset($total_by_store[ $_store_id ])) $total_by_store[ $_store_id ] = 0;
                                    $total_by_store[ $_store_id ] += isset($tmp['total_quantity'])
                                    ? $tmp['total_quantity']
                                    : 0;

                                    if (!isset($total_activated_by_store[ $_store_id ])) $total_activated_by_store[ $_store_id ] = 0;
                                    $total_activated_by_store[ $_store_id ] += isset($tmp['total_activated'])
                                    ? $tmp['total_activated']
                                    : 0;

                                    if (!isset($value_by_store[ $_store_id ])) $value_by_store[ $_store_id ] = 0;
                                    $value_by_store[ $_store_id ] += isset($tmp['total_value'])
                                    ? $tmp['total_value']
                                    : 0;

                                    if (!isset($value_activated_by_store[ $_store_id ])) $value_activated_by_store[ $_store_id ] = 0;
                                    $value_activated_by_store[ $_store_id ] += isset($tmp['total_value_activated'])
                                    ? $tmp['total_value_activated']
                                    : 0;

                                    unset($tmp);
                                } // END inner IF
                            } // END outer IF

                            unset($_data);
                        } // END inner foreach

                        unset($range);
                        $alpha++;
                    } // END middle foreach

                    unset($ranges);
                } // END outer foreach
            } // END big IF

            unset($value);
        } // END big foreach

        foreach ($all_store as $_store_id => $_data)
            // nếu có trong danh sách NV ở trên
            if (isset($total_by_store[$_store_id])) // đưa vào đúng dòng luôn
        $sheet->setCellValue($alpha . $store_array[$_store_id], $total_by_store[$_store_id]);

        $alpha++;
        unset($total_by_store);

        foreach ($products as $_product_id => $value) {
            // các model có đổi giá, tách thành 2 cột
            if (isset($list[$_product_id])) {
                foreach ($list[$_product_id] as $_color => $ranges) {
                    foreach ($ranges as $range) {
                        foreach ($sell_out_list as $_store_id => $_data) {

                            // nếu có trong danh sách NV ở trên
                            if (isset($store_array[$_store_id]) && isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ])) { // đưa vào đúng dòng luôn
                                $tmp = $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ];
                                $sheet->setCellValue($alpha . $store_array[$_store_id],
                                    isset($tmp['total_activated'])
                                    ? $tmp['total_activated']
                                    : 0
                                );

                                unset($tmp);
                            } // END if

                            unset($_data);
                        } // END inner foreach

                        unset($range);
                        $alpha++;
                    } // END middle foreach
                    unset($ranges);
                } // END outer foreach
            } // end big id
        } // end big foreach
        unset($sell_out_list);
        unset($products);
        unset($list);

        foreach ($all_store as $_store_id => $_data)
            // nếu có trong danh sách NV ở trên
            if (isset($total_activated_by_store[$_store_id])) // đưa vào đúng dòng luôn
        $sheet->setCellValue($alpha . $store_array[$_store_id], $total_activated_by_store[$_store_id]);

        $alpha++;
        unset($total_activated_by_store);

        foreach ($all_store as $_store_id => $_data)
            // nếu có trong danh sách NV ở trên
            if (isset($value_by_store[$_store_id])) // đưa vào đúng dòng luôn
        $sheet->setCellValue($alpha . $store_array[$_store_id], $value_by_store[$_store_id]);

        $alpha++;
        unset($value_by_store);

        foreach ($all_store as $_store_id => $_data)
            // nếu có trong danh sách NV ở trên
            if (isset($value_activated_by_store[$_store_id])) // đưa vào đúng dòng luôn
        $sheet->setCellValue($alpha . $store_array[$_store_id], $value_activated_by_store[$_store_id]);

        unset($all_store);
        unset($alpha);
        unset($value_activated_by_store);
        unset($store_array);

        $filename = 'Sell Out - Store - ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    public static function dealer($data)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        error_reporting(~E_ALL);
        ini_set("display_error", 0);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'Dealer ID',
            'Dealer Name',
            'Dealer Address',
            'Area',
            'Province',
            'Number of Stores',
            'Sell Out',
            // 'Sell Out activated',
        );

        $QTiming = new Application_Model_Timing();

        // $QGood = new Application_Model_Good();
        // $products_cached = $QGood->get_cache();

        // foreach ($products_cached as $name)
        //     $heads[] = $name;

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $index = 2;

        $db = Zend_Registry::get('db');
        $n = 1;

        foreach ($data as $item)
        {
            $alpha = 'A';
            $sheet->setCellValue($alpha++ . $index, $n++);
            $sheet->setCellValue($alpha++ . $index, isset($item['id']) ? $item['id'] : '-');
            $sheet->setCellValue($alpha++ . $index, isset($item['distributor_name']) ? $item['distributor_name'] :
                '(Unclassified)');
            $sheet->setCellValue($alpha++ . $index, isset($item['add']) ? $item['add'] :
                '(Unclassified)');
            $sheet->setCellValue($alpha++ . $index, isset($item['area_name']) ? $item['area_name'] :
                '(Unclassified)');
            $sheet->setCellValue($alpha++ . $index, isset($item['region_name']) ? $item['region_name'] :
                '(Unclassified)');
            $sheet->setCellValue($alpha++ . $index, $item['total_store']);
            $sheet->setCellValue($alpha++ . $index, $item['total_quantity']);
            // $sheet->setCellValue($alpha++ . $index, $item['sellout_activated']);
            // $product_by_store = $QTiming->count_product_by_dealer(isset($item['id']) ? $item['id'] : 'null', $params);

            // foreach ($products_cached as $id=>$name){
            //     if ($product_by_store){
            //         $count = 0;
            //         foreach ($product_by_store as $product){
            //             if ($product['product_id']==$id){
            //                 $count = $product['product_count'];
            //                 break;
            //             }
            //         }
            //         $sheet->setCellValue($alpha++.$index, $count);
            //     }else
            //         $sheet->setCellValue($alpha++.$index, 0);

            // }

            $index++;
        }

        $filename = 'Sell Out - Dealer - ' . date('d-m-Y H:i:s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    public static function area($data, $total_sales, $point_list, $params)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        ini_set('display_error', ~E_ALL);

        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array('Area', );

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        // các model có đổi giá
        $QKpi = new Application_Model_GoodPriceLog();
        $list = $QKpi->get_list($from, $to);

        $QGood = new Application_Model_Good();
        $products = $QGood->get_cache();

        $QGoodColor = new Application_Model_GoodColor();
        $product_colors = $QGoodColor->get_cache();

        $product_col = chr(ord('A') + count($heads));
        $product_color_list = array();

        $kpi_list = array();

        foreach ($data as $_key => $_value) {
            $kpi_list[ $_value['area_id'] ][ $_value['good_id'] ][ $_value['color_id'] ][ $_value['from_date'].'_'.$_value['to_date'] ] = array(
                'total_quantity'        => $_value['total_quantity'],
                'total_activated'       => $_value['total_activated'],
                'total_value'           => $_value['total_value'],
                'total_value_activated' => $_value['total_value_activated'],
            );
        }

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

                        foreach ($ranges as $range) {
                            $heads[] = $value . ' / ' . $product_colors[$_color] . ' / ' . (new DateTime($range['from']))->
                            format('d') . '->' . (new DateTime($range['to']))->format('d');
                        }
                    } else
                    {
                        foreach ($ranges as $range) {
                            $heads[] = $value . ' / ' . (new DateTime($range['from']))->format('d') . '->' . (new
                                DateTime($range['to']))->format('d');
                        }
                    }
                }
            }
        }

        $heads[] = 'Unit';

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

                        foreach ($ranges as $range) {
                            $heads[] = $value . ' / ' . $product_colors[$_color] . ' / ' . (new DateTime($range['from']))->
                            format('d') . '->' . (new DateTime($range['to']))->format('d')  . ' activated';
                        }
                    } else
                    {
                        foreach ($ranges as $range) {
                            $heads[] = $value . ' / ' . (new DateTime($range['from']))->format('d') . '->' . (new
                                DateTime($range['to']))->format('d')  . ' activated';
                        }
                    }
                }
            }
        }

        $heads[] = 'Unit activated';
        $heads[] = 'Value (80%)';
        $heads[] = 'Value activated (80%)';
        $heads[] = 'Region Share (%)';
        $heads[] = 'Point';

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

        // dùng mảng này để lưu thứ tự các area trong result set trên
        // vì duyệt xong thằng kia thì hết set rồi, con trỏ mà
        $area_array = array();

        // điền danh sách area ra file trước
        $alpha = 'A';
        foreach ($area_cache as $_area_id => $_area_name)
        {
            $sheet->setCellValue($alpha . $index, $_area_name);
            $area_array[$_area_id] = $index++; // lưu dòng ứng với area id
        }

        // duyệt qua dãy model để tính KPI,
        // mỗi lần duyệt tính KPI của nguyên list NV, theo 1 model của vòng lặp hiện tại
        $alpha = $product_col;

        $QAsm = new Application_Model_Asm();
        $asm_cache = $QAsm->get_cache();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $total_by_area = array();
        $total_activated_by_area = array();
        $total_value_by_area = array();

        foreach ($products as $_product_id => $value)
        {
            // các model có đổi giá, tách thành 2 cột
            if (isset($list[$_product_id]))
            {
                foreach ($list[$_product_id] as $_color => $ranges)
                {
                    foreach ($ranges as $range)
                    {
                        foreach ($kpi_list as $_area_id => $_data)
                        {
                            // nếu có trong danh sách NV ở trên
                            if (!isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]))  continue;

                            $tmp = $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ];

                            if (isset($area_array[$_area_id]) && isset($asm_cache[$userStorage->id]['area'])
                                && in_array($_area_id, $asm_cache[$userStorage->id]['area'])) {
                                // đưa vào đúng dòng luôn
                                if (!isset($total_by_area[ $_area_id ])) $total_by_area[ $_area_id ] = 0;
                            if (!isset($total_value_by_area[ $_area_id ])) $total_value_by_area[ $_area_id ] = 0;
                            if (!isset($total_value_activated_by_area[ $_area_id ])) $total_value_activated_by_area[ $_area_id ] = 0;

                            $total_by_area[ $_area_id ] += isset($tmp['total_quantity'])
                            ? $tmp['total_quantity'] : 0;

                            $total_value_by_area[ $_area_id ] += isset($tmp['total_value'])
                            ? $tmp['total_value'] : 0;

                            $total_value_activated_by_area[ $_area_id ] += isset($tmp['total_value_activated'])
                            ? $tmp['total_value_activated'] : 0;

                            $sheet->setCellValue($alpha . $area_array[$_area_id],
                                isset($tmp['total_quantity'])
                                ? $tmp['total_quantity'] : 0);
                        }
                        elseif (!in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) ||
                            My_Staff_Permission_Area::view_all($userStorage->id)) {
                            if (!isset($total_by_area[ $_area_id ])) $total_by_area[ $_area_id ] = 0;
                        if (!isset($total_value_by_area[ $_area_id ])) $total_value_by_area[ $_area_id ] = 0;
                        if (!isset($total_value_activated_by_area[ $_area_id ])) $total_value_activated_by_area[ $_area_id ] = 0;

                        $total_by_area[ $_area_id ] += isset($tmp['total_quantity'])
                        ? $tmp['total_quantity'] : 0;

                        $total_value_by_area[ $_area_id ] += isset($tmp['total_value'])
                        ? $tmp['total_value'] : 0;

                        $total_value_activated_by_area[ $_area_id ] += isset($tmp['total_value_activated'])
                        ? $tmp['total_value_activated'] : 0;

                        $sheet->setCellValue($alpha . $area_array[$_area_id],
                            isset($tmp['total_quantity'])
                            ? $tmp['total_quantity'] : 0);
                    }
                    else
                        $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
                }

                $alpha++;
            }
        }
    }
}

foreach ($area_cache as $_area_id => $_area_name)
{
    if (isset($total_by_area[ $_area_id ]))
        $sheet->setCellValue($alpha . $area_array[$_area_id], $total_by_area[ $_area_id ]);
    else
        $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
}

$alpha++;

foreach ($products as $_product_id => $value)
{
            // các model có đổi giá, tách thành 2 cột
    if (isset($list[$_product_id]))
    {
        foreach ($list[$_product_id] as $_color => $ranges)
        {
            foreach ($ranges as $range)
            {
                foreach ($kpi_list as $_area_id => $_data)
                {
                    if (!isset($_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ]))  continue;

                    $tmp = $_data[ $_product_id ][ $_color ][ $range['from'].'_'.$range['to'] ];

                            // nếu có trong danh sách NV ở trên
                    if (isset($area_array[$_area_id]) && isset($asm_cache[$userStorage->id]['area']) &&
                        in_array($_area_id, $asm_cache[$userStorage->id]['area'])) {
                                // đưa vào đúng dòng luôn
                        if (!isset($total_activated_by_area[ $_area_id ])) $total_activated_by_area[ $_area_id ] = 0;
                    $total_activated_by_area[ $_area_id ] += isset($tmp['total_activated'])
                    ? $tmp['total_activated'] : 0;

                    $sheet->setCellValue($alpha . $area_array[$_area_id],
                        isset($tmp['total_activated'])
                        ? $tmp['total_activated'] : 0);
                }
                elseif (!in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) ||
                    My_Staff_Permission_Area::view_all($userStorage->id)) {
                    if (!isset($total_activated_by_area[ $_area_id ])) $total_activated_by_area[ $_area_id ] = 0;
                $total_activated_by_area[ $_area_id ] += isset($tmp['total_activated'])
                ? $tmp['total_activated'] : 0;

                $sheet->setCellValue($alpha . $area_array[$_area_id],
                    isset($tmp['total_activated'])
                    ? $tmp['total_activated'] : 0);
            }
            else
                $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
        }

        $alpha++;
    }
}
}
}

foreach ($area_cache as $_area_id => $_area_name)
{
    if (isset($total_activated_by_area[ $_area_id ]))
        $sheet->setCellValue($alpha . $area_array[$_area_id], $total_activated_by_area[ $_area_id ]);
    else
        $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
}

$alpha++;

foreach ($area_cache as $_area_id => $_area_name)
{
    if (isset($total_value_by_area[ $_area_id ]))
        $sheet->setCellValue($alpha . $area_array[$_area_id], $total_value_by_area[ $_area_id ]);
    else
        $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
}

$alpha++;

foreach ($area_cache as $_area_id => $_area_name)
{
    if (isset($total_value_activated_by_area[ $_area_id ]))
        $sheet->setCellValue($alpha . $area_array[$_area_id], $total_value_activated_by_area[ $_area_id ]);
    else
        $sheet->setCellValue($alpha . $area_array[$_area_id], '-');
}

$alpha++;

$area_list = $QArea->fetchAll();

foreach ($area_list as $_key => $_area)
    if (isset($area_array[$_area['id']]))
        $sheet->setCellValue($alpha . $area_array[$_area['id']], $_area['region_share']);

    $alpha++;

    foreach ($point_list as $_area_id => $_point)
        if (isset($area_array[$_area_id]))
            $sheet->setCellValue($alpha . $area_array[$_area_id], $_point);

        $filename = 'Sell out - Area - ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');

        exit;
    }

    public static function storeList($stores)
    {
        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);
        $filename = 'Store List - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $head = array(
            'No.',
            'Store ID',
            'store Code',
            'Store Name',
            'Store Rank',
            'Status',
            //'Shop ID',
            //'Shop Code',
            'Store Created',
            //'Store Type',
            // 'Store Operation',
            // 'Store Level',
            // 'Market Type',
            // 'Market Name ID',
            'Market Name',
            //'IT Junction',
            'RGM',
            'RM',
            //'District',
            // 'Sub District',
            'Sale',
            // 'Company Address',
            // 'Shippping Address',
            // 'ID Oppo',
            // 'Distributor ID',
            'Distributor Code',
            'Distributor Name',
            'Superior Distributor',
            'Warehouse Name',
            'Leader',
            'Phone Number',
            'Shipping Address',
            //'Sale Name',
            'ASM Code',
            'ASM Name',
            'Sale Code',
            'Sale Name',
            'Sale MKT Code',
            'Sale MKT Name',
            'PCM Code',
            'PCM Name',
            'PCDB Code',
            'PCDB Name',
            'Number of PC',
            'PC Code',
            'PC Name',
            // 'Number of PC Stand By',
            // 'PC Stand By Name',
            // 'PC Manager / Trainer',
            // 'Agency Name',
            //'Phone Number'
        );

        fputcsv($output, $head);

        $QDealer = new Application_Model_Distributor();
        $all_dealer = $QDealer->get_cache();

        $QStoreMarket = new Application_Model_StoreMarket();
        $StoreMarket = $QStoreMarket->get_cache();

        $QStaff = new Application_Model_Staff();
        $staff = $QStaff->get_cache();

        $QStoreStaff = new Application_Model_StoreStaff();
        $Qscm = new Application_Model_StoreControlMap();
        ////////////////////////////////////////////////////
        /////////////////// Xu?t
        ////////////////////////////////////////////////////
        $no = 1;

        foreach ($stores as $store) {

            if ($store['it_junction'] == 1) { $it_junction = "Yes"; } else { $it_junction = "No"; }
            if ($store['store_status'] == 1) { $store_status = "In Cooperation"; } elseif ($stores['store_status'] == 2) { $store_status = "Suspend Cooperation"; } elseif ($stores['store_status'] == 3) { $store_status = "Close"; } else { $store_status = ""; }


            $QDistributor = new Application_Model_Distributor();
            $superi = $QDistributor->getSuperiorDistributor($store['w_id']);

            $row = array();
            $row[] = $no++;
            $row[] = $store['id'];
            $row[] = $store['store_code'];
            $row[] = $store['name'];
            $row[] = $store['rank'];
            $row[] = $store_status;

            // $row[] = $store['store_id'];
            // $row[] = $store['store_code'];

            $row[] = $store['created_at'];

            // $row[] = $store['store_type'];
            // $row[] = $store['store_operation'];
            // $row[] = $store['store_grade'];
            // $row[] = $StoreMarket[ $store['id'] ]['market_type'];
            // $row[] = $StoreMarket[ $store['id'] ]['market_name_id'];

            $row[] = $StoreMarket[ $store['id'] ]['market_name'];

            //$row[] = $it_junction;

            $row[] = $store['area_name'];
            $row[] = $store['regional_market_name'];

            //$row[] = $store['district_name'];
            // $row[] = $store['sd_name'];

            $row[] = $store['sa_name'];

            //$row[] = $store['company_address'];
            //$row[] = $store['shipping_address'];
            // $row[] = $store['oppo_id'];

            //$row[] = !is_null($store['d_id']) && $store['d_id'] != 0 ? $store['d_id'] : '';
            $row[] = $store['distributor_code'];
            $row[] = isset($all_dealer[$store['d_id']]['title']) ? $all_dealer[$store['d_id']]['title'] : '';
            $row[] = $superi[0]['title'];
            $row[] = $superi[0]['name'];


            $row[] = $store['owner'];
            $row[] = $store['phone_number'];
            $row[] = $store['shipping_address'];
            $row[] = $store['asm_code'];
            $row[] = $store['asm_name'];
            $row[] = $store['sale_code'];
            $row[] = $store['sale_name'];
            $row[] = $store['sale_mkt_code'];
            $row[] = $store['sale_mkt_name'];
            $row[] = $store['pcm_code'];
            $row[] = $store['pcm_name'];
            $row[] = $store['pcdb_code'];
            $row[] = $store['pcdb_name'];

            $staff_list = $QStoreStaff->getPC($store['id'], 0);
            $cnt = count($staff_list);
            $pc_code = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pc_code = $pc_code.$staff_list[$j]['pc_code']." / "; }
                    else { $pc_code = $pc_code.$staff_list[$j]['pc_code']; }
                }
            } else { $pc_code = "-"; }

            $row[] = $cnt;
            $row[] = $pc_code;

            // Get PC 
            $staff_list = $QStoreStaff->getPC($store['id'], 0);
            $cnt = count($staff_list);
            $pc_name = "";

            if ($cnt > 0 ) {
                for ($j=0;$j<$cnt;$j++) {
                    if ($j != $cnt-1) { $pc_name = $pc_name.$staff_list[$j]['pc_name']." / "; }
                    else { $pc_name = $pc_name.$staff_list[$j]['pc_name']; }
                }
            } else { $pc_name = "-"; }

            $row[] = $pc_name;

            // Get PC Stand By
            // $staff_list2 = $QStoreStaff->getPC($store['id'], 0);
            // $cnt2 = count($staff_list2);
            // $pc_name2 = "";

            // if ($cnt2 > 0 ) {
            //     for ($j=0;$j<$cnt2;$j++) {
            //         if ($j != $cnt2-1) { $pc_name2 = $pc_name2.$staff_list2[$j]['pc_name']." / "; }
            //         else { $pc_name2 = $pc_name2.$staff_list2[$j]['pc_name']; }
            //     }
            // } else { $pc_name2 = "-"; }

            // $row[] = $cnt2;
            // $row[] = $pc_name2;

            // // Get PCM 
            // $pcm_info = $Qscm->getPCM($store['id']);

            // $row[] = $pcm_info['staff_name'];
            // //$row[] = $store['trainer_name'];
            // $row[] = $store['agency_name'];
            //$row[] = $all_dealer['tel'];

            fputcsv($output, $row);
        }
        exit;
    }

    public static function storePcList($stores) {

        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        ini_set('memory_limit', -1);

        $filename = 'Store PC List - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $head = array(
            'No.',
            'Store ID',
            'Store Name',
            'Store Type',
            'Store Level',
            'Market Name',
            'Area',
            'Staff Code',
            'Staff Name',
            'Group',
            'PC Stand By',
            'Joined At',
            'Work Month',
            'Work Day',
        );

        fputcsv($output, $head);

        $QStoreMarket = new Application_Model_StoreMarket();
        $StoreMarket = $QStoreMarket->get_cache();

        $no = 1;

        foreach ($stores as $store) {

            $row = array();
            $row[] = $no++;
            $row[] = $store['st_id'];
            $row[] = $store['st_name'];
            $row[] = $store['st_type'];
            $row[] = $store['st_level'];
            $row[] = $StoreMarket[ $store['st_id'] ]['market_name'];
            $row[] = $store['area_name'];
            $row[] = $store['staff_code'];
            $row[] = $store['staff_name'];
            $row[] = $store['group_name'];
            $row[] = $store['pc_stand_by'];
            $row[] = $store['joined_at'];
            $row[] = $store['work_month'];
            $row[] = $store['work_day'];

            fputcsv($output, $row);
        }

        exit;
    }


// Export Store Visit
public static function storeVisit($stores)
{
    set_time_limit(0);
    error_reporting(0);
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);
    $filename = 'Store Visit List - '.date('d-m-Y H-i-s');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename.'.csv');
    echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
    $output = fopen('php://output', 'w');

    $head = array(
        'No.',
        'Store ID',
        'RGM',
        'RM',
        'Sales Area',
        'store Code',
        'Store Name',
        'Status',
        'Store Created',
        'Distributor Code',
        'Distributor Name',
        'Superior Distributor',
        'Warehouse Name',
        'Leader',
        'Phone Number',
        'Shipping Address',
        'Sale Code',
        'Location',
        'Latitude',
        'Longtitude',
        'Last_Month_Visit',
        'This__Month_Visit',
        'Total Visit',
    );

    fputcsv($output, $head);

    $QDealer = new Application_Model_Distributor();
    $all_dealer = $QDealer->get_cache();

    $QStoreMarket = new Application_Model_StoreMarket();
    $StoreMarket = $QStoreMarket->get_cache();

    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->get_cache();

    $QStoreStaff = new Application_Model_StoreStaff();
    $Qscm = new Application_Model_StoreControlMap();

    $no = 1;

    foreach ($stores as $store) {

        if ($store['lat'] == '') { $set_location= "No"; } else { $set_location = "Yes"; }
        if ($store['store_status'] == 1) { $store_status = "In Cooperation"; } elseif ($stores['store_status'] == 2) { $store_status = "Suspend Cooperation"; } elseif ($stores['store_status'] == 3) { $store_status = "Close"; } else { $store_status = ""; }

        $QDistributor = new Application_Model_Distributor();
        $superi = $QDistributor->getSuperiorDistributor($store['w_id']);

        $row = array();
        $row[] = $no++;
        $row[] = $store['id'];
        $row[] = $store['area_name'];
        $row[] = $store['regional_market_name'];
        $row[] = $store['sa_name'];
        $row[] = $store['store_code'];
        $row[] = $store['name'];
        $row[] = $store_status;
        $row[] = $store['created_at'];
        $row[] = $store['distributor_code'];
        $row[] = isset($all_dealer[$store['d_id']]['title']) ? $all_dealer[$store['d_id']]['title'] : '';
        $row[] = $superi[0]['title'];
        $row[] = $superi[0]['name'];

        $row[] = $store['owner'];
        $row[] = $store['phone_number'];
        $row[] = $store['shipping_address'];
        $row[] = $store['sale_code'];
        $row[] = $set_location;
        $row[] = $store['latitude'];
        $row[] = $store['longtitude'];
        $row[] = $store['last_total_visit'];
        $row[] = $store['this_total_visit'];
        $row[] = $store['total_visit'];
        fputcsv($output, $row);
    }
    exit;
}


// Export Market Research
public static function marketResearch($stores)
{
    set_time_limit(0);
    error_reporting(0);
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);
    $filename = 'Export Market Research - '.date('d-m-Y H-i-s');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename.'.csv');
    echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
    $output = fopen('php://output', 'w');

    $head = array(
        'No.',
        'RGM',
        'RM',
        'store Code',
        'Store Name',
        'Status',
        'Created By',

        'Status',
        'Store Type',
        'Brand_OPPO',
        'Brand_Vivo',
        'Brand_Samsung',
        // 'Brand_Huawei',
        'Brand_Realme',
        'Brand_Infinix',
        'Brand_Honor',
        'Brand_Tecno',

        'PC_OPPO',
        'PC_VIVO',
        'PC_Samsung',
        'PC_Huawei',
        'PC_Realme',
        'PC_Xiaomi',
        'PC_Honor',
        'PC_TECNO',

        'Sales OPPO ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales OPPO 2-3 ລ້ານ',
        'Sales OPPO 3-4 ລ້ານ',
        'Sales OPPO 4-5 ລ້ານ',
        'Sales OPPO 5 ລ້ານຂື້ນໄປ',
        'Sales VIVO ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales VIVO 2-3 ລ້ານ',
        'Sales VIVO 3-4 ລ້ານ',
        'Sales VIVO 4-5 ລ້ານ',
        'Sales VIVO 5 ລ້ານຂື້ນໄປ',
        'Sales Samsung ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales Samsung 2-3 ລ້ານ',
        'Sales Samsung 3-4 ລ້ານ',
        'Sales Samsung 4-5 ລ້ານ',
        'Sales Samsung 5 ລ້ານຂື້ນໄປ',
        'Sales Realme ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales Realme 2-3 ລ້ານ',
        'Sales Realme 3-4 ລ້ານ',
        'Sales Realme 4-5 ລ້ານ',
        'Sales Realme 5 ລ້ານຂື້ນໄປ',
        'Sales iNfinix ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales iNfinix 2-3 ລ້ານ',
        'Sales iNfinix 3-4 ລ້ານ',
        'Sales iNfinix 4-5 ລ້ານ',
        'Sales iNfinix 5 ລ້ານຂື້ນໄປ',
        'Sales Honor ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales Honor 2-3 ລ້ານ',
        'Sales Honor 3-4 ລ້ານ',
        'Sales Honor 4-5 ລ້ານ',
        'Sales Honor 5 ລ້ານຂື້ນໄປ',
        'Sales Tecno ຕ່ຳກ່ວາ 2 ລ້ານ',
        'Sales Tecno 2-3 ລ້ານ',
        'Sales Tecno 3-4 ລ້ານ',
        'Sales Tecno 4-5 ລ້ານ',
        'Sales Tecno 5 ລ້ານຂື້ນໄປ',
        'Sales Huawei',
        'Sales Xiaomi',
        'Sales iPhone',
        'Sales Other ອື່ນໆ',

        'OPPO Table ໂຕະໂຊ',
        'OPPO Counters ຕູ້ແກ້ວ',
        'OPPO Shop sign ປ້າຍໜ້າຮ້ານ',
        'Vivo Table ໂຕະໂຊ',
        'Vivo Counters ຕູ້ແກ້ວ',
        'Vivo Shop sign ປ້າຍໜ້າຮ້ານ',
        'Samsung Table ໂຕະໂຊ',
        'Samsung Counters ຕູ້ແກ້ວ',
        'Samsung Shop sign ປ້າຍໜ້າຮ້ານ',
        'Huawei Table ໂຕະໂຊ',
        'Huawei Counters ຕູ້ແກ້ວ',
        'Huawei Shop sign ປ້າຍໜ້າຮ້ານ',
        'Realme Table ໂຕະໂຊ',
        'Realme Counters ຕູ້ແກ້ວ',
        'Realme Shop sign ປ້າຍໜ້າຮ້ານ',
        'Xiaomi Table ໂຕະໂຊ',
        'Xiaomi Counters ຕູ້ແກ້ວ',
        'Xiaomi Shop sign ປ້າຍໜ້າຮ້ານ',
        'Honor Table ໂຕະໂຊ',
        'Honor Counters ຕູ້ແກ້ວ',
        'Honor Shop sign ປ້າຍໜ້າຮ້ານ',
        'Tecno Table ໂຕະໂຊ',
        'Tecno Counters ຕູ້ແກ້ວ',
        'Tecno Shop sign ປ້າຍໜ້າຮ້ານ',
        
        'Checked',
        'Total Visit',
    );

    fputcsv($output, $head);

    $QDealer = new Application_Model_Distributor();
    $all_dealer = $QDealer->get_cache();

    $QStoreMarket = new Application_Model_StoreMarket();
    $StoreMarket = $QStoreMarket->get_cache();

    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->get_cache();

    $QStoreStaff = new Application_Model_StoreStaff();
    $Qscm = new Application_Model_StoreControlMap();

    $no = 1;

    foreach ($stores as $store) {

        if ($store['mkr_check'] == '') { $checked= "No"; } else { $checked = "Yes"; }
        if ($store['store_status'] == 1) { $store_status = "In Cooperation"; } elseif ($stores['store_status'] == 2) { $store_status = "Suspend Cooperation"; } elseif ($stores['store_status'] == 3) { $store_status = "Close"; } else { $store_status = ""; }

        $QDistributor = new Application_Model_Distributor();
        $superi = $QDistributor->getSuperiorDistributor($store['w_id']);

        $row = array();
        $row[] = $no++;
        $row[] = $store['area_name'];
        $row[] = $store['regional_market_name'];
        $row[] = $store['store_code'];
        $row[] = $store['name'];
        $row[] = $store_status;
        $row[] = $store['created_by_name'];
        $row[] = $store['st_status']; 
        $row[] = $store['st_type']; 

        $row[] = $store['b_oppo']; 
        $row[] = $store['b_vivo']; 
        $row[] = $store['b_samsung']; 
        // $row[] = $store['b_huawei']; 
        $row[] = $store['b_realme']; 
        $row[] = $store['b_infinix']; 
        $row[] = $store['b_honor']; 
        $row[] = $store['b_tecno'];

        $row[] = $store['pc_oppo']; 
        $row[] = $store['pc_vivo']; 
        $row[] = $store['pc_samsung']; 
        $row[] = $store['pc_huawei']; 
        $row[] = $store['pc_realme']; 
        $row[] = $store['pc_xiaomi']; 
        $row[] = $store['pc_honor']; 

        $row[] = $store['pc_tecno']; 
        $row[] = $store['sp1_oppo']; 
        $row[] = $store['sp2_oppo']; 
        $row[] = $store['sp3_oppo']; 
        $row[] = $store['sp4_oppo']; 
        $row[] = $store['sp5_oppo'];
        
        $row[] = $store['sp1_vivo']; 
        $row[] = $store['sp2_vivo']; 
        $row[] = $store['sp3_vivo']; 
        $row[] = $store['sp4_vivo']; 
        $row[] = $store['sp5_vivo']; 

        $row[] = $store['sp1_samsung']; 
        $row[] = $store['sp2_samsung']; 
        $row[] = $store['sp3_samsung']; 
        $row[] = $store['sp4_samsung']; 
        $row[] = $store['sp5_samsung']; 

        $row[] = $store['sp1_realme']; 
        $row[] = $store['sp2_realme']; 
        $row[] = $store['sp3_realme']; 
        $row[] = $store['sp4_realme']; 
        $row[] = $store['sp5_realme']; 

        $row[] = $store['sp1_infinix']; 
        $row[] = $store['sp2_infinix']; 
        $row[] = $store['sp3_infinix']; 
        $row[] = $store['sp4_infinix']; 
        $row[] = $store['sp5_infinix']; 

        $row[] = $store['sp1_honor']; 
        $row[] = $store['sp2_honor']; 
        $row[] = $store['sp3_honor']; 
        $row[] = $store['sp4_honor']; 
        $row[] = $store['sp5_honor'];

        $row[] = $store['sp1_tecno']; 
        $row[] = $store['sp2_tecno']; 
        $row[] = $store['sp3_tecno']; 
        $row[] = $store['sp4_tecno']; 
        $row[] = $store['sp5_tecno'];

        $row[] = $store['sp_huawei']; 
        $row[] = $store['sp_xiaomi']; 
        $row[] = $store['sp_iphone']; 
        $row[] = $store['sp_others']; 

        $row[] = $store['ss_oppo']; 
        $row[] = $store['ct_oppo']; 
        $row[] = $store['tb_oppo'];

        $row[] = $store['ct_vivo']; 
        $row[] = $store['ss_vivo']; 
        $row[] = $store['tb_vivo']; 

        $row[] = $store['ct_samsung']; 
        $row[] = $store['ss_samsung']; 
        $row[] = $store['tb_samsung'];

        $row[] = $store['ct_huawei']; 
        $row[] = $store['ss_huawei']; 
        $row[] = $store['tb_huawei']; 

        $row[] = $store['ct_realme']; 
        $row[] = $store['ss_realme']; 
        $row[] = $store['tb_realme']; 

        $row[] = $store['ct_xiaomi']; 
        $row[] = $store['ss_xiaomi']; 
        $row[] = $store['tb_xiaomi']; 

        $row[] = $store['ct_honor']; 
        $row[] = $store['ss_honor']; 
        $row[] = $store['tb_honor']; 

        $row[] = $store['tb_tecno']; 
        $row[] = $store['ct_tecno']; 
        $row[] = $store['ss_tecno']; 

        $row[] = $checked;
        $row[] = $store['total_research'];
        fputcsv($output, $row);
    }
    exit;
}


}