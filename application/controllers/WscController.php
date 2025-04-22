<?php
/*
 * Web service - CLIENT
 */
class WscController extends Zend_Controller_Action
{
    public function indexAction()
    {
        // disable layouts and renderers
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
        $client = new nusoap_client(WSS_CS_URI);
        $client->setCredentials(WS_USERNAME, WS_PASSWORD, "basic");
        $result = $client->call("getUser", array('username'=>12));
        $err=$client->getError();
        var_dump($result);exit;
//        var_dump($result);exit;
        try {
            $result = $client->call("getListActions", array());

        } catch (Exception $e) {
            // $e->getMessage(); // xử lý Exception
        }

        if($client->fault)
        {
            // xử lý lỗi
        }
        else
        {
            // thành công
        }

    }

    public function setImeiStatusAction()
    {
        // disable layouts and renderers
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
        $client = new nusoap_client(WSS_WH_URI);

        $params = array(
            'imei' => 123456789,
            'status' => 64,
            'info' => serialize(array('key' => 'value')),
        );

        try {
            $result = $client->call("setImeiStatus", $params);
        } catch (Exception $e) {
            // $e->getMessage(); // xử lý Exception
        }

        if($client->fault)
        {
            // xử lý lỗi
        }
        else
        {
            // thành công
        }

    }
}