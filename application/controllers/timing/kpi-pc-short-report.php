<?php
$gkl_id 	= $this->getRequest()->getParam('gkl_id');
$staff_id 	= $this->getRequest()->getParam('staff_id');
$kpi_type 	= $this->getRequest()->getParam('kpi_type');

// Range Date [Current]
$first_day_this_month = date('Y-m-01');
$last_day_this_month = date('Y-m-t');

// Range Date [Last 1 Month]
$tmp_start_01 = new DateTime( $first_day_this_month );
$tmp_start_01->modify( 'first day of previous month' );
$first_day_last_month = $tmp_start_01->format( 'Y-m-d' );

$tmp_end_01 = new DateTime( $first_day_this_month );
$tmp_end_01->modify( 'last day of previous month' );
$last_day_last_month = $tmp_end_01->format( 'Y-m-d' );

if ($kpi_type == "last") {
	$from = $first_day_last_month;
	$to = $last_day_last_month;
} else {
	$from = $first_day_this_month;
	$to = $last_day_this_month;
} 

$params = array(
	'gkl_id'	=> $gkl_id,
    'staff_id' 	=> $staff_id,
    'from' 		=> $from,
    'to' 		=> $to,
);

//print_r($params);
$QTiming = new Application_Model_Timing();
$sales   = $QTiming->short_report_by_kpi_pc($params);
//print_r($sales);

$this->view->sales = $sales;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('partials/kpi-pc-short-report');