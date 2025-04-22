<?php
class NotificationController extends My_Application_Controller_Cli
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    public function clearExpiredAction()
    {
        exit;
        echo "\r\n".date('Y-m-d H:i:s')." : Start clearing expired notifications\r\n";
        $sql = sprintf("DELETE FROM notification_new  WHERE show_to < NOW() AND type = %d", My_Notification_Type::MassUpload);
        $db = Zend_Registry::get('db');
        $db->query($sql);
        echo "Done\r\n";

        exit;
    }

}