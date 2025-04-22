<?php
class Application_Model_StoreFocus extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_focus';

    function store_focus_list($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'area_name' => 'a.name',
        	'st_id'		=> 'st.id',
        	'st_name'	=> 'st.name',
        	'st_type'	=> 'o.org_name',
        	'st_status'	=> new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Closed' END)"),
        );

        $select = $db->select()
            ->from(array('sf' => 'store_focus'), $get)
            ->joinLeft(array('st' => 'store')			, 'sf.store_id = st.id'			, array())
            ->joinLeft(array('o'  => 'org')				, 'st.org_dealer = o.org_id'	, array())
            ->joinLeft(array('rm' => 'regional_market')	, 'st.regional_market = rm.id'	, array())
            ->joinLeft(array('a'  => 'area')			, 'rm.area_id = a.id'			, array())
            ->order(array('a.name ASC','st.id ASC'));

    	if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('st.id = ?', $params['store_id']);
        }

        if ( isset($params['store_name']) && $params['store_name'] ) {
            $select->where('st.name LIKE ?', '%'.$params['store_name'].'%');
        }

		// Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

}