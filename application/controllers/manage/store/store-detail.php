<?php
$id = $this->getRequest()->getParam('id');

$QStore 		= new Application_Model_Store();
$QArea 			= new Application_Model_Area();
$QDistributor   = new Application_Model_Distributor();
$QSubArea		= new Application_Model_SubArea();
$QStaff			= new Application_Model_Staff();
$QRegionalMarket 	= new Application_Model_RegionalMarket();

if($id){
    $storeRowSet = $QStore->find($id);
    $store       = $storeRowSet->current();

    $client 	 = $QStore->getClientByStoreID($id);


    if (!$store) {
        $flashMessenger->setNamespace('error')->addMessage('Invalid Store ID. Please Check And Try Agian !.');
        $this->_redirect(HOST.'manage/store');
    }

}

$this->view->client 		= $client;
$this->view->store 			= $store;
$this->view->area 			= $QArea->get_cache();
$this->view->distibutor 	= $QDistributor->get_cache();
$this->view->subArea 		= $QSubArea->get_cache();
$this->view->staff 			= $QStaff->get_cache();
$this->view->regional 		= $QRegionalMarket->get_cache();


$this->_helper->viewRenderer->setRender('store/partials/store-detail');

?>