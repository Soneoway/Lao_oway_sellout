<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);
$flashMessenger = $this->_helper->flashMessenger;
$old_staff_id 	 = $this->getRequest()->getParam('old_staff_id');
$grand_id 		 = $this->getRequest()->getParam('grand_id');


$staff_id 		 = $this->getRequest()->getParam('staff_id');
$staff_id               = is_array($staff_id) ? array_unique( array_filter( $staff_id ) ) : array();
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
print_r($staff_id);
// Validate Bind Sale Responsible
if ( $staff_id ) {

foreach ($staff_id as $key => $value) {

	$staff = $QStaff->find($value);
	$staff = $staff->current();
	// print_r($staff);

		if (!$staff) {
			echo '<script>
		            parent.palert("ไม่พบ Staff [RM] ในระบบ");
		            parent.alert("ไม่พบ Staff [RM] ในระบบ");
		        </script>';
		    exit;
		}

		if ( ! in_array( $staff['group_id'], array(RM_ID, RMSTANDBY_ID) ) ) {
			echo '<script>
		            parent.palert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ RM");
		            parent.alert("Staff : ['.$staff['code'].'] '.$staff['firstname'] .' '.$staff['lastname'].' ไม่ใช่ RM");
		        </script>';
		    exit;
		}
	}
}



$db = Zend_Registry::get('db');
$db->beginTransaction();

$QGrandAreaRm = new Application_Model_GrandAreaRm();
$QStoreStaffLog = new Application_Model_StoreStaffLog();

try { 
	
	// Case 01 : Change Sale Responsible
	if ($staff_id) {

		
		$ga_where = $QGrandAreaRm->getAdapter()->quoteInto('grand_area_id = ?', $grand_id);
		$ga_result = $QGrandAreaRm->fetchAll($ga_where);
		$rm_arr = array();
		foreach ($ga_result as $key => $value) {
			$rm_old[] = $value['rm_id'];
		}
		
		foreach ($staff_id as $k => $val) {
			if (!in_array($val,$rm_old)) {
				$data = array(
	                'grand_area_id'	=> $grand_id,
	                'rm_id' 		=> $val, 
	                'created_by' 	=> $userStorage->id,
	                'created_at' 	=> $now,
				);

	            $QGrandAreaRm->insert($data);
			}else{
				

			}
		}
		$diff = array_diff($rm_old,$staff_id);
		foreach ($diff as $k => $val) {
				$where = array();
	    		$where[] = $QGrandAreaRm->getAdapter()->quoteInto('grand_area_id = ?', $grand_id);
	    		$where[] = $QGrandAreaRm->getAdapter()->quoteInto('rm_id = ?', $val);
	    		$QGrandAreaRm->delete($where );
		}
	
		
		
	}



	$db->commit();
	 $flashMessenger->setNamespace('success')->addMessage('Done!');
} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    //exit;
}


/*
if ($result == 1) {
    $flashMessenger->setNamespace('success')->addMessage('Done!');
} else {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}*/

$back_url = $this->getRequest()->getParam('back_url');

//echo '<script>parent.location.href="'. ( HOST.'manage/area-control-edit?sub_area_id='.$sub_area_id ).'"</script>';
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/grand-area') ).'"</script>';