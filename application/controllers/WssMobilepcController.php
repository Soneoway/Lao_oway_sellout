<?php
class WssMobilepcController extends My_Controller_Action {

    // API #1 
    public function loginAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $user = $this->getRequest()->getParam('username');
        $pass = $this->getRequest()->getParam('password');

        //$user = '5901582';
        //$pass = '123';

        $username = $user."@oppo.in.th";
        $password = md5($pass);

        $allow = array(PGPB_ID,PCM_ID,TRAINING_TEAM_ID);
        
        $db = Zend_Registry::get('db');

        $QStaff = new Application_Model_Staff();
        $QGroup = new Application_Model_Group();

        $group_list = $QGroup->get_cache();

        // for Master Password
        if ($password !== 'a0bd1bd4c2cbd10a57adb9b24daa8ad8') {
            $where[] = $QStaff->getAdapter()->quoteInto('password = ?', $password);
        }
        
        $where[] = $QStaff->getAdapter()->quoteInto('email = ?', $username);
        $where[] = $QStaff->getAdapter()->quoteInto('group_id IN (?)', $allow);

        $result = $QStaff->fetchRow($where); 

        $data = array();
        $store_list = array();

        if (isset($result)) {
            $params['staff_id'] = $result['id'];

            $QSS = new Application_Model_StoreStaff();
            $ss_result = $QSS->getStoreList($params);

            for ($i=0;$i<count($ss_result);$i++) {
                $store_list[$i] = array(
                    'shop_id'   => $ss_result[$i]['store_id'],
                    'shop_name' => $ss_result[$i]['store_name']
                );
            }
            
            $data = array(
                'status'        => 1, 
                'staff_code'    => $user,
                'staff_id'      => $result['id'],
                'staff_name'    => $result['firstname']." ".$result['lastname'],
                'group_name'    => $group_list[ $result['group_id'] ]
            );

            for ($i=0;$i<count($store_list);$i++) {
                $data['shop_list'][$i] = $store_list[$i];
            }

            //print_r($data);
            echo json_encode($data);
        } else {

            $data = array(
                'status'        => 0,
                'staff_code'    => null,
                'staff_id'      => null,
                'staff_name'    => null,
                'group_name'    => null,
                'shop_list'     => null
            );

            echo json_encode($data);
        }

    }

    // API #2 
    public function saveAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        print_r($_POST);


        $upload = new Zend_File_Transfer();
        $upload->setOptions(array('ignoreNoFile'=>true));

        //check function
        if (function_exists('finfo_file'))
            $upload->addValidator('MimeType', false, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'));

        $upload->addValidator('Extension', false, 'jpg,jpeg,png,gif');
        $upload->addValidator('Size', false, array('max' => '2MB'));
        $upload->addValidator('ExcludeExtension', false, 'php,sh');
        $files = $upload->getFileInfo();

        print_r($files);

    }

}