<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$temp = array();
$result = array();

$target = $this->getRequest()->getParam('target');
$oat_id = $this->getRequest()->getParam('oat_id');

$target_hero = $this->getRequest()->getParam('target_hero');
$hero_oat_id = $this->getRequest()->getParam('hero_oat_id');

$month_year = $this->getRequest()->getParam('month_year');

$QOppoAreaTarget = new Application_Model_OppoAreaTarget();

// Normal Target
foreach ($target as $key => $value) {
	$temp['area_id'][] = $key;
	$temp['target'][] = $value;
}

for ($i=0;$i<count($temp['area_id']);$i++) {

	$data = array(
	    'area_id'		=> $temp['area_id'][$i],
	    'target' 		=> $temp['target'][$i],
	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),
	);

	if ( $data['target'] !== '' ) { 

		if ( isset( $oat_id[ $temp['area_id'][$i] ] ) && $oat_id[ $temp['area_id'][$i] ] ) {

			$tmp = explode("|", $oat_id[ $temp['area_id'][$i] ]);
			$id = $tmp[0];
			$old_target = $tmp[1];

			if ( $old_target != $data['target'] ) {

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = $QOppoAreaTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoAreaTarget->update($data,$where); 

			}

		} else {

			if ( isset($target_hero[ $temp['area_id'][$i] ]) && $target_hero[ $temp['area_id'][$i] ] !== '' ) { 
				$data['target_hero'] = $target_hero[ $temp['area_id'][$i] ]; 
			}

			$data['created_by'] = $userStorage->id;
			$data['created_at'] = date('Y-m-d H:i:s');

			$result = $QOppoAreaTarget->insert($data);

		}

	}

}

// Hero Target
foreach ($target_hero as $key2 => $value2) {
	$temp2['area_id'][] = $key2;
	$temp2['target_hero'][] = $value2;
}

for ($i=0;$i<count($temp2['area_id']);$i++) {

	$data2 = array(
	    'area_id'		=> $temp2['area_id'][$i],
	    'target'		=> $target[ $temp2['area_id'][$i] ],
	    'target_hero'	=> $temp2['target_hero'][$i],

	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),

	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);

	//print_r($data2);

	if ( isset($data2['target_hero']) && $data2['target_hero'] !== '' ) { 

		if ( isset($data2['target']) && $data2['target'] !== '' ) { 

			//echo "AAA";

			if ( isset( $hero_oat_id[ $temp2['area_id'][$i] ] ) && $hero_oat_id[ $temp2['area_id'][$i] ] ) {

				//echo "BBB";

				$tmp2 = explode("|", $hero_oat_id[ $temp2['area_id'][$i] ]);
				$id = $tmp2[0];
				$old_target = $tmp2[1];

				if ( $old_target != $data2['target_hero'] ) {

					echo "CCC";

					$where = $QOppoAreaTarget->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOppoAreaTarget->update($data2,$where); 

				}

			} else {

				//echo "DDD";

				$tmp = explode("|", $oat_id[ $temp2['area_id'][$i] ]);
				$id = $tmp[0];

				$where = $QOppoAreaTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoAreaTarget->update($data2,$where); 

			}

		}
	}

}

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

echo '<script>parent.location.href="'.HOST.'manage/oppo-area-target/oppo-area-target?month_year='.$month_year.'"</script>';