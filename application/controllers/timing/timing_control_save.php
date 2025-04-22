<?php
set_time_limit(0);
ini_set('memory_limit', '200M');

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$userStorage    = Zend_Auth::getInstance()->getStorage()->read();

$QTimingControl = new Application_Model_TimingControl();
$QWebImei = new Application_Model_WebImei();
$QTimingSale = new Application_Model_TimingSale();


if ($this->getRequest()->getMethod() == 'POST'){
	$imei        	 = $this->getRequest()->getParam('imei');
	$save_type		 = $this->getRequest()->getParam('save');

	$arr_imei = $_POST['imei'];
	$arr_imei = explode("\n", str_replace("\r", "", $arr_imei));

	$check= array();
	for ($i=0; $i<count($arr_imei); $i++) { // Check Imei Input Duplicate
		$check[$arr_imei[$i]]++;
		if ($check[$arr_imei[$i]]>1){
			echo '<script>
			parent.alert("ບໍສາມາດດຳເນິນການໄດ້ເນື່ອງຈາກວ່າ Imei '.$arr_imei[$i].' ຊ້ຳກັນ");
			</script>';
			echo '<script>parent.location.href="'.('/timing/timing-control-add' ).'"</script>';
			exit;

		}
	}


	foreach ($arr_imei as $imei) { // Check Imei Not Exit
		$where = $QWebImei->getAdapter()->quoteInto('imei_sn =?',$imei);
		$result = $QWebImei->fetchRow($where);

		if(!$result){
			echo '<script>
			parent.alert("Imei ບໍ່ມີໃນລະບົບ, ກະລຸນາກວດຄືນເເລ້ວລອງໃໝ່ອີກຄັ້ງ");
			</script>';
			echo '<script>parent.location.href="'.('/timing/timing-control-add' ).'"</script>';
			exit;
		}

		$where_check = $QTimingSale->getAdapter()->quoteInto('imei =?',$imei);
		$result_check = $QTimingSale->fetchRow($where_check);

		if($result_check){
			echo '<script>
			parent.alert("Imei ຖືກລາຍງານຍອດໄປເເລ້ວ ບໍສາມາດດຳເນິນການໄດ້");
			</script>';
			echo '<script>parent.location.href="'.('/timing/timing-control-add').'"</script>';
			exit;
		}

		

		if($save_type == 1){ // Insert To DB
			$where2 = $QTimingControl->getAdapter()->quoteInto('imei =?',$imei);
			$re_check = $QTimingControl->fetchRow($where2);

		if($re_check){ // Check Imei In Timing_control Table
			echo '<script>
			parent.alert("Imei '.$imei.' Timing Control ON.");
			</script>';
			echo '<script>parent.location.href="'.('/timing/timing-control' ).'"</script>';
			exit;
		}
		
		$data = array(
			'imei' => $imei,
			'create_at'		=> date('Y-m-d H-i-s'),
			'create_by'		=> $userStorage->id
		);

		$QTimingControl->insert($data);
	}

		if($save_type == 2){ // Remove Form DB Where
			$where = $QTimingControl->getAdapter()->quoteInto('imei = ?', $imei);
			$QTimingControl->delete($where);
		}
	}

	$flashMessenger = $this->_helper->flashMessenger;
	$flashMessenger->setNamespace('success')->addMessage('Done!');
	$this->_redirect('/timing/timing-control');
}
$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');
$this->_redirect('/timing/timing-control');

