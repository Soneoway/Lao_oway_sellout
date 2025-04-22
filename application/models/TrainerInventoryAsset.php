<?php
class Application_Model_TrainerInventoryAsset extends Zend_Db_Table_Abstract
{
    protected $_name = 'trainer_inventory_asset';

    function fetchPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['asset_id']) and $params['asset_id'])
            $select->where('p.asset_id = ?', $params['asset_id']);

        $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
