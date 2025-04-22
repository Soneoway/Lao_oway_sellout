<?php
$page            = $this->getRequest()->getParam('page', 1);
$area_id         = $this->getRequest()->getParam('area_id');
$org_id        = $this->getRequest()->getParam('org_id');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('t/m/Y'));
$export          = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = 0;

$params = array(
    'area_id'         => $area_id,
    'org_id'          => $org_id,
    'from'            => $from,
    'to'              => $to,
    'export'          => $export,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();

if (in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}
$this->view->areas = $areas;

$QpcResingnation = new Application_Model_PcResingnation();

$this->view->channels = $QpcResingnation->getAllChannel();


if ($export && $export == 1) {
    $pc_resingnation = $QpcResingnation->fetchPagination(null, null, $total, $params);
    $this->_exportCompetitorReport($pc_resingnation,$params);
}

$pc_resingnation = $QpcResingnation->fetchPagination($page, $limit, $total, $params);

$this->view->params = $params;
$this->view->pc_resingnation  = $pc_resingnation;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/pc-working-status/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-working-status/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-working-status/index');