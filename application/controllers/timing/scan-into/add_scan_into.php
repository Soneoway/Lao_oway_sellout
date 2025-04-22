<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;

$QStore = new Application_Model_Store();
$QStoreStaff = new Application_Model_StoreStaff();
$QSubArea = new Application_Model_SubArea();
$QAsm = new Application_Model_Asm();


if(in_array($userStorage->group_id, array(PCDB_ID,PGPB_ID))) {

	$QStoreStaff = new Application_Model_StoreStaff();

	$where = $QStoreStaff->getAdapter()->quoteInto('staff_id =?',$user_id);
	$storestaff = $QStoreStaff->fetchAll($where);

	foreach($storestaff as $key => $value) {
		$store_list[] = $value['store_id'];
	}
	$where2 = $QStore->getAdapter()->quoteInto('id IN (?)',$store_list);
	$store = $QStore->fetchAll($where2);

}elseif(in_array($userStorage->group_id, array(SALES_ID))){

	$where = $QSubArea->getAdapter()->quoteInto('staff_id =?',$user_id);
	$subarea= $QSubArea->fetchAll($where);

	foreach($subarea as $key => $value) {
		$area_list[] = $value['id'];
	}

	$where2 = $QStore->getAdapter()->quoteInto('agency IN (?) AND status = 1',$area_list);
	$store = $QStore->fetchAll($where2,new Zend_Db_Expr("`name` ASC"));

}elseif(in_array($userStorage->group_id, array(ASM_ID))){

	$where = $QAsm->getAdapter()->quoteInto('staff_id =?',$user_id);
	$asmarea= $QAsm->fetchAll($where);

	foreach($asmarea as $key => $value) {
		$regional_list[] = $value['area_id'];
	}

	$where2 = $QStore->getAdapter()->quoteInto('area_id IN (?) AND status = 1',$regional_list);
	$store = $QStore->fetchAll($where2,new Zend_Db_Expr("`name` ASC"));

}elseif(in_array($userStorage->group_id, array())){

}else{
	$where = $QStore->getAdapter()->quoteInto('status =?', 1);
	$store = $QStore->fetchAll($where,new Zend_Db_Expr("`name` ASC"));

}

$this->view->store = $store;
$this->_helper->viewRenderer->setRender('/scan-into/add-scan-into');
?>