<?php

    $sub_district_id = $this->getRequest()->getParam('sub_district_id');
    $sub_district_id = is_array($sub_district_id) ? array_unique( array_filter( $sub_district_id ) ) : array();

    $sub_area_id = $this->getRequest()->getParam('sub_area_id');
     
    $QModel = new Application_Model_AreaControl();
    if (isset($sub_district_id) && $sub_district_id) {
        foreach ($sub_district_id as $value) {
            $data = array(
                'sub_area_id' => $sub_area_id,
                'sub_district'  => $value,
                );

            $QModel->insert($data);
        }
    }

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

    $this->_redirect(HOST.'manage/area-control');
