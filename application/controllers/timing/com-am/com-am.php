<?php

$from   = $this->getRequest()->getParam('from', date('01/m/Y'));
$to     = $this->getRequest()->getParam('to', date('d/m/Y'));
$export = $this->getRequest()->getParam('export');

$total = 0;

$params = array(
    'from'  => $from,
    'to'    => $to,
    'flag'  => 1,
);
//print_r($params);

$QAm = new Application_Model_Am();

$am_list = $QAm->fetchPagination(null, null, $total, $params);

if ( isset($export) && $export ) {
    // if ($export == 1) { $this->_exportExcelAreaCoverage($params); }
} 

$this->view->params = $params;
$this->view->am_list = $am_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('com-am/partials/list');
} else
    $this->_helper->viewRenderer->setRender('com-am/index');

?>