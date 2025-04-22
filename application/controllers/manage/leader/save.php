<?php
$flashMessenger = $this->_helper->flashMessenger;
try {
    $id = $this->getRequest()->getParam('id');

    if (!$id) {
        throw new Exception("Invalid ID");
    }

    $QStaff = new Application_Model_Staff();
    $staff = $QStaff->find($id);
    $staff = $staff->current();

    if (!$staff) {
        throw new Exception("Invalid ID");
    }

    $pic = $this->getRequest()->getParam('store_ids');

    if (!$pic && ($pic && strlen($pic) == 0)) {
        // gỡ hết store của leader
    } else {
        $store_ids = explode(',', $pic);
        $store_ids = is_array($store_ids) ? array_filter($store_ids) : array();

        $store_ids = array_unique($store_ids);

        $joined_dates = $this->getRequest()->getParam('joined_dates');
        $joined_dates = json_decode($joined_dates, true);
        // PC::db($joined_dates[578]);

        $QStore = new Application_Model_Store();
        $QStoreLeader = new Application_Model_StoreLeader();
        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();

        // lấy store cũ thuộc leader này
        $where = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', $id);
        $stores = $QStoreLeader->fetchAll($where);

        $old_ids = array();

        foreach ($stores as $key => $store) {
            $old_ids[] = $store['store_id'];
        }

        // định dạng store id hiện tại
        foreach ($store_ids as $key => $s_id) {
            $store_ids[$key] = intval( trim( $s_id ) );
        }

        $del_ids = array_diff($old_ids, $store_ids);
        $del_ids = is_array($del_ids) ? array_filter($del_ids) : array();

        $new_ids = array_diff($store_ids, $old_ids);
        $new_ids = is_array($new_ids) ? array_filter($new_ids) : array();

        $time = time();

        // cập nhật d_id cho các store mới thêm
        foreach ($new_ids as $k => $s_id) {
            // kiểm tra store này có thuộc leader nào chưa
            $where = array();
            $where[] = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $s_id);
            $where[] = $QStoreLeader->getAdapter()->quoteInto('staff_id <> ?', $id);
            $check_store = $QStoreLeader->fetchRow($where);
            
            if ($check_store) {
                $tmp_store = $QStore->find($s_id);
                $tmp_store = $tmp_store->current();

                $tmp_leader = $QStaff->find($check_store['staff_id']);
                $tmp_leader = $tmp_leader->current();

                echo '<script>
                        parent.palert("Store thứ '.($k+1).' ['.$tmp_store['name'] .'] đã thuộc Leader ['.$tmp_leader['firstname'] .' ' .$tmp_leader['lastname'] . ' | '. str_replace(EMAIL_SUFFIX, '@', $tmp_leader['email']) .'] Vui lòng liên hệ ASM để xử lý.");

                        parent.alert("Store thứ '.($k+1).' ['.$tmp_store['name'] .'] đã thuộc Leader ['.$tmp_leader['firstname'] .' ' .$tmp_leader['lastname'] . ' | '. str_replace(EMAIL_SUFFIX, '@', $tmp_leader['email']) .'] Vui lòng liên hệ ASM để xử lý.");
                    </script>';
                exit;
            }

            if (!strtotime($joined_dates[$s_id])) {
                $tmp_store = $QStore->find($s_id);
                $tmp_store = $tmp_store->current();

                echo '<script>
                        parent.palert("Store thứ '.($k+1).' ['.$tmp_store['name'] .'] có ngày bắt đầu quản lý không đúng, vui lòng kiểm tra lại.");

                        parent.alert("Store thứ '.($k+1).' ['.$tmp_store['name'] .'] có ngày bắt đầu quản lý không đúng, vui lòng kiểm tra lại.");
                    </script>';
                exit;
            }

            $data = array(
                'store_id' => $s_id,
                'staff_id' => $id,
                'status'   => '0',
                );

            $parent = $QStoreLeader->insert($data);

            $data = array(
                'parent'    => $parent,
                'store_id'  => $s_id,
                'staff_id'  => $id,
                'joined_at' => isset($joined_dates[$s_id]) && strtotime($joined_dates[$s_id]) ? strtotime($joined_dates[$s_id]) : $time,
                );

            $QStoreLeaderLog->insert($data);
        }

        foreach ($del_ids as $s_id) {
            $where = array();
            $where[] = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $s_id);
            $where[] = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', $id);
            $QStoreLeader->delete($where);

            $where = array();
            $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('store_id = ?', $s_id);
            $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('joined_at IS NOT NULL', 1);
            $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('released_at IS NULL', 1);

            $data = array('released_at' => $time);
            $QStoreLeaderLog->update($data, $where);
        }

        $QLog = new Application_Model_Log();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'LEADER - Bind('.$id.') - Stores('.$pic.')';

        //todo log
        $QLog->insert( array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');

    echo '<script>parent.location.href="'. HOST.'manage/leader"</script>';
    exit;

} catch(Exception $ex) {
    echo '<script>
            parent.palert("'.$ex->getMessage().'");

            parent.alert("'.$ex->getMessage().'");
        </script>';
    exit;
}

exit;