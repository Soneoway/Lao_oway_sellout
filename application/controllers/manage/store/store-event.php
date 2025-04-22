<?php
$flashMessenger = $this->_helper->flashMessenger;

$id = $this->getRequest()->getParam('id');

$id 			= $this->getRequest()->getParam('id');
$event 			= $this->getRequest()->getParam('event');
$remark         = $this->getRequest()->getParam('remark');


$db = Zend_Registry::get('db');
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QStore = new Application_Model_Store();

if($id) {

	$storeRowSet = $QStore->find($id);
	$store       = $storeRowSet->current();

	if (!$store) {
		$flashMessenger->setNamespace('error')->addMessage('Invalid Store ID. Please Check And Try Agian !.');
		$this->_redirect(HOST.'manage/store');
	}


	try {

		if($event == "close") {
			$status_new = 3;
		} elseif ($event == "restart") {
			$status_new = 1;
		} elseif ($event == "puss") {
			$status_new = 2;
		}else{
			$flashMessenger->setNamespace('error')->addMessage('Invalid Event. Please Check And Try Agian !.');
			$this->_redirect(HOST.'manage/store');
		}

		$data = array(
			'status'        => $status_new,
			'remark'        => $remark,
			'updated_at'    => date('Y-m-d H:i:s'),
			'updated_by'    => $userStorage->id
		);


		$where = $QStore->getAdapter()->quoteInto('id = ?', $id);
		$updatedcase = $QStore->update($data, $where);


		if($updatedcase){
			$flashMessenger->setNamespace('success')->addMessage('Update Data Success!');
		}else{
			$flashMessenger->setNamespace('error')->addMessage('Something went wrong.! ( Update Faill ) Please check and try again.!');
		}

	}catch (Exception $e){

		echo '<script>
		parent.palert("Something went wrong.! Please check and try again.!");
		</script>';
		exit;
	}

	echo '<script>window.history.go(-1);</script>';
	exit;

}

?>