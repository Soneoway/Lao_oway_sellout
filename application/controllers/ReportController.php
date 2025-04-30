<?php

class ReportController extends My_Controller_Action
{
    //  Market Research
    public function marketResearchAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research.php';
    }

    public function marketResearchSaveAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research-save.php';
    }

    public function setMarketResearchAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'set-market-research.php';
    }

    public function maketResearchAddAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research-add.php';
    }

    public function marketResearchViewAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research-view.php';
    }
    
    public function marketResearchEditAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research-edit.php';
    }
    
    public function marketResearchEditSaveAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'market-research' . DIRECTORY_SEPARATOR . 'market-research-edit-save.php';
    }

    
    // Store Visit
    public function storeVisitAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'store-visit.php';
    }

    public function setStoreLocationAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'set-location.php';
    }

    public function locationViewAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'location-view.php';
    }

    public function saveStoreLocationAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'save-store-location.php';
    }

    public function storeVisitSaveAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'store-visit-save.php';
    }

    public function storeVisitCheckInAction()
    {
        require_once 'report' . DIRECTORY_SEPARATOR . 'store-visit' . DIRECTORY_SEPARATOR . 'store-visit-check-in.php';
    }

	public function inventoryTurnoverAction()
	{
        $userStorage             = Zend_Auth::getInstance()->getStorage()->read();
        $from                    = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to                      = $this->getRequest()->getParam('to', date('d/m/Y',strtotime("-1 day")));
        $date                    = $this->getRequest()->getParam('date', date('d/m/Y',strtotime("-1 day")));
        $export                  = $this->getRequest()->getParam('export');
        $invertory_by            = $this->getRequest()->getParam('invertory_by');
        $good                 = $this->getRequest()->getParam('good');
        $stock_by                = $this->getRequest()->getParam('stock_by');
        $invertory_type          = $this->getRequest()->getParam('invertory_type');
        $category_id             = $this->getRequest()->getParam('category');
        $brand                = $this->getRequest()->getParam('brand');

        $tmp_from = explode('/',$from);
        $tmp_to = explode('/',$to);

        $from_data = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to_data = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];

        $date_count = (strtotime($to_data) - strtotime($from_data))/  ( 60 * 60 * 24 );



        $QWebImei = new Application_Model_WebImei();
        $QGood = new Application_Model_Good();
        $QBrand = new Application_Model_Brand();
        $brands = $QGood->get_brand();

        $params = array( 
         'date'          => $date,
         'from'          => $from,
         'to'            => $to,
         'export'        => $export,
         'invertory_by'	  => $invertory_by,
         'stock_by'       => $stock_by,
         'invertory_type' => $invertory_type,
         'cat_id'         => $category_id,
         'brand'          => $brand,
         'good'           => $good,

     );

        if($userStorage->group_id == 28){
            $params['rd_id'] = $userStorage->id;
        }

        if(!empty($_GET)){
            if($invertory_type == 11){
                $params['count_date'] = $date_count;
                $data = $QWebImei->InventoryTurnOverByRGM($params);
                if($export){
                    $this->_exportInventoryrReport($data,$params);
                }
            }elseif($invertory_type == 12){
                $params['count_date'] = $date_count;
                $data = $QWebImei->InventoryTurnOverByModel($params);
                if($export){
                    $this->_exportInventoryrReportModel($data,$params);
                }
            }elseif($invertory_type == 13){

            }
        }


        // $where = array();
        // $where[] = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
        // $where[] = $QGood->getAdapter()->quoteInto('product_status = ?', 1);
        // $goods = $QGood->fetchAll($where, 'desc');

        $goods_list = $QGood->getProduct($params);

        $this->view->params     = $params;
        $this->view->data       = $data;
        $this->view->goods      = $goods_list;
        $this->view->brands     = $brands;


        if($this->getRequest()->isXmlHttpRequest()) {
         $this->_helper->layout->disableLayout();

         $this->_helper->viewRenderer->setRender('inventory-turnover/partials/list');
     } else

     $this->_helper->viewRenderer->setRender('inventory-turnover/index');

 }

 	public function onShelfrateAction(){

    $userStorage             = Zend_Auth::getInstance()->getStorage()->read();
    $category                = $this->getRequest()->getParam('category');
    $pc                      = $this->getRequest()->getParam('pc');
    $inventory_status        = $this->getRequest()->getParam('inventory_status');
    $good                    = $this->getRequest()->getParam('good');
    $on_shelf_view           = $this->getRequest()->getParam('on_shelf_view');
    $on_shelf_type           = $this->getRequest()->getParam('on_shelf_type');
    $area_id                 = $this->getRequest()->getParam('area_id');
    $export                  = $this->getRequest()->getParam('export');
    $brand_id                = $this->getRequest()->getParam('brand_id');

    $QGood          = new Application_Model_Good();
    $QWebImei       = new Application_Model_WebImei();
    $QArea          = new Application_Model_Area();
    $QBrand         = new Application_Model_Brand();

    $params = array(
        'category'                  => $category,
        'pc'                        => $pc,
        'inventory_status'          => $inventory_status,
        'good'                      => $good,
        'area_id'                   => $area_id,
        'on_shelf_view'             => $on_shelf_view,
        'on_shelf_type'             => $on_shelf_type,
        'brand'                     => $brand_id
    );


    $goods_list = $QGood->getProduct($params);


    if(!empty($_GET)){
        if($on_shelf_view == 1){
            $data = $QWebImei->OnShelfRateDistributor($params);
        }

        if($on_shelf_view == 2){
            $data = $QWebImei->OnShelfRateRGM($params);
        }
    }

    if($export){
        if($export == 1){
            if($on_shelf_view == 1){ // Distributor
                $data = $QWebImei->OnShelfRateDistributor($params);
                $this->_exportOnShelfrateDistributor($data,$params);
            }

            if($on_shelf_view == 2){ // RGM
                $data = $QWebImei->OnShelfRateRGM($params);
                $this->_exportOnShelfrateRGM($data,$params);
            }
        }
    }

    $this->view->brands      = $QBrand->fetchAll();
    $this->view->goods       = $goods_list;
    $this->view->params      = $params;
    $this->view->data        = $data;
    $this->view->areas       = $QArea->fetchAll(null, 'name');
    
    if($this->getRequest()->isXmlHttpRequest()) {
         $this->_helper->layout->disableLayout();

         $this->_helper->viewRenderer->setRender('On-Shelf-Rate/partials/list');
     } else

     $this->_helper->viewRenderer->setRender('On-Shelf-Rate/index');

}

public function _exportOnShelfrateDistributor($data,$params) {
    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();

    $heads = array(
        'No.',
        'RGM',
        'Provience',
        'Distributor ID',
        'Distributor Name',
        'STORES WITH ON-SHELF AVAILABILITY',
        'VALID STORE QUANTITY',
        'ON-SHELF AVAILABILITY RATE',
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet    = $PHPExcel->getActiveSheet();

    $alpha    = 'A';
    $index    = 1;


    foreach($heads as $key) {
        $sheet->setCellValue($alpha.$index, $key);
        $alpha++;
    }

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
    );

    $sheet->getStyle('A1:H1')->applyFromArray($style);

    $index = 2;

    for ($i=0;$i<count($data); $i++) {
        $total = 1;

        $avg = ($data[$i]['distributor_have_stock'] / $data[$i]['distributor_count']) * 100 ;

        if($data[$i]['distributor_stock'] > 0){
            $stock = "1";
            $avg_stock = "100 %";
        }else{
            $stock = "0";
            $avg_stock = "0 %";
        }

        $cnt = $i + 1;

        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, $cnt);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_area']);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_provience']);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_id']);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_name']);
        $sheet->setCellValue($alpha++.$index, $stock);
        $sheet->setCellValue($alpha++.$index, $total);
        $sheet->setCellValue($alpha++.$index, $avg_stock);
        $index++;

    }

    $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    $filename = 'OnShelfrate_Distributor_Report_'.date('Y-m-d');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    $objWriter->save('php://output');
    exit;
}


public function _exportOnShelfrateRGM($data,$params) {
    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();

    $heads = array(
        'No.',
        'RGM',
        'STORES WITH ON-SHELF AVAILABILITY',
        'VALID STORE QUANTITY',
        'ON-SHELF AVAILABILITY RATE',
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet    = $PHPExcel->getActiveSheet();

    $alpha    = 'A';
    $index    = 1;


    foreach($heads as $key) {
        $sheet->setCellValue($alpha.$index, $key);
        $alpha++;
    }

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
    );

    $sheet->getStyle('A1:H1')->applyFromArray($style);

    $index = 2;

    for ($i=0;$i<count($data); $i++) {

        $avg = ($data[$i]['distributor_have_stock'] / $data[$i]['distributor_count']) * 100 ;

        $cnt = $i + 1;

        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, $cnt);
        $sheet->setCellValue($alpha++.$index, $data[$i]['area_name']);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_have_stock']);
        $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_count']);
        $sheet->setCellValue($alpha++.$index, number_format($avg,2)." %");
        $index++;

    }

    $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    $filename = 'OnShelfrate_RGM_Report_'.date('Y-m-d');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    $objWriter->save('php://output');
    exit;
}


public function _exportInventoryrReportModel($data,$params) {
    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();

    $heads = array(
        'No.',
        'MODEL NAME',
        '销量/SALES VOLUME',
        '销量占比/PROPORTION(SALES VOLUM)',
        '全部库存/ALL INVENTORY',
        '渠道库存/DISTRIBUTOR INVENTORY',
        '仓库库存WAREHOUSE INVENTORY',
        '库存占比/PROPORTION(INVENTORY)',
        '平均流速（台/天）AVERAGE FLOW(UNITS/DAY)',
        '周转天数/TURNOVER DAY'
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet    = $PHPExcel->getActiveSheet();

    $alpha    = 'A';
    $index    = 1;


    foreach($heads as $key) {
        $sheet->setCellValue($alpha.$index, $key);
        $alpha++;
    }

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
    );

    $sheet->getStyle('A1:H1')->applyFromArray($style);

    $index = 2;

    for ($i=0;$i<count($data); $i++) {

      $proportion_sales = ($data[$i]['sellout'] / $data[$i]['total_sellout']) * 100 ;
      $total_inventory = ($data[$i]['distributor_stock'] + $data[$i]['warehouse_stock']);
      $all_inventory = ($data[$i]['total_warehouse_stock'] + $data[$i]['total_distributor_stock']);

      $avg_inventory = (($data[$i]['distributor_stock'] + $data[$i]['warehouse_stock']) / ($data[$i]['total_warehouse_stock'] + $data[$i]['total_distributor_stock'])) * 100;
      $avg_sellout = $data[$i]['sellout'] / $params['count_date'];
      $day = (($data[$i]['warehouse_stock'] + $data[$i]['distributor_stock']) / $data[$i]['sellout']) * $params['count_date'];

      $proportion_sales_resualt = ' - ';
      $turnOver_day = ' - ';
      if($data[$i]['sellout']){
        $proportion_sales_resualt = number_format($proportion_sales,1)."%";
        $turnOver_day = number_format($day);

    }

    $stock_warehouse = ' - ';
    if($data[$i]['warehouse_stock']){
        $stock_warehouse = $data[$i]['warehouse_stock'];
    }

    $cnt = $i + 1;

    $alpha    = 'A';
    $sheet->setCellValue($alpha++.$index, $cnt);
    $sheet->setCellValue($alpha++.$index, $data[$i]['model_name']);
    $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
    $sheet->setCellValue($alpha++.$index, $proportion_sales_resualt);
    $sheet->setCellValue($alpha++.$index, $total_inventory);
    $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_stock']);
    $sheet->setCellValue($alpha++.$index, $stock_warehouse);
    $sheet->setCellValue($alpha++.$index, number_format($avg_inventory,1)."%");
    $sheet->setCellValue($alpha++.$index, number_format($avg_sellout,1));
    $sheet->setCellValue($alpha++.$index, $turnOver_day);
    $index++;
}

$objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

$filename = 'Inventory_Turnover_Report_Model_'.date('Y-m-d');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

$objWriter->save('php://output');
exit;
}

public function _exportInventoryrReport($data,$params) {

    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();

    $heads = array(
        'No.',
        'AREA NAME',
        '省/PROVIENCE NAME',
        '销量/SALES VOLUME',
        '销量占比/PROPORTION(SALES VOLUM)',
        '全部库存/ALL INVENTORY',
        '渠道库存/DISTRIBUTOR INVENTORY',
        '仓库库存WAREHOUSE INVENTORY',
        '库存占比/PROPORTION(INVENTORY)',
        '平均流速（台/天）AVERAGE FLOW(UNITS/DAY)',
        '周转天数/TURNOVER DAY'
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet    = $PHPExcel->getActiveSheet();

    $alpha    = 'A';
    $index    = 1;


    foreach($heads as $key) {
        $sheet->setCellValue($alpha.$index, $key);
        $alpha++;
    }

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
    );

    $sheet->getStyle('A1:H1')->applyFromArray($style);

    $index = 2;

    for ($i=0;$i<count($data); $i++) {

       if($data[$i]['area_id'] == 120){ $area_name = "Realme LAOS"; }else{  $area_name =$data[$i]['area_name']; }
       if($data[$i]['area_id'] == 120){ $provience_name = "WH - Realme"; }else{ $provience_name = $data[$i]['provience_name']; }


       $proportion_sales = ($data[$i]['sellout'] / $data[$i]['total_sellout']) * 100 ;
       $total_inventory = ($data[$i]['distributor_stock'] + $data[$i]['warehouse_stock']);
       $all_inventory = ($data[$i]['total_warehouse_stock'] + $data[$i]['total_distributor_stock']);

       $avg_inventory = (($data[$i]['distributor_stock'] + $data[$i]['warehouse_stock']) / ($data[$i]['total_warehouse_stock'] + $data[$i]['total_distributor_stock'])) * 100;
       $avg_sellout = $data[$i]['sellout'] / $params['count_date'];
       $day = (($data[$i]['warehouse_stock'] + $data[$i]['distributor_stock']) / $data[$i]['sellout']) * $params['count_date'];

       $proportion_sales_resualt = ' - ';
       $turnOver_day = ' - ';
       if($data[$i]['sellout']){
        $proportion_sales_resualt = number_format($proportion_sales,1)."%";
        $turnOver_day = number_format($day);

    }

    $stock_warehouse = ' - ';
    if($data[$i]['warehouse_stock']){
        $stock_warehouse = $data[$i]['warehouse_stock'];
    }

    $cnt = $i + 1;

    $alpha    = 'A';
    $sheet->setCellValue($alpha++.$index, $cnt);
    $sheet->setCellValue($alpha++.$index, $area_name);
    $sheet->setCellValue($alpha++.$index, $provience_name);
    $sheet->setCellValue($alpha++.$index, $data[$i]['sellout']);
    $sheet->setCellValue($alpha++.$index, $proportion_sales_resualt);
    $sheet->setCellValue($alpha++.$index, $total_inventory);
    $sheet->setCellValue($alpha++.$index, $data[$i]['distributor_stock']);
    $sheet->setCellValue($alpha++.$index, $stock_warehouse);
    $sheet->setCellValue($alpha++.$index, number_format($avg_inventory,1)."%");
    $sheet->setCellValue($alpha++.$index, number_format($avg_sellout,1));
    $sheet->setCellValue($alpha++.$index, $turnOver_day);
    $index++;
}

$objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

$filename = 'Inventory_Turnover_Report_RGM_'.date('Y-m-d');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

$objWriter->save('php://output');
exit;
}

}