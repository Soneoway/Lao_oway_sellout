<?php

$from       = $this->getRequest()->getParam('from', date('01/m/Y') );
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$to_day  	=  date('d/m/Y');
$target_date = $this->getRequest()->getParam('from', date('Y-m-d'));
$export 	= $this->getRequest()->getParam('export');


$params = array(
	'from' => $from,
	'to'   => $to,
	'to_day' => $to_day,
	'target_date' => $target_date,
	'export'	=> $export
);

$QArea = new Application_Model_Area();
$QTiming = new Application_Model_Timing();

$areas = $QArea->fetchAll(null, 'name');

if(isset($export) and $export == 1){
	$data = $QTiming->reportTimng($params);
	$this->_exportreporttiming($data);

}elseif(isset($export) and $export == 2){
	$area_report = $QTiming->AreaTimingReport($params);
	$this->_exportreportAreatiming($area_report);

}elseif(isset($export) and $export == 3){

	$oppo_general_report = $QTiming->OppoGeneralReport($params);
	$oppo_hero_report = $QTiming->OppoHeroReport($params);
	$oppo_model_report = $QTiming->OppoModelReport($params);
	$oppo_area_model_report = $QTiming->OppoAreaModelReport($params);
	$oppo_score_report = $QTiming->OppoScoreReport($params);
	$this->_exportOppoReport($oppo_general_report,$oppo_hero_report,$oppo_model_report,$oppo_area_model_report,$oppo_score_report);
}

if (!empty($_GET)) {
	$data = $QTiming->reportTimng($params);
	$area_report = $QTiming->AreaTimingReport($params);
	
} 

$this->view->params = $params;
$this->view->data = $data;
$this->view->area_report = $area_report;
$this->view->areas           = $areas;
$this->_helper->viewRenderer->setRender('report-timing/report-timing');
?>