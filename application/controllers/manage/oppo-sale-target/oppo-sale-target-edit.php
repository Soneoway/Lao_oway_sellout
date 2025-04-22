<?php

$area_id = $this->getRequest()->getParam('area_id');
$area_name = $this->getRequest()->getParam('area_name');
$from = $this->getRequest()->getParam('from_date');
$to = $this->getRequest()->getParam('to_date');

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($from)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($from)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($from)));

$params = array(
	'area_id' 	=> $area_id,
	'area_name'	=> $area_name,
	'from'		=> $from,
	'to'		=> $to,
	'last_03'	=> $last_03,
	'last_02'	=> $last_02,
	'last_01'	=> $last_01,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$QOppoAreaTarget = new Application_Model_OppoAreaTarget();
$QOppoSaleTarget = new Application_Model_OppoSaleTarget();

$list = $QOppoSaleTarget->getLast3MonthBySaleStore($params);

$hero = $QOppoAreaTarget->getheroproduct();

// Get Current Area Target
$where_target[] = $QOppoAreaTarget->getAdapter()->quoteInto('from_date >= ?', $params['from']." 00:00:00");
$where_target[] = $QOppoAreaTarget->getAdapter()->quoteInto('to_date <= ?', $params['to']." 23:59:59");
$where_target[] = $QOppoAreaTarget->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
$area_target = $QOppoAreaTarget->fetchRow($where_target);

// Get Current Sale Target
$sale_target = $QOppoSaleTarget->getSaleTarget($params);

// Get Sale No Store but have Target
$list2 = $QOppoSaleTarget->getSaleNoStoreTarget($params);

$asm_target = $QOppoSaleTarget->getLeaderTarget($params, 0);
$rm_target = $QOppoSaleTarget->getLeaderTarget($params, 1);

$leader_list = array_merge($rm_target, $asm_target);

$this->view->params = $params;
$this->view->lists = $list;
$this->view->lists2 = $list2;
$this->view->sales_target = $sale_target;
$this->view->leader_list = $leader_list;
$this->view->target_max = $area_target['target'];
$this->view->target_hero_max = $area_target['target_hero'];
$this->view->hero = $hero;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/oppo-sale-target/create');