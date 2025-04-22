<?php
$name = $this->getRequest()->getParam('name');
$parent = $this->getRequest()->getParam('province');
$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try {
    if (!$name) throw new Exception("Name cannot be blank");
    if (!$parent) throw new Exception("Province is required");
    $name = trim($name);
    
    $QRegion = new Application_Model_RegionalMarket();

    if ($id) {
        $region = $QRegion->find($id);
        $region = $region->current();

        if (!$region) throw new Exception("Invalid ID");

        $where = array();
        $where[] = $QRegion->getAdapter()->quoteInto('name LIKE ?', $name);
        $where[] = $QRegion->getAdapter()->quoteInto('id <> ?', $id);
        $where[] = $QRegion->getAdapter()->quoteInto('parent <> 0', 1);
        $region = $QRegion->fetchRow($where);

        if ($region) throw new Exception("Duplicated name");

        $data = array(
            'name'   => $name,
            'parent' => $parent,
            );
        $where = $QRegion->getAdapter()->quoteInto('id = ?', $id);
        $QRegion->update($data, $where);
    } else {
        $where = array();
        $where[] = $QRegion->getAdapter()->quoteInto('name LIKE ?', $name);
        $where[] = $QRegion->getAdapter()->quoteInto('parent <> 0', 1);
        $region = $QRegion->fetchRow($where);

        if ($region) throw new Exception("Duplicated name");

        $data = array(
            'name'   => $name,
            'parent' => $parent,
            );
        $QRegion->insert($data);
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('regional_market_district_cache');
    $cache->remove('regional_market_district_by_province_cache');
    $cache->remove('asm_cache');
    
    $flashMessenger->setNamespace('success')->addMessage('Done');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/district');