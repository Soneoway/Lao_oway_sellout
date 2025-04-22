<?php
$id = $this->getRequest()->getParam('id');

if ($id) {
	$QDistributor = new Application_Model_Distributor();
    $distributor = $QDistributor->find($id);
    $distributor = $distributor->current();

	if ($distributor) {
		// lấy các store thuộc distributor này
		$QStore = new Application_Model_Store();
		$where = $QStore->getAdapter()->quoteInto('d_id = ?', $id);
		$stores = $QStore->fetchAll($where);

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();
        $QRegion = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegion->get_cache_all();

		$this->view->stores = $stores;
		$this->view->distributor = $distributor;
	} else {
		$this->_redirect(HOST.'manage/dealer');
	}

	$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
	
	$this->_helper->viewRenderer->setRender('dealer/edit');
} else {
	$this->_redirect(HOST.'manage/dealer');
}
