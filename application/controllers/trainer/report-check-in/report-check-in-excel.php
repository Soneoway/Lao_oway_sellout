<?php
set_time_limit(0);
ini_set('memory_limit', -1);
ini_set('display_error', 0);
error_reporting(~E_ALL);

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

require_once 'PHPExcel.php';
$PHPExcel = new PHPExcel();

$staff_id = $this->getRequest()->getParam('id');
$month_year = $this->getRequest()->getParam('start_date');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QPcCheckInLog = new Application_Model_PcCheckInLog();
$pc_check_in_detail = $QPcCheckInLog->getDetails($staff_id);
$check_group=$QPcCheckInLog->getRemarkGroup($staff_id);
// print_r($check_group);


$heads = array(
    'DAY',
    'DAY OF WEEK',
    'AREA',
    '[ID] STORE',
    'CHECK IN',
    'CHECK OUT',
    'STATUS',
    'APPROVE DATE',
    'REMARK',
);

$status_ap = array(
    'Y' => 'Approved',
    'N' => 'Rejected',
    'W' => 'Wait',
);

$leave_action = array(
    '1' => 'Sick Leave',
    '2' => 'Business Leave',
    '3' => 'Annual Leave',
    '4' => 'Day Off',
    '5' => 'Switch Day Off',
    '6' => 'Ordination Leave',
    '7' => 'Maternity Leave',
);

function DateTimeDisplay($Date){
    $day=substr($Date, 8, 2);
    $month=substr($Date, 5, 2);
    $year=substr($Date, 0, 4);
    $Hour=substr($Date, 11, 2);
    $Minute=substr($Date, 14, 2);
    $Second=substr($Date, 17, 2);
    if($year=="0000"){
        $result="";
    }else{
        $result="$day/$month/$year"." $Hour:$Minute";
    }
    return $result;
}

function TimeDisplay($Date)
{
    $year = substr($Date, 0, 4);
    $Hour = substr($Date, 11, 2);
    $Minute = substr($Date, 14, 2);
    $Second = substr($Date, 17, 2);
    if ($year == "0000") {
        $result = "";
    } else {
        $result = "$Hour:$Minute";
    }
    return $result;
}

function DayOfWeek($date, $style = 0)
{
    $arr[0] = 'Sun.';
    $arr[1] = 'Mon.';
    $arr[2] = 'Tues.';
    $arr[3] = 'Wed.';
    $arr[4] = 'Thurs.';
    $arr[5] = 'Fri.';
    $arr[6] = 'Sat.';

    $w = date('w', strtotime($date));
    if ($style == 1) {
        $result = $arr[$w];
    } else {
        $result = $w;
    }
    return $result;
}

function build_sorter($key, $dir = 'ASC')
{
    return function ($a, $b) use ($key, $dir) {
        $t1 = strtotime(is_array($a) ? $a[$key] : $a->$key);
        $t2 = strtotime(is_array($b) ? $b[$key] : $b->$key);
        if ($t1 == $t2) return 0;
        return (strtoupper($dir) == 'ASC' ? ($t1 < $t2) : ($t1 > $t2)) ? -1 : 1;
    };
}

function array_group_by($array, $group_by = 'id')
{
    $ids = array_column($array, $group_by);
    $ids = array_unique($ids);
    $result = array_filter($array, function ($key, $value) use ($ids) {
        return in_array($value, array_keys($ids));
    }, ARRAY_FILTER_USE_BOTH);

    return $result;
}

$start_date = date("Y-m-d", strtotime($month_year . "-21" . "-1 Month"));
$end_date = date('Y-m-d', strtotime($month_year . "-20"));

$date_range = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);

$arrDayOfMonth = array();
for ($i=0; $i<=$date_range; $i++) {
    $check_leave = $QPcCheckInLog->CheckLeave($staff_id, date('Y-m-d',strtotime($start_date . "+$i days")));
    $arrDayOfMonth[$i] = array(
        "day_month" => date('d',strtotime($start_date . "+$i days")),
        "day_week" => date('Y-m-d',strtotime($start_date . "+$i days")),
        "action_id" => $check_leave['action_id'],
        "status_ap" => isset($check_leave['status_ap']) ? $check_leave['status_ap'] : "O",
        "mode_code" => $check_leave['mode_code'],
        "leave_remark" => $check_leave['leave_remark'],
        "approve_time" => $check_leave['approve_time'],
        "remark" => $check_leave['remark'],
        "certificate_file" => $check_leave['certificate_file'],
        "certificate_at" => $check_leave['certificate_at'],
    );
}

$list_pc_check_in = $QPcCheckInLog->getPcCheckIn($staff_id, $pc_check_in_detail['store_id'], $start_date, $end_date);
$list_pc_leave = $QPcCheckInLog->getPcLeave($staff_id, $start_date, $end_date);

$checkInMerge = array_merge($list_pc_check_in, $arrDayOfMonth);
$mergeAllData = array_group_by(array_merge($list_pc_leave, $checkInMerge), 'day_week');

usort($mergeAllData, build_sorter("day_week", "ASC"));

$PHPExcel->setActiveSheetIndex(0);
$sheet = $PHPExcel->getActiveSheet();

$sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
$sheet->setCellValue('A2', "Staff Code : ");
$sheet->setCellValue('B2', " " . $pc_check_in_detail['staff_code']);
$sheet->setCellValue('A3', "Staff Name : ");
$sheet->setCellValue('B3', $pc_check_in_detail['staff_name']);
$sheet->setCellValue('A4', "Start Date : ");
$sheet->setCellValue('B4', date('d/m/Y', strtotime($pc_check_in_detail['joined_at'])));
$sheet->setCellValue('A5', "Created User : ");
$sheet->setCellValue('B5', date('d/m/Y', strtotime($pc_check_in_detail['created_at'])));
$sheet->setCellValue('A6', "First Check In : ");
$sheet->setCellValue('B6', date('d/m/Y', strtotime($pc_check_in_detail['first_check_in'])));
$sheet->setCellValue('A7', "Off Date : ");
$sheet->setCellValue('B7', isset($pc_check_in_detail['off_date']) ? date('d/m/Y', strtotime($pc_check_in_detail['off_date'])) : '-');

$sheet->setCellValue('A8', "Check in for " . date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)));

$alpha = 'A';
$index = 9;
foreach ($heads as $key) {
    $sheet->setCellValue($alpha . $index, $key);
    $alpha++;
}

$sheet->mergeCells('A1:I1');
$sheet->mergeCells('B2:I2');
$sheet->mergeCells('B3:I3');
$sheet->mergeCells('B4:I4');
$sheet->mergeCells('B5:I5');
$sheet->mergeCells('B6:I6');
$sheet->mergeCells('B7:I7');
$sheet->mergeCells('A8:I8');

$style = array(
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    )
);

$sheet->getStyle("A1:J1")->applyFromArray($style);

$index = 10;

$data = $mergeAllData;
// $check_g= $check_group;
// echo "<pre>";
// print_r($data);
// die;
$cnt_data = count($data);
$n=1;

for ($i = 0; $i < $cnt_data; $i++) {

    $cnt_data = count($data);

    for ($i = 0; $i < $cnt_data; $i++) {

        $alpha = 'A';
    
      
           
        
        if ($data[$i]['mode_code'] == "CHECKIN") {

            
            $data[$i]['remark'] = "" ;
            if (date('Y-m-d', strtotime($data[$i]['first_check_in'])) == date('Y-m-d', strtotime($data[$i]['day_week']))
                && date('Y-m-d', strtotime($data[$i]['day_week'])) == date('Y-m-d', strtotime($data[$i]['joined_at']))
            ) {
                 $data[$i]['remark'] = "เริ่มงานวันแรก";
            } else if (date('Y-m-d', strtotime($data[$i]['first_check_in'])) < date('Y-m-d', strtotime($pc_check_in_detail['first_leave'])) && date('Y-m-d', strtotime($data[$i]['first_check_in'])) == date('Y-m-d', strtotime($data[$i]['day_week']))) {
                 $data[$i]['remark'] = "เข้าระบบครั้งแรก";
            }

             if (isset($check_group)) {
                        foreach ($check_group as $key => $check_groups) :

                            if ($check_groups['created'] == $data[$i]['day_week']) {
                             if ( $data[$i]['remark']=="") {
                                 $data[$i]['remark']=$check_groups['change_group'];
                             }
                                else {
                                   $data[$i]['remark']=$data[$i]['remark'].','.$check_groups['change_group'];
                                }
                            
                            }
                             endforeach;
                        }
           
          
             

            $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
            $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
            $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
            $sheet->setCellValue($alpha++ . $index, "[".$data[$i]['store_id']."] ".$data[$i]['store_name']);
            $sheet->setCellValue($alpha++ . $index, isset($data[$i]['t_check_in']) ? TimeDisplay($data[$i]['t_check_in']) : "-");
            $sheet->setCellValue($alpha++ . $index, isset($data[$i]['t_check_out']) ? TimeDisplay($data[$i]['t_check_out']) : "-");
            $sheet->setCellValue($alpha++ . $index, $status_ap[$data[$i]['status_ap']]);
            $sheet->setCellValue($alpha++ . $index, ($data[$i]['status_ap'] == 'W') ? "" : DateTimeDisplay($data[$i]['approve_time']));
            $sheet->setCellValue($alpha++ . $index,  $data[$i]['remark'].'');

        } elseif ($data[$i]['mode_code'] == "LEAVE") {
           $data[$i]['remark']= "";
            // $remark_leave = "";
            if ($data[$i]['action_id'] == 1) {
               if ($data[$i]['certificate_file'] != null) {
                  $data[$i]['remark']= "ป่วยมีใบแพทย์";
               } else {
                   $data[$i]['remark'] = "ป่วยไม่มีใบแพทย์";
               }
            }
            if (date('Y-m-d', strtotime($data[$i]['first_check_in'])) == date('Y-m-d', strtotime($data[$i]['day_week']))
                && date('Y-m-d', strtotime($data[$i]['day_week'])) == date('Y-m-d', strtotime($pc_check_in_detail['joined_at']))
            ) {
                $data[$i]['remark']= "เริ่มงานวันแรก";
            } else if (date('Y-m-d', strtotime($data[$i]['first_check_in'])) < date('Y-m-d', strtotime($pc_check_in_detail['first_check_in']))
                && date('Y-m-d', strtotime($data[$i]['first_check_in'])) == date('Y-m-d', strtotime($data[$i]['day_week']))
            ) {
                $data[$i]['remark']= "เข้าระบบครั้งแรก";
            }

            if (isset($check_group)) {
                        foreach ($check_group as $key => $check_groups) :

                            if ($check_groups['created'] == $data[$i]['day_week']) {
                             if ( $data[$i]['remark']=="") {
                                 $data[$i]['remark']=$check_groups['change_group'];
                             }
                                else {
                                   $data[$i]['remark']=$data[$i]['remark'].','.$check_groups['change_group'];
                                }
                            
                            }
                             endforeach;
                        }
            $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
            $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
            $sheet->setCellValue($alpha++ . $index, $leave_action[$data[$i]['action_id']] . " (" . $data[$i]['leave_remark'] . ")");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, $status_ap[$data[$i]['status_ap']]);
            $sheet->setCellValue($alpha++ . $index, ($data[$i]['status_ap'] == 'W') ? "" : DateTimeDisplay($data[$i]['approve_time']));
            $sheet->setCellValue($alpha++ . $index, $remark_other.' '.$remark_leave.' '.$data[$i]['remark']);
            $sheet->mergeCells("C" . $index . ":F" . $index);
            $sheet->getStyle("C" . $index . ":F" . $index)->applyFromArray($style);
        } 

        elseif ($data[$i]['mode_code'] == "TRAINING") {

            $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
            $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
            $sheet->setCellValue($alpha++ . $index, "อบรมครั้งที่ ".$n);
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->setCellValue($alpha++ . $index, "");
            $sheet->mergeCells("C" . $index . ":F" . $index);
            $sheet->getStyle("C" . $index . ":F" . $index)->applyFromArray($style);
            $n++;
        } else {
            if (date('Y-m-d', strtotime($data[$i]['day_week'])) < date("Y-m-d")) {
                $dateBegin = date('Y-m-d', strtotime("2001-01-01"));
                if (date('Y-m-d', strtotime($data[$i]['day_week'])) > $dateBegin && date('Y-m-d', strtotime($data[$i]['day_week'])) < date('Y-m-d', strtotime($pc_check_in_detail['joined_at']))) {
                    $remark = "";
                    if(date('Y-m-d', strtotime($data[$i]['day_week'])) == date('Y-m-d', strtotime($pc_check_in_detail['joined_at']))) {
                        $remark = 'เริ่มงานวันแรก';
                    }
                    $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, "");
                    $sheet->setCellValue($alpha++ . $index, $remark);
                } else {
                    if (isset($pc_check_in_detail['off_date'])) {
                        if (date('Y-m-d', strtotime($data[$i]['day_week'])) >= date('Y-m-d', strtotime($pc_check_in_detail['off_date']))) {
                            if (date('Y-m-d', strtotime($data[$i]['day_week'])) == date('Y-m-d', strtotime($pc_check_in_detail['off_date']))) {
                                $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                                $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
                                $sheet->setCellValue($alpha++ . $index, "วันที่มีผลลาออก (".$pc_check_in_detail['off_date'].")");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->mergeCells("C" . $index . ":F" . $index);
                                $sheet->getStyle("C" . $index . ":F" . $index)->applyFromArray($style);
                            } else {
                                $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                                $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                                $sheet->setCellValue($alpha++ . $index, "");
                            }
                        } else {
                 $data[$i]['remark']="ขาดงาน";
                if (isset($check_group)) {
                foreach ($check_group as $key => $check_groups) :

                if ($check_groups['created'] == $data[$i]['day_week']) {
                 
                         $data[$i]['remark']= $data[$i]['remark'].','.$check_groups['change_group'];
                    }
                  endforeach;
              }
                            $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                            $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
                            $sheet->setCellValue($alpha++ . $index, "Not check in");
                            $sheet->setCellValue($alpha++ . $index, "");
                            $sheet->setCellValue($alpha++ . $index, "");
                            $sheet->setCellValue($alpha++ . $index, "");
                            $sheet->setCellValue($alpha++ . $index, "-");
                            $sheet->setCellValue($alpha++ . $index, "");
                            $sheet->setCellValue($alpha++ . $index,$data[$i]['remark']);
                            $sheet->mergeCells("C" . $index . ":F" . $index);
                            $sheet->getStyle("C" . $index . ":F" . $index)->applyFromArray($style);
                        }
                    } else {
                          $data[$i]['remark']="ขาดงาน";
                if (isset($check_group)) {
                foreach ($check_group as $key => $check_groups) :

                if ($check_groups['created'] == $data[$i]['day_week']) {
                 
                         $data[$i]['remark']= $data[$i]['remark'].','.$check_groups['change_group'];}
                   
                  endforeach; }
                        $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                        $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
                        $sheet->setCellValue($alpha++ . $index, "Not check in");
                        $sheet->setCellValue($alpha++ . $index, "");
                        $sheet->setCellValue($alpha++ . $index, "");
                        $sheet->setCellValue($alpha++ . $index, "");
                        $sheet->setCellValue($alpha++ . $index, "-");
                        $sheet->setCellValue($alpha++ . $index, "");
                        $sheet->setCellValue($alpha++ . $index,  $data[$i]['remark']);
                        $sheet->mergeCells("C" . $index . ":F" . $index);
                        $sheet->getStyle("C" . $index . ":F" . $index)->applyFromArray($style);
                    }
                }

            } else {
                   if (isset($check_group)) {
                foreach ($check_group as $key => $check_groups) :

                if ($check_groups['created'] == $data[$i]['day_week']) {
                 
                         $data[$i]['remark']= $check_groups['change_group'];
                    }
                  endforeach;
                }
                $sheet->setCellValue($alpha++ . $index, date("d/m/Y", strtotime($data[$i]['day_week'])));
                $sheet->setCellValue($alpha++ . $index, DayOfWeek($data[$i]['day_week'], 1));
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index, "");
                $sheet->setCellValue($alpha++ . $index,  $data[$i]['remark']);
            }
        }

        $index++;
    }
}

$filename = 'PC Check In Report-' . $pc_check_in_detail['staff_code'] . '-' . date('M', strtotime($start_date));
$objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

$objWriter->save('php://output');

exit;

