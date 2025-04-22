<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_Title();
    $titleRowset = $QModel->find($id);
    $title = $titleRowset->current();

    $this->view->title = $title;
}

$QTeam = new Application_Model_Team();
$this->view->teams = $QTeam->get_cache();

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('title/create');