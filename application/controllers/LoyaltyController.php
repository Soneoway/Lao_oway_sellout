<?php
class LoyaltyController extends My_Controller_Action
{
    function init(){
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    function planConfigAction(){
        $id = $this->getRequest()->getParam('id');
        $QLoyaltyPlan = new Application_Model_LoyaltyPlan();
        $QLoyaltyPlanRule = new Application_Model_LoyaltyPlanRule();

        $whereLoyaltyPlan = $QLoyaltyPlan->getAdapter()->quoteInto('id = ?', $id);
        $loyaltyPlan = $QLoyaltyPlan->fetchRow($whereLoyaltyPlan);

        if ($loyaltyPlan){
            $currentTime = date('Y-m-d H:i:s');
            $whereLoyaltyPlanRule = array();
            $whereLoyaltyPlanRule[] = $QLoyaltyPlanRule->getAdapter()->quoteInto('loyalty_plan_id = ?', $id);
            $whereLoyaltyPlanRule[] = $QLoyaltyPlanRule->getAdapter()->quoteInto('from_date <= ?', $currentTime);
            $whereLoyaltyPlanRule[] = $QLoyaltyPlanRule->getAdapter()->quoteInto('to_date IS NULL OR to_date >= ?', $currentTime);

            $loyaltyPlanRule = $QLoyaltyPlanRule->fetchAll($whereLoyaltyPlanRule);
            $this->view->loyaltyPlanRule = $loyaltyPlanRule;
        }

        $this->view->loyaltyPlan = $loyaltyPlan;
    }

    function createAction(){
        $id = $this->getRequest()->getParam('id');
        $dealer_id = $this->getRequest()->getParam('dealer_id');
        $QDealerLoyalty = new Application_Model_DealerLoyalty();
        $QDistributor = new Application_Model_Distributor();
        $QLoyaltyPlan = new Application_Model_LoyaltyPlan();

        $this->view->loyaltyPlanCached = $QLoyaltyPlan->get_cache();

        $flashMessenger = $this->_helper->flashMessenger;

        $distributorLoyalty = null;

        try {

            if ($id){

                $whereDistributorLoyalty = $QDealerLoyalty->getAdapter()->quoteInto('id = ?', $id);
                $distributorLoyalty = $QDealerLoyalty->fetchRow($whereDistributorLoyalty, 'from_date DESC');

                if (!$distributorLoyalty)
                    throw new Exception('Please choose Dealer');

                $whereDistributor = $QDistributor->getAdapter()->quoteInto('id = ?', $distributorLoyalty['dealer_id']);
                $distributor = $QDistributor->fetchRow($whereDistributor);

                if (!$distributor)
                    throw new Exception('The Dealer is invalid');

                $whereDistributorLoyalty = $QDealerLoyalty->getAdapter()->quoteInto('id = ?', $id);
                $distributorLoyalty = $QDealerLoyalty->fetchRow($whereDistributorLoyalty, 'from_date DESC');

                $this->view->distributorLoyalty = $distributorLoyalty;
            } elseif ($dealer_id){

                $whereDistributor = $QDistributor->getAdapter()->quoteInto('id = ?', $dealer_id);
                $distributor = $QDistributor->fetchRow($whereDistributor);

                if (!$distributor)
                    throw new Exception('The Dealer is invalid');

                $whereDistributorLoyalty = array();
                $whereDistributorLoyalty[] = $QDealerLoyalty->getAdapter()->quoteInto('dealer_id = ?', $dealer_id);
                if ($id)
                    $whereDistributorLoyalty[] = $QDealerLoyalty->getAdapter()->quoteInto('id <> ?', $id);


                $this->view->distributorLoyaltyHistory = $QDealerLoyalty->fetchAll($whereDistributorLoyalty, 'from_date DESC');

            } else
                throw new Exception('The Dealer is invalid');


            $this->view->distributor = $distributor;

            if ($this->getRequest()->getMethod() == 'POST'){
                $userStorage = Zend_Auth::getInstance()->getStorage()->read();

                $loyalty_plan_id    = $this->getRequest()->getParam('loyalty_plan_id');
                $from_date          = $this->getRequest()->getParam('from_date');
                $to_date            = $this->getRequest()->getParam('to_date');

                $sales_comparison   = $this->getRequest()->getParam('sales_comparison');
                $sales              = $this->getRequest()->getParam('sales');
                $sales_per_unit     = $this->getRequest()->getParam('sales_per_unit');
                $memorandum         = $this->getRequest()->getParam('memorandum');
                $guarantee_money    = $this->getRequest()->getParam('guarantee_money');
                $note               = $this->getRequest()->getParam('note');
                $sales_share        = $this->getRequest()->getParam('sales_share');
                $pictures_share     = $this->getRequest()->getParam('pictures_share');

                $currentTime = date('Y-m-d H:i:s');

                // kiem tra from & to
                if (!$from_date or !$to_date)
                    throw new Exception('Please choose From and To Date');

                if ($from_date){
                    list($year, $month, $day) = explode('-', $from_date);
                    if (strlen($year) != 4 or $year < 2015)
                        throw new Exception('Please choose Valid From and To Date');

                    if (strlen($month) != 2 or $month < 1 or $month > 12)
                        throw new Exception('Please choose Valid From and To Date');

                    if (strlen($day) != 2 or $day < 1 or $month > 31)
                        throw new Exception('Please choose Valid From and To Date');
                }

                if ($to_date){
                    list($year, $month, $day) = explode('-', $to_date);
                    if (strlen($year) != 4 or $year < 2015)
                        throw new Exception('Please choose Valid From and To Date');

                    if (strlen($month) != 2 or $month < 1 or $month > 12)
                        throw new Exception('Please choose Valid From and To Date');

                    if (strlen($day) != 2 or $day < 1 or $month > 31)
                        throw new Exception('Please choose Valid From and To Date');
                }

                if ($from_date > $to_date)
                    throw new Exception('Please choose Valid From and To Date');

                $whereDistributorLoyalty = array();
                $whereDistributorLoyalty[] = $QDealerLoyalty->getAdapter()->quoteInto('dealer_id = ?', $dealer_id);
                if ($id)
                    $whereDistributorLoyalty[] = $QDealerLoyalty->getAdapter()->quoteInto('id <> ?', $id);

                $listDistributorLoyalty = $QDealerLoyalty->fetchAll($whereDistributorLoyalty);
                if ($listDistributorLoyalty->count()){
                    foreach ($listDistributorLoyalty as $row){
                        if (
                            ($row['from_date'] <= $from_date and $from_date <= $row['to_date'])
                            or ($row['from_date'] <= $to_date and $to_date <= $row['to_date'])
                            or ($to_date <= $row['from_date'] and $row['to_date'] <= $to_date)
                        )
                            throw new Exception('Please choose Valid From and To Date');
                    }
                }
                // End kiem tra from & to

                if ($distributorLoyalty){
                    $whereDistributorLoyalty = $QDealerLoyalty->getAdapter()->quoteInto('id = ?', $id);

                    $QDealerLoyalty->update(array(
                        'sales'             => $sales,
                        /*'sales_comparison'  => $sales_comparison,
                        'sales_per_unit'    => $sales_per_unit,*/
                        'from_date'         => $from_date,
                        'to_date'           => $to_date,
                        'sales_share'       => $sales_share,
                        'pictures_share'    => $pictures_share,
                        'loyalty_plan_id'   => $loyalty_plan_id,
                        /*'memorandum'        => ($memorandum ? 1 : 0),*/
                        'memorandum'        => $memorandum,
                        'guarantee_money'   => $guarantee_money,
                        'note'              => $note,
                        'updated_at'        => $currentTime,
                        'updated_by'        => $userStorage->id,
                    ), $whereDistributorLoyalty);

                } else {
                    $QDealerLoyalty->insert(array(
                        'dealer_id'         => $dealer_id,
                        'sales'             => $sales,
                        'from_date'         => $from_date,
                        'to_date'           => $to_date,
                        /*'sales_comparison'  => $sales_comparison,
                        'sales_per_unit'    => $sales_per_unit,*/
                        'sales_share'       => $sales_share,
                        'pictures_share'    => $pictures_share,
                        'loyalty_plan_id'   => $loyalty_plan_id,
                        /*'memorandum'        => ($memorandum ? 1 : 0),*/
                        'memorandum'        => $memorandum,
                        'guarantee_money'   => $guarantee_money,
                        'note'              => $note,
                        'created_at'        => $currentTime,
                        'created_by'        => $userStorage->id,
                    ));
                }

                $flashMessenger->setNamespace('success')->addMessage('Done');
                $this->redirect('/loyalty/list');

            } // end of POST


        } catch (Exception $e){
            $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            $this->redirect('/loyalty/list');
        }
    }

    function indexAction(){

        $id             = $this->getRequest()->getParam('dealer_id');
        $page           = $this->getRequest()->getParam('page', 1);
        $name           = $this->getRequest()->getParam('name');
        $from_date      = $this->getRequest()->getParam('from_date');
        $to_date        = $this->getRequest()->getParam('to_date');
        $area           = $this->getRequest()->getParam('area');
        $regional_market= $this->getRequest()->getParam('regional_market');
        $loyalty_plan_id= $this->getRequest()->getParam('loyalty_plan_id');
        $district       = $this->getRequest()->getParam('district');
        $export         = $this->getRequest()->getParam('export');
        $export_type    = $this->getRequest()->getParam('export_type');

        $QArea            = new Application_Model_Area();
        $areas            = $QArea->get_cache();

        $QRegionalMarket  = new Application_Model_RegionalMarket();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;

        $loyalty_plan_id = is_array($loyalty_plan_id) ? array_filter($loyalty_plan_id) : null;
        $params = array(
            'name'          => $name,
            'area'          => $area,
            'from_date'     => $from_date,
            'to_date'       => $to_date,
            'regional_market' => $regional_market,
            'district'      => $district,
            'loyalty_plan_id'      => $loyalty_plan_id,
            'export'        => $export,
        );

        if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
            $params['asm'] = $userStorage->id;
        } elseif ($group_id == SALES_ID) {
            $params['sales_store'] = $userStorage->id;
        } elseif ($group_id == LEADER_ID) {
            $params['leader_province'] = $userStorage->id;
        }

        if ($area) {
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }

        if ($regional_market) {
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }

        $limit = LIMITATION;

        $QTiming = new Application_Model_Timing();
        $QDistributor = new Application_Model_Distributor();
        $total = 0;

        $result = $QTiming->report_loyalty_plan($page, $limit, $total, $params);

        $this->view->params            = $params;
        $this->view->areas            = $areas;
        $distributorCached = $QDistributor->get_cache();
        $this->view->distributorCached = $distributorCached;
        $this->view->result = $result;
        $this->view->offset = $limit*($page-1);
        $this->view->total  = $total;
        $this->view->limit  = $limit;
        $this->view->url    = HOST.'loyalty'.( $params ? '?'.http_build_query($params).'&' : '?' );

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;

        $QLoyaltyPlan = new Application_Model_LoyaltyPlan();
        $loyaltyPlanCached = $QLoyaltyPlan->get_cache();
        $this->view->loyaltyPlanCached = $loyaltyPlanCached;

        // Export
        if (isset($export) and $export){

            if ($from_date){
                list($day, $month, $year) = explode('/', $from_date);
                $from_date = $year.'-'.$month.'-'.$day;
            }

            if ($to_date){
                list($day, $month, $year) = explode('/', $to_date);
                $to_date = $year.'-'.$month.'-'.$day;
            }

            set_time_limit(0);
            error_reporting(0);
            ini_set('display_error', 0);
            ini_set('memory_limit', -1);

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
            $config = $config->toArray();

            $cf_tunnel = $config['resources']['db_tunnel']['params'];

            $con=mysqli_connect($cf_tunnel['host'],$cf_tunnel['username'],$cf_tunnel['password'],$cf_tunnel['dbname'], $cf_tunnel['port']);

            mysqli_set_charset($con, "utf8");

            // Check connection
            if (mysqli_connect_errno())
            {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            if ($export_type == 1){

                require_once 'PHPExcel.php';
                $PHPExcel = new PHPExcel();
                $heads = array(
                    'Dealer ID',
                    'Dealer Name',
                    'Area',
                    'Province',
                    'Plan',
                    'Result Sell In',
                    'Result Sell Out',
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
    
                $resultSql = mysqli_query($con,$result);
                while($row = @mysqli_fetch_array($resultSql)){
                    $alpha    = 'A';
                    $sheet->setCellValue($alpha++.$index, $row['id']);
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['title']) ? $distributorCached[$row['id']]['title'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['area_name']) ? $distributorCached[$row['id']]['area_name'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['region_name']) ? $distributorCached[$row['id']]['region_name'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($loyaltyPlanCached[$row['loyalty_plan_id']]) ? $loyaltyPlanCached[$row['loyalty_plan_id']] : 0) );
                    $sheet->setCellValue($alpha++.$index, (isset($row['total_result_sell_in']) ? $row['total_result_sell_in'] : 0) );
                    $sheet->setCellValue($alpha++.$index, (isset($row['total_result_sell_out']) ? $row['total_result_sell_out'] : 0) );
                    $index++;
                }

                $filename = 'REPORT_GENERAL_DIAMOND_PLAN_'.date('d-m-Y H:i:s');
                $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

                $objWriter->save('php://output');
                exit;

            } elseif ($export_type == 2){

                $QGood = new Application_Model_Good();
                $goodsCached = $QGood->get_cache();

                $QGoodColor = new Application_Model_GoodColor();
                $goodColorsCached = $QGoodColor->get_cache();

                $arrPlanType = unserialize(LOYALTY_PLAN_RULE_TYPE);

                require_once 'PHPExcel.php';
                $PHPExcel = new PHPExcel();
                $heads = array(
                    'Dealer ID',
                    'Dealer Name',
                    'Area',
                    'Province',
                    'Plan',
                );

                // get list rules
                $rulesLists = $QTiming->getListRule(array(
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                ));

                $productsList = array();
                if ($rulesLists){
                    foreach ($rulesLists as $item){
                        $productsList[$item['type']][$item['product_id']][$item['color']] = $item['color'];

                    }
                }

                foreach ($productsList as $planType => $item){
                    foreach ($item as $productId=>$item2){
                        foreach ($item2 as $colorId=>$item3){
                            if (!$colorId){
                                $heads[] = (isset($arrPlanType[$planType]) ? $arrPlanType[$planType] : '')
                                    . '-' . ( isset($goodsCached[$productId]) ? $goodsCached[$productId] : '') . ' Quantity';

                                $heads[] = (isset($arrPlanType[$planType]) ? $arrPlanType[$planType] : '')
                                    . '-' . ( isset($goodsCached[$productId]) ? $goodsCached[$productId] : '') . ' Result';

                                break;
                            }

                            $heads[] = (isset($arrPlanType[$planType]) ? $arrPlanType[$planType] : '')
                                . '-' . ( isset($goodsCached[$productId]) ? $goodsCached[$productId] : '')
                                . '-' . ( isset($goodColorsCached[$colorId]) ? $goodColorsCached[$colorId] : '')
                                . ' Quantity';

                            $heads[] = (isset($arrPlanType[$planType]) ? $arrPlanType[$planType] : '')
                                . '-' . ( isset($goodsCached[$productId]) ? $goodsCached[$productId] : '')
                                . '-' . ( isset($goodColorsCached[$colorId]) ? $goodColorsCached[$colorId] : '')
                                . ' Result';
                        }

                    }
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

                $resultSql = mysqli_query($con,$result);

                while($row = @mysqli_fetch_array($resultSql)){
                    $alpha    = 'A';
                    $sheet->setCellValue($alpha++.$index, $row['id']);
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['title']) ? $distributorCached[$row['id']]['title'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['area_name']) ? $distributorCached[$row['id']]['area_name'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['region_name']) ? $distributorCached[$row['id']]['region_name'] : '') );
                    $sheet->setCellValue($alpha++.$index, (isset($loyaltyPlanCached[$row['loyalty_plan_id']]) ? $loyaltyPlanCached[$row['loyalty_plan_id']] : '') );

                    foreach ($productsList as $planType => $item){
                        foreach ($item as $productId=>$item2){
                            foreach ($item2 as $colorId=>$item3){
                                $result = $QTiming->getResultByProductAndPlan(array(
                                    'from_date'     => $from_date,
                                    'to_date'       => $to_date,
                                    'dealer_id'     => $row['id'],
                                    'product_id'    => $productId,
                                    'color'         => $colorId,
                                ), $con);

                                if ($planType == LOYALTY_PLAN_RULE_TYPE_SELL_IN){
                                    $sheet->setCellValue($alpha++.$index, ($result['total_sell_in'] ? $result['total_sell_in'] : 0) );
                                    $sheet->setCellValue($alpha++.$index, ($result['total_result_sell_in'] ? $result['total_result_sell_in'] : 0) );
                                } elseif($planType == LOYALTY_PLAN_RULE_TYPE_SELL_OUT){
                                    $sheet->setCellValue($alpha++.$index, ($result['total_sell_out'] ? $result['total_sell_out'] : 0) );
                                    $sheet->setCellValue($alpha++.$index, ($result['total_result_sell_out'] ? $result['total_result_sell_out'] : 0) );
                                }
                            }
                        }
                    }

                    $index++;
                }

                $filename = 'REPORT_DETAIL_DIAMOND_PLAN_'.date('d-m-Y H:i:s');
                $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

                $objWriter->save('php://output');
                exit;
            }
        }
    }

    function listAction(){
        $id             = $this->getRequest()->getParam('dealer_id');
        $page           = $this->getRequest()->getParam('page', 1);
        $name           = $this->getRequest()->getParam('name');
        $from_date      = $this->getRequest()->getParam('from_date');
        $to_date        = $this->getRequest()->getParam('to_date');
        $area           = $this->getRequest()->getParam('area');
        $regional_market= $this->getRequest()->getParam('regional_market');
        $loyalty_plan_id= $this->getRequest()->getParam('loyalty_plan_id');
        $district       = $this->getRequest()->getParam('district');
        $export         = $this->getRequest()->getParam('export');
        $export_type    = $this->getRequest()->getParam('export_type');

        $QArea            = new Application_Model_Area();
        $areas            = $QArea->get_cache();

        $QRegionalMarket  = new Application_Model_RegionalMarket();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id = $userStorage->group_id;

        $loyalty_plan_id = is_array($loyalty_plan_id) ? array_filter($loyalty_plan_id) : null;
        $params = array(
            'name'          => $name,
            'area'          => $area,
            'from_date'     => $from_date,
            'to_date'       => $to_date,
            'regional_market' => $regional_market,
            'district'      => $district,
            'loyalty_plan_id'      => $loyalty_plan_id,
            'export'        => $export,
        );

        if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
            $params['asm'] = $userStorage->id;
        } elseif ($group_id == SALES_ID) {
            $params['sales_store'] = $userStorage->id;
        } elseif ($group_id == LEADER_ID) {
            $params['leader_province'] = $userStorage->id;
        }

        if ($area) {
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }

        if ($regional_market) {
            $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }

        $limit = LIMITATION;

        $QDealerLoyalty = new Application_Model_DealerLoyalty();
        $QDistributor = new Application_Model_Distributor();
        $total = 0;

        $result = $QDealerLoyalty->fetchPagination($page, $limit, $total, $params);

        $this->view->params            = $params;
        $this->view->areas            = $areas;
        $distributorCached = $QDistributor->get_cache();
        $this->view->distributorCached = $distributorCached;
        $this->view->result = $result;
        $this->view->offset = $limit*($page-1);
        $this->view->total  = $total;
        $this->view->limit  = $limit;
        $this->view->url    = HOST.'loyalty/list'.( $params ? '?'.http_build_query($params).'&' : '?' );

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        $this->view->messages_error = $messages_error;

        $QLoyaltyPlan = new Application_Model_LoyaltyPlan();
        $loyaltyPlanCached = $QLoyaltyPlan->get_cache();
        $this->view->loyaltyPlanCached = $loyaltyPlanCached;
    }
}