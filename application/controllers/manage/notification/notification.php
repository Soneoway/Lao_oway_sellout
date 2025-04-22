<?php
$page    = $this->getRequest()->getParam('page', 1);
$title   = $this->getRequest()->getParam('title');
$content = $this->getRequest()->getParam('content');
$sort    = $this->getRequest()->getParam('sort');
$desc    = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array_filter(array(
    'title'   => $title,
    'content' => $content,
    'sort'    => $sort,
    'desc'    => $desc,
));
$params['sort'] = $sort;
$params['desc'] = $desc;
$params['filter_display'] = 1;

$QModel = new Application_Model_Notification();
$notifications = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->params        = $params;
$this->view->sort          = $sort;
$this->view->desc          = $desc;
$this->view->notifications = $notifications;

$this->view->limit  = $limit;
$this->view->total  = $total;
$this->view->url    = HOST.'manage/notification/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('notification/partials/list');
} else
    $this->_helper->viewRenderer->setRender('notification/index');