<?php

$sub_area_id = $this->getRequest()->getParam('sub_area_id');

$QAreaControl = new Application_Model_AreaControl();

$area_binding = $QAreaControl->area_binding($sub_area_id);
$this->view->area_binding = $area_binding;

$store_list = $QAreaControl->store_list($sub_area_id);
$this->view->store_list = $store_list;

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('area-control/edit');
