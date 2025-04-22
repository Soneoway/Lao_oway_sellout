<?php

$grand_id = $this->getRequest()->getParam('id');

$QGrandArea 		= new Application_Model_GrandArea();
$GrandAreaRm 		= new Application_Model_GrandAreaRm();
$QArea              = new Application_Model_Area();
$QStaff             = new Application_Model_Staff();


$grand_list = $QGrandArea->getGrandArealist($grand_id);
$rm_list = $GrandAreaRm->getGrandAreaRM($grand_id);


$this->view->rm_list 		= $rm_list;
$this->view->grand_list 	= $grand_list;
$this->view->areas 			= $QArea->get_cache();
$this->view->staff 			= $QStaff->get_cache();

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('grand-area/add-rm');
