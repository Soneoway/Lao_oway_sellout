<?php
$area_id = $this->getRequest()->getParam('area_id');
$area_name = $this->getRequest()->getParam('area_name');
$month_year = $this->getRequest()->getParam('month_year');

$params = array(
	'area_id' 	 => $area_id,
	'area_name'  => $area_name,
	'month_year' => $month_year,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$QArea = new Application_Model_Area();

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

	$where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
    $this->view->areas = $QArea->fetchAll($where_area, 'name');

} else {
	$this->view->areas = $QArea->fetchAll(null, 'name');
}

$QMKHL = new Application_Model_MonthlyKpiHeadcountList();

$where = array();
$where[] = $QMKHL->getAdapter()->quoteInto('area_name = ?', $area_name);
$where[] = $QMKHL->getAdapter()->quoteInto('month_year = ?', $month_year);

$mkhd_list = $QMKHL->fetchAll($where)->ToArray();

$this->view->monthly_kpi_headcount_detail = $mkhd_list;
$this->view->area_id = $area_id;
$this->view->params = $params;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/monthly-headcount-kpi/create');