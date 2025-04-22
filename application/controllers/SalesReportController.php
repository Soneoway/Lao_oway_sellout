<?php
/**
 * Các report của phòng Sales
 */
class SalesReportController extends My_Controller_Action
{

    public function timingDetailAction()
    {

        $desc            = $this->getRequest()->getParam('desc', 1);
        $from            = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to              = $this->getRequest()->getParam('to', date('d/m/Y'));
        $phone_number    = $this->getRequest()->getParam('phone_number');
        $name            = $this->getRequest()->getParam('name');
        $staff_code      = $this->getRequest()->getParam('staff_code');
        $area_id         = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $store           = $this->getRequest()->getParam('store');
        $store_type      = $this->getRequest()->getParam('store_type');
        $chk_tme         = $this->getRequest()->getParam('chk_tme');
        $actived         = $this->getRequest()->getParam('actived');
        $export          = $this->getRequest()->getParam('export');
        $good_id         = $this->getRequest()->getParam('good');
        $company         = $this->getRequest()->getParam('company');
        $position        = $this->getRequest()->getParam('position');
        $brand_id        = $this->getRequest()->getParam('brand_id');

        $limit = LIMITATION;

        $params = array(
            'from'            => $from,
            'to'              => $to,
            'phone_number'    => $phone_number,
            'name'            => $name,
            'staff_code'      => trim($staff_code),
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'store'           => $store,
            'store_type'      => $store_type,
            'chk_tme'         => $chk_tme,
            'actived'         => $actived,
            'good'            => $good_id,
            'export2'         => 1,
            'company'         => $company,
            'position'        => $position,
            'brand'        => $brand_id
        );

        $QArea = new Application_Model_Area();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;

        if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
            $params['asm'] = $userStorage->id;

        //check am permission
        if ($userStorage->group_id == AM_ID)
            $params['am'] = $userStorage->id;

        //check sale permission
        if ($userStorage->group_id == SALES_ID)
            $params['sale_id'] = $userStorage->id;

        //check pcm permission
        if ($userStorage->group_id == PCM_ID)
            $params['pcm_id'] = $userStorage->id;

        //check sale leader permission
        if ($userStorage->group_id == LEADER_ID)
            $params['leader_id'] = $userStorage->id;

        //check brandshop manager permission
        if ($userStorage->group_id == BM_ID)
            $params['bm_id'] = $userStorage->id;

        //check admin brandshop permission
        if ($userStorage->group_id == 39)
            $params['admin_bs'] = $userStorage->id;

        if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
            $areas = $QArea->getAreaByAsmTable($userStorage->id);

            $data = array();
            foreach($areas as $item) {
                $data[] = $item['id'];
            }

            $params['rgm_area'] = implode(',', $data);

        } else {
            $areas = $QArea->fetchAll(null, 'name');
        }

        $total = 0;

        $QTiming = new Application_Model_Timing();
        //$report = $QTiming->reporttotal(1, null, $total, $params);
        
        //print_r($report);die;

        if (isset($export) && intval($export) == 1) { 
            $report = $QTiming->report(null, null, $total, $params);
            My_Report_Sales::timingDetail($report);
        }

        if (isset($export) && intval($export) == 2) { 
            $report = $QTiming->analytic_imei($params);
            My_Report_Sales::export_by_imei($report);
        }

        if (isset($export) && intval($export) == 3) { 
            $report = $QTiming->getFocusPC($params);
            My_Report_Sales::export_focus_pc($report, $params);
        }

        if (isset($export) && intval($export) == 4) { 
            $QGoodKpiLog = new Application_Model_GoodKpiLog();

            $report = $QGoodKpiLog->getComABM($params);
            My_Report_Sales::export_abm_com($report, $params);
        }

        if (isset($export) && intval($export) == 5) { 
            $report = $QTiming->analytic_imei($params);
            My_Report_Sales::export_by_imei_short($report);
        }
        
    }

    public function storeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $page       = $this->getRequest()->getParam('page', 1);
        $sort       = $this->getRequest()->getParam('sort', 'product_count');
        $desc       = $this->getRequest()->getParam('desc', 1);
        $from       = $this->getRequest()->getParam('from', date('01/m/Y') );
        $to         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $name       = $this->getRequest()->getParam('name');
        $area       = $this->getRequest()->getParam('area');
        $province   = $this->getRequest()->getParam('province');
        $district   = $this->getRequest()->getParam('district');
        $store      = $this->getRequest()->getParam('store');
        $has_pg     = $this->getRequest()->getParam('has_pg');
        $sales_from = $this->getRequest()->getParam('sales_from');
        $sales_to   = $this->getRequest()->getParam('sales_to');

        $params = array(
            'page'       => $page,
            'sort'       => $sort,
            'desc'       => $desc,
            'from'       => $from,
            'to'         => $to,
            'name'       => $name,
            'area'       => $area,
            'province'   => $province,
            'district'   => $district,
            'store'      => $store,
            'has_pg'     => $has_pg,
            'sales_from' => $sales_from,
            'sales_to'   => $sales_to,
        );

        $limit = LIMITATION;
        $total = 0;

        $QImeiKpi = new Application_Model_ImeiKpi();
        $sales = $QImeiKpi->fetchStore($page, $limit, $total, $params);

        $params['kpi'] = 1;
        $result = $QImeiKpi->fetchStore(null, null, $total, $params);
        My_Report_Sales::store($params['from'], $params['to'], $result);
    }

    public function dealerAction()
    {
        $page       = $this->getRequest()->getParam('page', 1);
        $sort       = $this->getRequest()->getParam('sort', 'total_quantity');
        $desc       = $this->getRequest()->getParam('desc', 1);
        $export     = $this->getRequest()->getParam('export', 0);
        $from       = $this->getRequest()->getParam('from', date('01/m/Y') );
        $to         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $name       = $this->getRequest()->getParam('name');
        $area       = $this->getRequest()->getParam('area');
        $province   = $this->getRequest()->getParam('province');
        $district   = $this->getRequest()->getParam('district');
        $sales_from = $this->getRequest()->getParam('sales_from');
        $sales_to   = $this->getRequest()->getParam('sales_to');

        $params = array(
            'page'       => $page,
            'sort'       => $sort,
            'desc'       => $desc,
            'from'       => $from,
            'to'         => $to,
            'name'       => $name,
            'area'       => $area,
            'province'   => $province,
            'district'   => $district,
            'sales_from' => $sales_from,
            'sales_to'   => $sales_to,
            'export'     => $export,
        );

        $limit = LIMITATION;
        $total = 0;

        $QTiming = new Application_Model_Timing();
        $sales = $QTiming->report_by_dealer(1, null, $total, $params);

        My_Report_Sales::dealer($sales);
    }

    public function dealerAllAction()
    {
        $page       = $this->getRequest()->getParam('page', 1);
        $sort       = $this->getRequest()->getParam('sort', 'total_quantity');
        $desc       = $this->getRequest()->getParam('desc', 1);
        $export     = $this->getRequest()->getParam('export', 0);
        $from       = $this->getRequest()->getParam('from', date('01/m/Y') );
        $to         = $this->getRequest()->getParam('to', date('d/m/Y'));
        $name       = $this->getRequest()->getParam('name');
        $area       = $this->getRequest()->getParam('area');
        $province   = $this->getRequest()->getParam('province');
        $district   = $this->getRequest()->getParam('district');
        $sales_from = $this->getRequest()->getParam('sales_from');
        $sales_to   = $this->getRequest()->getParam('sales_to');

        $params = array(
            'page'       => $page,
            'sort'       => $sort,
            'desc'       => $desc,
            'from'       => $from,
            'to'         => $to,
            'name'       => $name,
            'area'       => $area,
            'province'   => $province,
            'district'   => $district,
            'sales_from' => $sales_from,
            'sales_to'   => $sales_to,
            'export'     => $export,
        );

        $limit = LIMITATION;
        $total = 0;

        $QTiming = new Application_Model_Timing();
        $sales = $QTiming->report_by_dealer_all(1, null, $total, $params);

        My_Report_Sales::dealer($sales);
    }

    public function areaAction()
    {
        $from   = $this->getRequest()->getParam('from', date('01/m/Y') );
        $to     = $this->getRequest()->getParam('to', date('d/m/Y') );
        $area   = $this->getRequest()->getParam('area');

        $params = array(
            'from'   => $from,
            'to'     => $to,
            'area'   => $area,
        );

        $QImeiKpi = new Application_Model_ImeiKpi();
        $QArea = new Application_Model_Area();

        $params['kpi'] = true;

        $sell_out = $QImeiKpi->fetchArea($params);
        //var_dump($sellout);die;
        unset($params['kpi']);
        $data_for_point = $QImeiKpi->fetchArea($params);
        $params['get_total_sales'] = true;
        $total_sales = $QImeiKpi->fetchArea($params);
        $total_money = $total_sales['total_value_activated'];
        $point_list = array();

        $all_area = $QArea->fetchAll();
        $region_share = array();

        foreach ($all_area as $_key => $_value)
            $region_share[ $_value['id'] ] = $_value['region_share'];

        //tính point
        foreach($data_for_point as $item)
            $point_list[ $item['area_id'] ] = ( $total_money > 0 and ($region_share[ $item['area_id'] ]/100) > 0 )
        ?  round ( ($item ['total_value_activated']/$total_money) * 60 / ($region_share[ $item['area_id'] ]/100), 2 )
        : 0;

        My_Report_Sales::area($sell_out, $total_sales, $point_list, $params);
    }

    public function storeListAction()
    {
        $id              = $this->getRequest()->getParam('id');
        $name            = $this->getRequest()->getParam('name');
        $address         = $this->getRequest()->getParam('address');
        $area_id         = $this->getRequest()->getParam('area_id');
        $staff_email     = $this->getRequest()->getParam('staff_email');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $d_name          = $this->getRequest()->getParam('d_name');
        $market_type     = $this->getRequest()->getParam('market_type');
        $market_name     = $this->getRequest()->getParam('market_name');
        $store_type      = $this->getRequest()->getParam('store_type');
        $store_level     = $this->getRequest()->getParam('store_level');

        $have_pg         = $this->getRequest()->getParam('have_pg', 0);
        $have_sales      = $this->getRequest()->getParam('have_sales', 0);
        $no_staff        = $this->getRequest()->getParam('no_staff', 0);

        $st_active       = $this->getRequest()->getParam('st_active', 0);
        $st_disable      = $this->getRequest()->getParam('st_disable', 0);

        $export          = $this->getRequest()->getParam('export');

        $total = 0;

        $params = array_filter(array(
            'id'              => $id,
            'name'            => $name,
            'address'         => $address,
            'area_id'         => $area_id,
            'regional_market' => $regional_market,
            'district'        => $district,
            'staff_email'     => $staff_email,
            'have_pg'         => $have_pg,
            'no_staff'        => $no_staff,
            'have_sales'      => $have_sales,
            'd_name'          => $d_name,
            'market_type'     => $market_type,
            'market_name'     => $market_name,
            'store_type'      => $store_type,
            'st_active'       => $st_active,
            'st_disable'      => $st_disable,
            'store_level'     => $store_level,
            'export'          => $export,
        ));

        // $params['export'] = 1;

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;

        if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
            $params['asm'] = $userStorage->id;
        } elseif ($group_id == SALES_ID) {
            $params['sales_store'] = $userStorage->id;
        } elseif ($group_id == LEADER_ID) {
            $params['leader_province'] = $userStorage->id;
        }

        if ($group_id == AM_ID)
            $params['am'] = $userStorage->id;
        if ($group_id == 39)
            $params['admin_bs'] = $userStorage->id;

        $QModel = new Application_Model_Store();

        switch ($export) {
            case 1:
            $stores = $QModel->fetchPagination(1, null, $total, $params);
            My_Report_Sales::storeList($stores);
            break;

            case 2:
            $stores = $QModel->getStorePcList($params);
            My_Report_Sales::storePcList($stores);
            break;
            
            default: break;
        }

    }

    ////////////////////////////////////////////////////
    /**
     * @return [type] [description]
     */
    public function leaderAction()
    {
        $from_date = $this->getRequest()->getParam('from_date', date('01/m/Y'));
        $to_date   = $this->getRequest()->getParam('to_date', date('d/m/Y'));

        /*
         * Thống kê cho leader, về số cửa hàng trực tiếp, số cửa hàng gián tiếp,
         *     số salesman, doanh số của cửa hàng trực tiếp, gián tiếp
         */
        $export_leader_general = $this->getRequest()->getParam('export_leader_general', 0);

        $d = explode('/', $from_date);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $to_date);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $params = array(
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'from'      => $from,
            'to'        => $to,
        );

        $this->view->params = $params;

        if (1 == $export_leader_general) {
            $this->export_leader_general($params);
            exit;
        }
    }

    private function export_leader_general($params)
    {
        set_time_limit(0);
        error_reporting(0);
        ini_set('memory_limit', -1);

        $all_leader_ids     = array();
        $all_leader_ids_str = "";

        $all_leaders         = $this->all_leaders($params['from'], $params['to'], $all_leader_ids);
        $all_leader_ids_str = implode(',', $all_leader_ids);

        // lấy cache region, area
        $QArea   = new Application_Model_Area();
        $areas   = $QArea->get_cache();
        $QRegion = new Application_Model_RegionalMarket();
        $regions = $QRegion->get_cache_all();

        $direct_store_count = $this->direct_store_count($all_leader_ids_str, $params['from'], $params['to']);
        $all_store_count    = $this->all_store_count($params['from'], $params['to']);

        $direct_sellout     = $this->direct_sellout($params['from'], $params['to'], $all_leader_ids_str);
        $all_sellout        = $this->all_sellout($params['from'], $params['to'], $all_leader_ids_str);

        $salesman_count     = $this->count_salesman($params['from'], $params['to']);

        // xuất excel
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'No.',
            'FIRSTNAME',
            'LASTNAME',
            'AREA',
            'PROVINCE',
            'JOINED AT',
            'DIRECT STORE',
            'SELL OUT (DIRECT STORE)',
            'AREA STORE',
            'SELL OUT (AREA STORE)',
            'SALESMAN',
            'PHONE NUMBER',
            'EMAIL',
            'NOTE',
        );

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
        $stt = 1;
        foreach($all_leader_ids as $id){
            $alpha    = 'A';
            $sheet->setCellValue($alpha++.$index, $stt++);
            $sheet->setCellValue($alpha++.$index, $all_leaders[ $id ]['firstname']);
            $sheet->setCellValue($alpha++.$index, $all_leaders[ $id ]['lastname']);
            $sheet->setCellValue($alpha++.$index,
                isset( $areas[ $regions[ $all_leaders[ $id ]['regional_market'] ]['area_id'] ] )
                ? $areas[ $regions[ $all_leaders[ $id ]['regional_market'] ]['area_id'] ]
                : 'N/A'
            );
            $sheet->setCellValue($alpha++.$index,
                isset( $regions[ $all_leaders[ $id ]['regional_market'] ]['name'] )
                ? $regions[ $all_leaders[ $id ]['regional_market'] ]['name']
                : 'N/A'
            );
            $sheet->setCellValue($alpha++.$index, date('d/m/Y', strtotime($all_leaders[ $id ]['joined_at'])));
            $sheet->setCellValue($alpha++.$index, $direct_store_count[ $id ]);
            $sheet->setCellValue($alpha++.$index, $direct_sellout[ $id ]);
            $sheet->setCellValue($alpha++.$index, $all_store_count[ $id ]);
            $sheet->setCellValue($alpha++.$index, $all_sellout[ $id ]);
            $sheet->setCellValue($alpha++.$index, $salesman_count[ $id ]);
            $sheet->getCell($alpha++.$index)->setValueExplicit( $all_leaders[ $id ]['phone_number'] , PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++.$index, $all_leaders[ $id ]['email']);
            $sheet->setCellValue($alpha++.$index, $all_leaders[ $id ]['note']);

            $index++;
        }

        $filename = 'Leader General Report - '.date('Y-m-d H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    private function all_leaders($from, $to, &$all_leader_ids)
    {
        $db = Zend_Registry::get('db');
        $all_leader = array();

            // các leader có mặt trong bảng timing
        $sql = "SELECT DISTINCT s.id
        FROM timing t INNER JOIN staff s
        ON t.leader_id = s.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0
        AND t.from >= ? AND t.from <= ?";
        $timing_leaders = $db->query($sql, array($from, $to));

        $tmp_id_list = array();

        foreach ($timing_leaders as $key => $value)
            $tmp_id_list[] = $value['id'];

        $sql = "SELECT DISTINCT s.id, s.joined_at, s.firstname, s.lastname, s.regional_market, s.email, s.note, s.phone_number
        FROM staff s
        WHERE s.group_id = ? OR FIND_IN_SET(s.id, ?)";
        $current_leaders = $db->query($sql, array(LEADER_ID, implode(',', $tmp_id_list)));

        foreach ($current_leaders as $key => $value) {
            if ( !isset( $all_leader[$value['id']] ) ) {
                $all_leader[$value['id']] = $value;
                $all_leader_ids[] = $value['id'];
            }
        }

        $all_leader_ids = is_array($all_leader_ids) ? array_unique($all_leader_ids) : array();

        return $all_leader;
    }

    private function direct_store_count($all_leader_ids_str, $from, $to)
    {
        $db = Zend_Registry::get('db');
        $sql = "SELECT s.staff_id, COUNT(DISTINCT s.store_id) AS number_store
        FROM store_staff_log s
        WHERE s.is_leader = 1
        AND FIND_IN_SET(s.staff_id, ?)
        AND s.joined_at <= ?
        AND (
            s.released_at IS NULL OR s.released_at >= ?
            )
        GROUP BY s.staff_id
        ORDER BY s.staff_id";
        $direct_store_count_result = $db->query( $sql, array( $all_leader_ids_str, strtotime($to), strtotime($from) ) );

        $direct_store_count = array();

        foreach ($direct_store_count_result as $key => $value) {
            if ( ! isset( $direct_store_count[ $value['staff_id'] ] )  ) {
                $direct_store_count[ $value['staff_id'] ] = $value['number_store'];
            }
        }

        return $direct_store_count;
    }

    private function all_store_count($from, $to)
    {
        $db = Zend_Registry::get('db');
        $sql = "SELECT s.staff_id, COUNT(DISTINCT s.store_id) AS number_store
        FROM store_leader_log s
        WHERE s.joined_at <= ?
        AND (
            s.released_at IS NULL OR s.released_at >= ?
            )
        GROUP BY s.staff_id
        ORDER BY s.staff_id";
        $all_store_count_result = $db->query( $sql, array( strtotime($to), strtotime($from) ) );

        $all_store_count = array();

        foreach ($all_store_count_result as $key => $value) {
            if ( ! isset( $all_store_count[ $value['staff_id'] ] )  ) {
                $all_store_count[ $value['staff_id'] ] = $value['number_store'];
            }
        }

        return $all_store_count;
    }

    private function direct_sellout($from, $to, $all_leader_ids_str)
    {
        $db = Zend_Registry::get('db');

        $sql = "SELECT t.sales_id, COUNT(DISTINCT ts.imei) AS sellout
        FROM timing t INNER JOIN timing_sale ts
        ON t.id=ts.timing_id
        AND t.`from` >= ?
        AND t.`from` <= ?
        AND t.approved_at <> 0
        AND t.approved_at IS NOT NULL
        AND FIND_IN_SET(t.sales_id, ?)
        GROUP BY t.sales_id";
        $direct_sellout_result = $db->query($sql, array( $from . " 00:00:00", $to . " 23:59:59", $all_leader_ids_str ));

        $direct_sellout = array();

        foreach ($direct_sellout_result as $key => $value) {
            if ( ! isset( $direct_sellout[ $value['sales_id'] ] )  ) {
                $direct_sellout[ $value['sales_id'] ] = $value['sellout'];
            }
        }

        return $direct_sellout;
    }

    private function all_sellout($from, $to, $all_leader_ids_str)
    {
        $db = Zend_Registry::get('db');
        $sql = "SELECT t.leader_id, COUNT(DISTINCT ts.imei) AS sellout
        FROM timing t INNER JOIN timing_sale ts
        ON t.id=ts.timing_id
        AND t.`from` >= ?
        AND t.`from` <= ?
        AND t.approved_at <> 0
        AND t.approved_at IS NOT NULL
        AND FIND_IN_SET(t.leader_id, ?)
        GROUP BY t.leader_id";
        $all_sellout_result = $db->query($sql, array( $from . " 00:00:00", $to . " 23:59:59", $all_leader_ids_str ));

        $all_sellout = array();

        foreach ($all_sellout_result as $key => $value) {
            if ( ! isset( $all_sellout[ $value['leader_id'] ] )  ) {
                $all_sellout[ $value['leader_id'] ] = $value['sellout'];
            }
        }

        return $all_sellout;
    }

    private function count_salesman($from, $to)
    {
        $db = Zend_Registry::get('db');
        $sql = "SELECT sl.staff_id, COUNT(DISTINCT ss.staff_id) AS number_sales
        FROM store_leader_log sl INNER JOIN store_staff_log ss
        ON sl.store_id=ss.store_id
        AND ss.is_leader = 1
        AND sl.staff_id <> ss.staff_id
        AND ss.joined_at <= ?
        AND (
            ss.released_at IS NULL OR ss.released_at >= ?
            )
        GROUP BY sl.staff_id";
        $salesman_count_result = $db->query($sql, array(strtotime($to), strtotime($from)));

        $salesman_count = array();

        foreach ($salesman_count_result as $key => $value) {
            if ( ! isset( $salesman_count[ $value['staff_id'] ] )  ) {
                $salesman_count[ $value['staff_id'] ] = $value['number_sales'];
            }
        }

        return $salesman_count;
    }

 
// Store Visit Export
public function storeVisitAction()
{
    $id              = $this->getRequest()->getParam('id');
    $name            = $this->getRequest()->getParam('name');
    $address         = $this->getRequest()->getParam('address');
    $area_id         = $this->getRequest()->getParam('area_id');
    $staff_email     = $this->getRequest()->getParam('staff_email');
    $regional_market = $this->getRequest()->getParam('regional_market');
    $district        = $this->getRequest()->getParam('district');
    $d_name          = $this->getRequest()->getParam('d_name');
    $st_active       = $this->getRequest()->getParam('st_active', 0);
    $st_disable      = $this->getRequest()->getParam('st_disable', 0);
    $export          = $this->getRequest()->getParam('export');
    $total = 0;

    $params = array_filter(array(
        'id'              => $id,
        'name'            => $name,
        'address'         => $address,
        'area_id'         => $area_id,
        'regional_market' => $regional_market,
        'district'        => $district,
        'staff_email'     => $staff_email,
        'd_name'          => $d_name,
        'st_active'       => $st_active,
        'st_disable'      => $st_disable,
        'export'          => $export,
    ));


    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $group_id = $userStorage->group_id;

    if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
        $params['asm'] = $userStorage->id;
    } elseif ($group_id == SALES_ID) {
        $params['sales_store'] = $userStorage->id;
    } elseif ($group_id == LEADER_ID) {
        $params['leader_province'] = $userStorage->id;
    }

    if ($group_id == AM_ID)
        $params['am'] = $userStorage->id;
    if ($group_id == 39)
        $params['admin_bs'] = $userStorage->id;

    $QModel = new Application_Model_Store();

    switch ($export) {
        case 1:
        $stores = $QModel->fetchPagination(1, null, $total, $params);
        My_Report_Sales::storeVisit($stores);
        break;
        
        default: break;
    }

}


}