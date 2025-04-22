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
    $QTrainerOrderOut = new Application_Model_TrainerOrderOut();

    if($this->getRequest()->getMethod() == 'POST')
    {

        echo '<link href="/css/bootstrap.min.css" rel="stylesheet">';

        $date               = $this->getRequest()->getParam('date');
        $asset              = $this->getRequest()->getParam('asset');
        $quantity           = $this->getRequest()->getParam('quantity');
        $note               = $this->getRequest()->getParam('note');
        $order_out_id       = $this->getRequest()->getParam('order_out_id');
        $ids                = $this->getRequest()->getParam('ids');
        $output_to_staff_id = $this->getRequest()->getParam('output_to_staff_id');

        $params = array(
            'order_out_id'      => $order_out_id,
            'date'              => $date,
            'asset'             => $asset,
            'quantity'          => $quantity,
            'note'              => $note,
            'ids'               => $ids,
            'output_to_staff_id'=> $output_to_staff_id,
            'ip'                => $ip,
            'userStorage'       => $userStorage
        );


        $result = $QTrainerOrderOut->save($params);

        if($result['code'] == -1)
            throw new Exception($result['message']);

        $db->commit();
        $flashMessenger ->setNamespace('success')->addMessage('Done');
        echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders-out"})</script>';
    }
}
catch (Exception $e)
{
    $db->rollback();
    echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
    echo '<script>window.parent.unblockUI();</script>';
    echo '<div class="alert alert-error">Failed - '.$e->getMessage().'</div>';

}