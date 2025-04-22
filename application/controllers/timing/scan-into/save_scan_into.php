<?php
$flashMessenger = $this->_helper->flashMessenger;

if ($this->getRequest()->getMethod() == 'POST'){

	$store_id 						= $this->getRequest()->getParam('store_id');
	$imei_sn						= $this->getRequest()->getParam('imei_sn');
	$remark							= $this->getRequest()->getParam('remark');

	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
	$db = Zend_Registry::get('db');

	$QImeiScanInto = new Application_Model_ImeiScanInto();
	$QWebImei = new Application_Model_WebImei();
	$QTimingSale = new Application_Model_TimingSale();
	$QStore = new Application_Model_Store();


	if($imei_sn){

		// Check Imei In Table
		$where = $QWebImei->getAdapter()->quoteInto('imei_sn =?',$imei_sn);
		$imei = $QWebImei->fetchRow($where);

		// Imei Not Exit Case
		if(!$imei){
			$flashMessenger->setNamespace('error')->addMessage('IMEI ຍັງບໍມີໃນລະບົບ. ກະລຸນາກວດຄືນເເລ້ວລອງອີກຄັ້ງ!.');
			$this->_redirect(HOST.'timing/scan-into');
		}

		// Check Imei Timing
		$where2 = $QTimingSale->getAdapter()->quoteInto('imei =?',$imei_sn);
		$timing       = $QTimingSale->fetchRow($where2);

		// Imei Timing Case
		if($timing){
			$flashMessenger->setNamespace('error')->addMessage('IMEI ຖືກລາຍງານຍອດຂາຍໄປເເລ້ວ. ກະລຸນາກວດຄືນເເລ້ວລອງອີກຄັ້ງ!.');
			$this->_redirect(HOST.'timing/scan-into');
		}

		// Imei Not In Store
		if($imei->store_id == '' || $imei->distributor_id == '') {
			$flashMessenger->setNamespace('error')->addMessage('IMEI ຍັງຢູ່ໃນສາງ ບໍ່ທັນໄດ້ອອກເຄື່ອງໃຫ້ຮ້ານເທື່ອ. ກະລຸນາກວດຄືນເເລ້ວລອງອີກຄັ້ງ!.');
			$this->_redirect(HOST.'timing/scan-into');
		}

		// Store Data
		$where3 = $QStore->getAdapter()->quoteInto('id =?',$store_id);
		$store = $QStore->fetchRow($where3);

		// OPPO Service && Catty
		if(in_array($imei->store_id, array(10752,10753,11178,11148,11149,11154,11155,11156,11169,11177,10672,10987))) {
			$flashMessenger->setNamespace('error')->addMessage('IMEI ຢູ່ໃນຮ້ານຄ້າຂອງບໍລິສັດ. ບໍ່ສາມາດສະເເກນເຂົ້າຮ້ານຄ້າໄດ້ !.');
			$this->_redirect(HOST.'timing/scan-into');
		}

		// Store Not Exit Case
		if(!$store){
			$flashMessenger->setNamespace('error')->addMessage('ລະຫັດຮ້ານຄ້າບໍ່ຖືກຕ້ອງ. ກະລຸນາກວດຄືນເເລ້ວລອງອີກຄັ້ງ!.');
			$this->_redirect(HOST.'timing/scan-into');
		}

		// Not Chain Store Case
		if($store->d_id !== $imei->distributor_id) {
			$flashMessenger->setNamespace('error')->addMessage('ຮ້ານຄ້າບໍ່ໄດ້ເປັນຮ້ານສາຂາດຽວກັນ. ກະລຸນາກວດຄືນເເລ້ວລອງອີກຄັ້ງ!.');
			$this->_redirect(HOST.'timing/scan-into');
		}

	}

	try {

		$db->beginTransaction();

		$data = array(
			'imei_sn'			=> $imei_sn,
			'good_id'			=> $imei->good_id,
			'color_id'			=> $imei->good_color,
			'out_store_id'		=> $imei->store_id,
			'in_store_id'		=> $store_id,
			'remark'			=> $remark,
			'created_at'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $userStorage->id
		);



		$insertCase = $QImeiScanInto->insert($data);

		if($insertCase) {
			$data2 = array(
			       'store_id'=> $store_id
			);


			$where2 = $QWebImei->getAdapter()->quoteInto('imei_sn =?',$imei_sn);
			$QWebImei->update($data2,$where2);
		}

		$flashMessenger->setNamespace('success')->addMessage('ບັນທຶກຂໍ້ມູນສຳເລັດ. !');
		$db->commit(); 

	} catch (Exception $e) {

		$db->rollback();
		echo '<script> parent.palert("ມີຂໍຜິດພາດບາງຢ່າງເກິດຂື້ນ , ກະລຸນາຕິດຕໍ່ຫາ Admin."); </script>';
		exit;

	}

	echo '<script>parent.location.href="/timing/scan-into"</script>';
	exit;


}

?>