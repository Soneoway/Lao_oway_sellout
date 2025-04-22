<?php
/**
*
*/
class My_Kpi
{
    /**
     * Lấy tổng doanh số của một đối tượng trong khoảng thời gian
     * @param  int $object_id ID đối tượng
     * @param  My_Type_Kpi_Object $object_id Loại đối tượng (store, pg, sale...)
     * @param  date $from Từ ngày
     * @param  date $to Đến ngày
     * @param  My_Kpi_Type $type Loại dữ liệu (sell out, active...)
     * @return int Doanh số
     *
     * Example:
     *  Value of Area: My_Kpi::getTotal(15, My_Kpi_Object::Area, '2015-03-01', '2015-03-31', My_Kpi_Type::Value);
     *
     *  Sell out of Sale: My_Kpi::getTotal(9, My_Kpi_Object::Sale, '2015-03-01', '2015-03-31');
     *      or: My_Kpi::getTotal(9, My_Kpi_Object::Sale, '2015-03-01', '2015-03-31', My_Kpi_Type::SellOut);
     *
     */
    public static function getTotal($object_id, $object_type, $from, $to, $type = My_Kpi_Type::SellOut)
    {
        $QImeiKpi = new Application_Model_ImeiKpi();

        $params = array(
            'from' => $from,
            'to' => $to,
        );

        switch ($object_type) {
            case My_Kpi_Object::Area:
                $params['area'] = $object_id;
                break;
            case My_Kpi_Object::Dealer:
                $params['dealer'] = $object_id;
                break;
            case My_Kpi_Object::District:
                $params['district'] = $object_id;
                break;
            case My_Kpi_Object::Leader:
                $params['leader'] = $object_id;
                break;
            case My_Kpi_Object::Pg:
                $params['pg'] = $object_id;
                break;
            case My_Kpi_Object::Product:
                $params['good'] = $object_id;
                break;
            case My_Kpi_Object::Province:
                $params['province'] = $object_id;
                break;
            case My_Kpi_Object::Sale:
                $params['sale'] = $object_id;
                break;
            case My_Kpi_Object::Store:
                $params['store'] = $object_id;
                break;

            default:
                throw new Exception("Invalid object type.", 1);
                break;
        }

        return $QImeiKpi->getTotal($type, $params);
    }

    public static function getTotalByModel($object_id, $object_type, $from, $to, $type = My_Kpi_Type::SellOut)
    {
        $QImeiKpi = new Application_Model_ImeiKpi();

        $params = array(
            'from' => $from,
            'to' => $to,
        );

        switch ($object_type) {
            case My_Kpi_Object::Area:
                $params['area'] = $object_id;
                break;
            case My_Kpi_Object::Dealer:
                $params['dealer'] = $object_id;
                break;
            case My_Kpi_Object::District:
                $params['district'] = $object_id;
                break;
            case My_Kpi_Object::Leader:
                $params['leader'] = $object_id;
                break;
            case My_Kpi_Object::Pg:
                $params['pg'] = $object_id;
                break;
            case My_Kpi_Object::Province:
                $params['province'] = $object_id;
                break;
            case My_Kpi_Object::Sale:
                $params['sale'] = $object_id;
                break;
            case My_Kpi_Object::Store:
                $params['store'] = $object_id;
                break;

            default:
                throw new Exception("Invalid object type.", 1);
                break;
        }

        return $QImeiKpi->getTotalByModel($type, $params);
    }

    public static function fetchGrid($object_id, $from, $to)
    {
        $QImeiKpi = new Application_Model_ImeiKpi();

        $kpi_type = $QImeiKpi->getKpiType($object_id, $from, $to);
        $kpi_arr = array();

        foreach ($kpi_type as $_type => $value) {
            $params = array(
                'from' => $from,
                'to' => $to,
            );

            if ($value) {
                $params[ $_type ] = $object_id;
                $kpi_arr[ $_type ] = $QImeiKpi->fetchGrid($params);
            }
        }

        // switch ($object_type) {
        //     case My_Kpi_Object::Area:
        //         $params['area'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Dealer:
        //         $params['dealer'] = $object_id;
        //         break;
        //     case My_Kpi_Object::District:
        //         $params['district'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Leader:
        //         $params['leader'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Pg:
        //         $params['pg'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Province:
        //         $params['province'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Sale:
        //         $params['sale'] = $object_id;
        //         break;
        //     case My_Kpi_Object::Store:
        //         $params['store'] = $object_id;
        //         break;

        //     default:
        //         throw new Exception("Invalid object type.", 1);
        //         break;
        // }

        return $kpi_arr;
    }

    public static function fetchByMonth($object_id, $from, $to)
    {
        $QImeiKpi = new Application_Model_ImeiKpi();

        $kpi_type = $QImeiKpi->getKpiType($object_id, $from, $to);
        $kpi_arr = array();

        foreach ($kpi_type as $_type => $value) {
            $params = array(
                'from' => $from,
                'to' => $to,
            );

            if ($value) {
                $params[ $_type ] = $object_id;
                $kpi_arr[ $_type ] = $QImeiKpi->fetchByMonth($params);
            }
        }

        return $kpi_arr;
    }

    public static function add($imei_sn, $store_id, $staff_id, $timing_date)
    {
        $QWebImei = new Application_Model_WebImei();
        $where = $QWebImei->getAdapter()->quoteInto('imei_sn = ?', $imei_sn);
        $imei = $QWebImei->fetchRow($where);

        if (!$imei) return false;
        if (!isset($imei['good_id'])) return false;
        if (!isset($imei['good_color'])) return false;

        $good_id = $imei['good_id'];
        $good_color = $imei['good_color'];

        $db = Zend_Registry::get('db');
        $sql = "REPLACE INTO imei_kpi(
                    `imei_sn`,
                    `out_date`,
                    `timing_date`,
                    `activation_date`,
                    `store_id`,
                    `distributor_id`,
                    `pg_id`,
                    `sale_id`,
                    `leader_id`,
                    `good_id`,
                    `color_id`,
                    `district_id`,
                    `province_id`,
                    `area_id`,
                    `value`,
                    `kpi_pg`,
                    `kpi_sale`,
                    `kpi_leader`,
                    `status`
                )
                VALUE (
                    ?, -- `imei_sn`
                    get_out_date(?), -- `out_date`
                    ?, -- `timing_date`
                    get_activation_date(?), -- `activation_date`
                    ?, -- `store_id`
                    get_distributor_id(?), -- `distributor_id`
                    ?, -- `pg_id`
                    get_sale_id(?, DATE(?)), -- `sale_id`
                    get_leader_id(?, DATE(?)), -- `leader_id`
                    ?, -- `good_id`
                    ?, -- `color_id`
                    get_district_id(?), -- `district_id`
                    get_province_id(?), -- `province_id`
                    get_area_id(?), -- `area_id`
                    fn_get_price(?, ?, ?), -- `value`
                    fn_get_kpi_pg(?, ?, ?), -- `kpi_pg`
                    fn_get_kpi_sale(?, ?, ?), -- `kpi_sale`
                    fn_get_kpi_leader(?, ?, ?), -- `kpi_leader`
                    0
                );";

        try {
            $db->query($sql, array(
                $imei_sn, // `imei_sn`
                $imei_sn, // `out_date`
                $timing_date, // `timing_date`
                $imei_sn, // `activation_date`
                intval($store_id), // `store_id`
                $imei_sn, // `distributor_id`
                intval($staff_id), // `pg_id`
                intval($store_id), $timing_date, // `sale_id`
                intval($store_id), $timing_date, // `leader_id`
                intval($good_id), // `good_id`
                intval($good_color), // `color_id`
                intval($store_id), // `district_id`
                intval($store_id), // `province_id`
                intval($store_id), // `area_id`
                intval($good_id), intval($good_color), $timing_date, // `value`
                intval($good_id), intval($good_color), $timing_date, // `kpi_pg`
                intval($good_id), intval($good_color), $timing_date, // `kpi_sale`
                intval($good_id), intval($good_color), $timing_date, // `kpi_leader`
            ));
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Xóa IMEI khỏi bảng IMEI KPI khi bị xóa chấm công
     * @param  [type] $imei [description]
     * @return [type]       [description]
     */
    public static function remove($imei)
    {
        $db = Zend_Registry::get('db');
        $sql =  "DELETE FROM imei_kpi WHERE imei_sn = ?";
        $db->query($sql);
    }

    public static function getPreviousClosingDate($type)
    {
        $QClosingDate = new Application_Model_ClosingDate();
        $closing_cache = $QClosingDate->get_cache();
        return isset($closing_cache[ $type ]) && $closing_cache[ $type ] ? $closing_cache[ $type ] : null;
    }

    /**
     * Nếu ngày cuối cùng ($to) trước hoặc bằng "ngày chốt gần nhất, trước ngày hiện tại"
     *  thì tính theo số chốt
     * không thì tính theo số active
     * @param  [type]  $lastDay [description]
     * @return boolean          [description]
     */
    public static function isBeforeClosingDate($lastDay, $type = 1)
    {
        $QClosingDate = new Application_Model_ClosingDate();
        $closing_cache = $QClosingDate->get_cache();

        if (!isset($closing_cache[ $type ]) || !$closing_cache[ $type ])
            return true;

        $closing_date = $closing_cache[ $type ];

        // tra cứu đến sau ngày chốt
        if (strtotime($closing_date) < strtotime($lastDay))
            return false;

        // thời gian thực chưa tới ngày chốt
        if (time() < strtotime($closing_date))
            return false;

        return true; // chốtttttttttttttttttt
    }
}