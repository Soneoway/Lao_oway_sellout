<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QProduct = new Application_Model_Product();

    $product_id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $distributor_price = $this->getRequest()->getParam('distributor_price');
    $retailer_price = $this->getRequest()->getParam('retailer_price');
    $price_2 = $this->getRequest()->getParam('price_2');

    $data = array(
        'name' => $name,
        'distributor_price' => $distributor_price,
        'retailer_price' => $retailer_price,
        'price_2' => $price_2,
    );

    if ($product_id){
        $where = $QProduct->getAdapter()->quoteInto('id = ?', $product_id);

        $QProduct->update($data, $where);
    } else {
        $product_id = $QProduct->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('product_cache');
    $cache->remove('product_cache2');

    //update models
    $models = $this->getRequest()->getParam('models');
    $model_ids = $this->getRequest()->getParam('model_ids');
    $removed_models = $this->getRequest()->getParam('removed_models');
    $removed_models = explode(',', $removed_models);

    $QModel = new Application_Model_Model();

    foreach ($models as $k=>$model){
        $model_id = (isset($model_ids[$k]) and $model_ids[$k]) ? $model_ids[$k] : null;
        $data = array(
            'model' => $model,
            'product_id' => $product_id,
        );
        if ($model_id){ //update

            $where = $QModel->getAdapter()->quoteInto('id = ?', $model_id);
            $QModel->update($data, $where);
        } else
            $QModel->insert($data);
    }

    //remove models
    if (is_array($removed_models) and $removed_models) {
        $where = $QModel->getAdapter()->quoteInto('id IN (?)', $removed_models);
        $QModel->delete($where);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/product' ) );