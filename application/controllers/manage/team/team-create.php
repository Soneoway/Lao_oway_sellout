<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_Team();
    $teamRowset = $QModel->find($id);
    $team = $teamRowset->current();

    $this->view->team = $team;    
}

$QDepartment = new Application_Model_Department();
$this->view->departments = $QDepartment->get_cache();

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('team/create');