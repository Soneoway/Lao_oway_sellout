<?php

$bs_area_id = $this->getRequest()->getParam('bs_area_id');

$params = array(
    'bs_area_id' => $bs_area_id
);


$QBsAreaLog = new Application_Model_BsAreaLog();
$bs_log = $QBsAreaLog->short_report_bs_area_control_log($params);
$this->view->bs_log = $bs_log;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('bs-area-control/partials/bs-area-control-short-report');

?>