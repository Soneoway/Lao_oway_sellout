<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
$QSalesTeam = new Application_Model_SalesTeam();
$salesTeamRowset = $QSalesTeam->find($id);
$sales_team = $salesTeamRowset->current();

$this->view->sales_team = $sales_team;

//get staff assign
$QSalesTeamStaff = new Application_Model_SalesTeamStaff();
$where = array();
$where[] = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id = ?', $id);
$where[] = $QSalesTeamStaff->getAdapter()->quoteInto('is_leader = ?', 0);
$data = $QSalesTeamStaff->fetchAll($where);
if ($data->count()){
$tem = array();
foreach ($data as $item)
$tem[] = $item->staff_id;
$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
$this->view->staffs = $QStaff->fetchAll($where);
}

//leader
$where = array();
$where[] = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id = ?', $id);
$where[] = $QSalesTeamStaff->getAdapter()->quoteInto('is_leader = ?', 1);
$data = $QSalesTeamStaff->fetchAll($where);
if ($data->count()){
$tem = array();
foreach ($data as $item)
$tem[] = $item->staff_id;
$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id IN (?)', $tem);
$this->view->staffs1 = $QStaff->fetchAll($where);
}
}

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll();

$QRegionalMarket = new Application_Model_RegionalMarket();
$this->view->regional_market_all = $QRegionalMarket->get_cache();

if (isset($sales_team) and $sales_team) {
$where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $sales_team->area_id);

$this->view->regional_markets = $QRegionalMarket->fetchAll($where);
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('sales-team/create');