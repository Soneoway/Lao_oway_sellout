<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

if ($this->getRequest()->getMethod() == 'POST'){
    $QModel = new Application_Model_RegionalMarket();
    $QStaff = new Application_Model_Staff();

    $id      = $this->getRequest()->getParam('id');
    $name    = $this->getRequest()->getParam('name');
    $area_id = $this->getRequest()->getParam('area_id');
    $pic     = $this->getRequest()->getParam('pic');

    $pic = trim(trim($pic), ',');

    // validate
    $check = array_filter(explode(',', $pic));

    if (count($check) > 1) {
        echo '<script>
                parent.palert("Mỗi Province chỉ có thể có 01 Leader!");
            </script>';
        exit;
    }

    foreach ($check as $key => $value) {
        $staff = $QStaff->find($value);
        $staff = $staff->current();

        if ($staff['group_id'] != LEADER_ID) {
            echo '<script>
                    parent.palert("Staff ['.$staff['firstname'] .' ' .$staff['lastname'] . ' | ' .preg_replace('/'.EMAIL_SUFFIX.'/', '@', $staff['email']) .'] chưa thuộc group Leader. Vui lòng kiểm tra lại!");
                </script>';
            exit;
        }
    }
    //

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'PROVINCE - ';

    $QLeaderLog = new Application_Model_LeaderLog();

    if ($id) {
        // log vào bảng leader_log
        $region = $QModel->find($id);
        $region = $region->current();

        if ($region) {
            // so sánh xem có thay đổi leader hay không
            // Mặc dù chỉ chấp nhận 1 leader nhưng viết sẵn như này, sau này 
            //      có cho phép 2, 3 leader/tỉnh thì khỏi viết lại
            $old_leader = explode(',',
                trim( isset( $region['leader_ids'] )
                    ? $region['leader_ids']
                    : '' )
            );
            $old_leader = array_filter( is_array( $old_leader ) ? $old_leader : array() );

            $new_leader = explode(',',
                trim( isset( $pic ) ? $pic : '' )
            );
            $new_leader = array_filter( is_array( $new_leader ) ? $new_leader : array() );

            // danh sách các leader MỚI được thêm vào và loại ra
            // chỉ update log của các leader có thay đổi này
            $add_leader = array_diff($new_leader, $old_leader);
            $remove_leader = array_diff($old_leader, $new_leader);

            // update log các leader thêm vào
            if (count($add_leader) > 0) {
                foreach ($add_leader as $leader_id) {
                    $data = array(
                        'staff_id' => $leader_id,
                        'regional_market' => $id,
                        'from_date' => time(),
                    );

                    $QLeaderLog->insert($data);
                }
            }

            // update log các leader loại ra
            if (count($remove_leader) > 0) {
                foreach ($remove_leader as $leader_id) {
                    $where = array();
                    $where[] = $QLeaderLog->getAdapter()->quoteInto('staff_id = ?', $leader_id);
                    $where[] = $QLeaderLog->getAdapter()->quoteInto('regional_market = ?', $id);
                    $where[] = $QLeaderLog->getAdapter()->quoteInto('from_date IS NOT NULL AND from_date > ?', 0);
                    $where[] = $QLeaderLog->getAdapter()->quoteInto('to_date IS NULL OR to_date = ?', 0);

                    $data = array(
                        'to_date' => time(),
                    );

                    $QLeaderLog->update($data, $where);
                }
            }

            //
            // update bảng region
            $data = array(
                'name'       => $name,
                'area_id'    => $area_id,
                'leader_ids' => $pic,
            );

            $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
            $QModel->update($data, $where);

        } else {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('Invalid Province!');

            $back_url = $this->getRequest()->getParam('back_url');
            echo '<script>parent.location.href="'.( $back_url ? $back_url : '/timing' ).'"</script>';
        }

        $info .= 'Update('.$id.') - Info ('.serialize($data).')';
    } else {
        $data = array(
            'name'       => $name,
            'area_id'    => $area_id,
        );

        if (isset($pic) and $pic)
            $data['leader_ids'] = $pic;

        $id = $QModel->insert($data);

        // log vào bảng leader_log
        $new_leader = explode(',',
            trim( isset( $pic ) ? $pic : '' )
        );
        $new_leader = array_filter( is_array( $new_leader ) ? $new_leader : array() );

        if (count($new_leader) > 0) {
            foreach ($new_leader as $leader_id) {
                $data = array(
                    'staff_id' => $leader_id,
                    'regional_market' => $id,
                    'from_date' => time(),
                );

                $QLeaderLog->insert($data);
            }
        }

        $info .= 'Insert('.$id.') - Info ('.serialize($data).')';
    }
    //

    //todo log
    $QLog = new Application_Model_Log();

    $QLog->insert( array(
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

    //
    //

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('regional_market_cache');
    $cache->remove('regional_market_HCM_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'.( $back_url ? $back_url : '/timing' ).'"</script>';