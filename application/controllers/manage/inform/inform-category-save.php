<?php

$name   = $this->getRequest()->getParam('name');
$id     = $this->getRequest()->getParam('id');

try {
    if (!$name) throw new Exception("Name cannot be blank");

    $name   = trim($name);

    $QInformCategory = new Application_Model_InformCategory();

    $where = array();
    $where[] = $QInformCategory->getAdapter()->quoteInto('name = ?', $name);
    $category_check = $QInformCategory->fetchRow($where);

    if ($category_check) throw new Exception("Name exists");

    $data = array(
        'name' => $name,
        );

    if ($id) {
        $category_check = $QInformCategory->find($id);
        $category_check = $category_check->current();

        if (!$category_check) throw new Exception("Invalid ID");

        $where = $QInformCategory->getAdapter()->quoteInto('id = ?', $id);
        $QInformCategory->update($data, $where);
    } else {
        $id = $QInformCategory->insert($data);
    }

    if ($id) {
        $cache = Zend_Registry::get('cache');
        $cache->remove('inform_category_cache');

        $category_cache = $QInformCategory->get_cache();

        exit(json_encode(array(
            'type'     => 'success',
            'message'  => 'Success',
            'new_data' => $category_cache,
            )));
    } else
        throw new Exception("Insert failed");
        
} catch (Exception $e) {
    exit(json_encode(array(
        'type' => "danger",
        'message' => $e->getMessage(),
        )));
}