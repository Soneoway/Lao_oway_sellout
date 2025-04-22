<?php
if($this->getRequest()->getMethod() == 'POST')
{
    $asset_id               = $this->getRequest()->getParam('asset_id');
    $export                 = $this->getRequest()->getParam('export');
    $userStorage            = Zend_Auth::getInstance()->getStorage()->read();

    $params = array(
        'asset_id'          => $asset_id,
        'export'            => $export
    );

    $QTrainerTypeAsset                 = new Application_Model_TrainerTypeAsset();
    $QTrainerInventoryHistoryAsset     = new Application_Model_TrainerInventoryHistoryAsset();

    $cached_Asset               = $QTrainerTypeAsset->get_cache();
    $this->view->cached_asset   = $cached_Asset;

    $page                       = $this->getRequest()->getParam('page', 1);
    $limit                      = LIMITATION;
    $total                      = 0;
    $result                     = $QTrainerInventoryHistoryAsset->fetchPagination($page, $limit, $total, $params);

    $this->view->list    = $result;
    $this->view->limit   = $limit;
    $this->view->total   = $total;
    $this->view->params  = $params;
    $this->view->url     = HOST.'trainer/inventory-history-asset'.( $params ? '?'.http_build_query($params).'&' : '?' );
    $this->view->offset  = $limit*($page-1);
}

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

$QStaff = new Application_Model_Staff();
$staffCached = $QStaff->get_cache();
$this->view->staff_cached = $staffCached ;

$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;


if(isset($export) and $export == 1 )
{
    set_time_limit( 0 );
    error_reporting( 0 );
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);

    $name = "Export_inventory_history".date('Ymd').".csv";
    $fp = fopen('php://output', 'w');

    header('Content-Type: text/csv; charset=utf-8');
    echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
    header('Content-Disposition: attachment; filename='.$name);

    $heads = array(
        '#',
        'Asset',
        'Total First',
        'Total In',
        'Total Out',
        'Total End',
        'Created At',
        'Created By'
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

    $rows  = mysqli_query($con,$result);
    $count = 0;
    while($row = @mysqli_fetch_array($rows))
    {
        $count = $count +1;
        $write  = array(
            $count,
            $arrayTypeAsset[$row['asset_id']],
            $row['inventory_first'],
            $row['inventory_in'],
            $row['inventory_out'],
            $row['inventory_end'],
            $row['created_at'],
            $staffCached[$row['created_by']]
        );

        fputcsv($fp, $write);

    }
    fclose($fp);
    exit;

}
