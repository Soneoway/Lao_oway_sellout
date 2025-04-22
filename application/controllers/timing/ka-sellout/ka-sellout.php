<?php

$channel_id  = $this->getRequest()->getParam('channel_id');
$good_id     = $this->getRequest()->getParam('good_id');
$color_id    = $this->getRequest()->getParam('color_id');
$chk_compare = $this->getRequest()->getParam('chk_compare');
$from        = $this->getRequest()->getParam('from', date('01/m/Y'));
$to          = $this->getRequest()->getParam('to', date('d/m/Y'));
$export      = $this->getRequest()->getParam('export', 0);

$good_filter = $this->getRequest()->getParam('good_filter');

$params = array(
    'channel_id'    => $channel_id,
    'good_id'       => $good_id,
    'color_id'      => $color_id,
    'chk_compare'   => $chk_compare,
    'from'          => $from,
    'to'            => $to, 
    'export'        => $export,
    'good_filter'   => $good_filter,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$this->view->status_list = $status_list;

$QOrg = new Application_Model_Org();
$QTiming = new Application_Model_Timing();
$GoodColorCombined = new Application_Model_GoodColorCombined();

// $QGood   = new Application_Model_Good();
// $good_list = $QGood->get_cache2();

$QProductFilter = new Application_Model_ProductFilter();
$good_list = $QProductFilter->get_cache();

$where = array();
$where[] = $QOrg->getAdapter()->quoteInto('org_id NOT IN (?)', array(3,5,8,10,15,28,12)); // Not include Operator
$where[] = $QOrg->getAdapter()->quoteInto('store_type_id = ?', 1);

$this->view->ka_list = $QOrg->fetchAll($where, 'org_name');

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

if ($export) {

    if ($export == 1) {
        $ka_sellout = $QTiming->getKeyAccountSellout($params);
        $this->_exportKeyAccountSellout($ka_sellout,$params);
    }

    if ($export == 2) {

        $params_details = array();
        $export_type    = $this->getRequest()->getParam('type');
        $params_details['channel_id']    = $this->getRequest()->getParam('channel');
        $params_details['selected_date'] = $this->getRequest()->getParam('selected_date');

        if ( $export_type == 2 ) { 
            $params_details['channel_id'] = explode("|", $params_details['channel_id']);
        }

        if ( $export_type == 3 ) { 
            $params_details['selected_date'] = explode("|", $params_details['selected_date']);
        }

        if ( $export_type == 4 ) { 
            $params_details['channel_id'] = explode("|", $params_details['channel_id']);
            $params_details['selected_date'] = explode("|", $params_details['selected_date']);
        }

        // print_r($params_details); die;

        $details_list = $QTiming->getKaSelloutDetails($params_details);
        $this->_exportKaSelloutDetails($details_list,$params_details);

    } 
    
}

// Check First Time Not Show Data
if (!empty($_GET)) { 

    $ka_share = $QTiming->getKeyAccountSelloutShare($params);
    
    $total_sellout = 0;

    if ( !empty($ka_share) ) {

        foreach ($ka_share as $key => $value) { $total_sellout += $value['sellout']; }

        for ($i=0;$i<count($ka_share);$i++) {

            $ka_share[$i]['share'] = round(( $ka_share[$i]['sellout'] / $total_sellout ) * 100, 2);

        }

    }

    // echo "<br/><br/>Total Sellout : ".$total_sellout."<br/><br/>";
    // echo "<pre>"; print_r($ka_share);

    usort($ka_share, function($a, $b) { return $b['share'] > $a['share']; });

    // echo "<br/><br/>ORDER<br/><br/>";
    // echo "<pre>"; print_r($ka_share);

    $others_sellout = $others_share = 0;

    if (count($ka_share) > 10) {

        $first = array_slice($ka_share, 0, 10);
        $last = array_slice($ka_share, 10, count($ka_share) - 1);

        foreach ($last as $key => $value) { $others_sellout += $value['sellout']; }

        $others_share = round(( $others_sellout / $total_sellout ) * 100, 2);

        $product_others = array(array(
            'product_id'   => 0,
            'product_code' => '-',
            'product_name' => 'Others',
            'sellout'      => $others_sellout,
            'share'        => $others_share,
        ));

        $ka_share = array_merge($first,$product_others);

        // echo "<br/><br/>Last<br/><br/>";
        // echo "<pre>"; print_r($ka_share);

    }

    $ka_sellout = $QTiming->getKeyAccountSellout($params);

    // Compare Last Month
    if ($chk_compare == 1) {
        $params2 = $params;

        $d1 = explode('/', $params['from']);
        $d2 = explode('/', $params['to']);

        $from_temp = $d1[2].'-'.$d1[1].'-'.$d1[0];
        $to_temp = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $params2['from'] = date($d1[0].'/m/Y', strtotime("-31 Days", strtotime($to_temp)));
        $params2['to'] = date($d2[0].'/m/Y', strtotime("-31 Days", strtotime($to_temp)));

        $ka_sellout_last1 = $QTiming->getKeyAccountSellout($params2);

        $this->view->ka_sellout_last1 = $ka_sellout_last1;

        $ka_sellout_sum = array_merge($ka_sellout,$ka_sellout_last1);
        $this->view->ka_sellout_sum = $ka_sellout_sum;
    }

    // echo "<pre>"; print_r($ka_sellout);
    // echo "<pre>"; print_r($ka_sellout_last1);
    // echo "<pre>"; print_r($ka_sellout_sum);
}

$this->view->params = $params;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->ka_sellout = $ka_sellout;
$this->view->ka_share = $ka_share;
$this->view->total_sellout = $total_sellout;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('ka-sellout/partials/list');
} else
    $this->_helper->viewRenderer->setRender('ka-sellout/index');