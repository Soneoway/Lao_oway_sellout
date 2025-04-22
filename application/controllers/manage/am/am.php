<?php

$QStaff = new Application_Model_Staff();
$QAm = new Application_Model_Am();
$QOrg = new Application_Model_Org();

$this->view->org = $QOrg->get_cache();

$params = array();
$page = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$am = $QAm->fetchPagination($page, $limit, $total, $params);

$list = array();

foreach ($am as $key => $value) {
    if (! isset($list[ $value['staff_group'] ]) )
        $list[ $value['staff_group'] ] = array();

    if (! isset($list[ $value['staff_group'] ][ $value['staff_id'] ]) )
        $list[ $value['staff_group'] ]
                [ $value['staff_id'] ] = array(
                                    'staff_name'  => $value['staff_name'],
                                    'staff_email' => $value['staff_email'],
                                    'store_type'  => array(),
                                );

    $list[ $value['staff_group'] ]
            [ $value['staff_id'] ]
                ['store_type'][] = $value['org_id'];
}

$this->view->list = $list;

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();

$this->_helper->viewRenderer->setRender('am/am');