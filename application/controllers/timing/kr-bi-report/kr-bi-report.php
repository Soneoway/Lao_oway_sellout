<?php

$d_id        = $this->getRequest()->getParam('d_id');
$good_id     = $this->getRequest()->getParam('good_id');
$color_id    = $this->getRequest()->getParam('color_id');
$chk_compare = $this->getRequest()->getParam('chk_compare');
$from        = $this->getRequest()->getParam('from', date('01/m/Y'));
$to          = $this->getRequest()->getParam('to', date('d/m/Y'));
$export      = $this->getRequest()->getParam('export', 0);

$params = array(
    'd_id'          => $d_id,
    'good_id'       => $good_id,
    'color_id'      => $color_id,
    'chk_compare'   => $chk_compare,
    'from'          => $from,
    'to'            => $to, 
    'export'        => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$QGood = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();
$QDistributor = new Application_Model_Distributor();

$where = array();
$where[] = $QDistributor->getAdapter()->quoteInto('del = ?', 0);
$where[] = $QDistributor->getAdapter()->quoteInto('is_kr = ?', 1);

$this->view->distributor_list = $QDistributor->fetchAll($where, 'title')->ToArray();

$good_list = $QGood->get_cache2();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

$QTiming = new Application_Model_Timing();

if ($export) {

    if ($export == 1) {
        $this->_exportKrBiReport($params);
    }
    
}

// Check First Time Not Show Data
if (!empty($_GET)) { 

    $kr_sellout = $QTiming->getKrBiReport($params);

    // Compare Last Year
    if ($chk_compare == 1) {
        $params2 = $params;

        $d1 = explode('/', $params['from']);
        $d2 = explode('/', $params['to']);

        $from_temp = $d1[2].'-'.$d1[1].'-'.$d1[0];
        $to_temp = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $params2['from'] = date($d1[0].'/m/Y', strtotime("-1 Years", strtotime($from_temp)));
        $params2['to'] = date($d2[0].'/m/Y', strtotime("-1 Years", strtotime($to_temp)));

        $kr_sellout_last1 = $QTiming->getKrBiReport($params2);

        $this->view->kr_sellout_last1 = $kr_sellout_last1;

        $kr_sellout_sum = array_merge($kr_sellout,$kr_sellout_last1);
        $this->view->kr_sellout_sum = $kr_sellout_sum;
    }

    // echo "<pre>"; print_r($kr_sellout);
    // echo "<pre>"; print_r($kr_sellout_last1);
    // echo "<pre>"; print_r($kr_sellout_sum);
}

$this->view->params = $params;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->kr_sellout = $kr_sellout;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('kr-bi-report/partials/list');
} else
    $this->_helper->viewRenderer->setRender('kr-bi-report/index');