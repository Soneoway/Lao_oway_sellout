<?php
$product_id = $this->getRequest()->getParam('product_id');
$QModel = new Application_Model_Model();
$where = $QModel->getAdapter()->quoteInto('product_id = ?', $product_id);

$result = $QModel->fetchAll($where);

$data = null;
if ($result->count())
    foreach ($result as $item){
        $data[] = array(
            'id' => $item->id,
            'model' => $item->model,
        );
    }
echo json_encode($data);
exit;