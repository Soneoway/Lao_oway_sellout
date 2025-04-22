<?php
if (defined("LOCK_TIMING") && LOCK_TIMING) {
    $this->_helper->viewRenderer->setRender('lock');
    return;
}

$id = $this->getRequest()->getParam('id');

$QTiming = new Application_Model_Timing();
$timingRowset = $QTiming->find($id);
$timing = $timingRowset->current();

$flashMessenger = $this->_helper->flashMessenger;

if (!$timing){
    $flashMessenger->setNamespace('error')->addMessage('Invalid Report!');
    $this->_redirect('/timing');
}

$date = explode(' ', $timing->from);
$this->view->from = substr($date[1], 0, -3);
$tem = explode('-', $date[0]);
$this->view->date = $tem[2].'/'.$tem[1].'/'.$tem[0];
$to = explode(' ', $timing->to);
$this->view->to = substr($to[1], 0, -3);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

//check quyền view
//check có phải là quản lý của store
$QStoreStaffLog = new Application_Model_StoreStaffLog();
$QLeaderLog = new Application_Model_StoreLeaderLog();

if ($userStorage->id != $timing->staff_id and $userStorage->group_id != ADMINISTRATOR_ID) {

    if ($userStorage->group_id == SALES_ID) {

        if ( ! $QStoreStaffLog->belong_to($userStorage->id, $timing['store'], $timing['from'], true) ) {
            $salesTeamErr = 'Bạn không phải người quản lý của Store!';
            $flashMessenger->setNamespace('error')->addMessage($salesTeamErr);
            $back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->_redirect(($back_url ? $back_url : '/timing'));
        }

    } elseif ($userStorage->group_id == LEADER_ID) {
        
        if ( ! $QStoreStaffLog->belong_to( $userStorage->id, $timing['store'], $timing['from'], true )
            && ! $QLeaderLog->is_leader( $userStorage->id, $timing['store'], $timing['from'] ) ) {
            $salesTeamErr = 'Bạn không phải người quản lý/leader của Store!';
            $flashMessenger->setNamespace('error')->addMessage($salesTeamErr);
            $back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->_redirect(($back_url ? $back_url : '/timing'));
        }
    } elseif ($userStorage->group_id == ASM_ID) {
        $QAsm = new Application_Model_Asm();

        if (!$QAsm->is_asm($userStorage->id, $timing['store'])) {
            $salesTeamErr = 'Bạn không phải ASM khu vực này';
            $flashMessenger->setNamespace('error')->addMessage($salesTeamErr);
            $back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->_redirect(($back_url ? $back_url : '/timing'));
        }
    } elseif ($userStorage->group_id == ASMSTANDBY_ID) {
        $QAsm = new Application_Model_AsmStandby();

        if (!$QAsm->is_asm($userStorage->id, $timing['store'])) {
            $salesTeamErr = 'Bạn không phải ASM Standby khu vực này';
            $flashMessenger->setNamespace('error')->addMessage($salesTeamErr);
            $back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->_redirect(($back_url ? $back_url : '/timing'));
        }
    }
        
}

$this->view->timing = $timing;

$QShift = new Application_Model_Shift();
$_shifts = $QShift->get_cache();
$this->view->shifts = $_shifts;

//get staff info
$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id = ?', $timing->staff_id);
$this->view->staff = $QStaff->fetchRow($where);

//timing sale
$QTimingSale = new Application_Model_TimingSale();
$where = $QTimingSale->getAdapter()->quoteInto('timing_id = ?', $id);
$timing_sales = $QTimingSale->fetchAll($where);

$QGoodColor = new Application_Model_GoodColor();
$QGoodColorCombined = new Application_Model_GoodColorCombined();
$QCustomer = new Application_Model_Customer();
$data = array();
if ($timing_sales->count()){
    foreach ($timing_sales as $item){
        $where = $QGoodColorCombined->getAdapter()->quoteInto('good_id = ?', $item->product_id);
        $goodColorCombined = $QGoodColorCombined->fetchAll($where);

        $temp = array();
        foreach ($goodColorCombined as $jd)
            $temp[] = $jd->good_color_id;

        if (is_array($temp) && count($temp) > 0)
            $where = $QGoodColor->getAdapter()->quoteInto('id IN (?)', $temp);
        elseif($temp)
            $where = $QGoodColor->getAdapter()->quoteInto('id = ?', $temp);
        else
            $where = $QGoodColor->getAdapter()->quoteInto('1=0', 1);

        $goodColor = $QGoodColor->fetchAll($where, 'name');

        $where = $QCustomer->getAdapter()->quoteInto('id = ?', $item->customer_id);
        $customer = $QCustomer->fetchRow($where);

        $data[] = array(
            'id' => $item->id,
            'photo' => $item->photo,
            'product_id' => $item->product_id,
            'model_id' => $item->model_id,
            'models' => $goodColor,
            'customer' => $customer,
            'imei' => $item->imei,
        );
    }
}
$this->view->timing_sales = $data;

//get product
$QGood = new Application_Model_Good();
$where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
$goods = $QGood->fetchAll($where, 'desc');
$this->view->goods = $goods;

// load all model
$QGoodColor = new Application_Model_GoodColor();
$result = $QGoodColor->fetchAll();

$data = null;
if ($result->count()) {
    foreach ($goods as $good) {
        $colors = explode(',', $good->color);

        $temp = array();
        foreach ($result as $item){
            if (in_array($item['id'], $colors)) {
                $temp[] = array(
                    'id' => $item->id,
                    'model' => $item->name,
                );
            }
        }
        $data[$good['id']] = $temp;
    }
}
$this->view->good_colors = json_encode($data);

//get store
$QStore = new Application_Model_Store();
$QStoreStaffLog = new Application_Model_StoreStaffLog();

$store_id_list = $QStoreStaffLog->get_stores($userStorage->id, 
                                                strtotime($timing['from']));
$where = array();

if(is_array($store_id_list) && count($store_id_list) > 0)
    $where[] = $QStore->getAdapter()->quoteInto('id IN (?)', $store_id_list);

// Nếu mà Leader xem timing ko phải ở store của nó (làm sales) thì 
// chỉ load cửa hàng ứng với chấm công
elseif($userStorage->group_id == LEADER_ID && !empty($timing['store']))
    $where[] = $QStore->getAdapter()->quoteInto('id = ?', $timing['store']);

elseif( ! in_array($userStorage->group_id, array(ADMINISTRATOR_ID)) )
    $where[] = $QStore->getAdapter()->quoteInto('1=0', 1);

$this->view->stores = $QStore->fetchAll($where, 'name');
//

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');