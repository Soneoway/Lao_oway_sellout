<?php
if (defined("LOCK_TIMING") && LOCK_TIMING) {
    $this->_helper->viewRenderer->setRender('lock');
    return;
}

$QStore = new Application_Model_Store();
$QSubArea = new Application_Model_SubArea();
$QAsm = new Application_Model_Asm();


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;
$user_code = $userStorage->code;
$group_id = $userStorage->group_id;

/*
$QTime = new Application_Model_Time();
$check_status = $QTime->checkInStatus($user_id);

if(!$check_status) {
    $this->_redirect(HOST.'time/create');
}
*/

// $outsider = 0;
// $chk_outsider = substr($user_code, 0, 2);
// if ( $chk_outsider == "16" || $chk_outsider == "17" ) { $outsider = 1; }

if (in_array($group_id, array(PGPB_TEAM, SALES_ID, ASM_ID, ASMSTANDBY_ID, BM_ID, ABM_ID))) {
	$now = date('Y-m-d');
	$db = Zend_Registry::get('db');

	// Check CheckIn, Leave for Allow Timing
	$QStaffCheckInLog = new Application_Model_StaffCheckInLog();

	$table_result = $QStaffCheckInLog->getTableNameByGroup($group_id);

	// Get Leave Staff 
	$select_staff2 = $db->select()
		->from(array('lea' => $table_result['leave']), array('lea.*'))
		->where('lea.staff_id = ?', $user_id)
		->where('lea.status <> ?', 'N')
		->where("CONCAT(DATE(lea.from_date), ' 00:00:00') <= ?", $now.' 00:00:00')
		->where("CONCAT(DATE(lea.to_date), ' 23:59:59') >= ?", $now.' 23:59:59');

	$result_staff2 = $db->fetchRow($select_staff2);
	//print_r($result_staff2); 

	// Check Have Leave or Not?
	if (!empty($result_staff2)) {

		//echo "AAA";
		$cal_leave = (( strtotime($result_staff2['to_date']) - strtotime($result_staff2['from_date']) ) / (60*60*24) ) + 1;

		if ($cal_leave >= 3) {
			//echo "DDD";
			echo "<script>
				parent.alert('คุณอยู่ในช่วงการลาหยุดยาว ไม่สามารถรายงานยอดได้');
				window.location.href = '".HOST."index';
			</script>";
		} 
		
	} else {

		//echo "BBB";
		// Get CheckIn Staff 
		$select_staff = $db->select()
			->from(array('chk' => $table_result['check_in']), array('chk.*'))
			->where('action_id = ?', 1)
			->where('chk.staff_id = ?', $user_id)
			->where('chk.created_at >= ?', $now.' 00:00:00')
			->where('chk.created_at <= ?', $now.' 23:59:59');

		$result_staff = $db->fetchRow($select_staff);
		//print_r($result_staff); 

		// Check Have CheckIn or Not? 
		// if (empty($result_staff)) {
		// 	//echo "CCC";
		// 	echo "<script>
		// 			parent.alert('คุณยังไม่ได้ Check In ไม่สามารถรายงานยอดได้');
		// 			window.location.href = '".HOST."index';
		// 		</script>";
		// } 

	}

}

// ASM_ID

if(in_array($group_id, array(5))) {

	$where = array();
	$where[] = $QAsm->getAdapter()->quoteInto('staff_id =?',$user_id);
	$where[] = $QAsm->getAdapter()->quoteInto('type =?',2);
	$area = $QAsm->fetchAll($where);

	$area_list = array();
	foreach($area as $value) {
		$area_list[] = $value['area_id'];
	}

	$where2 = array();
	$where2[] = $QStore->getAdapter()->quoteInto('area_id IN (?)',$area_list);
	$where2[] = $QStore->getAdapter()->quoteInto('status =?',1);

	$this->view->stores = $QStore->fetchAll($where2);
}

elseif(in_array($group_id, array(SALES_ID))) {

	$where = $QSubArea->getAdapter()->quoteInto('staff_id =?',$user_id);
	$subarea= $QSubArea->fetchAll($where);

	// print_r($subarea); die;

	foreach($subarea as $key => $value) {
		$area_list[] = $value['id'];
	}

	$where2 = array();
	$where2[] = $QStore->getAdapter()->quoteInto('agency IN (?)',$area_list);
	$where2[] = $QStore->getAdapter()->quoteInto('status =?',1);
	$this->view->stores = $QStore->fetchAll($where2);

}elseif(in_array($group_id, array(PGPB_ID,PCDB_ID,30))) {

	$QStoreStaff = new Application_Model_StoreStaff();

	$where = $QStoreStaff->getAdapter()->quoteInto('staff_id =?',$user_id);
	$storestaff = $QStoreStaff->fetchAll($where);

	// print_r($storestaff); die;

	foreach($storestaff as $key => $value) {
		$store_list[] = $value['store_id'];
	}

	// print_r($store_list); die;

	$where2 = $QStore->getAdapter()->quoteInto('id IN (?)',$store_list);
	$this->view->stores = $QStore->fetchAll($where2);

}


if($group_id == 1 ){
 $this->view->stores = $QStore->fetchAll();
}


$flashMessenger = $this->_helper->flashMessenger;

// không cần check thuộc store nào vào lúc này,
// mình check ajax mỗi khi chọn store và ngày

$QShift = new Application_Model_Shift();
$_shifts = $QShift->get_cache();
$this->view->shifts = $_shifts;

// get store oppo
//
$QStoreOppo = new Application_Model_StoreOppo();
$this->view->oppo_store = $QStoreOppo->getStoreFromHr();


$QRegionalMarket = new Application_Model_RegionalMarket();
$where = array();
$where[] = $QRegionalMarket->getAdapter()->quoteInto('parent =?',0); // get provience
$where[] = $QRegionalMarket->getAdapter()->quoteInto('id <> ?',8413); // not get sellin provience
$regional_markets = $QRegionalMarket->fetchAll($where);
$this->view->regional_markets = $regional_markets;


//get goods
$QGood = new Application_Model_Good();
$where = array();
$where[] = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
//$where[] = $QGood->getAdapter()->quoteInto('status = ?', 1);
$goods = $QGood->fetchAll($where, 'desc');
$this->view->goods = $goods;

//get staff info
$QStaff = new Application_Model_Staff();
$where = $QStaff->getAdapter()->quoteInto('id = ?', $userStorage->id);
$this->view->staff = $QStaff->fetchRow($where);

$this->view->user_id = $userStorage->id;

// load all model
$QGoodColor = new Application_Model_GoodColor();
$result = $QGoodColor->fetchAll();

$data = null;
if ($result->count()) {
    foreach ($goods as $good) {
        $colors = explode(',', $good->color);

        $temp = array();
        foreach ($result as $item){
            if (in_array($item['id'], $colors)) {
                $temp[] = array(
                    'id' => $item->id,
                    'model' => $item->name,
                );
            }
        }
        $data[$good['id']] = $temp;
    }
}

$this->view->good_colors = json_encode($data);

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;