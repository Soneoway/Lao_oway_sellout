<?php
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');

$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));

$oppo_product_id    = $this->getRequest()->getParam('oppo_product_id');
$huawei_product_id  = $this->getRequest()->getParam('huawei_product_id');
$vivo_product_id    = $this->getRequest()->getParam('vivo_product_id');
$samsung_product_id = $this->getRequest()->getParam('samsung_product_id');

$status_id       = $this->getRequest()->getParam('status_id', array(0));
$export          = $this->getRequest()->getParam('export', 0);

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,

    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'from'            => $from,
    'to'              => $to, 

    'oppo_product_id'    => $oppo_product_id,
    'huawei_product_id'  => $huawei_product_id,
    'vivo_product_id'    => $vivo_product_id,
    'samsung_product_id' => $samsung_product_id,

    'status_id'       => $status_id,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

$QCompetitorProduct = new Application_Model_CompetitorProduct(); 
$QGood = new Application_Model_Good(); 

// OPPO 
$this->view->oppo_product_list = $QGood->get_cache2();

// Huawei
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 1);
$this->view->huawei_product_list = $QCompetitorProduct->fetchAll($where, 'name');

// VIVO
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 2);
$this->view->vivo_product_list = $QCompetitorProduct->fetchAll($where, 'name');

// Samsung
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 3);
$this->view->samsung_product_list = $QCompetitorProduct->fetchAll($where, 'name');

$this->view->brand_list = $brand_list;

$status_list = array(
    '0'  => array('id' => '0', 'name' => 'Normal'),
    '1'  => array('id' => '1', 'name' => 'Hero Product'),
    '2'  => array('id' => '2', 'name' => 'Flagship'),
    '3'  => array('id' => '3', 'name' => 'EOL'),
); 

$this->view->status_list = $status_list;

$QCompetitorRecord = new Application_Model_CompetitorRecord();

if ($export && $export == 1) {
    $competitor_sellout_01 = $QCompetitorRecord->getStoreFocusListDetails($params);
    // echo "<pre>"; print_r($competitor_sellout_01);

    $competitor_sellout_02 = $QCompetitorRecord->getStoreFocusListOPPO($params);
    // echo "<pre>"; print_r($competitor_sellout_02);

    foreach ($competitor_sellout_01 as $key => $value) {
        // $temp_01[ $value['timing_date']."|".$value['st_id']."|".$value['staff_code'] ] = $value;
        $temp_01[ $value['timing_date']."|".$value['st_id'] ] = $value;
    }

    // echo "--- Others Brand Data [temp_01]----";
    // echo "<pre>"; print_r($temp_01);

    foreach ($competitor_sellout_02 as $key => $value) {
        // $temp_02[ $value['timing_date']."|".$value['st_id']."|".$value['staff_code'] ] = $value;
        $temp_02[ $value['timing_date']."|".$value['st_id'] ] = $value;
    }
    // echo "--- OPPO Data [temp_02] ----";
    // echo "<pre>"; print_r($temp_02);

    // echo "--- Other Brand ----";
    $others_list = array_keys($competitor_sellout_01[0]);
    // echo "<pre>"; print_r($others_list);

    array_splice($others_list, count($others_list) - 10, 10);
    // echo "<pre>"; print_r($others_list);

    // echo "--- OPPO Brand ----";
    $oppo_list = array_keys($competitor_sellout_02[0]);
    // echo "<pre>"; print_r($oppo_list);

    array_splice($oppo_list, count($oppo_list) - 7, 7);
    // echo "<pre>"; print_r($oppo_list);

    // echo "--- Combined ----";

    $all_list = array_unique( array_merge($oppo_list,$others_list) );

    // echo "--- all list ----";
    // echo "<pre>"; print_r($all_list);

    $all_day = array_values( array_unique( array_merge(array_keys($temp_01),array_keys($temp_02)) ) );

    // echo "--- all day ----";
    // echo "<pre>"; print_r($all_day);

    $data = array();
    for ($i=0;$i<count($all_day);$i++) {

        // $data[$i]['AAA'] = $i." | ".$all_day[$i];

        if ( isset($temp_01[ $all_day[$i] ]) && isset($temp_02[ $all_day[$i] ]) ) { 

            for ($j=0;$j<count($all_list);$j++) {

                if ( substr($all_list[$j], 0, 4) == 'OPPO' ) { 

                    $data[$i][ $all_list[$j] ] = isset($temp_02[ $all_day[$i] ][ $all_list[$j] ]) ? $temp_02[ $all_day[$i] ][ $all_list[$j] ] : 0;

                } else {

                    $data[$i][ $all_list[$j] ] = isset($temp_01[ $all_day[$i] ][ $all_list[$j] ]) ? $temp_01[ $all_day[$i] ][ $all_list[$j] ] : 0;

                } 

            }

            $data[$i]['timing_date']    = $temp_01[ $all_day[$i] ]['timing_date'];
            $data[$i]['area_name']      = $temp_01[ $all_day[$i] ]['area_name'];
            $data[$i]['st_id']          = $temp_01[ $all_day[$i] ]['st_id'];
            $data[$i]['st_name']        = $temp_01[ $all_day[$i] ]['st_name'];
            $data[$i]['city_type']      = $temp_01[ $all_day[$i] ]['city_type'];
            $data[$i]['channel_type']   = $temp_01[ $all_day[$i] ]['channel_type'];
            // $data[$i]['staff_code']     = $temp_01[ $all_day[$i] ]['staff_code'];
            // $data[$i]['staff_name']     = $temp_01[ $all_day[$i] ]['staff_name'];
            // $data[$i]['staff_group']    = $temp_01[ $all_day[$i] ]['staff_group'];
            $data[$i]['total_oppo']     = $temp_02[ $all_day[$i] ]['total_oppo'];
            $data[$i]['total_unit']     = $temp_01[ $all_day[$i] ]['total_unit'];
            $data[$i]['com_1']          = $temp_01[ $all_day[$i] ]['com_1'];
            $data[$i]['com_2']          = $temp_01[ $all_day[$i] ]['com_2'];
            $data[$i]['com_3']          = $temp_01[ $all_day[$i] ]['com_3'];

        } else {

            if ( isset($temp_01[ $all_day[$i] ]) ) { $data_temp = $temp_01[ $all_day[$i] ]; } 
            elseif ( isset($temp_02[ $all_day[$i] ]) ) { $data_temp = $temp_02[ $all_day[$i] ]; } 

            for ($j=0;$j<count($all_list);$j++) {

                $data[$i][ $all_list[$j] ] = isset($data_temp[ $all_list[$j] ]) ? $data_temp[ $all_list[$j] ] : 0;

            }

            $data[$i]['timing_date']    = $data_temp['timing_date'];
            $data[$i]['area_name']      = $data_temp['area_name'];
            $data[$i]['st_id']          = $data_temp['st_id'];
            $data[$i]['st_name']        = $data_temp['st_name'];
            $data[$i]['city_type']      = $data_temp['city_type'];
            $data[$i]['channel_type']   = $data_temp['channel_type'];
            // $data[$i]['staff_code']     = $data_temp['staff_code'];
            // $data[$i]['staff_name']     = $data_temp['staff_name'];
            // $data[$i]['staff_group']    = $data_temp['staff_group'];

            $data[$i]['total_oppo']     = isset($data_temp['total_oppo']) ? $data_temp['total_oppo'] : 0;
            $data[$i]['total_unit']     = isset($data_temp['total_unit']) ? $data_temp['total_unit'] : 0;
            $data[$i]['com_1']          = isset($data_temp['com_1']) ? $data_temp['com_1'] : 0;
            $data[$i]['com_2']          = isset($data_temp['com_2']) ? $data_temp['com_2'] : 0;
            $data[$i]['com_3']          = isset($data_temp['com_3']) ? $data_temp['com_3'] : 0;

        }


    }
    // echo "--- Result ----";
    // echo "<pre>"; print_r($data);

    // die;

    $params['store_focus'] = 0;
    $this->_exportCompetitorReport($data,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 

    $competitor_sellout = $QCompetitorRecord->getStoreFocusList($params);

    $params['get_total_count'] = 0;
    $total_count = $QCompetitorRecord->getStoreFocusList($params);
    
}

$this->view->params = $params;
$this->view->competitor_sellout = $competitor_sellout;
$this->view->total_count = $total_count;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-focus/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-focus/index');