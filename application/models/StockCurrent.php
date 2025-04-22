<?php
class Application_Model_StockCurrent extends Zend_Db_Table_Abstract
{
	protected $_name = 'stock_current';

    public $remove_list = array(41,40,39,38,37,75);

	function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.*'));

        $select
        	->join(array('s'=>'store'), 'p.store_id=s.id', array())
        	->join(array('r'=>'regional_market'), 's.regional_market=r.id', array())
        	->join(array('a'=>'area'), 'r.area_id=a.id', array('area_name' => 'name'))
            ->join(array('d' => WAREHOUSE_DB.'.'.'distributor'), 'd.id=s.d_id', array());

        $select->where('p.product_id NOT IN (?)', $this->remove_list);
        
        if (isset($params['store']) and $params['store'])
            $select->where('p.store_id = ?', $params['store']);

        if (isset($params['area']) and $params['area']) {

            if (is_array($params['area'])) {
                $select->where('a.id IN (?)', $params['area']);
            } else {
                $select->where('a.id = ?', $params['area']);
            }
        }
            
        if (isset($params['region']) and $params['region'])
            $select->where('r.id = ?', $params['region']);

        if ( isset($params['dealer']) && trim($params['dealer']) != "" )
        	$select->where('d.title LIKE ?', '%'.$params['dealer'].'%');

        if (isset($params['date']) && $params['date']) {
        	$date = explode('/', $params['date']);
        	$date = $date[2] . '-' . $date[1] . '-' . $date[0];
        	$select->where('DATE(p.date) = ?', $date);
        }

        if ( isset( $params['sort'] ) && in_array($params['sort'], array('area', 'store')) ) {
        	$order_str = '';

        	switch ( $params['sort'] ) {
				case 'area':
					$order_str = 'a.`name`';
					break;
				case 'store':
					$order_str = 's.`name`';
					break;
				default:
					break;
			}

        	$desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
        	$collate = ' COLLATE utf8_unicode_ci ';
			$order_str .= $collate . $desc;
			$select->order(new Zend_Db_Expr($order_str));
        }

        // if($limit) // phân trang ở tầng PHP rồi
        	// $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        return $result;
    }
}