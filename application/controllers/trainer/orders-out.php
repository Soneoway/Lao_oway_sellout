<?php
$sort                   = $this->getRequest()->getParam('sort', '');
$desc                   = $this->getRequest()->getParam('desc', 1);
$sn                     = $this->getRequest()->getParam('sn');
$asset_id               = $this->getRequest()->getParam('asset_id');
$confirmed_at_from      = $this->getRequest()->getParam('confirmed_at_from');
$confirmed_at_to        = $this->getRequest()->getParam('confirmed_at_to');
$created_at_from        = $this->getRequest()->getParam('created_at_from');
$created_at_to          = $this->getRequest()->getParam('created_at_to');
$status                 = $this->getRequest()->getParam('status');
$output_to_staff_id     = $this->getRequest()->getParam('output_to_staff_id');
$export                 = $this->getRequest()->getParam('export');
$userStorage            = Zend_Auth::getInstance()->getStorage()->read();


$params = array(
    'sn'                => $sn,
    'asset_id'          => $asset_id,
    'confirmed_at_from' => $confirmed_at_from,
    'confirmed_at_to'   => $confirmed_at_to,
    'created_at_from'   => $created_at_from,
    'created_at_to'     => $created_at_to,
    'output_to_staff_id'=> $output_to_staff_id,
    'export'            => $export,
    'status'            => $status
);

$params['sort'] = $sort;
$params['desc'] = $desc;


$QTrainerOrderOut         = new Application_Model_TrainerOrderOut();
$page           = $this->getRequest()->getParam('page', 1);
$limit          = LIMITATION;
$total          = 0;
$result         = $QTrainerOrderOut->fetchPagination($page, $limit, $total, $params);

$this->view->list    = $result;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->params  = $params;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->url     = HOST.'trainer/orders-out'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset  = $limit*($page-1);

$QStaff              = new Application_Model_Staff();
$staffs_cached       = $QStaff->get_cache();
$this->view->staffs_cached = $staffs_cached;


$QTrainerTypeAsset          = new Application_Model_TrainerTypeAsset();
$whereTypeAsset             = $QTrainerTypeAsset->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
$type_asset                 = $QTrainerTypeAsset->fetchAll($whereTypeAsset);
$arrayTypeAsset             = array();
if($type_asset->count())
{
    foreach($type_asset as $key => $value)
    {
        $arrayTypeAsset[$value['id']] = $value['name'];
    }
}

$this->view->type_asset     = $arrayTypeAsset;

// danh sach trainer lay tu staff
$QTrainerOrderOut  = new Application_Model_TrainerOrderOut();
$this->view->list_trainer = $QTrainerOrderOut->getStaffTrainer();


$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

$cachedAsset = $QTrainerTypeAsset->get_cache();

if(isset($export) and $export == 1 )
{

    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    set_time_limit( 0 );
    error_reporting( 0 );
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);

    $name = "OutputTrainer".date('Ymd').".csv";
    $fp = fopen('php://output', 'w');

    header('Content-Type: text/csv; charset=utf-8');
    echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
    header('Content-Disposition: attachment; filename='.$name);

    $heads = array(
        'SN',
        'Date',
        'Asset',
        'Quantity',
        'Trainer'
    );

    fputcsv($fp, $heads);


    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

    $config = $config->toArray();

    $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

    mysqli_set_charset($con,'utf8');

    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $resultQuery = mysqli_query($con,$result);

    while($row = @mysqli_fetch_array($resultQuery))
    {

        $write  = array(
            $row['sn'],
            $row['date'],
            $cachedAsset[$row['asset_id']],
            $row['quantity'],
            $staffs_cached[$row['output_to_staff_id']]
        );

        fputcsv($fp, $write);

    }

    fclose($fp);
    exit;

}