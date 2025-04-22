<?php
if (defined("LOCK_TIMING") && LOCK_TIMING) {
    $this->_helper->viewRenderer->setRender('lock');
    return;
}

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;
$user_code = $userStorage->code;
$group_id = $userStorage->group_id;


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
	}

}


$flashMessenger = $this->_helper->flashMessenger;

// không cần check thuộc store nào vào lúc này,
// mình check ajax mỗi khi chọn store và ngày

$QShift = new Application_Model_Shift();
$_shifts = $QShift->get_cache();
$this->view->shifts = $_shifts;

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
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;
$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;