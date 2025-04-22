<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_Product();
    $rowset = $QModel->find($id);
    $product = $rowset->current();

    $this->view->product = $product;

    //get models
    $QModel = new Application_Model_Model();
    $where = $QModel->getAdapter()->quoteInto('product_id = ?', $id);
    $this->view->models = $QModel->fetchAll($where);
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('product/create');