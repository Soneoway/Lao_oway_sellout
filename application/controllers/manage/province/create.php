<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_RegionalMarket();
    $regional_marketRowset = $QModel->find($id);
    $regional_market = $regional_marketRowset->current();

    $this->view->regional_market = $regional_market;
    $this->view->region_cache = $QModel->get_cache();

    // get district list
    $where = $QModel->getAdapter()->quoteInto('parent = ?', $id);
    $this->view->districts = $QModel->fetchAll($where, 'name');
}

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('province/create');