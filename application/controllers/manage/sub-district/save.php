<?php
$name = $this->getRequest()->getParam('name');
$district = $this->getRequest()->getParam('district');
$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try {
    if (!$name) throw new Exception("Name cannot be blank");
    if (!$district) throw new Exception("Province is required");
    $name = trim($name);
    
    $QDistrict = new Application_Model_SubDistrict();

    if ($id) {
        $sub_district = $QDistrict->find($id);
        $sub_district = $sub_district->current();

        if (!$sub_district) throw new Exception("Invalid ID");

        $where = array();
        $where[] = $QDistrict->getAdapter()->quoteInto('name LIKE ?', $name);
        $where[] = $QDistrict->getAdapter()->quoteInto('id <> ?', $id);
        //$where[] = $QDistrict->getAdapter()->quoteInto('parent <> ?', 1);
        $sub_district = $QDistrict->fetchRow($where);

        if ($sub_district) throw new Exception("Duplicated name");

        $data = array(
            'name'   => $name,
            'district' => $district,
            );
        $where = $QDistrict->getAdapter()->quoteInto('id = ?', $id);
        $QDistrict->update($data, $where);
    } else {
        $where = array();
        $where[] = $QDistrict->getAdapter()->quoteInto('name LIKE ?', $name);
        //$where[] = $QDistrict->getAdapter()->quoteInto('parent <> ?', 1);
        $sub_district = $QDistrict->fetchRow($where);

        if ($sub_district) throw new Exception("Duplicated name");

        $data = array(
            'name'   => $name,
            'district' => $district,
            );
        $QDistrict->insert($data);
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('sub_district_cache');
    $cache->remove('sub_market_district_by_province_cache');
    $cache->remove('asm_cache');
    
    $flashMessenger->setNamespace('success')->addMessage('Done');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/sub-district');