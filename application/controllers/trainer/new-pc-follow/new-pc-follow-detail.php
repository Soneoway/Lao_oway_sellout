<?php
$id    = $this->getRequest()->getParam('id');
$round = $this->getRequest()->getParam('round');

$params = array(
    'id' => $id,
    'round' => $round
);

$QNewPcFollow = new Application_Model_NewPcFollow();
$result_list = $QNewPcFollow->getResultDetail($params);

$this->view->params = $params;
$this->view->result_list = $result_list;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('new-pc-follow/partials/modal');

?>
