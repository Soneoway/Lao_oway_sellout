<?php
class Application_Model_Good extends Zend_Db_Table_Abstract
{
	protected $_name = 'good';
    protected $_schema = WAREHOUSE_DB;

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.desc LIKE ?', '%'.$params['name'].'%');

        $select->order('p.desc', 'COLLATE utf8_unicode_ci ASC');

        $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_brand() {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('b' => WAREHOUSE_DB.'.brand'),array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS b.id'), 'b.*'));
        $result = $db->fetchAll($select);

        return $result;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if (!$result) {

            $where = array();
	    // $where[] = $this->getAdapter()->quoteInto('brand_id = ?', 1);
            // $where[] = $this->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);  // Old
            $where[] = $this->getAdapter()->quoteInto('cat_id != ?', ACCESS_CAT_ID);
            $where[] = $this->getAdapter()->quoteInto('del = ?', 0);

            $data = $this->fetchAll($where, 'desc');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function getProduct($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'))
                    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = p.brand_id',array('brand_name' => 'b.name'))
                    ->where('p.product_status IN (?)',array('1','2'));

        if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $select->where('p.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $select->where('p.brand_id = ?', intval($params['brand']));
        else
            $select->where('1=0', 1);
        }


        $result = $db->fetchAll($select);
        return $result;
    }

    function get_cache2() {
        $cache      = Zend_Registry::get('cache');
        //$result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache2');

        if (!$result) {
            
            $where = array();
			// $where[] = $this->getAdapter()->quoteInto('brand_id = ?', 1);
            $where[] = $this->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
            $where[] = $this->getAdapter()->quoteInto('del = ?', 0);

            $data = $this->fetchAll($where, 
                new Zend_Db_Expr("(
                    CASE 
                        WHEN id = 403 THEN 1 
                        WHEN id = 399 THEN 2 
                        WHEN id = 392 THEN 3 
                        WHEN id = 363 THEN 4 
                        WHEN id = 374 THEN 5 
                        WHEN id = 393 THEN 6 
                        WHEN id = 339 THEN 7 
                        WHEN id = 389 THEN 8 
                        WHEN id = 371 THEN 9 
                        WHEN id = 353 THEN 10 
                        WHEN id = 345 THEN 11 
                        WHEN id = 321 THEN 12 
                        WHEN id = 310 THEN 13 
                        WHEN id = 311 THEN 14 
                        WHEN id = 312 THEN 15 
                        ELSE 16 
                    END                    
                ) ASC, `name` ASC"));

            //print_r($data);
            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item;
                    $result[$item->id]->name = "[".$item->name."] ".$item->desc;
                }
            }

            //$cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache2', array(), null);
        }
        return $result;
    }

    function get_cat_model_color_cache()
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cat_model_color_cache');

        if (!$result) {

            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->distinct()
                ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name), array('product_id' => 'p.id', 'product_name' => 'p.name', 'product_desc' => 'p.desc'))
                ->join(array('c' => WAREHOUSE_DB.'.'.'good_category'), 'p.cat_id=c.id', array('cat_id' => 'c.id', 'cat_name' => 'c.name'))
                ->join(array('cc' => WAREHOUSE_DB.'.'.'good_color_combined'), 'p.id=cc.good_id', array())
                ->join(array('gc' => WAREHOUSE_DB.'.'.'good_color'), 'cc.good_color_id=gc.id', array('color_id' => 'gc.id','color_name' => 'gc.name'));

            $data = $db->fetchAll($select);

            $result = array();

            if ($data) {
                foreach ($data as $key => $value) {
                    if ( !isset($result[ $value['cat_id'] ]) )
                        $result[ $value['cat_id'] ] = array(
                                                        'name' => $value['cat_name'],
                                                        'children' => array(),
                                                        );

                    if ( ! isset( $result[ $value['cat_id'] ]
                                            ['children']
                                                [ $value['product_id'] ] ) )
                        $result[ $value['cat_id'] ]
                                    ['children']
                                        [ $value['product_id'] ] = array(
                                                                        'name' => $value['product_name'],
                                                                        'desc' => $value['product_desc'],
                                                                        'children' => array(),
                                                                        );

                    if ( ! isset( $result[ $value['cat_id'] ]
                                                ['children']
                                                    [ $value['product_id'] ]
                                                        ['children']
                                                            [ $value['color_id'] ] ) )
                        $result[ $value['cat_id'] ]
                                    ['children']
                                        [ $value['product_id'] ]
                                            ['children']
                                                [ $value['color_id'] ] = array(
                                                                            'name' => $value['color_name']
                                                                            );
                }
            }

            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cat_model_color_cache', array(), null);
        }
        return $result;
    }

    // Function for Webservice
    function getAll_Good($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('g' => WAREHOUSE_DB.'.'.$this->_name),array('g.*'));
        $select->where('g.cat_id = ?', PHONE_CAT_ID);

        $result = $db->fetchAll($select);
        return $result;
    }

    function get_acc_cache(){
        $cache      = Zend_Registry::get('cache');
        //$result     = $cache->load(WAREHOUSE_DB.'_ACC_'.$this->_name.'_cache');

        if (!$result) {
            
            $where = array();
            // $where[] = $this->getAdapter()->quoteInto('brand_id = ?', 1);
            $where[] = $this->getAdapter()->quoteInto('cat_id = ?', ACCESS_CAT_ID);
            $where[] = $this->getAdapter()->quoteInto('del = ?', 0);
            $where[] = $this->getAdapter()->quoteInto('bm_focus_acc = ?', 1);

            $data = $this->fetchAll($where, new Zend_Db_Expr("`name` ASC"));

            //print_r($data);
            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item;
                    $result[$item->id]->name = "[".$item->name."] ".$item->desc;
                }
            }

            //$cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache2', array(), null);
        }
        return $result;
    }

    function getBsStockScanProductList(){

        $db = Zend_Registry::get('db');

        $product_list = array(363,371,392,395,374,393,399,403);

        $get = array(
            'product_id'    => 'g.id',
            'product_name'  => 'g.desc',
            'color_id'      => 'gc.id',
            'color_name'    => 'gc.name',
        );

        $select = $db->select()
            ->from(array('g'   => WAREHOUSE_DB.'.good'), $get)
            ->join(array('gcc' => WAREHOUSE_DB.'.good_color_combined')  , 'g.id = gcc.good_id'          , array())
            ->join(array('gc'  => WAREHOUSE_DB.'.good_color')           , 'gcc.good_color_id = gc.id'   , array())
            ->where('g.id IN (?)', $product_list)
            ->where(new Zend_Db_Expr(
                "(
                    CASE 
                        WHEN g.id = 392 THEN gc.id <> 42 
                        WHEN g.id = 403 THEN gc.id <> 7 
                        ELSE 1=1 
                    END
                )"))
            ->group(array('g.id','gc.id'))
            ->order(array('product_name ASC','color_name ASC'));

        $result = $db->fetchAll($select);
        return $result;
    }

}
