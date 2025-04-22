<?php

$id = $this->getRequest()->getParam('dealer_id');
$from = $this->getRequest()->getParam('from');
$to = $this->getRequest()->getParam('to');
$page = $this->getRequest()->getParam('page', 1);

if ($id && $from && $to) {
    $QTimingSales = new Application_Model_TimingSale();

    if ($id != 'null') {
        $QDistributor = new Application_Model_Distributor();
        $dealer = $QDistributor->find($id);
        $dealer = $dealer->current();

        if ($dealer) {
            $this->view->dealer_name = $dealer['title'];
        }

        $params = array(
            'from'      => $from,
            'to'        => $to,
            'dealer_id' => $id,
            );

    } else {
        $params = array(
            'from'      => $from,
            'to'        => $to,
            'dealer_id' => 'null',
            );
    }

    $limit = LIMITATION;
    $total = 0;

    $this->view->imeis = $QTimingSales->fetchDetailPagination($page, $limit, $total, $params);

    $this->view->offset = $limit*($page-1);
    $this->view->total  = $total;
    $this->view->limit  = $limit;
    $this->view->url    = HOST.'timing/analytics-dealer-details'.( $params ? '?'.http_build_query($params).'&' : '?' );

} else {
    $this->_redirect(HOST.'timing/analytics-dealer');
}