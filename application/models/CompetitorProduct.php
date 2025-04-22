<?php
class Application_Model_CompetitorProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'competitor_product';

    function getProductList($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'cp_id'             => 'cp.id',
        	'brand_name'        => 'cb.name',
            'product_name'      => 'cp.name',
            'product_status'    => 'cp.status',
            'product_position'  => 'cp.position',
        );

        $select = $db->select()
            ->from(array('cp' => 'competitor_product'), $get)
            ->join(array('cb' => 'competitor_brand'), 'cp.brand_id = cb.id'	, array())
            ->order(array('cb.name ASC', 'cp.position ASC', 'cp.name ASC'));

        if ( isset($params['brand_id']) && $params['brand_id'] ) {
            $select->where('cp.brand_id IN (?)', $params['brand_id']);
        }

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

}