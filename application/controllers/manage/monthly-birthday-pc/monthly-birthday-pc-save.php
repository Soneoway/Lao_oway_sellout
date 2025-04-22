<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$mhl_id 		= $this->getRequest()->getParam('txt_mhl_id');
$staff_code 	= $this->getRequest()->getParam('txt_staff_code');
$staff_name 	= $this->getRequest()->getParam('txt_staff_name');
$staff_group 	= $this->getRequest()->getParam('txt_staff_group');
$phone_number 	= $this->getRequest()->getParam('txt_phone_number');
$area_name 		= $this->getRequest()->getParam('txt_area_name');
$asm_name 		= $this->getRequest()->getParam('txt_asm_name');
$rd_name 		= $this->getRequest()->getParam('txt_rd_name');
$month_year 	= $this->getRequest()->getParam('txt_month_year');

$save_type 		= $this->getRequest()->getParam('btn_save');

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QMBP = new Application_Model_MonthlyBirthdayPc();

try {

	// Check is Data exists
	$where = array();
	$where[] = $QMBP->getAdapter()->quoteInto('area_name = ?', $area_name[0]);
	$where[] = $QMBP->getAdapter()->quoteInto('month_year = ?', $month_year[0]);
	$result = $QMBP->fetchAll($where)->ToArray();

	// echo "<pre>"; print_r($result); 
	// echo "<pre>"; print_r($mhl_id);

	if ( empty($result) ) {

		// Case Create Insert All Row
		for ($i=0;$i<count($staff_code);$i++) {

			$data = array(
			    'staff_code'	=> trim($staff_code[$i]),
			    'staff_name'	=> trim($staff_name[$i]),
			    'staff_group' 	=> trim($staff_group[$i]),
			    'phone_number' 	=> trim($phone_number[$i]),
			    'area_name' 	=> trim($area_name[$i]),
			    'asm_name' 		=> trim($asm_name[$i]),
			    'rd_name' 		=> trim($rd_name[$i]),
			    'month_year'	=> trim($month_year[$i]),
			    'created_by'	=> $userStorage->id,
			    'created_at'	=> date('Y-m-d H:i:s'),
			);

			// echo "Create : <pre>"; print_r($data); 
			$result_insert = $db->insert('monthly_birthday_pc', $data); 
		}

	} else {

		$arr = array();
		foreach ($result as $key => $value) { $arr[] = $value['id']; }
		$chk_arr = array_diff($arr, $mhl_id);

		if ( !empty($chk_arr) ) {
			$where_del = "id IN (".implode(",", $chk_arr).")";
			$db->delete('monthly_birthday_pc', $where_del);
		}

		// Case Edit 
		for ($i=0;$i<count($mhl_id);$i++) {

			$data = array(
			    'staff_code'	=> trim($staff_code[$i]),
			    'staff_name'	=> trim($staff_name[$i]),
			    'staff_group' 	=> trim($staff_group[$i]),
			    'phone_number' 	=> trim($phone_number[$i]),
			    'area_name' 	=> trim($area_name[$i]),
			    'asm_name' 		=> trim($asm_name[$i]),
			    'rd_name' 		=> trim($rd_name[$i]),
			    'month_year'	=> trim($month_year[$i]),
			);

			// HR Approve 
			if ($save_type == 2) {
				$data['hr_by'] = $userStorage->id;
				$data['hr_at'] = date('Y-m-d H:i:s');
			}

			// Sale Admin Approve 
			if ($save_type == 3) {
				$data['admin_by'] = $userStorage->id;
				$data['admin_at'] = date('Y-m-d H:i:s');
			}

			// Management Approve 
			if ($save_type == 4) {
				$data['mgt_by'] = $userStorage->id;
				$data['mgt_at'] = date('Y-m-d H:i:s');
			}

			 // echo "Update : <pre>"; print_r($data); 

			if ( $mhl_id[$i] == 0 ) {

				$data['created_by'] = $userStorage->id;
				$data['created_at'] = date('Y-m-d H:i:s');

				$result_insert = $db->insert('monthly_birthday_pc', $data); 

			} else {

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = array();
        		$where['id = ?'] = $mhl_id[$i];
				$result_insert = $db->update('monthly_birthday_pc', $data, $where); 

			}
			
		}

	}

	$db->commit();

	$flashMessenger = $this->_helper->flashMessenger;

	if ($result_insert) {
	    $flashMessenger->setNamespace('success')->addMessage('Done!');
	} else {
	    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
	}

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
}

$back_url = $this->getRequest()->getParam('back_url');
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/monthly-birthday-pc') ).'"</script>';