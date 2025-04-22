<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_ContractTerm();
    $rowset = $QModel->find($id);
    $contract_term = $rowset->current();

    $this->view->contract_term = $contract_term;
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('contract-term/create');