<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$mhl_id 		= $this->getRequest()->getParam('txt_mhl_id');
$staff_code 	= $this->getRequest()->getParam('txt_staff_code');
$staff_name 	= $this->getRequest()->getParam('txt_staff_name');
$staff_nickname	= $this->getRequest()->getParam('txt_staff_nickname');
$staff_group 	= $this->getRequest()->getParam('txt_staff_group');
$phone_number 	= $this->getRequest()->getParam('txt_phone_number');
$area_name 		= $this->getRequest()->getParam('txt_area_name');
$rd_code 		= $this->getRequest()->getParam('txt_rd_code');
$rd_name 		= $this->getRequest()->getParam('txt_rd_name');
$month_year 	= $this->getRequest()->getParam('txt_month_year');
$remark			= $this->getRequest()->getParam('txt_remark');

// $save_type 		= $this->getRequest()->getParam('btn_save');

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QMKHL = new Application_Model_MonthlyKpiHeadcountList();

try {

	// Check is Data exists
	$where = array();
	$where[] = $QMKHL->getAdapter()->quoteInto('area_name = ?', $area_name[0]);
	$where[] = $QMKHL->getAdapter()->quoteInto('month_year = ?', $month_year[0]);
	$result = $QMKHL->fetchAll($where)->ToArray();

	// echo "<pre>"; print_r($result); 
	// echo "<pre>"; print_r($mhl_id);

	if ( empty($result) ) {

		// Case Create Insert All Row
		for ($i=0;$i<count($staff_code);$i++) {

			$data = array(
			    'staff_code'	=> trim($staff_code[$i]),
			    'staff_name'	=> trim($staff_name[$i]),
			    'staff_nickname'=> trim($staff_nickname[$i]),
			    'staff_group' 	=> trim($staff_group[$i]),
			    'phone_number' 	=> trim($phone_number[$i]),
			    'area_name' 	=> trim($area_name[$i]),
			    'rd_code' 		=> trim($rd_code[$i]),
			    'rd_name' 		=> trim($rd_name[$i]),
			    'month_year'	=> trim($month_year[$i]),
			    'remark'		=> trim($remark[$i]),
			    'created_by'	=> $userStorage->id,
			    'created_at'	=> date('Y-m-d H:i:s'),
			);

			// echo "Create : <pre>"; print_r($data); 
			$result_insert = $db->insert('monthly_kpi_headcount_list', $data); 
		}

	} else {

		$arr = array();
		foreach ($result as $key => $value) { $arr[] = $value['id']; }
		$chk_arr = array_diff($arr, $mhl_id);

		if ( !empty($chk_arr) ) {
			$where_del = "id IN (".implode(",", $chk_arr).")";
			$db->delete('monthly_kpi_headcount_list', $where_del);
		}

		// Case Edit 
		for ($i=0;$i<count($mhl_id);$i++) {

			$data = array(
			    'staff_code'	=> trim($staff_code[$i]),
			    'staff_name'	=> trim($staff_name[$i]),
			    'staff_nickname'=> trim($staff_nickname[$i]),
			    'staff_group' 	=> trim($staff_group[$i]),
			    'phone_number' 	=> trim($phone_number[$i]),
			    'area_name' 	=> trim($area_name[$i]),
			    'rd_code' 		=> trim($rd_code[$i]),
			    'rd_name' 		=> trim($rd_name[$i]),
			    'month_year'	=> trim($month_year[$i]),
			    'remark'		=> trim($remark[$i]),
			);

			 // echo "Update : <pre>"; print_r($data); 

			if ( $mhl_id[$i] == 0 ) {

				$data['created_by'] = $userStorage->id;
				$data['created_at'] = date('Y-m-d H:i:s');

				$result_insert = $db->insert('monthly_kpi_headcount_list', $data); 

			} else {

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = array();
        		$where['id = ?'] = $mhl_id[$i];
				$result_insert = $db->update('monthly_kpi_headcount_list', $data, $where); 

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
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/monthly-headcount-kpi') ).'"</script>';