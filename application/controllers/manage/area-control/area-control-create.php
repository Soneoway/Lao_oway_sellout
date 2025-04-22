<?php

    
    $QSubArea = new Application_Model_SubArea();
    $QSubDistrict = new Application_Model_SubDistrict();
    
    $sub_district = $QSubDistrict->get_sub_district();
    $sub_area = $QSubArea->get_sub_area();


    $this->view->sub_district = $sub_district;
    $this->view->sub_area = $sub_area;

    $this->_helper->viewRenderer->setRender('area-control/create');
