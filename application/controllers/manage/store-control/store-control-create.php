<?php

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('store-control/create');
