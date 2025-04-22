<?php
class Application_Model_DealerLoyalty extends Zend_Db_Table_Abstract
{
	protected $_name = 'dealer_loyalty';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('dl' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS dl.id'), 'dl.id', 'd.district', 'd.title', 'd.parent', 'd.del',
                'dl.dealer_id', 'dl.loyalty_plan_id', 'dl.from_date', 'dl.to_date'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.'.'distributor'), 'dl.`dealer_id` = d.`id`', array())
            ;

        if (isset($params['asm']) && intval($params['asm']) > 0) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions))
                $select->where('d.district IN (?)', $list_regions);
            else
                $select->where('1 = 0');
        }

        $select->where('d.`del` = ?', 0);

        if (isset($params['name']) and $params['name'])
            $select->where('d.`title` LIKE ?', '%'.$params['name'].'%');

        if (isset($params['loyalty_plan_id']) and $params['loyalty_plan_id'])
            $select->where('dl.`loyalty_plan_id` IN (?)', $params['loyalty_plan_id']);

        /*if (isset($params['from_date']) and $params['from_date']){
            list($day, $month, $year) = explode('/', $params['from_date']);
            $from_date = $year.'-'.$month.'-'.$day;
            $select->where('dl.`date` >= ?', $from_date);
        }

        if (isset($params['to_date']) and $params['to_date']){
            list($day, $month, $year) = explode('/', $params['to_date']);
            $to_date = $year.'-'.$month.'-'.$day;
            $where .= ' AND '.$db->quoteInto('dlpr.`date` <= ?', $to_date);
        }*/

        if (isset($params['area']) and $params['area']){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $districtsByArea = $QRegionalMarket->get_district_by_area_cache($params['area']);
            if ($districtsByArea){
                $arrDistricts = array();
                foreach ($districtsByArea as $id=>$val)
                    $arrDistricts[] = $val;

                $select->where('d.`district` IN (?)', $arrDistricts);

            } else
                $select->where(' 1=0 ');

        }

        if (isset($params['regional_market']) and $params['regional_market']){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $districtsByProvince = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
            if ($districtsByProvince){
                $arrDistricts = array();
                foreach ($districtsByProvince as $id=>$val)
                    $arrDistricts[] = $id;

                $select->where('d.`district` IN (?)', $arrDistricts);

            } else
                $select->where(' 1=0 ');
        }

        if (isset($params['district']) and $params['district']){
            $select->where('d.`district` = ?', $params['district']);
        }


        $select->order('d.title');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }
}                                                      
