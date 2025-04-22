<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel = new Application_Model_Notification();
    
    $id                 = $this->getRequest()->getParam('id');
    $title              = $this->getRequest()->getParam('title');
    $content            = $this->getRequest()->getParam('content');
    $all_staff          = $this->getRequest()->getParam('all_staff', 0);
    $officer            = $this->getRequest()->getParam('officer', 0);
    $department_objects = $this->getRequest()->getParam('departments', array());
    $team_objects       = $this->getRequest()->getParam('teams', array());
    $title_objects      = $this->getRequest()->getParam('staff_titles', array());
    $area_objects       = $this->getRequest()->getParam('areas', array());
    $staffs             = $this->getRequest()->getParam('pic', NULL);
    $category           = $this->getRequest()->getParam('category');
    $pop_up             = $this->getRequest()->getParam('pop_up', 0);
    $from               = $this->getRequest()->getParam('from', null);
    $to                 = $this->getRequest()->getParam('to', null);
    $status             = $this->getRequest()->getParam('status', 0);
    
    $all_staff = intval($all_staff);
    $officer   = intval($officer);
    $pop_up    = intval($pop_up);
    $category  = intval($category);

    $staff_objects = array();

    if ($staffs && strlen($staffs))
        $staff_objects = explode(',', $staffs);

    $staff_objects = is_array($staff_objects) ? $staff_objects : array();

    $key         = $this->getRequest()->getParam('key');
    $file_id     = $this->getRequest()->getParam('file_id');
    $hash        = $this->getRequest()->getParam('hash');
    $nfile_id    = $this->getRequest()->getParam('nfile_id');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    try {
        // kiểm tra category
        $QCategory = new Application_Model_NotificationCategory();
        $category_check = $QCategory->find($category);
        $category_check = $category_check->current();

        if (!$category_check) throw new Exception("Invalid category");

        // lưu nội dung
        $data = array(
            'title'       => $title,
            'content'     => $content,
            'category_id' => $category,
            'status'      => 1,
            'pop_up'      => $pop_up >= 1 ? 1 : 0,
            'show_from'   => !$from ? null : DateTime::createFromFormat('d/m/Y H:i', $from)->format('Y-m-d H:i:00'),
            'show_to'     => !$to ? null : DateTime::createFromFormat('d/m/Y H:i', $to)->format('Y-m-d H:i:59'),
            'status'      => $status,
        );

        if ($id){
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;
            $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
            $QModel->update($data, $where);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage->id;
            $id = $QModel->insert($data);
        }

        // lưu file
        $QFileLog            = new Application_Model_FileUploadLog();
        $QNotificationFile   = new Application_Model_NotificationFile();
        $QNotificationObject = new Application_Model_NotificationObject();

        if (isset($file_id) && is_array($file_id)) {
            foreach ($file_id as $_key => $_value) {

                if (!isset($hash[$_key]) || !$hash[$_key] || !isset($key[$_key]) || !$key[$_key])
                    throw new Exception("Invalid key");

                // check trong log để đảm bảo file đã up lên
                $log = $QFileLog->find($_value);
                $log = $log->current();

                if (!$log) throw new Exception("Invalid file uploaded");

                // check để đảm bảo file upload lên và id nó gửi lên là đúng
                $hash_check = hash("sha256", $log['folder'] . $key[$_key] . $log['filename'] . 'notification' .$log['id']);

                if ($hash[$_key] != $hash_check) throw new Exception("Invalid file");

                // lưu thông tin file đính kèm
                $data = array(
                    'notification_id'   => $id,
                    'file_display_name' => $log['real_file_name'],
                    'file_real_name'    => $log['filename'],
                    'unique_folder'     => $log['folder'],
                    );

                if (isset($nfile_id[$_key]) && $nfile_id[$_key]) {
                    $where = $QNotificationFile->getAdapter()->quoteInto('id = ?', $nfile_id[ $_key ] );
                    $QNotificationFile->update($data, $where);

                } else {
                    $QNotificationFile->insert($data);
                }
            } // END foreach file_id
        } // END if check file_id

        // lấy nhóm hiển thị đã lưu trước đó
        $where = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', $id);
        $old_objects = $QNotificationObject->fetchAll($where);

        $old_department_objects = array();
        $old_team_objects       = array();
        $old_title_objects      = array();
        $old_area_objects       = array();
        $old_staff_objects      = array();

        // sắp xếp theo nhóm các đối tượng
        foreach ($old_objects as $_key => $_value) {
            switch ($_value['type']) {
                case My_Notification::DEPARTMENT:
                    $old_department_objects[] = $_value['object_id'];
                    break;

                case My_Notification::STAFF:
                    $old_staff_objects[] = $_value['object_id'];
                    break;

                case My_Notification::AREA:
                    $old_area_objects[] = $_value['object_id'];
                    break;

                case My_Notification::TEAM:
                    $old_team_objects[] = $_value['object_id'];
                    break;

                case My_Notification::TITLE:
                    $old_title_objects[] = $_value['object_id'];
                    break;

                case My_Notification::ALL_STAFF:
                case My_Notification::OFFICER:
                    break;
                default:
                    throw new Exception("Invalid object type");
                    break;
            }
        }
        // update nhóm hiển thị
        $add_department_objects = array_diff($department_objects, $old_department_objects);
        $remove_department_objects = array_diff($old_department_objects, $department_objects);

        foreach ($add_department_objects as $_key => $_value) {
            $data = array(
                'notification_id' => intval($id),
                'object_id'       => intval($_value),
                'type'            => My_Notification::DEPARTMENT,
                );

            $QNotificationObject->insert($data);
        }

        foreach ($remove_department_objects as $_key => $_value) {
            $where = array();
            $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('object_id = ?', intval($_value));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::DEPARTMENT);

            $QNotificationObject->delete($where);
        }
        ////////////////////////////////////////////////////////////////
        $add_team_objects = array_diff($team_objects, $old_team_objects);
        $remove_team_objects = array_diff($old_team_objects, $team_objects);

        foreach ($add_team_objects as $_key => $_value) {
            $data = array(
                'notification_id' => intval($id),
                'object_id'       => intval($_value),
                'type'            => My_Notification::TEAM,
                );

            $QNotificationObject->insert($data);
        }

        foreach ($remove_team_objects as $_key => $_value) {
            $where = array();
            $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', $id);
            $where[] = $QNotificationObject->getAdapter()->quoteInto('object_id = ?', $_value);
            $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::TEAM);

            $QNotificationObject->delete($where);
        }

        ////////////////////////////////////////////////////////////////
        $add_title_objects = array_diff($title_objects, $old_title_objects);
        $remove_title_objects = array_diff($old_title_objects, $title_objects);

        foreach ($add_title_objects as $_key => $_value) {
            $data = array(
                'notification_id' => intval($id),
                'object_id'       => intval($_value),
                'type'            => My_Notification::TITLE,
                );

            $QNotificationObject->insert($data);
        }

        foreach ($remove_title_objects as $_key => $_value) {
            $where = array();
            $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('object_id = ?', intval($_value));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::TITLE);

            $QNotificationObject->delete($where);
        }

        ////////////////////////////////////////////////////////////////
        $add_area_objects = array_diff($area_objects, $old_area_objects);
        $remove_area_objects = array_diff($old_area_objects, $area_objects);

        foreach ($add_area_objects as $_key => $_value) {
            $data = array(
                'notification_id' => intval($id),
                'object_id'       => intval($_value),
                'type'            => My_Notification::AREA,
                );

            $QNotificationObject->insert($data);
        }

        foreach ($remove_area_objects as $_key => $_value) {
            $where = array();
            $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('object_id = ?', intval($_value));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::AREA);

            $QNotificationObject->delete($where);
        }
        ////////////////////////////////////////////////////////////////
        $add_staff_objects = array_diff($staff_objects, $old_staff_objects);
        $remove_staff_objects = array_diff($old_staff_objects, $staff_objects);

        foreach ($add_staff_objects as $_key => $_value) {
            $data = array(
                'notification_id' => intval($id),
                'object_id'       => intval($_value),
                'type'            => My_Notification::STAFF,
                );

            $QNotificationObject->insert($data);
        }

        foreach ($remove_staff_objects as $_key => $_value) {
            $where = array();
            $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('object_id = ?', intval($_value));
            $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::STAFF);

            $QNotificationObject->delete($where);
        }
        ////////////////////////////////////////////////////////////////
        $data = array(
            'notification_id' => intval($id),
            'object_id'       => intval($all_staff),
            'type'            => My_Notification::ALL_STAFF,
            );

        $where = array();
        $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
        $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::ALL_STAFF);

        $object_all_staff = $QNotificationObject->fetchRow($where);


        if ($object_all_staff) {
            $QNotificationObject->update($data, $where);
        } else {
            $all_staff_id = $QNotificationObject->insert($data);
            
            if (!$all_staff_id) throw new Exception("Insert failed");
        }
        ////////////////////////////////////////////////////////////////
        $data = array(
            'notification_id' => intval($id),
            'object_id'       => intval($officer),
            'type'            => My_Notification::OFFICER,
            );

        $where = array();
        $where[] = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', intval($id));
        $where[] = $QNotificationObject->getAdapter()->quoteInto('type = ?', My_Notification::OFFICER);

        $object_officer = $QNotificationObject->fetchRow($where);


        if ($object_officer) {
            $QNotificationObject->update($data, $where);
        } else {
            $officer_id = $QNotificationObject->insert($data);

            if (!$officer_id) throw new Exception("Insert failed");
        }
        ////////////////////////////////////////////////////////////////

        $db->commit();

        $flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('Success');
        My_Controller::redirect(HOST.'manage/notification');

    } catch (Exception $e) {
        $db->rollback();
        My_Controller::palert($e->getMessage());
    }
}