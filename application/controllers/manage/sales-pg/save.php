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

    $is_leader = $staff['group_id'] == SALES_ID ? 1 : 0;

    $pic = $this->getRequest()->getParam('store_ids');

    if (!$pic && ($pic && strlen($pic) == 0)) {
        // gỡ hết store của leader
    } else {
        $store_ids = explode(',', $pic);
        $store_ids = is_array($store_ids) ? array_filter($store_ids) : array();

        $store_ids = array_unique($store_ids);

        $joined_dates = $this->getRequest()->getParam('joined_dates', '{}');
        $joined_dates = json_decode($joined_dates, true);

        $QStore          = new Application_Model_Store();
        $QStoreLeader    = new Application_Model_StoreStaff();
        $QStoreLeaderLog = new Application_Model_StoreStaffLog();
        $QRegion         = new Application_Model_RegionalMarket();
        $QCasual         = new Application_Model_CasualWorker();
        $QLog            = new Application_Model_Log();
        $region_cache    = $QRegion->get_cache_all();

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
            // Kiểm tra cửa hàng này có sales nào đứng chưa, trường hợp gán cho sales
            if ($is_leader) {
                $where = array();
                $where[] = $QStoreLeader->getAdapter()->quoteInto('staff_id <> ?', $id);
                $where[] = $QStoreLeader->getAdapter()->quoteInto('store_id = ?', $s_id);

                $store_check = $QStoreLeader->fetchRow($where);

                if ($store_check) {
                    $store_check = $QStore->find($s_id);
                    $store_check = $store_check->current();

                    echo '<script>
                            parent.palert("Trong danh sách cửa hàng, cửa hàng thứ ['.($k+1).'] (<a href=\"'.HOST.'manage/store-edit?id='.$store_check['id'].'\" target=\"_blank\">'
                                .$store_check['name'] .'</a>) đã có Sales phụ trách. Vui lòng kiểm tra lại.");

                            parent.alert("Trong danh sách cửa hàng, cửa hàng thứ ['.($k+1).'] ('.$store_check['id'].''
                                .$store_check['name'] .') đã có Sales phụ trách. Vui lòng kiểm tra lại.");
                        </script>';
                    exit;
                }
            }
            
            // kiểm tra vùng và ngoại lệ
            // 
            $where = $QRegion->getAdapter()->quoteInto('area_id IN (?)', array(HCMC1, HCMC2, HCMC3, HCMC4, HCMC5, HN1, HN2, HN3, HN4));
            $hcm_hn_regions = $QRegion->fetchAll($where);
            $hcm_hn_regions_arr = array();

            foreach ($hcm_hn_regions as $key => $value) {
                $hcm_hn_regions_arr[] = $value['id'];
            }

            $store_check = $QStore->find($s_id);
            $store_check = $store_check->current();

            if (!$store_check) {
                echo '<script>
                        parent.palert("Store thứ '.($k+1) .' không có thật, vui lòng kiểm tra lại.");

                        parent.alert("Store thứ '.($k+1) .' không có thật, vui lòng kiểm tra lại.");
                    </script>';
                exit;
            }
            
            if ($staff['regional_market'] != $store_check['regional_market'] ) {
                if ( ! (in_array($store_check['regional_market'], $hcm_hn_regions_arr) && in_array($staff['regional_market'], $hcm_hn_regions_arr)) ) {

                    // check ngoại lệ region
                    $casuals = $QCasual->get_cache();

                    if (isset( $casuals[ $staff['id'] ] ) 
                        && $casuals[ $staff['id'] ]['status'] == 1
                        && $casuals[ $staff['id'] ]['area_id'] == @$region_cache[ $store_check['regional_market'] ]['area_id']) {
                        
                    } else {

                        echo '<script>
                                parent.palert("Trong danh sách cửa hàng, cửa hàng thứ ['.($k+1).'] ('
                                    .$store_check['name'] .') nằm khác tỉnh so với hồ sơ của nhân viên. Vui lòng liên hệ Phòng Nhân sự nếu cần điều chỉnh.");

                                parent.alert("Trong danh sách cửa hàng, cửa hàng thứ ['.($k+1).'] ('
                                    .$store_check['name'].') nằm khác tỉnh so với hồ sơ của nhân viên. Vui lòng liên hệ Phòng Nhân sự nếu cần điều chỉnh.");
                            </script>';
                        exit;
                    }
                }
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
                'store_id'  => $s_id,
                'staff_id'  => $id,
                'is_leader' => $is_leader,
                );

            $parent = $QStoreLeader->insert($data);

            $data = array(
                'parent'    => $parent,
                'is_leader' => $is_leader,
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

        
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = 'SALES/PG - Bind('.$id.') - Stores('.$pic.')';

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

    echo '<script>parent.location.href="'. HOST.'manage/sales-pg"</script>';
    exit;

} catch(Exception $ex) {
    echo '<script>
            parent.palert("'.$ex->getMessage().'");

            parent.alert("'.$ex->getMessage().'");
        </script>';
    exit;
}

exit;