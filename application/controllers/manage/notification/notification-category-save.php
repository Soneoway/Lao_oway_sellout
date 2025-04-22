<?php

$name   = $this->getRequest()->getParam('name');
$parent = $this->getRequest()->getParam('parent', 0);
$id     = $this->getRequest()->getParam('id');

try {
    if (!$name) throw new Exception("Name cannot be blank");

    $name   = trim($name);
    $parent = intval($parent);

    $QNotificationCategory = new Application_Model_NotificationCategory();

    if ($parent) {
        $category_check = $QNotificationCategory->find($parent);
        $category_check = $category_check->current();

        if (!$category_check) throw new Exception("Invalid parent");
    }

    $where = array();
    $where[] = $QNotificationCategory->getAdapter()->quoteInto('name = ?', $name);
    $where[] = $QNotificationCategory->getAdapter()->quoteInto('parent = ?', $parent);
    $category_check = $QNotificationCategory->fetchRow($where);

    if ($category_check) throw new Exception("Name exists");

    $data = array(
        'name' => $name,
        'parent' => $parent,
        );

    if ($id) {
        $category_check = $QNotificationCategory->find($id);
        $category_check = $category_check->current();

        if (!$category_check) throw new Exception("Invalid ID");

        $where = $QNotificationCategory->getAdapter()->quoteInto('id = ?', $id);
        $QNotificationCategory->update($data, $where);
    } else {
        $id = $QNotificationCategory->insert($data);
    }

    if ($id) {
        $cache = Zend_Registry::get('cache');
        $cache->remove('notification_category_cache');
        $cache->remove('notification_category_full_cache');
        $cache->remove('notification_category_string_cache');

        $category_cache = $QNotificationCategory->get_string_cache();

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