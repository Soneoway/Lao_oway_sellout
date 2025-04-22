<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$staff_id	= $this->getRequest()->getParam('staff_id');
$store		= $this->getRequest()->getParam('store');
$sc_name	= $this->getRequest()->getParam('store_control_name');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$QStaff = new Application_Model_Staff();

// Validate Bind Sale Responsible
if ( $staff_id ) {

	$staff = $QStaff->find($staff_id);
	$staff = $staff->current();
	//print_r($staff);

	if (!$staff) {
		echo '<script>
	            parent.palert("ไม่พบ Staff ในระบบ");
	            parent.alert("ไม่พบ Staff นระบบ");
	        </script>';
	    exit;
	}

	if ( ! in_array( $staff['group_id'], array(TRAINING_TEAM_ID) ) ) {
		echo '<script>
	            parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ PCM");
	            parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ PCM");
	        </script>';
	    exit;
	}

}

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QStoreControl = new Application_Model_StoreControl();
$QStoreControlLog = new Application_Model_StoreControlLog();
$Qscm = new Application_Model_StoreControlMap();
$QscmLog = new Application_Model_StoreControlMapLog();

try { 

	$data = array();

	// Add New Store Control
	$data = array('name' => $sc_name, 'staff_id' => $staff_id);
	$id = $QStoreControl->insert($data);

	// Add Store Control Log
	unset($data['name']);
	$data['store_control_id'] = $id;
	$data['from_date'] 	= $now;
	$data['created_by'] = $userStorage->id;
	$data['created_at'] = $now;

	$QStoreControlLog->insert($data);

	$data2 = array();
	for ($i=0;$i<count($store);$i++) {

		// Add New Store Control Map
		$data2 = array('store_control_id' => $id, 'store_id' => $store[$i]);
		$Qscm->insert($data2);

		// Add Store Control Log
		$data2['from_date']	 = $now;
		$data2['created_by'] = $userStorage->id;
		$data2['created_at'] = $now;

		$QscmLog->insert($data2);
	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/store-control') ).'"</script>';