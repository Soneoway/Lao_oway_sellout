<?php

$QArea              = new Application_Model_Area();
$QGrandArea         = new Application_Model_GrandArea();
$QGrandAreaRm       = new Application_Model_GrandAreaRm();
$QGrandAreaList     = new Application_Model_GrandAreaList();
$QStaff             = new Application_Model_Staff();

$this->view->areas = $QArea->get_cache();


$params     = array();
$page       = $this->getRequest()->getParam('page', 1);
$limit      = LIMITATION;
$total      = 0;

$grand_area = $QGrandArea->fetchPagination($page, $limit, $total, $params);
$list       = array();
$area_list = array();
foreach ($grand_area as $key => $m) {
            $params['grand_id'] = $m['id'];
            $total2 = 0;
            $area_list[$m['id']] = $QGrandAreaList->fetchPagination(1, null, $total2, $params);
        }
foreach ($grand_area as $key => $m) {
            $params['grand_id'] = $m['id'];
            $total2 = 0;
            $rm_list[$m['id']] = $QGrandAreaRm->fetchPagination(1, null, $total2, $params);
        }

$this->view->list       = $grand_area;
$this->view->area_list  = $area_list;
$this->view->rm_list    = $rm_list;
$this->view->staff    = $QStaff->get_cache();

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();

$this->_helper->viewRenderer->setRender('grand-area/grand-area');