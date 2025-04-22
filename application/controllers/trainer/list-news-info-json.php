<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
$itemModel          = new Application_Model_NewsInfo();
$id = $this->getRequest()->getParam('id');
$display = $this->getRequest()->getParam('display');
$type = $this->getRequest()->getParam('type');
$result = array();
if($id){
	$rowset = $itemModel->find($id);
	$item = $rowset->current();
	$result['name'] = $item->name;
    $result['file_link']  = $item->file_link == '' ? '':HOST.'trainer/'.$item->file_link;
    $result['video_link'] = $item->video_link;
    $result['type'] = $item->type;
    $result['detail'] = $item->detail;
}else if($display == "P") {
	$data = $itemModel->getNewsInfo($display);

	if ($data){
		$result['product_info'] = $data;
		$result['success'] = "COMPLETE";
	} else {
		$result['success'] = "FAIL";
	}
}else{
	$data = $itemModel->getNewsInfo("Y", $type);

	if ($data){
		$result['product_info'] = $data;
		$result['success'] = "COMPLETE";
	} else {
		$result['success'] = "FAIL";
	}
}

echo json_encode($result);