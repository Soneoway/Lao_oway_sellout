<?php

if ($this->getRequest()->getMethod() == 'POST')
{
    $QModel = new Application_Model_Inform();

    $id = $this->getRequest()->getParam('id');
    $title = $this->getRequest()->getParam('title');
    $content = $this->getRequest()->getParam('content');
    $all_staff = $this->getRequest()->getParam('all_staff', 0);
    $officer = $this->getRequest()->getParam('officer', 0);
    $team_objects = $this->getRequest()->getParam('teams', array());
    $category = $this->getRequest()->getParam('category');

    $all_staff = intval($all_staff);
    $officer = intval($officer);

    $staff_objects = array();

    $key = $this->getRequest()->getParam('key');
    $file_id = $this->getRequest()->getParam('file_id');
    $hash = $this->getRequest()->getParam('hash');
    $nfile_id = $this->getRequest()->getParam('nfile_id');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    try
    {

        $QCategory = new Application_Model_InformCategory();
        $category_check = $QCategory->find($category);
        $category_check = $category_check->current();

        if (!$category_check)
            throw new Exception("Invalid category");

        // lưu nội dung
        $data = array(
            'title' => $title,
            'content' => $content,
            'category_id' => $category,
            'status' => 1,
            );

        if ($id)
        {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;
            $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
            $QModel->update($data, $where);
        } else
        {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage->id;
            $id = $QModel->insert($data);
        }

        $QInformTeam = new Application_Model_InformTeam();
        $where = $QInformTeam->getAdapter()->quoteInto('id = ?', $id);
        $old_objects = $QInformTeam->fetchAll($where);

        $old_team_objects = array();

        // sắp xếp theo nhóm các đối tượng
        foreach ($old_objects as $_key => $_value)
        {
            $old_team_objects[] = $_value['object_id'];
        }

        $add_department_objects = array_diff($department_objects, $old_department_objects);
        $remove_department_objects = array_diff($old_department_objects, $department_objects);

        ////////////////////////////////////////////////////////////////
        $add_team_objects = array_diff($team_objects, $old_team_objects);
        $remove_team_objects = array_diff($old_team_objects, $team_objects);

        foreach ($add_team_objects as $_key => $_value)
        {
            $data = array(
                'id' => $id,
                'object_id' => $_value,
                'type' => My_Notification::TEAM,
                );

            $QInformTeam->insert($data);
        }

        foreach ($remove_team_objects as $_key => $_value)
        {
            $where = array();
            $where[] = $QInformTeam->getAdapter()->quoteInto('id = ?', $id);
            $where[] = $QInformTeam->getAdapter()->quoteInto('object_id = ?', $_value);
            $where[] = $QInformTeam->getAdapter()->quoteInto('type = ?', My_Notification::
                TEAM);

            $QInformTeam->delete($where);
        }


        $QFileLog = new Application_Model_FileUploadLog();
        $QInformFile = new Application_Model_InformFile();

        if (isset($file_id) && is_array($file_id))
        {
            foreach ($file_id as $_key => $_value)
            {

                if (!isset($hash[$_key]) || !$hash[$_key] || !isset($key[$_key]) || !$key[$_key])
                    throw new Exception("Invalid key");

                $log = $QFileLog->find($_value);
                $log = $log->current();

                if (!$log)
                    throw new Exception("Invalid file uploaded");
                $hash_check = hash("sha256", $log['folder'] . $key[$_key] . $log['filename'] .
                    'inform' . $log['id']);

                if ($hash[$_key] != $hash_check)
                    throw new Exception("Invalid file");
             
                $data = array(
                    'inform_id' => $id,
                    'file_display_name' => $log['real_file_name'],
                    'name' => $log['filename'],
                    'unique_folder' => $log['folder'],
                    );

                if (isset($nfile_id[$_key]) && $nfile_id[$_key])
                {
                    $where = $QInformFile->getAdapter()->quoteInto('id = ?', $nfile_id[$_key]);
                    $QInformFile->update($data, $where);

                } else
                {
                    $QInformFile->insert($data);
                }
            } 
        }


        $db->commit();

        $flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('Success');
        My_Controller::redirect(HOST . 'manage/inform');

    }
    catch (exception $e)
    {
        $db->rollback();
        My_Controller::palert($e->getMessage());
    }
}
