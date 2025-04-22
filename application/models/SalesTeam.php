<?php
class Application_Model_SalesTeam extends Zend_Db_Table_Abstract
{
	protected $_name = 'sales_team';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->where('p.del = ?', 0);

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['regional_market']) and $params['regional_market'])
            $select->where('p.regional_market = ?', $params['regional_market']);

        if (isset($params['area_id']) and $params['area_id']){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            
            if (is_array($params['area_id'])) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_id']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();
            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            $select->where('p.regional_market IN (?)', $tem);
        }

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if ( ( isset($params['staff_email']) && $params['staff_email'] ) || ( isset($params['staff_name']) && $params['staff_name'] ) ) {
        	$select
        		->join(array('sts' => 'sales_team_staff'), 'sts.sales_team_id=p.id', array())
        		->join(array('s' => 'staff'), 'sts.staff_id=s.id', array());

    		if (isset($params['staff_email']) && $params['staff_email']) {
    			$params['staff_email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['staff_email']);
    			$select->where('email LIKE ?', $params['staff_email'].EMAIL_SUFFIX);
    		}

    		if (isset($params['staff_name']) && $params['staff_name']) {
    			$select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
    		}
        }

        $select->order('p.name', 'COLLATE utf8_unicode_ci ASC');

        $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $where = $this->getAdapter()->quoteInto('del = ?', 0);

            $data = $this->fetchAll($where);

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
}                                                      
