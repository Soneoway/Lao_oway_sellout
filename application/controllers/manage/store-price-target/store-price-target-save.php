<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$target_price 	= $this->getRequest()->getParam('target_price');
$spt_id			= $this->getRequest()->getParam('price_spt_id');

$tmp_area 		= $this->getRequest()->getParam('area_id');
$tmp_sale 		= $this->getRequest()->getParam('sale_id');
$tmp_my 		= $this->getRequest()->getParam('month_year');

$month_year 	= date('Y-m', strtotime($tmp_my));

$QSPT = new Application_Model_StorePriceTarget();

echo "Cnt new Target :".count($target_price);
echo "<pre>"; print_r($target_price);

echo "Cnt old Target :".count($spt_id);
echo "<pre>"; print_r($spt_id);


// Price Target
foreach ($target_price as $key => $value) {

	$temp['store_id'][] = $key;
	$temp['target_price'][] = $value;
}

for ($i=0;$i<count($target_price);$i++) {

	$data = array(
	    'store_id'		=> $temp['store_id'][$i],
	    'target_price'	=> $temp['target_price'][$i],
	);

	// Case Update 
	if ( isset($spt_id[ $temp['store_id'][$i] ]) && $spt_id[ $temp['store_id'][$i] ] ) {

		$tmp = explode("|", $spt_id[ $temp['store_id'][$i] ] );
		$id = $tmp[0];
		$old_target = $tmp[1];

		if ( $old_target != $data['target_price'] ) { 

			echo "Update<br/>";

			$data['updated_by'] = $userStorage->id;
			$data['updated_at'] = date('Y-m-d H:i:s');

			$where = $QSPT->getAdapter()->quoteInto('id = ?', $id);
			$result = $QSPT->update($data,$where); 
		}

	} else {

		// Case Insert 
		if ( isset($temp['target_price'][$i]) && $temp['target_price'][$i] ) {

			echo "Add<br/>";

			$data['from_date'] 	= date('Y-m-01', strtotime($month_year));
			$data['to_date'] 	= date('Y-m-t', strtotime($month_year));
			$data['created_by'] = $userStorage->id;
			$data['created_at'] = date('Y-m-d H:i:s');

			$result = $QSPT->insert($data); 
		}

	}

}

// Crated URL Back Area
if ( isset($tmp_area) && $tmp_area ) {
	$txt_area = "";
	$area_id = json_decode($tmp_area);
	foreach ($area_id as $key => $value) { $txt_area .= '&area_id[]='.$value; }
}

// Crated URL Back Sale
if ( isset($tmp_sale) && $tmp_sale ) {
	$txt_sale = "";
	$sale_id = json_decode($tmp_sale);
	foreach ($sale_id as $key => $value) { $txt_sale .= '&sale_id[]='.$value; }
}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

echo '<script>parent.location.href="'.HOST.'manage/store-price-target/store-price-target?month_year='.$month_year.$txt_area.$txt_sale.'"</script>';
