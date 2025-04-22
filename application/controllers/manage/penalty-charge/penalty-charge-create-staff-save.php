<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id 		 = $this->getRequest()->getParam('pm_id');
$punish_type = $this->getRequest()->getParam('punish_type');
$area_id	 = $this->getRequest()->getParam('area_id');
$price		 = $this->getRequest()->getParam('price');
$remark		 = $this->getRequest()->getParam('remark');
$from_date	 = $this->getRequest()->getParam('from_date');
$to_date	 = $this->getRequest()->getParam('to_date');
$staff_id	 = $this->getRequest()->getParam('staff_id');


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$d = explode('/', $from_date);
$from = $d[2].'-'.$d[1].'-'.$d[0];

$d = explode('/', $to_date);
$to = $d[2].'-'.$d[1].'-'.$d[0];

$store_list = implode(",", $store);

$data = array(
	'area_id'	=> $area_id,
	'staff_id' 	=> $staff_id,
	'punish_id'	=> $punish_type,
	'price'		=> $price,
	'remark'	=> $remark,
	'from_date'	=> $from,
	'to_date'	=> $to,
);

// print_r($data); die;

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QPunishMemo = new Application_Model_PunishMemo();

try { 

	if (isset($id) && $id != '') {

		$data['updated_by'] = $userStorage->id;
		$data['updated_at'] = $now;

		$where = $QPunishMemo->getAdapter()->quoteInto('id = ?', $id);
		$QPunishMemo->update($data,$where);

	} else {

		$data['created_by'] = $userStorage->id;
		$data['created_at'] = $now;

		$QPunishMemo->insert($data);

	}
	
	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/penalty-charge?penalty_type_id=2') ).'"</script>';