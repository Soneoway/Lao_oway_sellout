<?php

$id = $this->getRequest()->getParam('id');




$QArea          = new Application_Model_Area();
$QGrandArea     = new Application_Model_GrandArea();
if ($id) {
    $grand_list = $QGrandArea->getGrandArealist($id);
    $this->view->grand_list = $grand_list;
}

$this->view->areas = $QArea->get_cache();
$this->view->id = $id;
$this->_helper->viewRenderer->setRender('grand-area/create');
