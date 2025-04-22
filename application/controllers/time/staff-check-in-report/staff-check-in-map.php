<?php

$lat = $this->getRequest()->getParam('lat');
$lng = $this->getRequest()->getParam('lng');

$params = array(
	'lat' => $lat,
	'lng' => $lng,
);

$this->view->params = $params;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('staff-check-in-report/staff-check-in-map');

?>