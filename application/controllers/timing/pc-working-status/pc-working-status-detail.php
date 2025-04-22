<?php
$type = $this->getRequest()->getParam('type');
$area_id = $this->getRequest()->getParam('area_id');
$org_id = $this->getRequest()->getParam('org_id');
$channel = $this->getRequest()->getParam('channel');
$from = $this->getRequest()->getParam('from');
$to = $this->getRequest()->getParam('to');

$params = array(
    'type'        => $type,
    'area_id'     => $area_id,
    'org_id'      => $org_id,
    'channel'     => $channel,
    'from'        => $from,
    'to'          => $to,
);

$QPcResingnation = new Application_Model_PcResingnation();
$pc_resingnation = $QPcResingnation->getPcWorkingStatusDetail($params);

$this->view->pc_resingnation = $pc_resingnation;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('pc-working-status/partials/modal-detail');

?>