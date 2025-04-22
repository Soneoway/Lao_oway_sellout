<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$id = $this->getRequest()->getParam('id');
$title = $this->getRequest()->getParam('title');
$category = $this->getRequest()->getParam('category');

$params = array(
    'title' => $title,
    'category' => $category,
);

$db = Zend_Registry::get('db');

$get = array(
    'sks'          => '*',
    'staff_code'   => 's.code',
    'staff_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
    'staff_group'  => 'g.name',
    'area_name'    => 'a.name',
);

$select = $db->select()
    ->from(array('sks'=> 'sales_knowledge_statistic'), $get)
    ->join(array('s' => 'staff'), 's.id = sks.staff_id', array())
    ->join(array('rm'=> 'regional_market'), 's.regional_market = rm.id', array())
    ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
    ->join(array('g' => 'group'), 's.group_id = g.id', array())
    ->where(new Zend_Db_Expr("FIND_IN_SET($id, sks.id_info)"));

$query = $db->fetchAll($select);

$result = array();
foreach ($query as $i => $value) {
    $position = array_search($id, explode(',',$value['id_info']));
    $info =  explode(',',$value['id_info'])[$position];
    $timer =  explode(',',$value['timer'])[$position];
    $last_date =  explode(',',$value['last_date'])[$position];

    $result[$i]['id'] = $value['id'];
    $result[$i]['id_info'] = $info;
    $result[$i]['timer'] = $timer;
    $result[$i]['last_date'] = $last_date;
    $result[$i]['staff_id'] = $value['staff_id'];
    $result[$i]['staff_code'] = $value['staff_code'];
    $result[$i]['staff_name'] = $value['staff_name'];
    $result[$i]['staff_group'] = $value['staff_group'];
    $result[$i]['area_name'] = $value['area_name'];
}

exportExcel($result, $params);

function exportExcel($data, $params)
{
    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();

    $heads = array(
        'Staff ID',
        'Staff Code',
        'Staff Name',
        'Staff Group',
        'Area',
        'Time length',
        'Last Date',
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet = $PHPExcel->getActiveSheet();

    $sheet->setCellValue('A1', 'THAI OPPO CO.,LTD');
    $sheet->setCellValue('A2', "Category : ");
    $sheet->setCellValue('A3', "Title : ");

    $sheet->setCellValue('B2', $params['category']);
    $sheet->setCellValue('B3', $params['title']);

    $alpha = 'A';
    $index = 4;
    foreach ($heads as $key) {
        $sheet->setCellValue($alpha . $index, $key);
        $alpha++;
    }

    $sheet->mergeCells('A1:F1');
    $sheet->mergeCells('B2:F2');
    $sheet->mergeCells('B3:F3');

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
    );

    $sheet->getStyle("A1:F1")->applyFromArray($style);

    $index = 5;
    for ($i = 0; $i < count($data); $i++) {
        $alpha = 'A';
        $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_id']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_code']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_name']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['staff_group']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['area_name']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['timer']);
        $sheet->setCellValue($alpha++ . $index, $data[$i]['last_date']);
        $index++;
    }

    $filename = $params['title'].'-'. time();
    $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    $objWriter->save('php://output');
    exit;
}