<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$temp = $temp2 = $temp3 = array();
$result = array();
$null_row = 0;

$target = $this->getRequest()->getParam('target');
$oit_id = $this->getRequest()->getParam('oit_id');

$target_hero = $this->getRequest()->getParam('target_hero');
$opt_id = $this->getRequest()->getParam('opt_id');


$target_price = $this->getRequest()->getParam('target_price');
$ost_id = $this->getRequest()->getParam('ost_id');

$tmp_area = $this->getRequest()->getParam('area_id');
$tmp_sale = $this->getRequest()->getParam('sale_id');
$tmp_my	= $this->getRequest()->getParam('month_year');

$month_year = date('Y-m', strtotime($tmp_my));

$QOIT = new Application_Model_OppoIndividualTarget();

//Target
foreach ($target as $key => $value) {

	$temp['staff_id'][] = $key;
	$temp['target'][] = $value;
}

$insert_key = array();

for ($i=0;$i<count($target);$i++) {

	$data = array(
	    'staff_id'		=> $temp['staff_id'][$i],
	    'target' 		=> $temp['target'][$i],
	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),
	);

	if ( $data['target'] != '' ) {  

		if ( isset( $oit_id[ $temp['staff_id'][$i] ] ) && $oit_id[ $temp['staff_id'][$i] ] ) { 

			$tmp = explode("|", $oit_id[ $temp['staff_id'][$i] ] );
			$id = $tmp[0];
			$old_target = $tmp[1];

			if ( $old_target != $data['target'] ) { 

				unset($data['from_date']);
				unset($data['to_date']);

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = $QOIT->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOIT->update($data,$where); 
			}

		} else {

			// Check Already Exists Data 
			$where_chk = array();
			$where_chk[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $temp['staff_id'][$i]);
			$where_chk[] = $QOIT->getAdapter()->quoteInto('from_date = ?', date('Y-m-01', strtotime($month_year)));
			$where_chk[] = $QOIT->getAdapter()->quoteInto('to_date = ?', date('Y-m-t', strtotime($month_year)));

			$result_chk = $QOIT->fetchRow($where_chk);

			if ( empty($result_chk) ) {

				$data['created_by'] = $userStorage->id;
				$data['created_at'] = date('Y-m-d H:i:s');

				$result = $QOIT->insert($data); 

				$insert_key[$i] = $result;

			} else {

				unset($data['from_date']);
				unset($data['to_date']);

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = array();
				$where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $temp['staff_id'][$i]);
				$where[] = $QOIT->getAdapter()->quoteInto('from_date = ?', date('Y-m-01', strtotime($month_year)));
				$where[] = $QOIT->getAdapter()->quoteInto('to_date = ?', date('Y-m-t', strtotime($month_year)));

				$result = $QOIT->update($data,$where); 

				$null_row = 1;
			}

		}
		
	} 
}

// Hero Target
foreach ($target_hero as $key2 => $value2) {

	$temp2['staff_id'][] = $key2;
	$temp2['target_hero'][] = $value2;
}

for ($i=0;$i<count($target_hero);$i++) {

	$data2 = array(
	    'staff_id'		=> $temp2['staff_id'][$i],
	    'target' 		=> $target[$temp2['staff_id'][$i]],
	    'target_hero'	=> $temp2['target_hero'][$i],
	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);


	if ( isset($data2['target_hero']) && $data2['target_hero'] !== '' ) {
		if ( isset($data2['target']) && $data2['target'] !== '' ) { 


			if ( isset( $opt_id[ $temp2['staff_id'][$i] ] ) && $opt_id[ $temp2['staff_id'][$i] ] ) { 


				$tmp2 = explode("|", $opt_id[ $temp2['staff_id'][$i] ] );
				$id = $tmp2[0];
				$old_target = $tmp2[1];

				if ( $old_target != $data2['target_hero'] ) { 

					$where = $QOIT->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOIT->update($data2,$where); 
				}

			} else {


				if ( isset($oit_id[ $temp2['staff_id'][$i] ]) && $oit_id[ $temp2['staff_id'][$i] ] ) {
					$tmp = explode("|", $oit_id[ $temp2['staff_id'][$i] ]);
					$id = $tmp[0];
				} else {
					$id = $insert_key[$i];
				}

				$where = $QOIT->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOIT->update($data2,$where); 

			}
			if ($null_row == 1) {

				$where = array();
				$where[] = $QOIT->getAdapter()->quoteInto('staff_id = ?', $temp2['staff_id'][$i]);
				$where[] = $QOIT->getAdapter()->quoteInto('from_date = ?', date('Y-m-01', strtotime($month_year)));
				$where[] = $QOIT->getAdapter()->quoteInto('to_date = ?', date('Y-m-t', strtotime($month_year)));

				$result = $QOIT->update($data2,$where); 

			}
		}
	}

}


//Price Target
// foreach ($target_price as $key3 => $value3) {

// 	$temp3['staff_id'][] = $key3;
// 	$temp3['target_price'][] = $value3;
// }
// 	for ($i=0;$i<count($target_price);$i++) {

// 		$data3 = array(
// 		    'staff_id'		=> $temp3['staff_id'][$i],
// 		    'target'		=> $target[$temp3['staff_id'][$i]],
// 		    'target_hero' 	=> $target_hero[$temp3['target_hero'][$i]],
// 		    'target_price'	=> $temp3['target_price'][$i],

// 		    'updated_by'	=> $userStorage->id,
// 	    	'updated_at'	=> date('Y-m-d H:i:s'),
// 		);
// 			if(isset($data3['target']) && $data3['target'] !== ''){

// 		if ( isset( $ost_id[ $temp3['staff_id'][$i] ] ) && $ost_id[ $temp3['staff_id'][$i] ] ) { 

// 			$tmp3 = explode("|", $ost_id[ $temp3['staff_id'][$i] ] );
// 			$id = $tmp3[0];
// 			$old_target_price = $tmp3[1];

// 			if ( $old_target_price != $data3['target_price'] ) { 

// 				// echo "Update<br/>";

// 				// $data['updated_by'] = $userStorage->id;
// 				// $data['updated_at'] = date('Y-m-d H:i:s');

// 				$where = $QOIT->getAdapter()->quoteInto('id = ?', $id);
// 				$result = $QOIT->update($data3,$where); 
// 			}

// 		} else {

// 		if ( isset($oit_id[$temp3['staff_id'][$i]]) && $oit_id[$temp3['target_price'][$i]]) {
// 		$tmp = explode("|", $oit_id[ $temp3['staff_id'][$i] ]);
// 					$id = $tmp[0];
// 				} else {
// 					$id = $insert_key[$i];
// 				}
				
// 				$where = $QOIT->getAdapter()->quoteInto('id = ?', $id);
// 				$result = $QOIT->update($data3,$where); 

// 			}

// 		}
// 	}

	$flashMessenger = $this->_helper->flashMessenger;
	$flashMessenger->setNamespace('success')->addMessage('Done!');

// Crated URL Back Area
if ( isset($tmp_area) && $tmp_area ) {
	$txt_area = "";
	$area_id = json_decode($tmp_area);
	foreach ($area_id as $key => $value) { $txt_area .= '&area_id[]='.$value; }
}

// Crated URL Back Sale
if ( isset($tmp_sale) && $tmp_sale ) {
	
	$txt_sale = '&sale_id='.$tmp_sale;
	$txt_sale = str_replace('"', '', $txt_sale);
}

echo '<script>parent.location.href="'.HOST.'manage/oppo-individual-target/oppo-individual-target?month_year='.$month_year.$txt_area.$txt_sale.'"</script>';
