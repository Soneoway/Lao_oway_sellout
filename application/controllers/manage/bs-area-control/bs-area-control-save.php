<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$old_staff_id 	 = $this->getRequest()->getParam('old_staff_id');

$bs_area_id = $this->getRequest()->getParam('bs_area_id');
$staff_id 		 = $this->getRequest()->getParam('staff_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$QStaff	      = new Application_Model_Staff();

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

	if ( ! in_array( $staff['group_id'], array(ASM_ID, ASMSTANDBY_ID, 33, 34) ) ) {
		echo '<script>
	            parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ ASM, ASM Stand By, RD Assistant, ASM Assistant");
	            parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ ASM, ASM Stand By, RD Assistant, ASM Assistant");
	        </script>';
	    exit;
	}

}

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QBsArea = new Application_Model_BsArea();
$QBsAreaLog = new Application_Model_BsAreaLog();

try { 

	// Case 01 : Change ABM Responsible
	if ( $old_staff_id != $staff_id ) {

		// Check Current BS Area Control
		$ba_where = $QBsArea->getAdapter()->quoteInto('id = ?', $bs_area_id);
		$ba_result = $QBsArea->fetchRow($ba_where);

		if ( !empty($ba_result) ) {

			if ( $staff_id != $ba_result['staff_id'] ) {

				// Update New ABM of BS Area Control
				$where = array();
	    		$where[] = $QBsArea->getAdapter()->quoteInto('id = ?', $bs_area_id);

	    		$data = array( 'staff_id' => $staff_id );

	    		$QBsArea->update($data, $where);

	    		// Update BS Area Control Log [to_date]
	    		$where = array();
	    		$where[] = $QBsAreaLog->getAdapter()->quoteInto('bs_area_id = ?', $bs_area_id);
	    		$where[] = $QBsAreaLog->getAdapter()->quoteInto('staff_id = ?', $ba_result['staff_id']);
	    		$where[] = $QBsAreaLog->getAdapter()->quoteInto('to_date IS NULL', 1);

				$data = array( 'to_date' => $now );

				$QBsAreaLog->update($data, $where);

	    		// Insert New Sale of Area Control Log [from_date]
				$data = array(
	                'staff_id' 		=> $staff_id,
	                'bs_area_id'	=> $bs_area_id,
	                'from_date' 	=> $now, 
	                'created_by' 	=> $userStorage->id,
	                'created_at' 	=> $now,
				);

	            $QBsAreaLog->insert($data);

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

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/bs-area-control') ).'"</script>';