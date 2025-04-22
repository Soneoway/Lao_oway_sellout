<?php
$id = $this->getRequest()->getParam('id');

$flashMessenger = $this->_helper->flashMessenger;

try {
    $QCategory = new Application_Model_NotificationCategory();
    $category_check = $QCategory->find($id);
    $category_check = $category_check->current();

    if (!$category_check) throw new Exception("Invalid ID");
    
    $this->view->category = $category_check;
    $this->view->category_string_cache = $QCategory->get_string_cache();

    $this->_helper->viewRenderer->setRender('notification/category-create');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST.'manage/notification-category');
}

