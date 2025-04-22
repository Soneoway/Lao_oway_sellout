<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
$this->view->staff = $QStaff->fetchRow($where);


$QGood = new Application_Model_Good();
$where = array();
$where[] = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
$where[] = $QGood->getAdapter()->quoteInto('pre_order_product = ?',1);

$goods = $QGood->fetchAll($where, 'desc');

$this->view->goods = $goods;

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

?>