<?php
class SurveyController extends My_Controller_Action
{
    public function indexAction()
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $QSurveyToken = new Application_Model_SurveyToken();
        $where = array();
        $where[] = $QSurveyToken->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);

        $survey_token = $QSurveyToken->fetchRow($where);
        if (!$survey_token){
           
            $token = md5(FILENAME_SALT.time());

            $iv = substr( md5($token,true), 0, 8 );

            $enc = mcrypt_encrypt( MCRYPT_BLOWFISH, FILENAME_SALT, $userStorage->email, MCRYPT_MODE_CBC, $iv);

            $p1 = base64_encode($enc);

            $data = array(
                'staff_id' => $userStorage->id,
                'email' => $p1,
                'token' => $token,
            );

            $QSurveyToken->insert($data);
        } else {
            $token = $survey_token['token'];
        }

        $this->view->r = $this->getRequest()->getParam('r');

        $this->view->token = $token;
    }
}
