<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$old_staff_id 	 = $this->getRequest()->getParam('old_staff_id');

$sub_area_id = $this->getRequest()->getParam('sub_area_id');
$staff_id 		 = $this->getRequest()->getParam('staff_id');
$asm_id 		 = $this->getRequest()->getParam('asm_id');
$store_list		 = $this->getRequest()->getParam('chk_asm');
$tmp_unchk	 	 = $this->getRequest()->getParam('store_unchk');
$tmp_unchk_old 	 = $this->getRequest()->getParam('store_unchk_old');
$sa_com_rate	 = $this->getRequest()->getParam('sa_com_rate');

$store_unchk = explode(",", rtrim($tmp_unchk, ","));

// echo "staff_id : ".$staff_id;
// echo "<br/>";
// echo "sub_area_id : ".$sub_area_id;
// echo "<br/>";
// print_r($_POST);
// echo "<br/>";
// print_r($store_unchk);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$QStaff	      = new Application_Model_Staff();
// $QAreaControl = new Application_Model_AreaControl();
// $QAreaControlLog = new Application_Model_AreaControlLog();
$QSubArea = new Application_Model_SubArea();
$QSubAreaLog = new Application_Model_SubAreaLog();

// Validate Bind Sale Responsible
if ( $staff_id ) {

	$staff = $QStaff->find($staff_id);
	$staff = $staff->current();
	//print_r($staff);

	if (!$staff) {
		echo '<script>
	            parent.palert("ไม่พบ Staff [Sale] ในระบบ");
	            parent.alert("ไม่พบ Staff [Sale] นระบบ");
	        </script>';
	    exit;
	}

	if ( ! in_array( $staff['group_id'], array(SALES_ID, RM_ID, ASM_ID, ASMSTANDBY_ID) ) ) {
		echo '<script>
	            parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ Sale");
	            parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ Sale");
	        </script>';
	    exit;
	}

	// Check Current ComRate of This Sale
	$sa_result = array();
	$sa_where = array();
	$sa_where[] = $QSubArea->getAdapter()->quoteInto('staff_id = ?', $staff_id);
	$sa_result = $QSubArea->fetchRow($sa_where);

	if (isset($sa_result['com_rate'])) {
		if ($sa_com_rate != $sa_result['com_rate']) {
			echo '<script>
		            parent.palert("Sale 1 คน ต่อ 1 Commission Rate เท่านั้น!");
		            parent.alert("Sale 1 คน ต่อ 1 Commission Rate เท่านั้น!");
		        </script>';
		    exit;
		}
	} 

}

// Validate Bind ASM Responsible
if ( $asm_id ) {

	$staff = $QStaff->find($asm_id);
	$staff = $staff->current();
	//print_r($staff);

	if (!$staff) {
		echo '<script>
	            parent.palert("ไม่พบ Staff [ASM] ในระบบ");
	            parent.alert("ไม่พบ Staff [ASM] นระบบ");
	        </script>';
	    exit;
	}

	if ( ! in_array( $staff['group_id'], array(RM_ID, ASM_ID, ASMSTANDBY_ID) ) ) {
		echo '<script>
	            parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ RM/ASM/ASM Stand by");
	            parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ RM/ASM/ASM Stand by");
	        </script>';
	    exit;
	}

}

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QStoreStaff = new Application_Model_StoreStaff();
$QStoreStaffLog = new Application_Model_StoreStaffLog();

try { 

	// Case 01 : Change Sale Responsible
	if ( ($old_staff_id != $staff_id) || ($tmp_unchk != $tmp_unchk_old) ) {

		for ($i=0;$i<count($store_unchk);$i++) {

			// Check Current Store Staff
			$ss_where = array();
			$ss_where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $store_unchk[$i]);
			$ss_where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);
			$ss_result = $QStoreStaff->fetchRow($ss_where);

			if ( !empty($ss_result) ) {

				if ( $staff_id != $ss_result['staff_id'] ) {

					// Update Store Staff 
					$where = array();
		    		$where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $store_unchk[$i]);
		    		$where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);

		    		$data = array( 'staff_id' => $staff_id );

		    		$QStoreStaff->update($data, $where);

		    		// Update Last Store Staff Log [Release At]
		    		$where = array();
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $store_unchk[$i]);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', $ss_result['staff_id']);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = ?', 1);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);

					$data = array( 'released_at' => time() );

					$QStoreStaffLog->update($data, $where);

		    		// Insert New Store Staff Log [Joined At]
					$data = array(
		                'staff_id' => $staff_id,
		                'store_id' => $store_unchk[$i],
		                'is_leader' => 1,
		                'joined_at' => time(),
					);

		            $QStoreStaffLog->insert($data);

				}

			} 

		} 

		// Check Current Area Control
		$ac_where = $QSubArea->getAdapter()->quoteInto('id = ?', $sub_area_id);
		$ac_result = $QSubArea->fetchRow($ac_where);

		if ( !empty($ac_result) ) {

			if ( $staff_id != $ac_result['staff_id'] ) {

				// Update New Sale of Area Control
				$where = array();
	    		$where[] = $QSubArea->getAdapter()->quoteInto('id = ?', $sub_area_id);

	    		$data = array( 'staff_id' => $staff_id );

	    		$QSubArea->update($data, $where);

	    		// Update Area Control Log [to_date]
	    		$where = array();
	    		$where[] = $QSubAreaLog->getAdapter()->quoteInto('sub_area_id = ?', $sub_area_id);
	    		$where[] = $QSubAreaLog->getAdapter()->quoteInto('staff_id = ?', $ac_result['staff_id']);
	    		$where[] = $QSubAreaLog->getAdapter()->quoteInto('to_date IS NULL', 1);

				$data = array( 'to_date' => $now );

				$QSubAreaLog->update($data, $where);

	    		// Insert New Sale of Area Control Log [from_date]
				$data = array(
	                'staff_id' 		=> $staff_id,
	                'sub_area_id'	=> $sub_area_id,
	                'from_date' 	=> $now, 
	                'created_by' 	=> $userStorage->id,
	                'created_at' 	=> $now,
				);

	            $QSubAreaLog->insert($data);

			}

		} else {
/*
			// Insert Area Control
			$data = array(
                'staff_id'		=> $staff_id,
                'id'		=> $sub_area_id,
			);

            $QSubArea->insert($data);

            // Insert Area Control Log
            $data = array(
                'staff_id' 		=> $staff_id,
                'sub_area_id'	=> $sub_area_id,
                'from_date' 	=> $now, 
	         	'created_by' 	=> $userStorage->id,
				'created_at' 	=> $now,
			);

            $QSubAreaLog->insert($data);
*/
		}
		
	}

	// Case 02 : Change ASM Responsible 
	if ( $asm_id ) {

		for ($i=0;$i<count($store_list);$i++) {

			// Check Current Store Staff
			$ss_where = array();
			$ss_where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $store_list[$i]);
			$ss_where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);
			$ss_result = $QStoreStaff->fetchRow($ss_where);

			if ( !empty($ss_result) ) {

				if ( $asm_id != $ss_result['staff_id'] ) {

					// Update Store Staff 
					$where = array();
		    		$where[] = $QStoreStaff->getAdapter()->quoteInto('store_id = ?', $store_list[$i]);
		    		$where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = ?', 1);

		    		$data = array( 'staff_id' => $asm_id );

		    		$QStoreStaff->update($data, $where);

		    		// Update Last Store Staff Log [Release At]
		    		$where = array();
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('store_id = ?', $store_list[$i]);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', $ss_result['staff_id']);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('is_leader = ?', 1);
		    		$where[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);

					$data = array( 'released_at' => time() );

					$QStoreStaffLog->update($data, $where);

		    		// Insert New Store Staff Log [Joined At]
					$data = array(
		                'staff_id' => $asm_id,
		                'store_id' => $store_list[$i],
		                'is_leader' => 1,
		                'joined_at' => time(),
					);

		            $QStoreStaffLog->insert($data);

				}

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
/*
if ($result == 1) {
    $flashMessenger->setNamespace('success')->addMessage('Done!');
} else {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}*/

$back_url = $this->getRequest()->getParam('back_url');

//echo '<script>parent.location.href="'. ( HOST.'manage/area-control-edit?sub_area_id='.$sub_area_id ).'"</script>';
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/area-control') ).'"</script>';