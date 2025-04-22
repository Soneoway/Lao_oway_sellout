<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id 		 = $this->getRequest()->getParam('su_id');
$unpunish_id = $this->getRequest()->getParam('unpunish_id');
$con_01		 = $this->getRequest()->getParam('con_01');
$con_02		 = $this->getRequest()->getParam('con_02');
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
	'staff_id'		=> $staff_id,
	'type_id'		=> $unpunish_id,
	'condition_01'	=> intval($con_01),
	'condition_02'	=> intval($con_02),
	'from_date'		=> $from,
	'to_date'		=> $to,
);

// print_r($data); die;

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QStaffUnPunish = new Application_Model_StaffUnPunish();

try { 

	if (isset($id) && $id != '') {

		$data['updated_by'] = $userStorage->id;
		$data['updated_at'] = $now;

		$where = $QStaffUnPunish->getAdapter()->quoteInto('id = ?', $id);
		$QStaffUnPunish->update($data,$where);

	} else {

		$data['created_by'] = $userStorage->id;
		$data['created_at'] = $now;

		$QStaffUnPunish->insert($data);

	}
	
	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/pc-unpunish') ).'"</script>';