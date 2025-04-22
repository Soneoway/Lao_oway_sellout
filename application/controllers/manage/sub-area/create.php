<?php
$id = $this->getRequest()->getParam('id');
$QModel = new Application_Model_SubArea();
//$QRegoinModel = new Application_Model_RegionalMarket();
$this->view->staff = $QModel->get_subarea();
$flashMessenger = $this->_helper->flashMessenger;

if ($id) {
    $sub_areaRowset = $QModel->find($id);
    $sub_area = $sub_areaRowset->current();

    if (!$sub_area) {
        $flashMessenger->setNamespace('error')->addMessage('Invalid ID');
        $this->_redirect(HOST.'manage/sub-area');
    }

    $this->view->sub_area = $sub_area;
}

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('sub-area/create');