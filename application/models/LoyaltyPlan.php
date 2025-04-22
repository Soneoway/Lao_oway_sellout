<?php
class Application_Model_LoyaltyPlan extends Zend_Db_Table_Abstract
{
	protected $_name = 'loyalty_plan';

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if (!$result) {

            $data = $this->fetchAll();

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

    public function get_all_cache()
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_all_cache');

        if ($result === false) {
            $data = $this->fetchAll(null, 'sales_from');

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[$item->id] = array(
                        'name'                => $item->name,
                        'sales_from'          => $item->sales_from,
                        'sales_to'            => $item->sales_to,
                        'sales_market_share'  => $item->sales_market_share,
                        'figure_market_share' => $item->figure_market_share,
                    );

            $cache->save($result, $this->_name.'_all_cache', array(), null);
        }
        return $result;
    }

    public function get_all_index_cache()
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_all_index_cache');
        if ($result === false) {
            $data = $this->fetchAll(null, 'sales_from');

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[] = array(
                        'id'                  => $item->id,
                        'name'                => $item->name,
                        'sales_from'          => $item->sales_from,
                        'sales_to'            => $item->sales_to,
                        'sales_market_share'  => $item->sales_market_share,
                        'figure_market_share' => $item->figure_market_share,
                    );

            $cache->save($result, $this->_name.'_all_index_cache', array(), null);
        }
        //var_dump($result);die;
        return $result;
    }
}                                                      
