<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$temp = array();
$result = array();

$target = $this->getRequest()->getParam('target');
$pmt_id = $this->getRequest()->getParam('pmt_id');

$target_hero = $this->getRequest()->getParam('target_hero');
$hero_pmt_id = $this->getRequest()->getParam('hero_pmt_id');

$month_year = date('Y-m-d');

$QOppoMktTarget = new Application_Model_OppoMktTarget();

// Normal Target
foreach ($target as $key => $value) {
	$temp['staff_id'][] = $key;
	$temp['target'][] = $value;
}

for ($i=0;$i<count($temp['staff_id']);$i++) {

	$data = array(
	    'staff_id'		=> $temp['staff_id'][$i],
	    'target' 		=> $temp['target'][$i],
	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),
	);

	if ( $data['target'] !== '' ) { 

		if ( isset( $pmt_id[ $temp['staff_id'][$i] ] ) && $pmt_id[ $temp['staff_id'][$i] ] ) {

			$tmp = explode("|", $pmt_id[ $temp['staff_id'][$i] ]);
			$id = $tmp[0];
			$old_target = $tmp[1];

			if ( $old_target != $data['target'] ) {

				$data['updated_by'] = $userStorage->id;
				$data['updated_at'] = date('Y-m-d H:i:s');

				$where = $QOppoMktTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoMktTarget->update($data,$where); 

			}

		} else {

			if ( isset($target_hero[ $temp['staff_id'][$i] ]) && $target_hero[ $temp['staff_id'][$i] ] !== '' ) { 

					$data['target_hero'] = $target_hero[ $temp['staff_id'][$i] ]; 
			}

			$data['created_by'] = $userStorage->id;
			$data['created_at'] = date('Y-m-d H:i:s');

			$result = $QOppoMktTarget->insert($data);

		}

	}

}


// Hero Target
foreach ($target_hero as $key2 => $value2) {
	$temp2['staff_id'][] = $key2;
	$temp2['target_hero'][] = $value2;
}

for ($i=0;$i<count($temp2['staff_id']);$i++) {

	$data2 = array(
	    'staff_id'		=> $temp2['staff_id'][$i],
	    'target'		=> $target[ $temp2['staff_id'][$i] ],
	    'target_hero'	=> $temp2['target_hero'][$i],

	    'from_date'		=> date('Y-m-01', strtotime($month_year)),
	    'to_date'		=> date('Y-m-t', strtotime($month_year)),

	    'updated_by'	=> $userStorage->id,
	    'updated_at'	=> date('Y-m-d H:i:s'),
	);

	if ( isset($data2['target_hero']) && $data2['target_hero'] !== '' ) { 

		if ( isset($data2['target']) && $data2['target'] !== '' ) { 

			if ( isset( $hero_pmt_id[ $temp2['staff_id'][$i] ] ) && $hero_pmt_id[ $temp2['staff_id'][$i] ] ) {

				$tmp2 = explode("|", $hero_pmt_id[ $temp2['staff_id'][$i] ]);
				$id = $tmp2[0];
				$old_target = $tmp2[1];

				if ( $old_target != $data2['target_hero'] ) {

					// echo "CCC";

					$where = $QOppoMktTarget->getAdapter()->quoteInto('id = ?', $id);
					$result = $QOppoMktTarget->update($data2,$where); 

				}

			} else {

				$tmp = explode("|", $pmt_id[ $temp2['staff_id'][$i] ]);
				$id = $tmp[0];

				$where = $QOppoMktTarget->getAdapter()->quoteInto('id = ?', $id);
				$result = $QOppoMktTarget->update($data2,$where); 

			}

		}
	}

}


$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

echo '<script>parent.location.href="'.HOST.'manage/mkt-target/mkt-target?month_year='.$month_year.'"</script>';