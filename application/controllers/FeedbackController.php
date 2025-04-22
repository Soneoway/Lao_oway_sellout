<?php
class FeedbackController extends My_Controller_Action {

    public function indexAction() {	
    	
    	$page       = $this->getRequest()->getParam('page', 1);
		$sort       = $this->getRequest()->getParam('sort');
		$desc       = $this->getRequest()->getParam('desc', 1);
		$store_name = $this->getRequest()->getParam('store_name');
		$from       = $this->getRequest()->getParam('from');
		$to       	= $this->getRequest()->getParam('to');
		$area_id    = $this->getRequest()->getParam('area_id');

    	$QStaff = new Application_Model_Staff();
    	$QArea = new Application_Model_Area();
    	$QFeedback = new Application_Model_Feedback();
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$areas = $QArea->fetchAll(null, 'name');
		$this->view->areas = $areas;
		$limit = LIMITATION;
		$total = 0;
		$params = array(
			'store_name' => $store_name,
			'from'		 => $from,
			'to'		 => $to,	
			'area_id'    => $area_id,	
			'page'       => $page,
		    'sort'       => $sort,
		    'desc'       => $desc,
			'staff_id'   => $userStorage->id
			);
		
		
		if (in_array($userStorage->group_id, array(ASM_ID))) {
			$QAsm = new Application_Model_Asm();
			$storeList = $QAsm->get_asm_store($userStorage->id);
			$params['store_id']=$storeList;
		}
		
		$feedback = $QFeedback->fetchPagination($page, $limit, $total, $params);

		$this->view->feedback        = $feedback;
		$this->view->url             = HOST.'feedback'.( $params ? '?'.http_build_query($params).'&' : '?' );
		$this->view->staff			 = $QStaff->get_cache();
		$this->view->offset          = $limit*($page-1);
		$this->view->total           = $total;
		$this->view->limit           = $limit;
		$this->view->params          = $params;
    	$flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;
    }

    public function createAction() {
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$id = $this->getRequest()->getParam('id');

    	$QStoreStaff = new Application_Model_StoreStaff();
    	$params['staff_id'] = $userStorage->id;
    	$store_id  = $QStoreStaff->getStoreList($params);

    	// print_r($store_id);die;
    	if ($id) {
    		$QFeedback = new Application_Model_Feedback();
    		$where = $QFeedback->getAdapter()->quoteInto('id = ? ',$id );
    		$this->view->feedback = $QFeedback->fetchRow($where);
    	}

    	$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    	$this->view->store_id = $store_id;
    }

    public function deleteFeedAction() {	
        $flashMessenger = $this->_helper->flashMessenger;
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$id = $this->getRequest()->getParam('id');
    	$QStoreStaff = new Application_Model_StoreStaff();
    	$params['staff_id'] = $userStorage->id;
    	$store_id  = $QStoreStaff->getStoreList($params);

    	if ($id) {
    		$QFeedback = new Application_Model_Feedback();
    		$where = $QFeedback->getAdapter()->quoteInto('id = ? ',$id );
    		$QFeedback->delete($where);
    	}

    	$flashMessenger->setNamespace('success')->addMessage('<i class="icon-ok"></i> Delete Successful!');
    	$url = $this->getRequest()->getServer('HTTP_REFERER');
               $this->redirect($url);
    }

    public function viewAction() {	

    	$id = $this->getRequest()->getParam('id');
    	$act = $this->getRequest()->getParam('act');
        $mark = $this->getRequest()->getParam('mark');
    	$flashMessenger = $this->_helper->flashMessenger;
        
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$QFeedback = new Application_Model_Feedback();
    	$QStoreStaff = new Application_Model_StoreStaff();
    	
    	$params['staff_id'] = $userStorage->id;
    	$params['id'] = $id;
    	$store_id  = $QStoreStaff->getStoreList($params);
    	$feedback  = $QFeedback->viweFeedback($params);
    
    	if (isset($act) and $act == 'read_mark') {

            if (isset($feedback['read_by'])) {
                $read_by = $feedback['read_by'].','.$userStorage->id;
            } else {

                $read_by =$userStorage->id;
            }
            if ($mark == 1) {
               $paramsRead  = array(
                'read_mark' =>$mark,
                'read_by'   =>$read_by,
                'read_at'   =>date('Y-m-d H:i:s')
            );
               // print_r($paramsRead);  die;
            $where = $QFeedback->getAdapter()->quoteInto('id = ? ',$id );
            $QFeedback->update($paramsRead,$where);
            $flashMessenger->setNamespace('success')->addMessage('<i class="icon-ok"></i> Mark as read Successful!');
            }
    		
            $this->redirect(HOST . 'feedback');
    	}

    	// print_r($store_id);die;
    	$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    	$this->view->store_id = $store_id;
    	
    	$this->view->feedback = $feedback;
    }

    public function saveAction() {	

    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$id = $this->getRequest()->getParam('id');
        $store_id = $this->getRequest()->getParam('store_id');
        $title = $this->getRequest()->getParam('title');
        $content = $this->getRequest()->getParam('content');

    	$QFeedback = new Application_Model_Feedback();
    	$flashMessenger = $this->_helper->flashMessenger;
      
        if ($this->getRequest()->getMethod() == 'POST') { 
            $db = Zend_Registry::get('db');

            try {
                $db->beginTransaction();
                
                if ($id) {
                		$params  = array(
            	    		'title'			=>$title,
            	    		'content'		=>$content,
            	    		'store_id'		=>$store_id,
            	    		'update_at'	=>date('Y-m-d H:i:s')
        	    		);

                		$where = $QFeedback->getAdapter()->quoteInto('id = ? ',$id );
                    	$QFeedback->update($params,$where);
                         $flashMessenger->setNamespace('success')->addMessage('<i class="icon-ok"></i> Edit Successful!');
                    } else {
                    	$params  = array(
            	    		'title'			=>$title,
            	    		'content'		=>$content,
            	    		'store_id'		=>$store_id,
            	    		'created_by'	=>$userStorage->id,
            	    		'created_at'	=>date('Y-m-d H:i:s')
        	    		);
                    	$QFeedback->insert($params);
                         $flashMessenger->setNamespace('success')->addMessage('<i class="icon-ok"></i> Create Successful!');
                    }   

           	 	$db->commit();
               
                $this->redirect(HOST . 'feedback');
            } catch (exception $e) {
                $db->rollback();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
            }
        }
	}

}

