<?php
class My_Controller_Plugin_ACL extends Zend_Controller_Plugin_Abstract
{
    protected $_defaultRole = 'guest';

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        $acl = new Zend_Acl();

        $default = array(
            'default::test::rolling-game',
            'default::test::phone',
            'default::test::server',
            'default::test::reset',
            'default::test::get-result',
            'default::test::phpinfo',
            'default::test::reset-add',
            'default::test::upload-retailer',
            'default::test::generate-code',
            'default::test::print-list',
            'default::test::upload-photo',

            'default::extools::index',

            'default::wss::index',
            'default::wsc::index',
            'default::wsc::set-imei-status',
            'default::ws::soap',
            'default::ws::add',
            'default::ws::wsdl',
            'default::error::error',
            'default::user::login',
            'default::user::logout',
            'default::user::auth',
            'default::user::reset',
            'default::user::new-pass',
            'default::user::noauth',
            'default::user::lock',
            'default::trainer::list-product-info-json',
            'default::trainer::list-news-info-json',
            'default::trainer::list-push-notification-json',
            'default::trainer::report-check-in-excel',
            'default::index::mobile-notification-json',
            'default::wss-mobile::login',
            'default::wss-mobile::send',
            'default::wss-mobile::history',
            'default::wss-mobile::shopinventory',
            'default::wss-mobile::shopinventorybystore',
            'default::wss-mobile::shopinventorylow',
            'default::wss-mobile::shopinventorybysellout',
            'default::wss-mobile::shopinventorybyallscan',
            'default::wss-kerry::test',
            'default::wss-kerry::shipmentupdate',
            'default::wss-mobilepc::login',
            'default::wss-mobilepc::save',
            'default::timing::api-save-timing',
            'default::timing::api-save-timing-issue',
            'default::timing::api-get-sellout-by-sale',
            'default::timing::api-get-sellout-by-store',
            'default::timing::api-get-analysis-by-area-days',
            'default::timing::api-get-analysis-by-area-months',
            'default::timing::api-get-analysis-by-channel-days',
            'default::timing::api-get-analysis-by-channel-months',
            'default::timing::api-get-pc-com',
            'default::timing::api-get-bs-stock-scan',
            'default::timing::api-check-reg-warranty-imei',
            'default::time::api-get-time-by-staffcode',
            'default::staff::api-save-pc-first-training',
            'default::staff::api-get-staff-info',
            'default::staff::api-update-group-by-hr-change-position',
            'default::staff::api-get-all-staff-by-store',
            'default::staff::api-get-area-by-staff-code',
            'default::staff::api-get-market-name-by-staff-code',
            'default::staff::api-get-store-by-market-name-staff',
            'default::staff::api-get-am-info-by-staff-code',
            'default::staff::api-get-rd-code-by-staff-code',
            'default::ajax::get-staff-for-update',
            'default::ajax::api-get-all-area-grand-bkk',
            'default::sales-report::timing-detail',
        );

        foreach ($default as $access){
//            var_dump($access);exit;
            $acl->add(new Zend_Acl_Resource($access));
        }

        $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');

        if($auth->hasIdentity()) {
            $user = $auth->getIdentity();

            if ( ! ( @$user->id == SUPERADMIN_ID || @$user->group_id == ADMINISTRATOR_ID ) ) {
                $user_role = @$user->role;
                $user_accesses = @$user->accesses;
                if (!$user_role)
                    $user_role = $this->_defaultRole;

                if (!$user_accesses)
                    $user_accesses = $default;

                $acl->addRole(new Zend_Acl_Role($user_role));
                foreach ($default as $access)
                    $acl->allow($user_role, $access);

                if ($user_accesses) {
                    foreach ($user_accesses as $access){
                        if (!$acl->has($access))
                            $acl->add(new Zend_Acl_Resource($access));
                        $acl->allow($user_role, $access);
                    }


                    if (!$acl->has($request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()))
                        $acl->add(new Zend_Acl_Resource($request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()));

                    if( !$acl->isAllowed($user_role, $request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()) ) {
                        $r->gotoUrl('/user/noauth')->redirectAndExit();
                    }
                } else {

                    $r->gotoUrl('/user/noauth')->redirectAndExit();
                }
            }

            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $view = $viewRenderer->view;
            $view->name = @$user->lastname;

            // kiểm tra nếu bị bắt đổi pass mà còn đi lung tung thì hốt nó về trang đổi pass
            if ( My_Staff_Password::force(
                    $request->getControllerName(), 
                    $request->getActionName()) )
                $r->gotoUrl(HOST.'user/change-pass')->redirectAndExit();

        } else {

            $acl->addRole(new Zend_Acl_Role($this->_defaultRole));

            if (!$acl->has($request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()))
                $acl->add(new Zend_Acl_Resource($request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()));

            foreach ($default as $access){

                $acl->allow($this->_defaultRole, $access);
            }

            if ( !$acl->isAllowed($this->_defaultRole, $request->getModuleName() . '::' . $request->getControllerName() . '::' . $request->getActionName()) ){

                $redirect_url = '/user/login';

                if ( $request->getControllerName() != 'index' )

                    $redirect_url .= '?b=' . urlencode( $request->getRequestUri() );

                $r->gotoUrl($redirect_url)->redirectAndExit();
            }
        }
    }
 
}