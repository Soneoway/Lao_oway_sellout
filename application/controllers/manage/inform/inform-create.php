<?php

        $id = $this->getRequest()->getParam('id');
        
        if ($id)
        {
            $QModel = new Application_Model_Inform();
            $rowset = $QModel->find($id);
            $inform = $rowset->current();
        
            if (!$inform)
                $this->_redirect(HOST . 'manage/inform');
        
            $this->view->inform = $inform;
        
            $QStaff = new Application_Model_Staff();
        
            $QInformTeam = new Application_Model_InformTeam();
            $where = $QInformTeam->getAdapter()->quoteInto('id = ?', $id);
            $old_objects = $QInformTeam->fetchAll($where);
        
            $old_team_objects = array();
        
            $QInformFile = new Application_Model_InformFile();
            $where = $QInformFile->getAdapter()->quoteInto('inform_id = ?', $id);
            $files = $QInformFile->fetchAll($where);
        
        
            foreach ($old_objects as $_key => $_value)
            {
                $old_team_objects[] = $_value['object_id'];
            }
        
            $this->view->old_team_objects = $old_team_objects;
        
            $this->view->userStorage = Zend_Auth::getInstance()->getStorage()->read();
        }
        
        $QTeam = new Application_Model_Team();
        $QInformCategory = new Application_Model_InformCategory();
        
        
        $this->view->files = $files;
        $this->view->team_cache = $QTeam->get_cache_team();
        $this->view->category_string_cache = $QInformCategory->get_cache();
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
        
        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
        
        $this->_helper->viewRenderer->setRender('inform/create');
