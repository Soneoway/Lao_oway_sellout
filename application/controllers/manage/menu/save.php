<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_Menu();
    $id      = $this->getRequest()->getParam('id');
    $title    = $this->getRequest()->getParam('title');
    $parent_id = $this->getRequest()->getParam('parent_id');
    $url = $this->getRequest()->getParam('url');
    $position = $this->getRequest()->getParam('position');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    $info = 'PROVINCE - ';

    if ($id) {
        // log vào bảng leader_log
        $menu = $QModel->find($id);
        $menu = $menu->current();

        if ($menu) {
            // update bảng menu
            $data = array(
                'title'    => $title,
                'parent_id' => $parent_id,
                'url'    => $url,
                'position'  => $position,
                'group_id' => 1,
            );

            $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
            $QModel->update($data, $where);

        } else {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('Invalid Province!');

            $back_url = $this->getRequest()->getParam('back_url');
            echo '<script>parent.location.href="'.( $back_url ? $back_url : (HOST.'manage/menu') ).'"</script>';
            exit;
        }

        $info .= 'Update('.$id.') - Info ('.serialize($data).')';
    } else {
        $data = array(
                'title'    => $title,
                'parent_id' => $parent_id,
                'url'    => $url,
                'position'  => $position,
                'group_id' => 1,
        );

        $id = $QModel->insert($data);

        $info .= 'Insert('.$id.') - Info ('.serialize($data).')';
    }
    // set parent for menus
    $menus = explode(',', $menus);
    $menus = is_array($menus) ? $menus : array();
    
    foreach ($menus as $key => $value)
        $menus[$key] = intval( trim($value) );    

    $menus = array_filter($menus);

    $where = $QModel->getAdapter()->quoteInto('parent_id = ?', $id);
    $old_district = $QModel->fetchAll($where);
    $current_menus = array();

    foreach ($old_district as $key => $value)
        $current_menus[] = $value['id'];
    
    $del_ids = array_diff($current_menus, $menus);
    $new_ids = array_diff($menus, $current_menus);

    foreach ($del_ids as $key => $value) {
        $check_menu = $QModel->find($value);
        $check_menu = $check_menu->current();

        if ($check_menu) {
            $where = $QModel->getAdapter()->quoteInto('id = ?', $value);
            $data = array('parent_id' => null);
            $QModel->update($data, $where);
        }
    }

    //todo log

    //

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('menu_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'.( $back_url ? $back_url : (HOST.'manage/menu') ).'"</script>';
exit;