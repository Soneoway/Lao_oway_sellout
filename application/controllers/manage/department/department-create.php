<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_Department();
    $depRowset = $QModel->find($id);
    $dep = $depRowset->current();

    $this->view->dep = $dep;
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('department/create');