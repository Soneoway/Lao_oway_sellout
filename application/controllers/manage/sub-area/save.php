<?php
$name           = $this->getRequest()->getParam('name');
$staff_id          = $this->getRequest()->getParam('staff_id');
$id             = $this->getRequest()->getParam('id');
$status         = $this->getRequest()->getParam('status',1);

$flashMessenger = $this->_helper->flashMessenger;

try {
    if (!$name) throw new Exception("Name cannot be blank");
    $name = trim($name);
    
    $QArea = new Application_Model_SubArea();

    if ($id) {
        $sub_area = $QArea->find($id);
        $sub_area = $sub_area->current();

        if (!$sub_area) throw new Exception("Invalid ID");

        $where = array();
        $where[] = $QArea->getAdapter()->quoteInto('name LIKE ?', $name);
        $where[] = $QArea->getAdapter()->quoteInto('id <> ?', $id);
        $sub_area = $QArea->fetchRow($where);

        if ($sub_area) throw new Exception("Duplicated name");

        $data = array(
            'name'          => $name,
            'staff_id'      => $staff_id,
            'status'        => $status
        );

        $where = $QArea->getAdapter()->quoteInto('id = ?', $id);
        $QArea->update($data, $where);

    } else {

        $where = array();
        $where[] = $QArea->getAdapter()->quoteInto('name LIKE ?', $name);
        $sub_area = $QArea->fetchRow($where);

        if ($sub_area) throw new Exception("Duplicated name");

        $data = array(
            'name'          => $name,
            'staff_id'      => $staff_id,
            'status'        => $status
            );
        $QArea->insert($data);

    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('sub_staff_cache');
    $cache->remove('sub_market_staff_by_province_cache');
    $cache->remove('asm_cache');
    
    $flashMessenger->setNamespace('success')->addMessage('Done');

} catch (Exception $e) {

    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/sub-area');