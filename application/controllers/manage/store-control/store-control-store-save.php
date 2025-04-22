<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$store_control_id = $this->getRequest()->getParam('store_control_id');
$store = $this->getRequest()->getParam('store');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$db = Zend_Registry::get('db');
$db->beginTransaction();

$Qscm = new Application_Model_StoreControlMap();
$QscmLog = new Application_Model_StoreControlMapLog();

// echo "Store Control ID : ".$store_control_id; echo "<br/>";
// print_r($store); echo "<br/>";

try { 

	// Check Current Store Control Map
	$scm_where = $Qscm->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
	$scm_temp = $Qscm->fetchAll($scm_where);

	for ($i=0;$i<count($scm_temp);$i++) { $scm_result[$i] = $scm_temp[$i]['store_id']; }

	// print_r($scm_result); echo "<br/>";

	$new_store = array_values(array_diff($store, $scm_result));
	$old_store = array_values(array_diff($scm_result, $store));

	// print_r($new_store); echo "<br/>";
	// print_r($old_store); echo "<br/>";

	// Insert New Store Control Map
	if ( !empty($new_store) ) {

		for ($i=0;$i<count($new_store);$i++) {
			$data = array();

			$data = array('store_control_id'=>$store_control_id,'store_id'=>$new_store[$i]);
			$Qscm->insert($data);

			// Log
			$data['from_date'] = $now;
			$data['created_by'] = $userStorage->id;
			$data['created_at'] = $now;
			$QscmLog->insert($data);
		}

	}

	// Remove Old Store Control Map
	if ( !empty($old_store) ) {

		// Update Log
		$data = $where = array();
		$data = array(
			'to_date' 	 => $now,
			'updated_by' => $userStorage->id,
			'updated_at' => $now,
		);

		$where[] = $QscmLog->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
		$where[] = $QscmLog->getAdapter()->quoteInto('store_id IN (?)', $old_store);
		$where[] = $QscmLog->getAdapter()->quoteInto('to_date IS NULL', 1);
		$QscmLog->update($data,$where);

		// Delete Store Control Map
		$data = $where = array();

		$where[] = $Qscm->getAdapter()->quoteInto('store_control_id = ?', $store_control_id);
		$where[] = $Qscm->getAdapter()->quoteInto('store_id IN (?)', $old_store);
		$Qscm->delete($where);
	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    //exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

echo '<script>parent.location.href="'.HOST.'manage/store-control-store-edit?id='.$store_control_id.'"</script>';