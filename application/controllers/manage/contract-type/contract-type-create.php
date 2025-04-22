<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_ContractType();
    $rowset = $QModel->find($id);
    $contract_type = $rowset->current();

    $this->view->contract_type = $contract_type;
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('contract-type/create');