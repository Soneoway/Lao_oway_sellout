<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$old_staff_id 	 = $this->getRequest()->getParam('old_staff_id');

$store_control_id = $this->getRequest()->getParam('store_control_id');
$staff_id 		 = $this->getRequest()->getParam('staff_id');

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

try { 

	// Case 01 : Change PCM Responsible
	if ( $old_staff_id != $staff_id ) {

		// Check Current Store Control
		$sc_where = $QStoreControl->getAdapter()->quoteInto('id = ?', $store_control_id);
		$sc_result = $QStoreControl->fetchRow($sc_where);

		if ( !empty($sc_result) ) {

			if ( $staff_id != $sc_result['staff_id'] ) {

				// Update New PCM of Store Control
				$where = array();
	    		$where[] = $QStoreControl->getAdapter()->quoteInto('id = ?', $store_control_id);

	    		$data = array( 'staff_id' => $staff_id );

	    		$QStoreControl->update($data, $where);

	    		// Update Store Control Log [to_date]
	    		$where = array();
	    		$where[] = $QStoreControlLog->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
	    		$where[] = $QStoreControlLog->getAdapter()->quoteInto('staff_id = ?', $sc_result['staff_id']);
	    		$where[] = $QStoreControlLog->getAdapter()->quoteInto('to_date IS NULL', 1);

				$data = array( 'to_date' => $now );

				$QStoreControlLog->update($data, $where);

	    		// Insert New PCM of Store Control Log [from_date]
				$data = array(
	                'staff_id' 			=> $staff_id,
	                'store_control_id'	=> $store_control_id,
	                'from_date' 		=> $now, 
	                'created_by' 		=> $userStorage->id,
	                'created_at' 		=> $now,
				);

	            $QStoreControlLog->insert($data);

			}

		} 
		
	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    //exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/store-control') ).'"</script>';