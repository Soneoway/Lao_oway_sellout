<?php

$grand_id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;
$QGrandArea 		= new Application_Model_GrandArea();
$QGrandAreaList 		= new Application_Model_GrandAreaList();
$QGrandAreaRm 		= new Application_Model_GrandAreaRm();

$where 		= $QGrandArea->getAdapter()->quoteInto('id = ?', $grand_id);
$whereList  = $QGrandAreaList->getAdapter()->quoteInto('grand_area_id = ?', $grand_id);
$whereRm 	= $QGrandAreaRm->getAdapter()->quoteInto('grand_area_id = ?', $grand_id);

$QGrandArea->delete($where);
$QGrandAreaList->delete($whereList);
$QGrandAreaRm->delete($whereRm);

$flashMessenger->setNamespace('success')->addMessage('Done!');
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->redirect(HOST.'manage/grand-area');
