<?php
if (defined("LOCK_TIMING") && LOCK_TIMING) {
    $this->_helper->viewRenderer->setRender('lock');
    return;
}

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$flashMessenger = $this->_helper->flashMessenger;

// không cần check thuộc store nào vào lúc này,
// mình check ajax mỗi khi chọn store và ngày

$QShift = new Application_Model_Shift();
$_shifts = $QShift->get_cache();
$this->view->shifts = $_shifts;

//get goods
$QGood = new Application_Model_Good();
$where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
$goods = $QGood->fetchAll($where, 'desc');
$this->view->goods = $goods;

//get staff info
$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
$this->view->staff = $QStaff->fetchRow($where);

$this->view->user_id = $userStorage->id;

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

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;