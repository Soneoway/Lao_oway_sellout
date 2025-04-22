<?php
class TestController extends My_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $QTiming = new Application_Model_Timing();
        //var_dump( $QTiming->checkImeiDealer('866893029095933',29) );

        $data = array(
            'id'    => '001',
            'name'  => 'TEST & Task',
            'desc'  => 'TEST02',
        );

        //echo serialize($data);

        foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        echo rtrim($fields_string, '&');
        
    }

    public function phpinfoAction()
    {
        echo phpinfo();
    }
}