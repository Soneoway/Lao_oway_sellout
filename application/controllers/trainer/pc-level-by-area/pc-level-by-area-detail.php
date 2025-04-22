<?php

$area_id = $this->getRequest()->getParam('area_id');
$type_id = $this->getRequest()->getParam('type_id');

$params = array(
    'area_id' => $area_id,
    'type_id' => $type_id
);

$QStaff = new Application_Model_Staff();
$pc_list = $QStaff->getPcLevelByAreaDetails($params);
$this->view->pc_list = $pc_list;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('pc-level-by-area/partials/pc-level-by-area-detail');

?>