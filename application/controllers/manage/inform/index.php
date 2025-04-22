<?php
$page = $this->getRequest()->getParam('page', 1);
$title = $this->getRequest()->getParam('title');
$content = $this->getRequest()->getParam('content');
$sort = $this->getRequest()->getParam('sort');
$desc = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array_filter(array(
    'title' => $title,
    'content' => $content,
    'sort' => $sort,
    'desc' => $desc,
    ));
$params['sort'] = $sort;
$params['desc'] = $desc;
$params['group_cat'] = 1;
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;



if ($userStorage->id == SUPERADMIN_ID || in_array($group_id, array(
    ADMINISTRATOR_ID,
    HR_ID,
    HR_EXT_ID,
    BOARD_ID))) {
    $this->_helper->viewRenderer->setRender('inform/inform-edit');
    unset($params['group_cat']);
    $QModel = new Application_Model_Inform();
    $inform = $QModel->fetchPagination($page, $limit, $total, $params);
    $QStaff = new Application_Model_Staff();
    $QInformCategory = new Application_Model_InformCategory();
    $inform_category = $QInformCategory->get_cache();
    $staff = $QStaff->get_cache();
    $informs = array();
    $this->view->inform_category = $inform_category;
    $this->view->informs = $inform;
} else {
    if ($userStorage->id != SUPERADMIN_ID || !in_array($group_id, array(
    ADMINISTRATOR_ID,
    HR_ID,
    HR_EXT_ID,
    BOARD_ID)))
    $params['team'] = $userStorage->team;
    $QModel = new Application_Model_Inform();
    $inform = $QModel->fetchPagination($page, $limit, $total, $params);

    $informs = array();

    foreach ($inform as $key => $m) {

        $where = array();
        $where[] = $QModel->getAdapter()->quoteInto('category_id = ?', $m['category_id']);
        $where[] = $QModel->getAdapter()->quoteInto('status = ?', 1);
        $informs[$m['category_id']] = $QModel->fetchAll($where);

    }

    $QStaff = new Application_Model_Staff();
    $QInformCategory = new Application_Model_InformCategory();
    $inform_category = $QInformCategory->get_cache();
    $staff = $QStaff->get_cache();
    $this->view->informs = $informs;
    $this->view->inform_category = $inform_category;
    $this->view->cat_informs = $inform;
    $this->_helper->viewRenderer->setRender('inform/index');
}
$this->view->params = $params;
$this->view->sort = $sort;
$this->view->desc = $desc;
$this->view->staff = $staff;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST . 'manage/inform/' . ($params ? '?' . http_build_query($params) .
    '&' : '?');
$this->view->offset = $limit * ($page - 1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
