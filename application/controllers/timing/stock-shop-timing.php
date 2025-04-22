<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$data = array();

$permission_group = array(ASM_ID, SALES_ID, PGPB_ID, LEADER_ID);

$QStore = new Application_Model_Store();

if (in_array($userStorage->group_id, $permission_group)) {

	if ($userStorage->group_id == LEADER_ID) {
		$QStoreLeader = new Application_Model_StoreLeader();

		$where[] = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
		$where[] = $QStoreLeader->getAdapter()->quoteInto('status = 1');

		$result = $QStoreLeader->fetchAll($where);

	} else {
		$QStoreStaff = new Application_Model_StoreStaff();

		$where[] = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
		$where[] = $QStoreStaff->getAdapter()->quoteInto('is_leader = 1');

		$result = $QStoreStaff->fetchAll($where);
	}

	$store_list = array();
	for ($i=0;$i<count($result);$i++){ $store_list[$i] = $result[$i]['store_id']; }

	$where_store[] = $QStore->getAdapter()->quoteInto('id IN (?)', $store_list);
	$where_store[] = $QStore->getAdapter()->quoteInto('del IS NULL');
	$data = $QStore->fetchAll($where_store);
}

if ($userStorage->group_id == ADMINISTRATOR_ID) {
	$where_store[] = $QStore->getAdapter()->quoteInto('del IS NULL');
	$data = $QStore->fetchAll($where_store);
}

$this->view->store_list = $data;

?>