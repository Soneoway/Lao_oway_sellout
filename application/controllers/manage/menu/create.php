<?php
$id = $this->getRequest()->getParam('id');
$QModel = new Application_Model_Menu();
$this->view->parents = $QModel->get_cache();
if ($id) {
    $QModel = new Application_Model_Menu();
    $menuRowset = $QModel->find($id);
    $menu = $menuRowset->current();

    $this->view->menu = $menu;
    $this->view->parents = $QModel->get_cache();

    // get district list
    $where = $QModel->getAdapter()->quoteInto('parent_id = ?', $id);
    $this->view->main_menu = $QModel->fetchAll($where, 'title');
}


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('menu/create');