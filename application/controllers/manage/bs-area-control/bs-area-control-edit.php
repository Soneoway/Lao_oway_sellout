<?php

$bs_area_id = $this->getRequest()->getParam('bs_area_id');

$QBsArea = new Application_Model_BsArea();

$bs_area_binding = $QBsArea->bs_area_binding($bs_area_id);
$this->view->bs_area_binding = $bs_area_binding;

$store_list = $QBsArea->store_list($bs_area_id);
$this->view->store_list = $store_list;

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('bs-area-control/edit');
