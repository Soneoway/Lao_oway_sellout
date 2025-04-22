<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$tmp_imei 	= $this->getRequest()->getParam('imei');
$remark 	= $this->getRequest()->getParam('remark');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$now = date('Y-m-d H:i:s');

if ($tmp_imei) { $imei = explode("\n", $tmp_imei); }

$imei = array_map('trim', $imei);
$imei = array_filter($imei);
$imei = array_values($imei);

$data = array(
	'remark'	 => $remark,
	'created_by' => $userStorage->id,
	'created_at' => $now,
);

// print_r($data); die;

$db = Zend_Registry::get('db');

// Check Already Timing 
$QTimingSale = new Application_Model_TimingSale();

$where = array();
$where[] = $QTimingSale->getAdapter()->quoteInto('imei IN (?)', $imei);
$result_timing = $QTimingSale->fetchAll($where)->ToArray();

// echo "<pre>"; print_r($result_timing);

// Check WMS IMEI 
$QImei = new Application_Model_WebImei();

$where = array();
$where[] = $QImei->getAdapter()->quoteInto('imei_sn IN (?)', $imei);
$result_imei = $QImei->fetchAll($where)->ToArray();

// echo "<pre>"; print_r($result_imei);

// Check Already Bypass
$QBypassImei = new Application_Model_BypassImei();

$where = array();
$where[] = $QBypassImei->getAdapter()->quoteInto('imei IN (?)', $imei);
$result_bypass = $QBypassImei->fetchAll($where)->ToArray();

$check_01 = array();
foreach ($result_timing as $key => $value) { $check_01[] = $value['imei']; }
$check_02 = array();
foreach ($result_imei as $key => $value) { $check_02[] = $value['imei_sn']; }
$check_03 = array();
foreach ($result_bypass as $key => $value) { $check_03[] = $value['imei']; }

// echo "<pre>"; print_r($imei); echo "<br/>";
// echo "<pre>"; print_r($check_01); echo "<br/>";
// echo "<pre>"; print_r($check_02); echo "<br/>";
// echo "<pre>"; print_r($check_03); echo "<br/>";

for ($i=0;$i<count($imei);$i++) {

	if (!in_array($imei[$i], $check_02) ) { echo '<script> parent.palert("IMEI : '.$imei[$i].' นี้ ไม่มีในระบบค่ะ!"); </script>'; exit; }
	if ( in_array($imei[$i], $check_01) ) { echo '<script> parent.palert("IMEI : '.$imei[$i].' นี้ ถูกรายงานยอดไปแล้วค่ะ!"); </script>'; exit; } 
	if ( in_array($imei[$i], $check_03) ) { echo '<script> parent.palert("IMEI : '.$imei[$i].' นี้ มีอยู่ในรายการ Bypass แล้วค่ะ!"); </script>'; exit; } 
}

// Start 
$db->beginTransaction();

try { 

	for ($i=0;$i<count($imei);$i++) {

		$data['imei'] = $imei[$i];
		$QBypassImei->insert($data);

		// print_r($data); echo "<br/>";
	}

	$db->commit();

} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    exit;
}

$back_url 	= $this->getRequest()->getParam('back_url');

$flashMessenger = $this->_helper->flashMessenger;
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/bypass-imei') ).'"</script>';