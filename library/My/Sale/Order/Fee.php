<?php
/**
* Lưu trữ loại phí cho đơn hàng và giá từng loại phí
*/
class My_Sale_Order_Fee extends My_Type_Enum
{
    const Shipping = 1;

    public static $name = array(
        self::Shipping => "Phí vận chuyển",
    );

    public static $min_limit = array(
        self::Shipping => 10000000, // 10 triệu
    );

    private static function calculate_fee($total_value, $district_id, $type)
    {
        return self::getDistrictFee($total_value, $district_id, $type);
    }

    public static function setFee($distributor_id, $sn, $value, $type = self::Shipping, $user_uncheck = false)
    {
        return false; // disable fee
        $QDistributor = new Application_Model_Distributor();
        $where = $QDistributor->getAdapter()->quoteInto('id = ?', intval($distributor_id));
        $distributor = $QDistributor->fetchRow($where);

        if (!$distributor) throw new Exception("Invalid Distributor", 2);
        if (!isset($distributor['district']) || !intval($distributor['district'])) throw new Exception("Please setup District for Distributor: ".$distributor['title'], 3);

        $fee = self::calculate_fee($value, $distributor['district'], $type);

        $QMarket = new Application_Model_Market();
        $where = $QMarket->getAdapter()->quoteInto('sn = ?', $sn);
        $market = $QMarket->fetchRow($where);

        if (!$market) throw new Exception("Invalid SN", 1);

        $QMarketFee = new Application_Model_MarketFee();

        $where = array();
        $where[] = $QMarketFee->getAdapter()->quoteInto('sales_sn = ?', $sn);
        $where[] = $QMarketFee->getAdapter()->quoteInto('fee_id = ?', self::Shipping);

        if (!$fee && !$user_uncheck) {

            // nếu không có phí thì xóa dòng đi, không để dòng mà phí = 0
            $QMarketFee->delete($where);

        } else {
            if ($user_uncheck) {
                $data = array(
                    'sales_sn'     => $sn,
                    'fee_id'       => self::Shipping,
                    'value'        => 0,
                    'user_uncheck' => 1,
                );
            } else {
                $data = array(
                    'sales_sn'     => $sn,
                    'fee_id'       => self::Shipping,
                    'value'        => $fee,
                    'user_uncheck' => 0,
                );
            }

            $fee_check = $QMarketFee->fetchRow($where);

            try {
                if ($fee_check)
                    $QMarketFee->update($data, $where);
                else
                    $QMarketFee->insert($data);

            } catch (Exception $e) {
            }
        }

    }

    public static function removeFee($sn)
    {
        $QMarketFee = new Application_Model_MarketFee();

        $where = array();
        $where[] = $QMarketFee->getAdapter()->quoteInto('sales_sn = ?', $sn);
        $where[] = $QMarketFee->getAdapter()->quoteInto('fee_id = ?', self::Shipping);

        $QMarketFee->delete($where);
    }

    public static function getFee($sn, $type = self::Shipping, $all = false)
    {
        return false; // disable fee
        $order_fee = array();

        $QMarketFee = new Application_Model_MarketFee();
        $where = array();

        if ($all)
            $where[] = $QMarketFee->getAdapter()->quoteInto('user_uncheck = 1 OR user_uncheck = 0', 1);
        else
            $where[] = $QMarketFee->getAdapter()->quoteInto('user_uncheck = ?', 0);

        $where[] = $QMarketFee->getAdapter()->quoteInto('sales_sn = ?', $sn);
        $where[] = $QMarketFee->getAdapter()->quoteInto('fee_id = ?', $type);
        $shipping_fee = $QMarketFee->fetchRow($where);

        if ($shipping_fee) {
            $order_fee[ $shipping_fee['fee_id'] ] = $shipping_fee;
        }

        return $order_fee;
    }

    public static function getDistrictFee($value, $district_id, $type = self::Shipping)
    {
        return 0; // disable fee
        if ($value >= self::$min_limit[$type]) return 0;

        $QRegion = new Application_Model_RegionalMarket();
        $district_cache = $QRegion->get_district_cache();

        if (!isset($district_cache[ $district_id ])) throw new Exception("Invalid district", 10);

        $QDistrictFee = new Application_Model_DistrictFee();
        $where = array();
        $where[] = $QDistrictFee->getAdapter()->quoteInto('district_id = ?', $district_id);
        $where[] = $QDistrictFee->getAdapter()->quoteInto('fee_id = ?', $type);
        $fee = $QDistrictFee->fetchRow($where);

        if (!$fee) {
            file_put_contents(APPLICATION_PATH."/../logs/delivery_fee.txt", date("Y-m-d H:i:s").": Fee not found. Please setup fee for District: ".$district_cache[$district_id]['name']."\r\n", FILE_APPEND);
            throw new Exception("Fee not found. Please setup fee for District: ".$district_cache[$district_id]['name'], 12);
        }

        if (isset($fee['no_delivery']) && $fee['no_delivery']) throw new Exception("No delivery", 13);

        if (!isset($fee['value']) || !isset($fee['additional_value'])) {
            file_put_contents(APPLICATION_PATH."/../logs/delivery_fee.txt", date("Y-m-d H:i:s").": Invalid fee, Distrrict: ".$district_cache[$district_id]['name']."\r\n", FILE_APPEND);
            throw new Exception("Invalid fee", 11);
        }

        return ( $fee['value'] + $fee['additional_value'] );
    }
}