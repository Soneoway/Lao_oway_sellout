<?php
class Application_Model_AsmStandby extends Zend_Db_Table_Abstract
{
    protected $_name = 'asm_standby';

    /**
     * Lấy danh sách các region theo từng ASM
     * @param  int $staff_id    Staff ID (Default null)
     * @return array            Format array( asm ID => array(list region) )
     */
    public function get_region_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_region_new_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $data = $this->fetchAll();
            $result = array();

            if ($data){
                $QRegion = new Application_Model_RegionalMarket();

                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array();

                    if ($item['type'] == 1) {
                        $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $item['area_id']);
                        $regions = $QRegion->fetchAll($where);
                        
                        if ($regions)
                            foreach ($regions as $reg)
                                $result[$item['staff_id']][] = $reg['id'];
                    } elseif ($item['type'] == 2) {
                        $result[$item['staff_id']][] = $item['area_id'];
                    }
                }

                foreach ($result as $s_id => $region)
                    $result[$s_id] = array_unique($region);
            }

            $cache->save($result, $this->_name.'_region_new_cache', array(), null);
        }

        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }

    public function is_asm($staff_id, $store_id)
    {
        $QStore = new Application_Model_Store();
        $store = $QStore->find($store_id);
        $store = $store->current();

        if (!$store) return false;

        $regions = $this->get_region_cache($staff_id);

        if ($regions && is_array($regions))
            return in_array($store['regional_market'], $regions);

        return false;
    }
}