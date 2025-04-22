<?php
$id = $this->getRequest()->getParam('id');
$QModel = new Application_Model_RegionalMarket();
$this->view->provinces = $QModel->get_cache();
$flashMessenger = $this->_helper->flashMessenger;

if ($id) {
    $regional_marketRowset = $QModel->find($id);
    $regional_market = $regional_marketRowset->current();

    if (!$regional_market) {
        $flashMessenger->setNamespace('error')->addMessage('Invalid ID');
        $this->_redirect(HOST.'manage/district');
    }

    $this->view->regional_market = $regional_market;
}

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('district/create');