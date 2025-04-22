<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

try
{

    $db->beginTransaction();

    $QTrainerOrder = new Application_Model_TrainerOrder();
    $QTrainerOrderDetail = new Application_Model_TrainerOrderDetail();

    if($this->getRequest()->getMethod() == 'POST')
    {

        echo '<link href="/css/bootstrap.min.css" rel="stylesheet">';

        $date       = $this->getRequest()->getParam('date');
        $asset      = $this->getRequest()->getParam('asset');
        $quantity   = $this->getRequest()->getParam('quantity');
        $note       = $this->getRequest()->getParam('note');
        $order_id   = $this->getRequest()->getParam('order_id');
        $ids        = $this->getRequest()->getParam('ids');

        $params = array(
            'order_id'    => $order_id,
            'date'        => $date,
            'asset'       => $asset,
            'quantity'    => $quantity,
            'note'        => $note,
            'ids'         => $ids,
            'ip'          => $ip,
            'userStorage' => $userStorage
        );

        $result = $QTrainerOrder->save($params);

        if($result['code'] == -1)
            throw new Exception($result['message']);

        $db->commit();
        $flashMessenger ->setNamespace('success')->addMessage('Done');
        echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders"})</script>';
    }
}
catch (Exception $e)
{
    $db->rollback();
    echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
    echo '<script>window.parent.unblockUI();</script>';
    echo '<script>window.parent.scroll2Top();</script>';
    echo '<div class="alert alert-error">Failed - '.$e->getMessage().'</div>';

}