<?php
$mn_id = $this->getRequest()->getParam('mn_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$QMarketType = new Application_Model_MarketType();
$market_type_list = $QMarketType->fetchAll(null, 'name');
$this->view->market_type = $market_type_list;

$QArea = new Application_Model_Area();

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

	$where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
    $this->view->areas = $QArea->fetchAll($where_area, 'name');

} else {
	$this->view->areas = $QArea->fetchAll(null, 'name');
}

$QMarketName = new Application_Model_MarketName();
$where = $QMarketName->getAdapter()->quoteInto('id = ?', $mn_id);
$market_name_detail = $QMarketName->fetchRow($where, 'name');

$this->view->market_name_detail = $market_name_detail;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/market-name/create');