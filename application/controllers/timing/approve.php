<?php
$id = $this->getRequest()->getParam('id');
try {
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $user_id = $userStorage->id;

    // chỉ sales mới đc approve (trừ admin)
    if (! in_array( $userStorage->group_id, array(SALES_ID, LEADER_ID, ASM_ID) ) && $userStorage->group_id != ADMINISTRATOR_ID && $userStorage->group_id != SUPERADMIN_ID) {
        echo '-69';
        exit;
    }

    $QTime = new Application_Model_Time();
    $check_status = $QTime->checkInStatus($user_id);
    //chua checkin thi k dc approve
    if(!$check_status)
    {
       echo '-79';
       exit;
    }

    $Timing = new Application_Model_Timing();
    $where = $Timing->getAdapter()->quoteInto('id = ?', $id);
    $item = $Timing->fetchRow($where);

    $from = $item['from'];

    //prevent approve after 02 day
    if ( strtotime( date('Y-m-d') ) - strtotime( date( 'Y-m-d', strtotime( $from ) ) ) > TIME_LIMIT_TIMING ){
        $happyTime = defined('HAPPY_TIME') ? unserialize(HAPPY_TIME) : null;
        $currentTime = date('Y-m-d H:i:s');

        if (
            !
                (
                    $happyTime
                    and
                        $currentTime >= $happyTime['editFrom']
                    and
                        $currentTime <= $happyTime['editTo']
                    and
                        $from >= $happyTime['from']
                    and
                        $from <= $happyTime['to']
                )
        ){
            echo 2;
            echo '
<script langquage="javascript">
window.location="http://center.oppo.in.th/timing";
</script>';
        }
    }

    if (!$item){
        echo 3;
        echo '
<script langquage="javascript">
window.location="http://center.oppo.in.th/timing";
</script>';
    } else {
        // check có phải là quản lý của store
        // tại ngày chấm công của timing
        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();

        // co phai quan ly cua hang
        $isSaleOfStore = $QStoreStaffLog->belong_to($userStorage->id, $item['store'], $item['from'], true);

        // co phai leader cua hang
        $isLeaderOfStore = $QStoreLeaderLog->is_leader($userStorage->id, $item['store'], $item['from']);

        if (
            ! $isSaleOfStore // neu khong phai sale
            and ! $isLeaderOfStore // va khong phai leader thi phan
        ) {
            echo 4;
            echo '
<script langquage="javascript">
window.location="http://center.oppo.in.th/timing";
</script>';
        }
    }

    $data = array(
        'status' => 1,
        'approved_at' => date('Y-m-d H:i:s'),
        'approved_by' => $userStorage->id,
    );
    $Timing->update($data, $where);

    $QTimingSale = new Application_Model_TimingSale();
    $where = $QTimingSale->getAdapter()->quoteInto('timing_id = ?', $item['id']);
    $ts = $QTimingSale->fetchAll($where);

    if ($ts)
        foreach ($ts as $key => $value)
            My_Kpi::add($value['imei'], $item['store'], $item['staff_id'], $item['from']);

    $QLog = new Application_Model_Log();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = "TIMING - Approve (".$id.")";
    //todo log
    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

	
	echo '
<script langquage="javascript">
window.location="http://center.oppo.in.th/timing";
</script>';

	
    exit;
} catch (Exception $e){
    echo 1;
    echo '
<script langquage="javascript">
window.location="http://center.oppo.in.th/timing";
</script>';
}