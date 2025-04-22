<?php
$id = $this->getRequest()->getParam('id');
$QModel = new Application_Model_SubDistrict();
//$QRegoinModel = new Application_Model_RegionalMarket();
$this->view->district = $QModel->get_subdist();
$flashMessenger = $this->_helper->flashMessenger;

if ($id) {
    $sub_districtRowset = $QModel->find($id);
    $sub_district = $sub_districtRowset->current();

    if (!$sub_district) {
        $flashMessenger->setNamespace('error')->addMessage('Invalid ID');
        $this->_redirect(HOST.'manage/sub-district');
    }

    $this->view->sub_district = $sub_district;
}

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('sub-district/create');