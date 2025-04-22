<?php
$QShift = new Application_Model_Shift();
$shifts = $QShift->get_cache();


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$messages_error = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;
$this->view->messages_error = $messages_error;

$this->view->shifts = $shifts;
$this->_helper->viewRenderer->setRender('shift/index');