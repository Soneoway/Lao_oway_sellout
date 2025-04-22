<?php
$name = $this->getRequest()->getParam('name');
$id = $this->getRequest()->getParam('id');

$flashMessenger = $this->_helper->flashMessenger;

try {
    if (!$name || strlen(trim($name)) == 0) {
        throw new Exception("Invalid name.");    
    }

    $QCompany = new Application_Model_Company();
    $where = array();

    if ($id)
        $where[] = $QCompany->getAdapter()->quoteInto('id <> ?', $id);
    
    $where[] = $QCompany->getAdapter()->quoteInto('name = ?', $name);
    $company_check = $QCompany->fetchRow($where);

    if ($company_check) {
        throw new Exception("Name existed.");
    }

    if ($id) {
        $company_check = $QCompany->find($id);
        $company_check = $company_check->current();

        if (!$company_check) {
            throw new Exception("Invalid ID.");
        }

        $data = array('name' => $name);
        $where = $QCompany->getAdapter()->quoteInto('id = ?', $id);
        $QCompany->update($data, $where);
    } else {
        $data = array('name' => $name);
        $QCompany->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('company_cache');

    $flashMessenger->setNamespace('success')->addMessage('Success');    
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/company');