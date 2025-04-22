<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id 		= $this->getRequest()->getParam('pms_id');

$from_date	= $this->getRequest()->getParam('from_date');
$to_date	= $this->getRequest()->getParam('to_date');
$remark		= $this->getRequest()->getParam('remark');
$store		= $this->getRequest()->getParam('store');

$chk_gfk	= $this->getRequest()->getParam('chk_gfk', 0);
$chk_rd		= $this->getRequest()->getParam('chk_rd', 0);
$chk_asm	= $this->getRequest()->getParam('chk_asm', 0);
$chk_sale	= $this->getRequest()->getParam('chk_sale', 0);
$chk_pc		= $this->getRequest()->getParam('chk_pc', 0);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

$d = explode('/', $from_date);
$from = $d[2].'-'.$d[1].'-'.$d[0];

$d = explode('/', $to_date);
$to = $d[2].'-'.$d[1].'-'.$d[0];

$store_list = implode(",", $store);

$data = array(
	'from_date'	=> $from,
	'to_date'	=> $to,
	'remark'	=> $remark,
	'store' 	=> $store_list,
	'flag_gfk' 	=> $chk_gfk,
	'flag_rd' 	=> $chk_rd,
	'flag_asm' 	=> $chk_asm,
	'flag_sale'	=> $chk_sale,
	'flag_pc' 	=> $chk_pc,
);

// print_r($data); die;

$db = Zend_Registry::get('db');
$db->beginTransaction();

$QPunishMemoStore = new Application_Model_PunishMemoStore();

try { 

	if (isset($id) && $id != '') {

		$data['updated_by'] = $userStorage->id;
		$data['updated_at'] = $now;

		$where = $QPunishMemoStore->getAdapter()->quoteInto('id = ?', $id);
		$QPunishMemoStore->update($data,$where);

	} else {

		$data['created_by'] = $userStorage->id;
		$data['created_at'] = $now;

		$QPunishMemoStore->insert($data);

	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/penalty-charge?penalty_type_id=1') ).'"</script>';