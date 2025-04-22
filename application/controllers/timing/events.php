<?php
$start = $this->getRequest()->getParam('start');
$end = $this->getRequest()->getParam('end');
$id = $this->getRequest()->getParam('id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QTiming = new Application_Model_Timing();

$where = array();
$where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?', ($id ? $id : $userStorage->id));
$where[] = $QTiming->getAdapter()->quoteInto('date(`from`) >= ?', date('Y-m-d', $start));
$where[] = $QTiming->getAdapter()->quoteInto('date(`to`) <= ?', date('Y-m-d', $end));

$result = $QTiming->fetchAll($where);

$data = null;
if ($result->count()){
    $db = Zend_Registry::get('db');

    foreach ($result as $item){
        $select = $db->select()
            ->from(array('p' => 'timing_sale'),
                array('p.*'))
            ->join(array('de' => WAREHOUSE_DB.'.'.'good'),
                'p.product_id = de.id',
                array('product_name'=>'de.desc'))
            ->join(array('mo' => WAREHOUSE_DB.'.'.'good_color'),
                'p.model_id = mo.id',
                array('model_name'=>'mo.name'))
            ->joinLeft(array('cus' => 'customer'),
                'p.customer_id = cus.id',
                array('customer_name'=>'cus.name', 'customer_phone'=>'cus.phone_number', 'customer_address'=>'cus.address'))
        ;
        $select->where('p.timing_id = ?', $item->id);
        $infos = $db->fetchAll($select);

        $title = 'Report '.$item->from.' to '.$item->to;

        $data[] = array(
            'id' => $item->id,
            'title' => $title,
            'start' => $item->from,
            'end' => $item->to,
            'allDay' => false,
            'infos' => $infos,
            'className' => ( $infos ? 'customCalendar' : '' )
        );
    }
}
echo json_encode($data);
exit;