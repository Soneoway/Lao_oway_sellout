<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$temp = $temp2 = $temp3 = array();
$result = array();

$save_type = $this->getRequest()->getParam('btn_save');

$target = $this->getRequest()->getParam('target');
$ost_id = $this->getRequest()->getParam('ost_id');

$target_hero = $this->getRequest()->getParam('target_hero');
$hero_ost_id = $this->getRequest()->getParam('hero_ost_id');

$target_price = $this->getRequest()->getParam('target_price');
$price_ost_id = $this->getRequest()->getParam('price_ost_id');

$remark = $this->getRequest()->getParam('remark');
$remark_ost_id = $this->getRequest()->getParam('remark_ost_id');

$leader_remark = $this->getRequest()->getParam('leader_remark');
$otr_id = $this->getRequest()->getParam('otr_id');

$com 	= $this->getRequest()->getParam('com');
$area_id = $this->getRequest()->getParam('area_id');

$tmp_my = $this->getRequest()->getParam('month_year');
$month_year = date('Y-m', strtotime($tmp_my));

$QOppoSaleTarget = new Application_Model_OppoSaleTarget();
$QOppoTargetRemark = new Application_Model_OppoTargetRemark();
$QSubarea = new Application_Model_SubArea();

// print_r($target);
// print_r($ost_id);
//  exit;

// Normal Target
foreach ($target as $key => $value) {
	$temp['sub_id'][] = $key;
	$temp['target'][] = $value;
	// $temp['staff_id'][] = $value;
}

$insert_key = array();

for ($i=0;$i<count($temp['sub_id']);$i++) {

	$sale_com = 1;

	if ( isset($com[ $temp['sub_id'][$i] ]) ) { $sale_com = 1; } 

	$data = array(
	    'area_id'		=> $area_id,
	    // 'staff_id' 		=> $temp['staff_id'][$i],
	    'sub_area_id'	=> $temp['sub_id'][$i],
	    'target' 		=> $temp['target'][$i],
	    'com'			=> $sale_com,
	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),
	    'rd_approve_by' => $userStorage->id,
	    'rd_approve_at' => date('Y-m-t H:i:S' ),
	    'sd_approve_by' => $userStorage->id,
	    'sd_approve_at' => date('Y-m-t H:i:S' ),
	);

	if($data['sub_area_id']){
		$where = $QSubarea->getAdapter()->quoteInto('id =?',$data['sub_area_id']);
		$data123 = $QSubarea->fetchAll($where);
		$data['staff_id'] = $data123[0]['staff_id'];
	}
		// print_r($data123[0]['staff_id']);
		// print_r($data);
		// exit();


	if ( $data['target'] !== '' ) { 

		if ( isset( $ost_id[ $temp['sub_id'][$i] ] ) && $ost_id[ $temp['sub_id'][$i] ] ) { 

			$tmp = explode("|", $ost_id[ $temp['sub_id'][$i] ] );
			$id = $tmp[0];
			$old_target = $tmp[1];
			$old_com = $tmp[2];

			if ( $old_target != $data['target'] || $old_com != $sale_com ) {

				unset($data['from_date']);
				unset($data['to_date']);
				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoSaleTarget->update($data,$where); 
			}

		} else {

			if ( isset($target_hero[ $temp['sub_id'][$i] ]) && $target_hero[ $temp['sub_id'][$i] ] !== '' ) { 
				$data['target_hero'] = $target_hero[ $temp['sub_id'][$i] ]; 
			}

			$data['created_by'] = $userStorage->id;
			$data['created_at'] = date('Y-m-d H:i:s');

			$result = $QOppoSaleTarget->insert($data); 

			$insert_key[$i] = $result;
		}

	}

}

// Hero Target
foreach ($target_hero as $key2 => $value2) {
	$temp2['sub_id'][] = $key2;
	$temp2['target_hero'][] = $value2;
}

for ($i=0;$i<count($temp2['sub_id']);$i++) {

	$sale_com = 1;

	if ( isset($com[ $temp2['sub_id'][$i] ]) ) { $sale_com = 1; } 
	$data2 = array(
	    'area_id'		=> $area_id,
	    // 'staff_id' 		=> $temp2['staff_id'][$i],
	    'sub_area_id'	=> $temp2['sub_id'][$i],
	    'target' 		=> $target[ $temp2['sub_id'][$i] ],
	    'target_hero'	=> $temp2['target_hero'][$i],

	    'com'			=> $sale_com,
	    //'from_date'	=> date('Y-m-01', strtotime($month_year)),
	    //'to_date'		=> date('Y-m-t', strtotime($month_year)),

	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);

	if($data['sub_area_id']){
		$where = $QSubarea->getAdapter()->quoteInto('id =?',$data2['sub_area_id']);
		$data123 = $QSubarea->fetchAll($where);
		$data2['staff_id'] = $data123[0]['staff_id'];
	}

	// print_r($data2);
	// exit();

	if ( isset($data2['target_hero']) && $data2['target_hero'] !== '' ) { 

		if ( isset($data2['target']) && $data2['target'] !== '' ) { 

			//echo "AAA";

			if ( isset( $hero_ost_id[ $temp2['sub_id'][$i] ] ) && $hero_ost_id[ $temp2['sub_id'][$i] ] ) { 

				//echo "BBB";

				$tmp2 = explode("|", $hero_ost_id[ $temp2['sub_id'][$i] ] );
				$id = $tmp2[0];
				$old_target = $tmp2[1];

				if ( $old_target != $data2['target_hero'] ) {

					//echo "CCC";

					// unset($data2['from_date']);
					// unset($data2['to_date']);

					$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOppoSaleTarget->update($data2,$where); 
				}

			} else {

				//echo "DDD";

				if ( isset($ost_id[ $temp2['sub_id'][$i] ]) && $ost_id[ $temp2['sub_id'][$i] ]) {
					$tmp = explode("|", $ost_id[ $temp2['sub_id'][$i] ]);
					$id = $tmp[0];
				} else {
					$id = $insert_key[$i];
				}
				
				$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoSaleTarget->update($data2,$where); 

			}

		}


	}

}

// Price Target
foreach ($target_price as $key7 => $value7) {
	$temp7['staff_id'][] = $key7;
	$temp7['target_price'][] = $value7;
}

for ($i=0;$i<count($temp7['staff_id']);$i++) {

	$sale_com = 1;

	if ( isset($com[ $temp7['staff_id'][$i] ]) ) { $sale_com = 1; } 
	$data7 = array(
	    'area_id'		=> $area_id,
	    'staff_id' 		=> $temp7['staff_id'][$i],
	    'target' 		=> $target[ $temp7['staff_id'][$i] ],
	    'target_hero'	=> $target_hero[ $temp7['staff_id'][$i] ],
	    'target_price'	=> $temp7['target_price'][$i],
	    'com'			=> $sale_com,
	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);

	//print_r($data7);

	// if ( isset($data7['target_price']) && $data7['target_price'] !== '' ) { 

		if ( isset($data7['target']) && $data7['target'] !== '' ) { 

			//echo "AAA";

			if ( isset( $price_ost_id[ $temp7['staff_id'][$i] ] ) && $price_ost_id[ $temp7['staff_id'][$i] ] ) { 

				//echo "BBB";

				$tmp7 = explode("|", $price_ost_id[ $temp7['staff_id'][$i] ] );
				$id = $tmp7[0];
				$old_price = $tmp7[1];

				if ( $old_price != $data7['target_price'] ) {

					//echo "CCC";

					// unset($data7['from_date']);
					// unset($data7['to_date']);

					$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOppoSaleTarget->update($data7,$where); 
				}

			} else {

				//echo "DDD";

				if ( isset($ost_id[ $temp7['staff_id'][$i] ]) && $ost_id[ $temp7['staff_id'][$i] ]) {
					$tmp = explode("|", $ost_id[ $temp7['staff_id'][$i] ]);
					$id = $tmp[0];
				} else {
					$id = $insert_key[$i];
				}
				
				$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoSaleTarget->update($data7,$where); 

			}

		}


	// }

}

// Remark
foreach ($remark as $key3 => $value3) {
	$temp3['staff_id'][] = $key3;
	$temp3['remark'][] = $value3;
}

for ($i=0;$i<count($temp3['staff_id']);$i++) {

	$sale_com = 1;

	if ( isset($com[ $temp3['staff_id'][$i] ]) ) { $sale_com = 1; } 
	$data3 = array(
	    'area_id'		=> $area_id,
	    'staff_id' 		=> $temp3['staff_id'][$i],
	    'target' 		=> $target[ $temp3['staff_id'][$i] ],
	    'target_hero'	=> $target_hero[ $temp3['staff_id'][$i] ],
	    'remark'		=> $temp3['remark'][$i],

	    'com'			=> $sale_com,
	    //'from_date'	=> date('Y-m-01', strtotime($month_year)),
	    //'to_date'		=> date('Y-m-t', strtotime($month_year)),

	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);

	//print_r($data3);

	// if ( isset($data3['remark']) && $data3['remark'] !== '' ) { 

		if ( isset($data3['target']) && $data3['target'] !== '' ) { 

			//echo "AAA";

			if ( isset( $remark_ost_id[ $temp3['staff_id'][$i] ] ) && $remark_ost_id[ $temp3['staff_id'][$i] ] ) { 

				//echo "BBB";

				$tmp3 = explode("|", $remark_ost_id[ $temp3['staff_id'][$i] ] );
				$id = $tmp3[0];
				$old_remark = $tmp3[1];

				if ( $old_remark != $data3['remark'] ) {

					//echo "CCC";

					// unset($data3['from_date']);
					// unset($data3['to_date']);

					$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOppoSaleTarget->update($data3,$where); 
				}

			} else {

				//echo "DDD";

				if ( isset($ost_id[ $temp3['staff_id'][$i] ]) && $ost_id[ $temp3['staff_id'][$i] ]) {
					$tmp = explode("|", $ost_id[ $temp3['staff_id'][$i] ]);
					$id = $tmp[0];
				} else {
					$id = $insert_key[$i];
				}
				
				$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoSaleTarget->update($data3,$where); 

			}

		}

	// }

}

// Leader Remark 
foreach ($leader_remark as $key6 => $value6) {
	$temp6['staff_id'][] = $key6;
	$temp6['remark'][] = $value6;
}

$insert_key_otr = array();

for ($i=0;$i<count($temp6['staff_id']);$i++) {

	$data6 = array(
	    'area_id'		=> $area_id,
	    'staff_id' 		=> $temp6['staff_id'][$i],
	    'remark' 		=> $temp6['remark'][$i],
	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),
	);

	//if ( $data6['remark'] !== '' ) { 

		if ( isset( $otr_id[ $temp6['staff_id'][$i] ] ) && $otr_id[ $temp6['staff_id'][$i] ] ) { 

			$tmp = explode("|", $otr_id[ $temp6['staff_id'][$i] ] );
			$id = $tmp[0];
			$old_remark = $tmp[1];

			if ( $old_remark != $data6['remark']) {

				unset($data6['from_date']);
				unset($data6['to_date']);
				$data6['updated_by'] = $userStorage->id;
				$data6['updated_at'] = date('Y-m-d H:i:s');

				$where = $QOppoTargetRemark->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoTargetRemark->update($data6,$where); 
			}

		} else {

			if ( $data6['remark'] !== '' ) { 

				$data6['created_by'] = $userStorage->id;
				$data6['created_at'] = date('Y-m-d H:i:s');

				$result = $QOppoTargetRemark->insert($data6); 

				$insert_key_otr[$i] = $result;

			}
			
		}

	//}

}


// RD Approve 
if ($save_type == 2) {

	$data4 = array();
	$data4['rd_approve_by'] = $userStorage->id;
	$data4['rd_approve_at'] = date('Y-m-d H:i:s');

	foreach ($ost_id as $key4 => $value4) {
		$tmp4 = explode("|", $value4);

		$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $tmp4[0]);
		$result = $QOppoSaleTarget->update($data4,$where); 
	}

}

// RD Approve 
if ($save_type == 3) {

	$data5 = array();
	$data5['sd_approve_by'] = $userStorage->id;
	$data5['sd_approve_at'] = date('Y-m-d H:i:s');

	foreach ($ost_id as $key5 => $value5) {
		$where = $QOppoSaleTarget->getAdapter()->quoteInto('id = ?', $value5);
		$result = $QOppoSaleTarget->update($data5,$where); 
	}

	$back_url = $this->getRequest()->getParam('back_url');
	echo '<script>parent.location.href="'.$back_url.'"</script>';

}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

$back_url = $this->getRequest()->getParam('back_url');
echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'manage/oppo-sale-target') ).'"</script>';