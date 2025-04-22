<?php

$cp_id = $this->getRequest()->getParam('id');

if ( isset($cp_id) && $cp_id ) {

	$QCompetitorProduct = new Application_Model_CompetitorProduct();

	$where = $QCompetitorProduct->getAdapter()->quoteInto('id = ?', $cp_id);
	$this->view->product_info = $QCompetitorProduct->fetchRow($where)->ToArray();

}

$QCompetitorBrand = new Application_Model_CompetitorBrand();
$this->view->brand_list = $QCompetitorBrand->fetchAll(null, 'name');

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('competitor-product/create');
