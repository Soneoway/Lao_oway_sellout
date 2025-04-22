<?php
class Application_Model_SalesArea extends Zend_Db_Table_Abstract
{
    protected $_name = 'sales_area';

    public function get_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $db = Zend_Registry::get('db');

            $select_area = $db->select()
                ->from(array('p' => $this->_name, array('p.staff_id')))
                ->joinLeft(array('r' => 'regional_market'), 'r.area_id=p.area_id', array('province' => 'r.id'))
                ->joinLeft(array('d' => 'regional_market'), 'd.parent=r.id', array('district' => 'd.id'))
                ->where('type = ?', My_Region::Area);

            $select_province = $db->select()
                ->from(array('p' => $this->_name, array('p.staff_id')))
                ->joinLeft(array('r' => 'regional_market'), 'r.id=p.area_id', array('province' => 'r.id'))
                ->joinLeft(array('d' => 'regional_market'), 'd.parent=r.id', array('district' => 'd.id'))
                ->where('type = ?', My_Region::Province);

            $select_district = $db->select()
                ->from(array('p' => $this->_name, array('p.staff_id')))
                ->joinLeft(array('d' => 'regional_market'), 'd.id=p.area_id', array('province' => new Zend_Db_Expr('0'), 'district' => 'd.id'))
                ->where('type = ?', My_Region::District);

            $select = $db->select()
                ->union(array($select_area, $select_province, $select_district));
//echo $select; die;
            $data = $db->fetchAll($select);
//print_r($data);
            $result = array();

            if ($data){
                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array('area' => array(), 'province' => array(), 'district' => array());

                    if ($item['type'] == My_Region::Area)
                        $result[$item['staff_id']]['area'][] = $item['area_id'] ;

                    $result[$item['staff_id']]['province'][] = $item['province'] ;
                    $result[$item['staff_id']]['district'][] = $item['district'] ;
                }

                foreach ($result as $_staff_id => $value) {
                    $result[ $_staff_id ]['area'] = array_unique($value['area']);
                    $result[ $_staff_id ]['province'] = array_unique($value['province']);
                    $result[ $_staff_id ]['district'] = array_unique($value['district']);
                }
            }

            //$cache->save($result, $this->_name.'_cache', array(), null);
        }
//print_r($result);
        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }
    
     
}