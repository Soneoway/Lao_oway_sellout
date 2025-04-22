<?php
$id = $this->getRequest()->getParam('id');

$flashMessenger = $this->_helper->flashMessenger;

if (!$id) {
    $flashMessenger->setNamespace('error')->addMessage('Invalid ID.');
    $this->_redirect(HOST.'manage/company');
}

$QCompany = new Application_Model_Company();
$company = $QCompany->find($id);
$company = $company->current();

if (!$company) {
    $flashMessenger->setNamespace('error')->addMessage('Invalid ID.');
    $this->_redirect(HOST.'manage/company');
}

$where = $QCompany->getAdapter()->quoteInto('id = ?', $id);
$QCompany->delete($where);

$flashMessenger->setNamespace('success')->addMessage('Success');
$this->_redirect(HOST.'manage/company');