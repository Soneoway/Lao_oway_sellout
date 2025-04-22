<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id = $this->getRequest()->getParam('id');

$flashMessenger = $this->_helper->flashMessenger;
$back_url = $this->getRequest()->getParam('back_url');
$back_url = $back_url ? $back_url : (HOST.'manage/dealer');

if ($id) {
	//check id của distributor
	$QDistributor = new Application_Model_Distributor();
    $distributor = $QDistributor->find($id);
    $distributor = $distributor->current();

	if ( ! $distributor ) {
		// báo lỗi
		echo '<script>parent.location.href="'.( $back_url ).'"</script>';
		exit;
	}

    if (isset($distributor['parent']) && $distributor['parent'] != 0) {
        echo '<script>
                parent.palert("Đây là Dealer con (sub). Bạn không thể thêm Store vào Dealer này.");

                parent.alert("Đây là Dealer con (sub). Bạn không thể thêm Store vào Dealer này.");
            </script>';
        exit;
    }
	
	$pic = $this->getRequest()->getParam('store_ids');
	$store_ids = explode(',', $pic);
	$store_ids = is_array($store_ids) ? array_filter($store_ids) : array();

	$QStore = new Application_Model_Store();

	// lấy store cũ thuộc dealer này
	$where = $QStore->getAdapter()->quoteInto('d_id = ?', $id);
	$stores = $QStore->fetchAll($where);

	$old_ids = array();

	foreach ($stores as $key => $store) {
		$old_ids[] = $store['id'];
	}

	// định dạng store id hiện tại
	foreach ($store_ids as $key => $s_id) {
		$store_ids[$key] = intval( trim( $s_id ) );
	}

	$del_ids = array_diff($old_ids, $store_ids);
	$del_ids = is_array($del_ids) ? array_filter($del_ids) : array();

	$new_ids = array_diff($store_ids, $old_ids);
	$new_ids = is_array($new_ids) ? array_filter($new_ids) : array();

	// cập nhật d_id cho các store mới thêm
	foreach ($new_ids as $s_id) {
		// kiểm tra store này có thuộc dealer nào chưa
		$where = $QStore->getAdapter()->quoteInto('d_id IS NOT NULL AND d_id <> 0 AND id = ?', $s_id);
		$check_store = $QStore->fetchRow($where);

		if ($check_store) {
            $check_dealer = $QDistributor->find($check_store['d_id']);
            $check_dealer = $check_dealer->current();

            if ($check_dealer['del'] != 1) {
    			echo '<script>
    			        parent.palert("Store ['.$check_store['name'] .'] đã thuộc Dealer [<a href=\"'.HOST.'manage/dealer-edit?id='.$check_dealer['id'].'\" target=\"_blank\">'.$check_dealer['title'] .'</a>] địa chỉ ['
    			        	.$check_dealer['add_tax'].'] Vui lòng vào Dealer đó để remove Store này ra. Sau đó add lại tại đây.");

    			        parent.alert("Store ['.$check_store['name'] .'] đã thuộc Dealer ['.$check_dealer['title'] .'] địa chỉ ['
    			        	.$check_dealer['add_tax'].'] Vui lòng vào Dealer đó để remove Store này ra. Sau đó add lại tại đây.");
    			    </script>';
    			exit;
            }
		}

		$data = array('d_id' => $id);
		$where = $QStore->getAdapter()->quoteInto('id = ?', $s_id);
		$QStore->update($data, $where);
	}

	// cập nhật d_id là NULL cho các store bị remove
	foreach ($del_ids as $s_id) {
		$data = array('d_id' => NULL);
		$where = $QStore->getAdapter()->quoteInto('id = ?', $s_id);
		$QStore->update($data, $where);
	}

	$QLog = new Application_Model_Log();
	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'DEALER - Bind('.$id.') - Stores('.$pic.')';

    //todo log
    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

	$flashMessenger->setNamespace('success')->addMessage('Done');
} else {
	$flashMessenger->setNamespace('error')->addMessage('Invalid ID');
}

echo '<script>parent.location.href="'.( $back_url ).'"</script>';
exit;