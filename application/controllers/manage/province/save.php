<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_RegionalMarket();
    $id      = $this->getRequest()->getParam('id');
    $name    = $this->getRequest()->getParam('name');
    $area_id = $this->getRequest()->getParam('area_id');
    $districts = $this->getRequest()->getParam('districts');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'PROVINCE - ';

    if ($id) {
        // log vào bảng leader_log
        $region = $QModel->find($id);
        $region = $region->current();

        if ($region) {
            // update bảng region
            $data = array(
                'name'    => $name,
                'area_id' => $area_id,
                'parent'  => 0,
            );

            $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
            $QModel->update($data, $where);

        } else {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('Invalid Province!');

            $back_url = $this->getRequest()->getParam('back_url');
            echo '<script>parent.location.href="'.( $back_url ? $back_url : (HOST.'manage/province') ).'"</script>';
            exit;
        }

        $info .= 'Update('.$id.') - Info ('.serialize($data).')';
    } else {
        $data = array(
            'name'    => $name,
            'area_id' => $area_id,
            'parent'  => 0,
        );

        $id = $QModel->insert($data);

        $info .= 'Insert('.$id.') - Info ('.serialize($data).')';
    }
    // set parent for districts
    $districts = explode(',', $districts);
    $districts = is_array($districts) ? $districts : array();
    
    foreach ($districts as $key => $value)
        $districts[$key] = intval( trim($value) );    

    $districts = array_filter($districts);

    $where = $QModel->getAdapter()->quoteInto('parent = ?', $id);
    $old_district = $QModel->fetchAll($where);
    $current_districts = array();

    foreach ($old_district as $key => $value)
        $current_districts[] = $value['id'];
    
    $del_ids = array_diff($current_districts, $districts);
    $new_ids = array_diff($districts, $current_districts);

    foreach ($del_ids as $key => $value) {
        $check_district = $QModel->find($value);
        $check_district = $check_district->current();

        if ($check_district) {
            $where = $QModel->getAdapter()->quoteInto('id = ?', $value);
            $data = array('parent' => null);
            $QModel->update($data, $where);
        }
    }

    foreach ($new_ids as $key => $value) {
        $check_district = $QModel->find($value);
        $check_district = $check_district->current();

        if (!$check_district) {
            echo '<script>parent.palert("'.'Huyện thứ '.($key+1).' không tồn tại. Vui lòng kiểm tra lại.")</script>';
            exit;
        }

        if (!is_null($check_district['parent']) && $check_district['parent'] == 0) {
            echo '<script>parent.palert("'.'Huyện thứ '.($key+1).' ['.$check_district['name'].'] là một tỉnh. Vui lòng kiểm tra lại.")</script>';
            exit;
        }

        if (!is_null($check_district['parent']) && $check_district['parent'] <> 0 && $check_district['parent'] <> $id) {
            $check_province = $QModel->find($check_district['parent']);
            $check_province = $check_province->current();

            if ($check_province) {
                echo '<script>parent.palert("'.'Huyện thứ '.($key+1).' ['.
                        $check_district['name'].'] đã thuộc tỉnh [<a href=\"'.HOST.'manage/province-create?id='.$check_province['id'].'\" target=\"_blank\">'.
                        $check_province['name'].'</a>]. Vui lòng kiểm tra lại.")</script>';
                exit;
            }
        }

        $where = $QModel->getAdapter()->quoteInto('id = ?', $value);
        $data = array('parent' => $id);
        $QModel->update($data, $where);
    }

    //todo log
    $QLog = new Application_Model_Log();

    $QLog->insert( array(
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

    //

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('regional_market_cache');
    $cache->remove('regional_market_HCM_cache');
    $cache->remove('regional_market_region_cache');
    $cache->remove('regional_market_all_cache');
    $cache->remove('regional_market_district_cache');
    $cache->remove('regional_market_district_by_province_cache');
    $cache->remove('asm_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'.( $back_url ? $back_url : (HOST.'manage/province') ).'"</script>';
exit;