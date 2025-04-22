<?php
/**
 * Controller Manage nặng quá nên tách mấy cái mang tính config qua đây cho dễ
 * Cái này để dành admin xài thôi
 */
class ConfigController extends My_Controller_Action
{
	public function indexAction()
	{
		
	}



    /////////////////////////////////////////////
    /////////// Quản lý email sinh nhật
    /////////////////////////////////////////////
    
    public function birthdayAction()
    {
    	$QConfig = new Application_Model_Config();
    	$configs = $QConfig->fetchAll();

    	foreach ($configs as $key => $conf) {
    		if ( $conf['key'] == 'birthday_image' ) {
    			$this->view->birthday_image = $conf;
    		}

    		if ( $conf['key'] == 'birthday_wish' ) {
    			$this->view->birthday_wish = $conf;
    		}
    	}

    	$flashMessenger = $this->_helper->flashMessenger;
    	$this->view->messages = $flashMessenger->setNamespace('success')->getMessages();
    }

    public function birthdaySaveAction()
    {
    	$image = $this->getRequest()->getParam('image');
    	$wish = $this->getRequest()->getParam('wish');

    	$QConfig = new Application_Model_Config();

    	$data = array(
    		'value' => $image,
    		);

    	$where = $QConfig->getAdapter()->quoteInto('`key` = ?', 'birthday_image');
    	$QConfig->update($data, $where);

    	$data = array(
    		'value' => $wish,
    		);

    	$where = $QConfig->getAdapter()->quoteInto('`key` = ?', 'birthday_wish');
    	$QConfig->update($data, $where);
    	
    	$flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('Done!');

		$cache = Zend_Registry::get('cache');
    	$cache->remove('config_cache');

    	$this->_redirect(HOST.'config/birthday');
    }

    /////////////////////////////////////////////
    /////////// Quản lý dashboard
    /////////////////////////////////////////////

    public function dashboardAction()
    {
		$page   = $this->getRequest()->getParam('page', 1);
		$total  = 0;
		$limit  = LIMITATION;
		$params = array();

    	$QDashboard = new Application_Model_Dashboard();
		
		$this->view->dashboards = $QDashboard->fetchPagination($page, $limit, $total, $params);
		$this->view->params     = $params;
		$this->view->limit      = $limit;
		$this->view->total      = $total;
		$this->view->url        = HOST.'manage/dashboard/'.( $params ? '?'.http_build_query($params).'&' : '?' );

        $flashMessenger = $this->_helper->flashMessenger;
		$messages       = $flashMessenger->setNamespace('success')->getMessages();
		$messages_error = $flashMessenger->setNamespace('error')->getMessages();
		$this->view->messages       = $messages;
		$this->view->messages_error = $messages_error;

        $this->_helper->viewRenderer->setRender('dashboard/index');
    }

    public function dashboardCreateAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$flashMessenger = $this->_helper->flashMessenger;
		$QGroup = new Application_Model_Group();
		$this->view->groups = $QGroup->get_cache();

    	if ($id) {
    		// lấy dashboard theo id
    		$QDashboard = new Application_Model_Dashboard();
    		$dashboard = $QDashboard->find($id);
    		$dashboard = $dashboard->current();

    		if ( $dashboard ) {
    			$this->view->dashboard = $dashboard;
    			$this->view->group_ids = array_filter( explode( ',', trim( $dashboard->group_ids ) ) );
	            $ids = array_filter( explode( ',', trim( $dashboard->staff_ids ) ) );
	            $QStaff = new Application_Model_Staff();
	            
	            if (is_array($ids) && count($ids) > 0) {
	            	$where = $QStaff->getAdapter()->quoteInto('id IN (?)', $ids);
	            	$staffs = $QStaff->fetchAll($where);
	            } else {
	            	$staffs = null;
	            }

	            $this->view->staffs = $staffs;

	            $QRegionalMarket = new Application_Model_RegionalMarket();
	            $this->view->regional_market_all = $QRegionalMarket->get_cache();
	            $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    		} else {
    			$flashMessenger->setNamespace('error')->addMessage('Invalid ID');
    			$this->_redirect(HOST.'manage/dashboard');
    		}
    	}
		
		$this->_helper->viewRenderer->setRender('dashboard/create');
    }

    public function dashboardSaveAction()
    {
		$id           = $this->getRequest()->getParam('id');
		$name         = $this->getRequest()->getParam('name');
		$description  = $this->getRequest()->getParam('description');
		$table        = $this->getRequest()->getParam('table');
		$cols_existed = $this->getRequest()->getParam('cols_existed');
		$cols_needed  = $this->getRequest()->getParam('cols_needed');
		$group_ids    = $this->getRequest()->getParam('group_ids');
		$status       = $this->getRequest()->getParam('status', 0);
		$editable     = $this->getRequest()->getParam('editable', 0);
		$add_note     = $this->getRequest()->getParam('add_note', 0);
		$add_email    = $this->getRequest()->getParam('add_email', 0);
		$ids          = $this->getRequest()->getParam('pic', NULL);

		if ($this->getRequest()->getMethod() == 'POST') {
    		$QDashboard = new Application_Model_Dashboard();

            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $data = array(
				'name'         => $name,
				'description'  => trim($description),
				'group_ids'    => ( $group_ids ? implode(',', $group_ids) : null),
				'staff_ids'    => $ids,
				'table'        => $table,
				'status'       => $status,
				'editable'     => $editable,
				'add_note'     => $add_note,
				'add_email'    => $add_email,
				'cols_existed' => trim($cols_existed),
				'cols_needed'  => trim($cols_needed),
            );

			if ($id) {
				$where = $QDashboard->getAdapter()->quoteInto('id = ?', $id);
				$QDashboard->update($data, $where);
			} else {
				$QDashboard->insert($data);
			}

			$flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('success')->addMessage('Done!');

			$cache = Zend_Registry::get('cache');
        	$cache->remove('dashboard_cache');
		}

		$this->_redirect(HOST.'config/dashboard');
    }

    
}