<?php 
$this->_helper->viewRenderer->setRender('notification/mass-upload');

$QNotificationCategory = new Application_Model_NotificationCategory();

$this->view->category_cache        = $QNotificationCategory->get_full_cache();
$this->view->categories            = $QNotificationCategory->get_cache();
$this->view->category_string_cache = $QNotificationCategory->get_string_cache();