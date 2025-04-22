<?php

$area_id         = $this->getRequest()->getParam('area_id');
$export          = $this->getRequest()->getParam('export', 0);

$params = array(
    'area_id'         => $area_id,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QStaff = new Application_Model_Staff();

if ($export && $export == 1 || $export == 2) {
    $params['option'] = $export;
    $qexport = $QStaff->getPcLevelByChannelExport($params);

    $data_export = array();
    foreach ($qexport as $value) {
        $data_export[$value['area_name']][] = $value;
    }

//    echo '<pre>';
//    print_r($data_export); die;

    $this->export_PC_Level_By_Channel($data_export, $params);
}

//option = 1 :: PC 1 Shop
//option = 2 :: PC More than 1 shop
for ($i=1; $i<=2; $i++){
    $params['option'] = $i;
    $pc_level_list[$i] = $QStaff->getPcLevelByChannel($params);
}

$this->view->params = $params;
$this->view->pc_level_list = $pc_level_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-level-by-channel/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-level-by-channel/index');