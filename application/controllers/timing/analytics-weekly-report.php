<?php

$from       = $this->getRequest()->getParam('from', date('d/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export');

$params = array(
    'from'  => $from,
    'to'    => $to,
);
//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id; 

if ( isset($export) && $export ) {

    // if ($export == 1) { $this->_exportExcelAreaCoverage($params); }
    // if ($export == 2) { $this->_exportExcelInventorySellout($params); }
    // if ($export == 3) { $this->_exportExcelInventoryRemain(); }
    // if ($export == 4) { $this->_exportExcelInventoryDistributorChain(); }
    // if ($export == 5) { $this->_exportExcelSalePerformance($params); }

    if ($export == 6) { $this->_exportExcelMondayReport($params); }
    if ($export == 7) { $this->_exportExcelFridayReport($params); }

    // if ($export == 8) { $this->_exportExcelT3Report($params); }

    if ($export == 9) { $this->_exportExcelAreaGFK($params); }
    if ($export == 10) { $this->_exportExcelRmGFK($params); }
    if ($export == 11) { $this->_exportExcelPcByModel($params); }

    //khuan
    if($export == 17) {
        $QGoodKpiLog = new Application_Model_GoodKpiLog();
        $resualt = $QGoodKpiLog->AchieveRemark($params);

        $this->_exportExcelAchieveRemark($resualt); 
    }

    // if ($export == 12) { $this->_exportExcelSaleHero($params); }
    // if ($export == 13) { $this->_exportExcelAsmHero($params); }
    // if ($export == 14) { $this->_exportExcelHeroPerformance($params); }

    if ($export == 15) { $this->_exportExcelOprStock($params); }

    if ($export == 16) { 
        $params['ach_target'] = $this->getRequest()->getParam('ach_target');
        $this->_exportExcelAchieveReport($params); 
    }

} 

$this->view->params = $params;

?>