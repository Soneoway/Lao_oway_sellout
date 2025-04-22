<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$temp = array();
$data = array();
$result = array();

$flag = 0;
$null_row = 0;

$target         = $this->getRequest()->getParam('target');
$ost_target     = $this->getRequest()->getParam('ost_target');

$hero_target = $this->getRequest()->getParam('hero_target');
$hero_ost_id = $this->getRequest()->getParam('hero_ost_id');

$tmp_area       = $this->getRequest()->getParam('area_id');
$tmp_sale       = $this->getRequest()->getParam('sale_id');
$tmp_my         = $this->getRequest()->getParam('month_year');



$tmp_my = $this->getRequest()->getParam('month_year');
$month_year = date('Y-m', strtotime($tmp_my));

$QOppoPcTarget = new Application_Model_OppoPcTarget();

// Normal Target
foreach ($target as $key => $value) {
    $temp['store_id'][] = $key;
    $temp['target'][] = $value;
}


$insert_key = array();

for ($i=0;$i<count($target);$i++) {

    $data = array(
        'store_id'      => $temp['store_id'][$i],
        'target'        => $temp['target'][$i],
        'from_date'     => date('Y-m-01', strtotime($month_year)),
        'to_date'       => date('Y-m-t', strtotime($month_year)),
    );

    $tmp = explode("|", $ost_target[ $temp['store_id'][$i] ] );

    if ( $data['target'] != '' ) {  

        if ( isset( $ost_target[ $temp['store_id'][$i] ] ) && $ost_target[ $temp['store_id'][$i] ] ) { 

            $tmp = explode("|", $ost_target[ $temp['store_id'][$i] ] );
            $id = $tmp[0];
            $old_target = $tmp[1];


            if ( $old_target != $data['target'] ) { 

                unset($data['from_date']);
                unset($data['to_date']);

                $data['updated_by'] = $userStorage->id;
                $data['updated_at'] = date('Y-m-d H:i:s');

                $where = $QOppoPcTarget->getAdapter()->quoteInto('id = ?', $id);
                $result = $QOppoPcTarget->update($data,$where); 
            }


        } else {

            // Check Already Exists Data 
            $where_chk = array();
            $where_chk[] = $QOppoPcTarget->getAdapter()->quoteInto('store_id = ?', $temp['store_id'][$i]);
            $where_chk[] = $QOppoPcTarget->getAdapter()->quoteInto('from_date = ?', date('Y-m-01', strtotime($month_year)));
            $where_chk[] = $QOppoPcTarget->getAdapter()->quoteInto('to_date = ?', date('Y-m-t', strtotime($month_year)));

            $result_chk = $QOppoPcTarget->fetchRow($where_chk);

            if ( empty($result_chk) ) {

                $data['created_by'] = $userStorage->id;
                $data['created_at'] = date('Y-m-d H:i:s');

                $result = $QOppoPcTarget->insert($data); 

                $insert_key[$i] = $result;

                if ($flag == 1) {
                    $where2 = $QOppoPcTarget->getAdapter()->quoteInto('id = ?', $result);
                    $result2 = $QOppoPcTarget->update($data2,$where2);
                }

            } else {

                unset($data['from_date']);
                unset($data['to_date']);

                $data['updated_by'] = $userStorage->id;
                $data['updated_at'] = date('Y-m-d H:i:s');

                $where = array();
                $where[] = $QOppoPcTarget->getAdapter()->quoteInto('store_id = ?', $temp['store_id'][$i]);
                $where[] = $QOppoPcTarget->getAdapter()->quoteInto('from_date = ?', date('Y-m-01', strtotime($month_year)));
                $where[] = $QOppoPcTarget->getAdapter()->quoteInto('to_date = ?', date('Y-m-t', strtotime($month_year)));

                $result = $QOppoPcTarget->update($data,$where); 


            }

        }
        
    } 
}

// Hero Target
foreach ($hero_target as $key2 => $value2) {

    $temp2['store_id'][] = $key2;
    $temp2['target_hero'][] = $value2;
}


for ($i=0;$i<count($temp2['store_id']);$i++) {

    $data2 = array(
        'store_id'      => $temp2['store_id'][$i],
        'target'        => $target[$temp2['store_id'][$i]],
        'target_hero'   => $temp2['target_hero'][$i],
        'updated_by'    => $userStorage->id,
        'updated_at'    => date('Y-m-d H:i:s'),
    );

    if ( isset($data2['target']) && $data2['target'] !== '' ) { 
        if ( isset($data2['target_hero']) && $data2['target_hero'] !== '' ) {

            // echo "AAA";

            if( isset( $hero_ost_id[ $temp2['store_id'][$i] ] ) && $hero_ost_id[ $temp2['store_id'][$i] ] == '') {

                // echo "BBB";

                $tmp3 = explode("|", $ost_target[ $temp['store_id'][$i] ] );
                $id = $tmp3[0];

                $where = $QOppoPcTarget->getAdapter()->quoteInto('id = ?', $id);
                $result = $QOppoPcTarget->update($data2,$where); 

            }else if( isset( $hero_ost_id[ $temp2['store_id'][$i] ] ) && $hero_ost_id[ $temp2['store_id'][$i] ] ) {

                // echo "CCC";

                $tmp3 = explode("|", $hero_ost_id[ $temp2['store_id'][$i] ] );
                $id = $tmp3[0];
                $old_target = $tmp3[1];


                if ( $old_target !== $data2['target_hero'] ) { 

                    $where = $QOppoPcTarget->getAdapter()->quoteInto('id = ?', $id);
                    $result = $QOppoPcTarget->update($data2,$where); 
                }

            } else {

                // echo "DDD";


                if ( isset($opt_id[ $temp2['store_id'][$i] ]) && $opt_id[ $temp2['store_id'][$i] ] ) {
                    $tmp = explode("|", $opt_id[ $temp2['store_id'][$i] ]);
                    $id = $tmp[0];
                } else {
                    $id = $insert_key[$i];
                }
                
                    $where = $QOppoPcTarget->getAdapter()->quoteInto('id = ?', $id);
                    $result = $QOppoPcTarget->update($data2,$where); 

            }

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

echo '<script>parent.location.href="'.HOST.'manage/oppo-pc-target/oppo-pc-target?month_year='.$month_year.$txt_area.$txt_sale.'"</script>';