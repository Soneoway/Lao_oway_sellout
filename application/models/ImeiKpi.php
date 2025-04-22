<?php
class Application_Model_ImeiKpi extends Zend_Db_Table_Abstract
{
    protected $_name = 'imei_kpi';
   protected $_brandname = 'store';
    /**
     * Lấy tổng doanh số của một đối tượng trong khoảng thời gian
     * @param  My_Kpi_Type $type Loại dữ liệu (sell out, active...)
     * @param  array $params các tham số
     * @return int Doanh số
     */
    public function getTotal($type, array $params)
    {
        $total = 0;

        $db = Zend_Registry::get('db');
        $select = $db->select();

        switch ($type) {
            case My_Kpi_Type::SellOut:
                $type_mysql_string = 'COUNT(DISTINCT i.imei_sn)';
                break;
            case My_Kpi_Type::Value:
                $type_mysql_string = 'SUM(i.value) * 0.8';
                break;
            case My_Kpi_Type::Activated:
                $type_mysql_string = 'COUNT(DISTINCT i.imei_sn)';
                $params['activated'] = true;
                break;
            case My_Kpi_Type::Activation:
                $type_mysql_string = 'COUNT(DISTINCT i.imei_sn)';

                if (isset($params['from']) && $params['from']) {
                    $params['activation_from'] = $params['from'];
                    unset($params['from']);
                }

                if (isset($params['to']) && $params['to']){
                    $params['activation_to'] = $params['to'];
                    unset($params['to']);
                }
                break;

            default:
                throw new Exception("Invalid KPI type", 2);
                break;
        }

        $select->from(array('i' => $this->_name), array('total' => $type_mysql_string));

        if (isset($params['from']) && $params['from'])
            $select->where('DATE(i.timing_date) >= ?', $params['from']);

        if (isset($params['to']) && $params['to'])
            $select->where('DATE(i.timing_date) <= ?', $params['to']);

        if (isset($params['activated']) && $params['activated'])
            $select->where('i.activation_date IS NOT NULL AND i.activation_date <> 0 AND i.activation_date <> \'\'', 1);

        if (isset($params['activation_from']) && $params['activation_from'])
            $select->where('DATE(i.activation_date) >= ?', $params['activation_from']);

        if (isset($params['activation_to']) && $params['activation_to'])
            $select->where('DATE(i.activation_date) <= ?', $params['activation_to']);

        if (isset($params['pg']) && $params['pg'])
            $select->where('i.pg_id = ?', intval($params['pg']));

        if (isset($params['sale']) && $params['sale'])
            $select->where('i.sale_id = ?', intval($params['sale']));

        if (isset($params['leader']) && $params['leader'])
            $select->where('i.leader_id = ?', intval($params['leader']));

        if (isset($params['area']) && $params['area'])
            $select->where('i.area_id = ?', intval($params['area']));

        if (isset($params['district']) && $params['district'])
            $select->where('i.district_id = ?', intval($params['district']));

        if (isset($params['province']) && $params['province'])
            $select->where('i.province_id = ?', intval($params['province']));

        if (isset($params['store']) && $params['store'])
            $select->where('i.store_id = ?', intval($params['store']));

        if (isset($params['good']) && $params['good'])
            $select->where('i.good_id = ?', intval($params['good']));

        if (isset($params['color']) && $params['color'])
            $select->where('i.color_id = ?', intval($params['color']));

        if (isset($params['dealer']) && $params['dealer'])
            $select->where('i.distributor_id = ?', intval($params['dealer']));

        $total = $db->fetchOne($select);

        return $total;
    }

    /**
     * Lấy doanh số chi tiết theo loại đối tượng, trả về mảng, mỗi phần tử là doanh số của một model
     * @param  int $object_id   ID của đối tượng
     * @param  My_Kpi_Object $object_type Loại đối tượng (sale, pg, store...)
     * @param  date $from        Từ ngày
     * @param  date $to          Đến ngày
     * @param  My_Kpi_Type $object_type Loại đối tượng (sale, pg, store...)
     * @return array              Mảng array(good_id => sell_out)
     */
    public function getTotalByModel($type, $params)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('i' => $this->_name), array('i.good_id', 'total' => 'COUNT(DISTINCT i.imei_sn)'));

        switch ($type) {
            case My_Kpi_Type::SellOut:

                break;
            case My_Kpi_Type::Activated:
                $select->where('i.activation_date IS NOT NULL AND i.activation_date <> \'\'', 1);
                break;
            case My_Kpi_Type::Not_Activated:
                $select->where('i.activation_date IS NULL OR i.activation_date = \'\'', 1);
                break;
            default:

                break;
        }

        if (isset($params['from']) && $params['from'])
            $select->where('DATE(i.timing_date) >= ?', $params['from']);

        if (isset($params['to']) && $params['to'])
            $select->where('DATE(i.timing_date) <= ?', $params['to']);

        if (isset($params['pg']) && $params['pg'])
            $select->where('i.pg_id = ?', intval($params['pg']));

        if (isset($params['sale']) && $params['sale'])
            $select->where('i.sale_id = ?', intval($params['sale']));

        if (isset($params['leader']) && $params['leader'])
            $select->where('i.leader_id = ?', intval($params['leader']));

        if (isset($params['area']) && $params['area'])
            $select->where('i.area_id = ?', intval($params['area']));

        if (isset($params['district']) && $params['district'])
            $select->where('i.district_id = ?', intval($params['district']));

        if (isset($params['province']) && $params['province'])
            $select->where('i.province_id = ?', intval($params['province']));

        if (isset($params['store']) && $params['store'])
            $select->where('i.store_id = ?', intval($params['store']));

        if (isset($params['dealer']) && $params['dealer'])
            $select->where('i.distributor_id = ?', intval($params['dealer']));

        $select->group('i.good_id');

        $sell_out = array();
        $result = $db->fetchAll($select);

        if ($result)
            foreach ($result as $key => $item)
                $sell_out[ $item['good_id'] ] = isset($item['total']) ? $item['total'] : 0;

        return $sell_out;
    }

    /**
     * Lấy dữ liệu hiển thị dạng bảng, cho từng người cụ thể xem, hoặc HR kiểm tra KPI
     * @param  [type] $type   [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function fetchGrid($params)
    {
        if (!isset($params['from']) || !$params['from'] || !strtotime($params['from']))
            return false;

        if (!isset($params['to']) || !$params['to'] || !strtotime($params['to']))
            return false;

        $db = Zend_Registry::get('db');
        $select = $db->select();
        $before_closing = My_Kpi::isBeforeClosingDate($params['to']);

        if (!$before_closing) {
            $get = array(
                'g.good_id', 'color_id' => new Zend_Db_Expr('IFNULL(g.color_id, 0)'),
                'quantity' => 'COUNT(DISTINCT i.imei_sn)',
                'activated' => new Zend_Db_Expr('SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> \'\' THEN 1 ELSE 0 END)'),
                'non_activated' => new Zend_Db_Expr('SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> \'\' THEN 0 ELSE 1 END)'),
            );
        } else {
            $get = array(
                'g.good_id', 'color_id' => new Zend_Db_Expr('IFNULL(g.color_id, 0)'),
                'quantity' => 'COUNT(DISTINCT i.imei_sn)',
                'activated' => new Zend_Db_Expr('SUM(CASE WHEN i.`status` IS NOT NULL AND i.`status` > 0 THEN 1 ELSE 0 END)'),
                'non_activated' => new Zend_Db_Expr('SUM(CASE WHEN i.`status` IS NOT NULL AND i.`status` > 0 THEN 0 ELSE 1 END)'),
            );
        }

        $group = array();
        //
        if (isset($params['pg'])) {
            if (isset($params['kpi']) && $params['kpi'])
                $get['staff_id'] = 'i.pg_id';

            if (!$before_closing) {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> \'\'
                   AND i.pg_id <> i.sale_id AND i.pg_id <> i.leader_id
                   THEN i.kpi_pg
                   ELSE 0 END)');
            } else {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.`status` IS NOT NULL AND i.`status` > 0
                   AND i.pg_id <> i.sale_id AND i.pg_id <> i.leader_id
                   THEN i.kpi_pg
                   ELSE 0 END)');
            }

        } elseif (isset($params['sale'])) {
            if (isset($params['kpi']) && $params['kpi'])
                $get['staff_id'] = 'i.sale_id';

            if (!$before_closing) {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> \'\'
                   THEN i.kpi_sale
                   ELSE 0 END)');
            } else {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.`status` IS NOT NULL AND i.`status` > 0
                   THEN i.kpi_sale
                   ELSE 0 END)');
            }

        } elseif (isset($params['leader'])) {
            if (isset($params['kpi']) && $params['kpi'])
                $get['staff_id'] = 'i.leader_id';

            if (!$before_closing) {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> \'\'
                        THEN (i.`value` * 0.8 * 0.002)
                    ELSE 0 END)');
            } else {
                $get['kpi'] = new Zend_Db_Expr('SUM(CASE WHEN i.`status` IS NOT NULL AND i.`status` > 0
                        THEN (i.`value` * 0.8 * 0.002)
                    ELSE 0 END)');
            }
        }
        //
        $select->from(array('i' => $this->_name), $get);
        $select->where('DATE(i.timing_date) >= ?', $params['from']);
        $select->where('DATE(i.timing_date) <= ?', $params['to']);
        //

        if (isset($params['pg']) && $params['pg']) {
            $select->where('i.pg_id <> i.sale_id', 1);

            if (!isset($params['kpi']) || !$params['kpi']) {
                $select->where('i.pg_id = ?', intval($params['pg']));

            } else {
                if (is_array($params['pg']) && count($params['pg']))
                    $select->where('i.pg_id IN (?)', $params['pg']);
                else
                    $select->where('1=0', 1);

                $group = array('i.pg_id');
            }

            $select->where('g.type = ?', My_Kpi_Object::Pg);

        } elseif (isset($params['sale']) && $params['sale']) {
            if (!isset($params['kpi']) || !$params['kpi']) {
                $select->where('i.sale_id = ?', intval($params['sale']));

            } else {
                if (is_array($params['sale']) && count($params['sale']))
                    $select->where('i.sale_id IN (?)', $params['sale']);
                else
                    $select->where('1=0', 1);

                $group = array('i.sale_id');
            }

            $select->where('g.type = ?', My_Kpi_Object::Sale);

        } elseif (isset($params['leader']) && $params['leader']) {
            if (!isset($params['kpi']) || !$params['kpi']) {
                $select->where('i.leader_id = ?', intval($params['leader']));

            } else {
                if (is_array($params['leader']) && count($params['leader']))
                    $select->where('i.leader_id IN (?)', $params['leader']);
                else
                    $select->where('1=0', 1);

                $group = array('i.leader_id');
            }
        }
        //

        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                    $select->where('i.area_id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                    $select->where('i.area_id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                    $select->where('i.district_id IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                    $select->where('i.district_id = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['province']) && $params['province']) {
            if (is_array($params['province']) && count($params['province']))
                    $select->where('i.province_id IN (?)', $params['province']);
            elseif (is_numeric($params['province']))
                    $select->where('i.province_id = ?', intval($params['province']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                    $select->where('i.store_id IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                    $select->where('i.store_id = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['dealer']) && $params['dealer']) {
            if (is_array($params['dealer']) && count($params['dealer']))
                    $select->where('i.distributor_id IN (?)', $params['dealer']);
            elseif (is_numeric($params['dealer']))
                    $select->where('i.distributor_id = ?', intval($params['dealer']));
            else
                $select->where('1=0', 1);
        }
        // INNER JOIN good_kpi_log g ON i.good_id=g.good_id AND g.from_date<= i.timing_date AND ( (g.to_date IS NULL OR g.to_date = '') OR g.to_date >= i.timing_date )

        // Leader thì lấy KPI trên % giá bán
        if (isset($params['leader']) && intval($params['leader'])) {
            $join_get = array(
                'from_date' => new Zend_Db_Expr(sprintf("CASE WHEN g.from_date < '%s' THEN '%s' ELSE g.from_date END", $params['from'], $params['from'])),
                'to_date' => new Zend_Db_Expr(sprintf("CASE WHEN g.to_date IS NULL OR g.to_date > '%s' THEN '%s' ELSE g.to_date END", $params['to'], $params['to'])),
                'kpi_unit' => new Zend_Db_Expr('g.price * 0.002 * 0.8'),
            );

            $select->join(
                array('g' => WAREHOUSE_DB.'.good_price_log'),
                'g.good_id=i.good_id
                AND
                    CASE WHEN g.color_id <> 0 AND g.color_id IS NOT NULL THEN i.color_id=g.color_id
                    ELSE i.color_id NOT IN (
                            SELECT '.WAREHOUSE_DB.'.`good_price_log`.color_id
                            FROM '.WAREHOUSE_DB.'.`good_price_log`
                            WHERE '.WAREHOUSE_DB.'.`good_price_log`.color_id <> 0
                            AND '.WAREHOUSE_DB.'.`good_price_log`.good_id = i.good_id
                            AND '.WAREHOUSE_DB.'.`good_price_log`.from_date <= DATE(i.timing_date)
                            AND ('.WAREHOUSE_DB.'.`good_price_log`.to_date IS NULL OR '.WAREHOUSE_DB.'.`good_price_log`.to_date = 0 OR '.WAREHOUSE_DB.'.`good_price_log`.to_date >= DATE(i.timing_date))
                        )
                    END
                AND g.from_date<= DATE(i.timing_date)
                AND ( g.to_date IS NULL OR g.to_date = 0 OR g.to_date >= DATE(i.timing_date) )',
                $join_get
            );

        } else {
            $join_get = array(
                'from_date' => new Zend_Db_Expr(sprintf("CASE WHEN g.from_date < '%s' THEN '%s' ELSE g.from_date END", $params['from'], $params['from'])),
                'to_date' => new Zend_Db_Expr(sprintf("CASE WHEN g.to_date IS NULL OR g.to_date > '%s' THEN '%s' ELSE g.to_date END", $params['to'], $params['to'])),
                'kpi_unit' => 'g.kpi',
            );

            $select->join(
                array('g' => 'good_kpi_log'),
                'g.good_id=i.good_id
                AND
                    CASE WHEN g.color_id <> 0 AND g.color_id IS NOT NULL THEN i.color_id=g.color_id
                    ELSE i.color_id NOT IN (
                            SELECT good_kpi_log.color_id
                            FROM `good_kpi_log`
                            WHERE good_kpi_log.color_id <> 0
                            AND good_kpi_log.good_id = i.good_id
                            AND good_kpi_log.from_date <= DATE(i.timing_date)
                            AND (good_kpi_log.to_date IS NULL OR good_kpi_log.to_date = 0 OR good_kpi_log.to_date >= DATE(i.timing_date))
                        )
                    END
                AND g.from_date<= DATE(i.timing_date)
                AND ( g.to_date IS NULL OR g.to_date = 0 OR g.to_date >= DATE(i.timing_date) )',
                $join_get
            );
        }

        $union = clone $select; // thằng select thuộc loại tham chiếu, clone ra để group nó tiếp, union thì không group
        $default_group = array('g.good_id', 'g.color_id', 'g.from_date');
        $select->group( array_merge( $group,  $default_group) );

        if (isset($params['kpi']) && $params['kpi']) {
            return $db->fetchAll($select);

        } else {
            $total_select = $db->select()->union(array($select, $union));

            return $db->fetchAll($total_select);
        }


    }

    public function fetchArea($params)
    {
        if (!isset($params['from']) || !$params['from'])
            return false;

        if (!isset($params['to']) || !$params['to'])
            return false;

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $before_closing = My_Kpi::isBeforeClosingDate($to);

        $db = Zend_Registry::get('db');
        $select = $db->select();

        if (!$before_closing) {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN 1 ELSE 0 END)"),
            );
        } else {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.`status`>0 THEN 1 ELSE 0 END)"),
            );
        }

        if (isset($params['kpi']) && $params['kpi']) {
            $join_get = array(
                'g.good_id',
                'color_id'    => new Zend_Db_Expr('IFNULL(g.color_id, 0)'),
                'from_date'   => new Zend_Db_Expr(sprintf("CASE WHEN g.from_date < '%s' THEN '%s' ELSE g.from_date END", $from, $from)),
                'to_date'     => new Zend_Db_Expr(sprintf("CASE WHEN g.to_date IS NULL OR g.to_date > '%s' THEN '%s' ELSE g.to_date END", $to, $to)),
                'total_value' => new Zend_Db_Expr("SUM(g.price) * 0.8"),
            );

            if (!$before_closing) {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN g.price ELSE 0 END) * 0.8");
            } else {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN g.price ELSE 0 END) * 0.8");
            }
        } else {
            $get['total_value'] = new Zend_Db_Expr("SUM(i.`value`) * 0.8");

            if (!$before_closing) {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN i.`value` ELSE 0 END) * 0.8");
            } else {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN i.`value` ELSE 0 END) * 0.8");
            }
        }
/* 
        $select->from(array('i' => $this->_name), $get);
        $select->join(
            array('a' => 'area'), 
            'a.id = i.area_id', 
            array('area_id' => 'a.id', 'area_name' => 'a.name', 'region_share' => 'a.region_share')
        );
*/
        $select->from(array('a' => 'area'), $get);

/*
        if (isset($params['from']) && $params['from'])
            $select->where('DATE(i.timing_date) >= ?', $from);

        if (isset($params['to']) && $params['to'])
            $select->where('DATE(i.timing_date) <= ?', $to);
*/

        if (isset($params['from']) && $params['from'])
            $tmp_from = " AND (DATE(i.timing_date) >= '".$from." 00:00:00') ";
        if (isset($params['to']) && $params['to'])
            $tmp_to = " AND (DATE(i.timing_date) <= '".$to." 23:59:59') ";

        $select->joinLeft(
            array('i' => $this->_name), 
            'i.area_id = a.id '.$tmp_from.$tmp_to, 
            array('area_id' => 'a.id', 'area_name' => 'a.name', 'region_share' => 'a.region_share')
        );

        if (isset($params['kpi']) && $params['kpi']) {
            $select->join(
                array('g' => WAREHOUSE_DB.'.good_price_log'),
                'g.good_id=i.good_id
                AND
                    CASE WHEN g.color_id <> 0 AND g.color_id IS NOT NULL THEN i.color_id=g.color_id
                    ELSE i.color_id NOT IN (
                            SELECT '.WAREHOUSE_DB.'.`good_price_log`.color_id
                            FROM '.WAREHOUSE_DB.'.`good_price_log`
                            WHERE '.WAREHOUSE_DB.'.`good_price_log`.color_id <> 0
                            AND '.WAREHOUSE_DB.'.`good_price_log`.good_id = i.good_id
                            AND '.WAREHOUSE_DB.'.`good_price_log`.from_date <= DATE(i.timing_date)
                            AND ('.WAREHOUSE_DB.'.`good_price_log`.to_date IS NULL OR '.WAREHOUSE_DB.'.`good_price_log`.to_date = 0 OR '.WAREHOUSE_DB.'.`good_price_log`.to_date >= DATE(i.timing_date))
                        )
                    END
                AND g.from_date<= DATE(i.timing_date)
                AND ( g.to_date IS NULL OR g.to_date = 0 OR g.to_date >= DATE(i.timing_date) )',
                $join_get
            );
        }

        $select->where('a.id NOT IN (48,49,72)');
/*       
        if (isset($params['area']) && $params['area'])
            $select->where('a.id = ?', $params['area']);
*/
        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                $select->where('a.id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                $select->where('a.id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        if ( isset($params['am']) && $params['am'] ) {
            $select->join(array('st' => 'store'), 'i.store_id = st.id' ,array());

            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            if (count($list_org) > 0)
                $select->where( 'st.org_dealer IN (?)', $list_org);
            else
                $select->where('1=0', 1);

            //echo $select;
        }

        if (!isset($params['get_total_sales']) || !$params['get_total_sales']) {

            if (isset($params['kpi']) && $params['kpi'])
                $select->group( array('a.id', 'g.good_id', 'g.color_id', 'g.from_date') );
            else
                $select->group('a.id');
            

            if (isset($params['export']) && $params['export'] == 1) {
                $select->order('a.name ASC');
            } else {
                $select->order('total_quantity DESC');
            }
            
            //echo $select;
            return $db->fetchAll($select);
        } else {
            if (isset($params['list_regions']) && $params['list_regions']) {
                $select->where('a.id IN (?)', $params['list_regions']);
            }
            //echo $select;
            return $db->fetchRow($select);
        }
    }

    public function fetchProduct($page, $limit, &$total, $params)
    {
        if (!isset($params['from']) || !$params['from'] || !date_create_from_format("d/m/Y", $params['from']))
            return false;

        if (!isset($params['to']) || !$params['to'] || !date_create_from_format("d/m/Y", $params['to']))
            return false;

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $before_closing = My_Kpi::isBeforeClosingDate($to);

        $db = Zend_Registry::get('db');
        $select = $db->select();

        if (!$before_closing) {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN 1 ELSE 0 END)"),
            );
        } else {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.`status`>0 THEN 1 ELSE 0 END)"),
            );
        }

        $get['good_id'] = 'i.good_id';

        if (isset($params['kpi']) && $params['kpi']) {
            $join_get = array(
                'g.good_id',
                'color_id'    => new Zend_Db_Expr('IFNULL(g.color_id, 0)'),
                'from_date'   => new Zend_Db_Expr(sprintf("CASE WHEN g.from_date < '%s' THEN '%s' ELSE g.from_date END", $from, $from)),
                'to_date'     => new Zend_Db_Expr(sprintf("CASE WHEN g.to_date IS NULL OR g.to_date > '%s' THEN '%s' ELSE g.to_date END", $to, $to)),
                'total_value' => new Zend_Db_Expr("SUM(g.price) * 0.8"),
            );

            if (!$before_closing) {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN g.price ELSE 0 END) * 0.8");
            } else {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN g.price ELSE 0 END) * 0.8");
            }
        } else {
            $get['total_value'] = new Zend_Db_Expr("SUM(i.`value`) * 0.8");

            if (!$before_closing) {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN i.`value` ELSE 0 END) * 0.8");
            } else {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN i.`value` ELSE 0 END) * 0.8");
            }
        }

        $select->from(array('i' => $this->_name), $get);

        if (isset($params['kpi']) && $params['kpi']) {
            $select->join(
                array('g' => WAREHOUSE_DB.'.good_price_log'),
                'g.good_id=i.good_id
                AND
                    CASE WHEN g.color_id <> 0 AND g.color_id IS NOT NULL THEN i.color_id=g.color_id
                    ELSE i.color_id NOT IN (
                            SELECT '.WAREHOUSE_DB.'.`good_price_log`.color_id
                            FROM '.WAREHOUSE_DB.'.`good_price_log`
                            WHERE '.WAREHOUSE_DB.'.`good_price_log`.color_id <> 0
                            AND '.WAREHOUSE_DB.'.`good_price_log`.good_id = i.good_id
                            AND '.WAREHOUSE_DB.'.`good_price_log`.from_date <= DATE(i.timing_date)
                            AND ('.WAREHOUSE_DB.'.`good_price_log`.to_date IS NULL OR '.WAREHOUSE_DB.'.`good_price_log`.to_date = 0 OR '.WAREHOUSE_DB.'.`good_price_log`.to_date >= DATE(i.timing_date))
                        )
                    END
                AND g.from_date<= DATE(i.timing_date)
                AND ( g.to_date IS NULL OR g.to_date = 0 OR g.to_date >= DATE(i.timing_date) )',
                $join_get
            );
        }
        //
        $select->join(array('s' => 'store'), 's.id=i.store_id', array());

        if (isset($params['sale_id']) && $params['sale_id'])
            $select->where('i.sale_id = ?', intval($params['sale_id']));

        if (isset($params['leader_id']) && $params['leader_id'])
            $select->where('i.leader_id = ?', intval($params['leader_id']));

        if (isset($params['distributor_id']) && $params['distributor_id'])
            $select->where('s.d_id = ?', intval($params['distributor_id']));

        if (isset($params['area']) && $params['area']) {
            if (is_array($params['area']) && count($params['area']))
                    $select->where('i.area_id IN (?)', $params['area']);
            elseif (is_numeric($params['area']))
                    $select->where('i.area_id = ?', intval($params['area']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['area_list']) && $params['area_list']) {
            if (is_array($params['area_list']) && count($params['area_list']))
                    $select->where('i.area_id IN (?)', $params['area_list']);
            elseif (is_numeric($params['area_list']))
                    $select->where('i.area_id = ?', intval($params['area_list']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                    $select->where('i.district_id IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                    $select->where('i.district_id = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['province']) && $params['province']) {
            if (is_array($params['province']) && count($params['province']))
                    $select->where('i.province_id IN (?)', $params['province']);
            elseif (is_numeric($params['province']))
                    $select->where('i.province_id = ?', intval($params['province']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                    $select->where('i.store_id IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                    $select->where('i.store_id = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store_list']) && $params['store_list']) {
            if (is_array($params['store_list']) && count($params['store_list']))
                    $select->where('i.store_id IN (?)', $params['store_list']);
            elseif (is_numeric($params['store_list']))
                    $select->where('i.store_id = ?', intval($params['store_list']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['from']) && $params['from'])
            $select->where('DATE(i.timing_date) >= ?', $from);

        if (isset($params['to']) && $params['to'])
            $select->where('DATE(i.timing_date) <= ?', $to);

        if (isset($params['name']) && $params['name']) {
            $select->joinRight(array('ss' => 'store'), 'ss.id=i.store_id', array());
            $select->where('ss.name LIKE ?', '%'.$params['name'].'%');
        }

        if (isset($params['get_total_sales']) && $params['get_total_sales'])
            return $db->fetchRow($select);

        if (isset($params['kpi']) && $params['kpi'])
            $select->group( array('i.store_id', 'g.good_id', 'g.color_id', 'g.from_date') );
        else
            $select->group('i.good_id');

        if (isset($params['kpi']) && $params['kpi'])
            $main_get = array('B.good_id', 'B.color_id', 'B.from_date', 'B.to_date');
        else
            $main_get = array();

        $main_get = array_merge($main_get, array('B.total_quantity', 'B.total_activated', 'B.total_value_activated', 'B.total_value'));

        $main_select = $db->select()
            ->from(array('w' => WAREHOUSE_DB.'.good'), array('good_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS w.id'), 'product_name' => 'w.name', 'product_desc' => 'w.desc'))
            ->joinLeft(array('B' => $select), 'B.good_id=w.id', $main_get);

        $main_select->where('w.cat_id = ?', 11);

        if (isset($params['good_id']) && $params['good_id']) {
            if (is_array($params['good_id']) && count($params['good_id']))
                $main_select->where('w.id IN (?)', $params['good_id']);
            elseif (is_numeric($params['good_id']))
                $main_select->where('w.id = ?', intval($params['good_id']));
            else
                $main_select->where('1=0', 1);
        }

        if (isset($params['name']) && $params['name'])
            $main_select->where('w.name LIKE ?', '%'.$params['name'].'%');

        $main_select->order('total_quantity DESC');

        if (isset($params['export']) && $params['export'])
            return $main_select->__toString();

        if ($limit)
            $main_select->limitPage($page, $limit);

        $result = $db->fetchAll($main_select);

        if ((!isset($params['export']) || !$params['export']) && $limit)
            $total = $db->fetchOne("SELECT FOUND_ROWS()");

        return $result;
    }


    // Report By Store : Export Sell All Brand
    public function fetchStorebrand($page, $limit, &$total, $params) {
        $db = Zend_Registry::get('db');

        if (!isset($params['from']) || !$params['from'])
            return false;

        if (!isset($params['to']) || !$params['to'])
            return false;

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $select = $db->select()
            ->from(array('s' => $this->_brandname),
                array(new Zend_Db_Expr('s.name')));

        $select->joinLeft( array('t'    => 'timing')            , 's.id = t.store'              , array('samsung' => 'SUM(t.samsung)', 'vivo' => 'SUM(t.vivo)' ,'other' => 'SUM(t.other)' ) );
        $select->joinLeft( array('ts'   => 'timing_sale')       , 't.id = ts.timing_id'         , array('oppo' => 'SUM(ts.quantity)') );
        $select->joinLeft( array('rm'   => 'regional_market')   , 's.regional_market = rm.id'   , array('regional_market' => 'rm.name') );
        $select->joinLeft( array('ar'   => 'area')              , 'rm.area_id = ar.id'          , array('area' => 'ar.name') );

        if (isset($params['from']) and $params['from'])
            $select->where('t.created_at >= ?',$from);

         if (isset($params['to']) and $params['to'])
            $select->where('t.created_at <= ?',$to);

        $select->group('s.name');

        //echo $select;die;

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    // Model get ORG List
    public function fetchORG($page, $limit, &$total, $params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('o' => 'org'),
                array(new Zend_Db_Expr('o.org_id'), 'org_name' => 'o.org_name'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    // Model get ORG List
    public function countGood($store_id,$params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('g' => WAREHOUSE_DB.'.good'), array(new Zend_Db_Expr('g.id'), 'good_name' => 'g.name'));

        $select->joinLeft(array('gcc' => WAREHOUSE_DB.'.good_color_combined'), 'g.id = gcc.good_id', array());
        $select->joinLeft(array('gc'  => WAREHOUSE_DB.'.good_color'), 'gcc.good_color_id = gc.id', array('color_name' => 'gc.name'));

        if (!is_null($store_id)) {
            $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
            $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

            $select->joinLeft(array('i' => 'imei_kpi') , "g.id = i.good_id AND gc.id = i.color_id AND i.store_id = ".$store_id." AND DATE(i.timing_date) >= '".$from."' AND DATE(i.timing_date) <= '".$to."' ", 
                array('cnt' => 'COUNT(i.imei_sn)'));
        } 
        
        $select->where('g.cat_id = ?', PHONE_CAT_ID);  
        $select->group(array('g.id','gc.id'));
        $select->order('g.name ASC');

        //echo $select;die;
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        //print_r($result);die;
        return $result;
    }

    // Store List for Report by Store
    public function fetchStore($page, $limit, &$total, $params)
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        if (!isset($params['from']) || !$params['from'])
            return false;

        if (!isset($params['to']) || !$params['to'])
            return false;

        $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
        $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");

        $before_closing = My_Kpi::isBeforeClosingDate($to);

        $db = Zend_Registry::get('db');
        $select = $db->select();
/*
->join(array('i' => WAREHOUSE_DB.'.imei'), 
                "   ts.imei = i.imei_sn 
                    AND i.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                    AND i.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
                ", array())
*/
        if (!$before_closing) {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.activation_date IS NOT NULL AND i.activation_date <> 0 THEN 1 ELSE 0 END)"),
                'total_not_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.activation_date IS NULL THEN 1 ELSE 0 END)"),
                /*
                'com_activated' => new Zend_Db_Expr("
                        SUM(
                            CASE WHEN i.activation_date IS NOT NULL 
                                AND i.activation_date <> 0 
                                AND i.activation_date <= DATE_FORMAT(LAST_DAY(i.timing_date) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59') 
                                AND i.activation_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(i.timing_date),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
                            THEN 
                                1 ELSE 0 END)"),*/
            );
        } else {
            $get = array(
                'total_quantity'  => new Zend_Db_Expr("COUNT(DISTINCT i.imei_sn)"),
                'total_activated' => new Zend_Db_Expr("SUM(CASE WHEN i.`status`>0 THEN 1 ELSE 0 END)"),
            );
        }

        $get['store_id'] = 'i.store_id';
        $get['sale_id'] = 'i.sale_id';
        

        if (isset($params['kpi']) && $params['kpi']) {
            $join_get = array(
                'g.good_id',
                'color_id'    => new Zend_Db_Expr('IFNULL(g.color_id, 0)'),
                'from_date'   => new Zend_Db_Expr(sprintf("CASE WHEN g.from_date < '%s' THEN '%s' ELSE g.from_date END", $from, $from)),
                'to_date'     => new Zend_Db_Expr(sprintf("CASE WHEN g.to_date IS NULL OR g.to_date > '%s' THEN '%s' ELSE g.to_date END", $to, $to)),
                'total_value' => new Zend_Db_Expr("SUM(g.price) * 0.8"),
            );

            if (!$before_closing) {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN g.price ELSE 0 END) * 0.8");
            } else {
                $join_get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN g.price ELSE 0 END) * 0.8");
            }
        } else {
            $get['total_value'] = new Zend_Db_Expr("SUM(i.`value`) * 0.8");

            if (!$before_closing) {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`activation_date` IS NOT NULL AND i.`activation_date` <> 0 THEN i.`value` ELSE 0 END) * 0.8");
            } else {
                $get['total_value_activated'] = new Zend_Db_Expr("SUM(CASE WHEN i.`status`> 0 THEN i.`value` ELSE 0 END) * 0.8");
            }
        }

        $select->from(array('i' => $this->_name), $get);

        if (isset($params['kpi']) && $params['kpi']) {
            $select->join(
                array('g' => WAREHOUSE_DB.'.good_price_log'),
                'g.good_id=i.good_id
                AND
                    CASE WHEN g.color_id <> 0 AND g.color_id IS NOT NULL THEN i.color_id=g.color_id
                    ELSE i.color_id NOT IN (
                            SELECT '.WAREHOUSE_DB.'.`good_price_log`.color_id
                            FROM '.WAREHOUSE_DB.'.`good_price_log`
                            WHERE '.WAREHOUSE_DB.'.`good_price_log`.color_id <> 0
                            AND '.WAREHOUSE_DB.'.`good_price_log`.good_id = i.good_id
                            AND '.WAREHOUSE_DB.'.`good_price_log`.from_date <= DATE(i.timing_date)
                            AND ('.WAREHOUSE_DB.'.`good_price_log`.to_date IS NULL OR '.WAREHOUSE_DB.'.`good_price_log`.to_date = 0 OR '.WAREHOUSE_DB.'.`good_price_log`.to_date >= DATE(i.timing_date))
                        )
                    END
                AND g.from_date<= DATE(i.timing_date)
                AND ( g.to_date IS NULL OR g.to_date = 0 OR g.to_date >= DATE(i.timing_date) )',
                $join_get
            );
        }
        //

        if (isset($params['sale_id']) && $params['sale_id'])
            $select->where('i.sale_id = ?', intval($params['sale_id']));

        if (isset($params['leader_id']) && $params['leader_id'])
            $select->where('i.leader_id = ?', intval($params['leader_id']));

        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('i.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                    $select->where('i.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['area_list']) && $params['area_list']) {
            if (is_array($params['area_list']) && count($params['area_list']))
                    $select->where('i.area_id IN (?)', $params['area_list']);
            elseif (is_numeric($params['area_list']))
                    $select->where('i.area_id = ?', intval($params['area_list']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                    $select->where('i.district_id IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                    $select->where('i.district_id = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                    $select->where('i.province_id IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                    $select->where('i.province_id = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                    $select->where('i.store_id IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                    $select->where('i.store_id = ?', intval($params['store']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store_list']) && $params['store_list']) {
            if (is_array($params['store_list']) && count($params['store_list']))
                    $select->where('i.store_id IN (?)', $params['store_list']);
            elseif (is_numeric($params['store_list']))
                    $select->where('i.store_id = ?', intval($params['store_list']));
            else
                $select->where('1=0', 1);
        }

        if (isset($params['from']) && $params['from'])
            $select->where('i.timing_date >= ?', $from.' 00:00:00');

        if (isset($params['to']) && $params['to'])
            $select->where('i.timing_date <= ?', $to.' 23:59:59');

        if (isset($params['name']) && $params['name']) {
            $select->joinRight(array('ss' => 'store'), 'ss.id=i.store_id', array());
            $select->where('ss.name LIKE ?', '%'.$params['name'].'%');
        }

        if (isset($params['id']) && $params['id']) {
            $select->where('i.store_id LIKE ?', '%'.$params['id'].'%');
        }

        // Adding Code by Sak : Start

        // filter ORG Dealer
        if (isset($params['org']) and $params['org']) {
            if (is_array($params['org']) && count($params['org'])) {
                $select->joinLeft( array('st' => 'store'), 'st.id = i.store_id', array());
                $select->where('st.org_dealer IN (?)', $params['org']);
            } elseif (is_numeric($params['org'])) {
                $select->joinLeft( array('st' => 'store'), 'st.id = i.store_id', array());
                
                
                $select->where('st.org_dealer = ?', intval($params['org']));
            } else {
                $select->where('1=0', 1);
            }
        }

        if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {

            $select->joinLeft( array('sm' => 'store_market'), 'i.store_id = sm.store_id'    , array());
            $select->joinLeft( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array());

            // filter Market Type
            if (isset($params['market_type']) and $params['market_type']) {
                if (is_array($params['market_type']) && count($params['market_type'])) { 

                    $select->where('mn.market_type_id IN (?)', $params['market_type']);

                } elseif (is_numeric($params['market_type'])) {
                    
                    $select->where('mn.market_type_id = ?', intval($params['market_type']));

                } else {
                    $select->where('1=0', 1);
                }
            }

            // Filter Market Name
            if (isset($params['market_name']) && $params['market_name'])
                $select->where('mn.name LIKE ?', '%'.$params['market_name'].'%');

        }



        // Adding Code by Sak : End

/*
        if (isset($params['get_total_sales']) && $params['get_total_sales'])
            return $db->fetchRow($select);*/

        if (isset($params['kpi']) && $params['kpi'])
            $select->group( array('i.store_id', 'g.good_id', 'g.color_id', 'g.from_date') );
        else
            $select->group('i.store_id');

        if (isset($params['kpi']) && $params['kpi'])
            $main_get = array('B.good_id', 'B.color_id', 'B.from_date', 'B.to_date');
        else
            $main_get = array();

        $main_get = array_merge($main_get, array('B.total_quantity', 'B.total_activated', 'B.total_not_activated', 'B.total_value_activated', 'B.total_value'));

        $main_select = $db->select()
            ->from(array('s' => 'store'), 
                array('store_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 
                    'store_name'        => 's.name', 
                    'shop_id'           => 's.store_id',
                    'shop_code'         => 's.store_code',
                    'store_rank'        => 's.rank',
                    'store_district'    => 's.district', 
                    's.company_address'))
            ->joinLeft( array('B'   => $select)             , 'B.store_id = s.id'           , $main_get)
            ->joinLeft( array('stf' => 'staff')             , 'stf.id = B.sale_id'          , array('sale_firstname' => 'stf.firstname', 'sale_lastname' => 'stf.lastname'))
            ->joinLeft( array('rm1' => 'regional_market')   , 'rm1.id = s.district'         , array('district_name' => 'rm1.name'))
            ->joinLeft( array('rm2' => 'regional_market')   , 'rm2.id = s.regional_market'  , array('province_name' => 'rm2.name'))
            ->joinLeft( array('a'   => 'area')              , 'rm2.area_id = a.id'          , array('area_name' => 'a.name'))
            ->joinLeft( array('sm'  => 'store_market')      , 's.id = sm.store_id'          , array())
            ->joinLeft( array('mn'  => 'market_name')       , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'))
            ->joinLeft( array('mt'  => 'market_type')       , 'mn.market_type_id = mt.id'   , array('market_type' => 'mt.name'));
        $main_select->where('s.del IS NULL');
        $main_select->where('s.rank <> 3');

        if (isset($params['store']) && $params['store']) {
            if (is_array($params['store']) && count($params['store']))
                $main_select->where('s.id IN (?)', $params['store']);
            elseif (is_numeric($params['store']))
                $main_select->where('s.id = ?', intval($params['store']));
            else
                $main_select->where('1=0', 1);
        }

        if (isset($params['store_list']) && $params['store_list']) {
            if (is_array($params['store_list']) && count($params['store_list']))
                $main_select->where('s.id IN (?)', $params['store_list']);
            elseif (is_numeric($params['store_list']))
                $main_select->where('s.id = ?', intval($params['store_list']));
            else
                $main_select->where('1=0', 1);
        }

        if (isset($params['area_list']) && $params['area_list']) {
            if (is_array($params['area_list']) && count($params['area_list']))
                $main_select
                    ->join(array('dt' => 'regional_market'), 'dt.id=s.district', array())
                    ->join(array('pr' => 'regional_market'), 'pr.id=dt.parent', array())
                    ->where('pr.area_id IN (?)', $params['area_list']);
            elseif (is_numeric($params['area_list']))
                $main_select
                    ->join(array('dt' => 'regional_market'), 'dt.id=s.district', array())
                    ->join(array('pr' => 'regional_market'), 'pr.id=dt.parent', array())
                    ->where('pr.area_id = ?', intval($params['area_list']));
            else
                $main_select->where('1=0', 1);
        }

        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $main_select->where('s.district IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $main_select->where('s.district = ?', intval($params['district']));
            else
                $main_select->where('1=0', 1);

        } elseif (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $main_select
                    //->join(array('dt2' => 'regional_market'), 'dt2.id=s.district', array())
                    ->where('s.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $main_select
                    //->join(array('dt2' => 'regional_market'), 'dt2.id=s.district', array())
                    ->where('s.regional_market = ?', intval($params['regional_market']));
            else
                $main_select->where('1=0', 1);

        } elseif (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $main_select
                    //->join(array('dt2' => 'regional_market'), 'dt2.id=s.district', array())
                    //->join(array('pr2' => 'regional_market'), 'pr2.id=dt2.parent', array())
                    ->where('rm2.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area']))
                $main_select
                    //->join(array('dt2' => 'regional_market'), 'dt2.id=s.district', array())
                    //->join(array('pr2' => 'regional_market'), 'pr2.id=dt2.parent', array())
                    ->where('rm2.area_id = ?', intval($params['area_id']));
            else
                $main_select->where('1=0', 1);
        }

        // Store Info
        if (isset($params['id']) && $params['id'])
            $main_select->where('s.id LIKE ?', '%'.$params['id'].'%');

        // Store Info
        if (isset($params['name']) && $params['name'])
            $main_select->where('s.name LIKE ?', '%'.$params['name'].'%');

        // Staff Info
        
        if (isset($params['staff_name']) && $params['staff_name'])
            $main_select->where('CONCAT(stf.firstname, " ",stf.lastname) LIKE ?', '%'.$params['staff_name'].'%');
        if (isset($params['staff_code']) && $params['staff_code'])
            $main_select->where('stf.code LIKE ?', '%'.$params['staff_code'].'%');

        // Adding Code by Sak : Start

        // Filter ORG Dealer
        $main_select->joinLeft( array('o' => 'org'), 'o.org_id = s.org_dealer', array('org_name'=>'o.org_name'));
        $main_select->joinLeft( array('stt' => 'store_type'), 'stt.store_type_id = o.store_type_id', array('store_type_id'=>'stt.store_type_id', 'store_type'=>'stt.store_type_name'));

        if (isset($params['org']) and $params['org']) {
            if (is_array($params['org']) && count($params['org'])) {
                $main_select->where('s.org_dealer IN (?)', $params['org']);
            } elseif (is_numeric($params['org'])) {
                $main_select->where('s.org_dealer = ?', intval($params['org']));
            } else {
                $main_select->where('1=0', 1);
            }
        }

        // Filter Market Type
        if (isset($params['market_type']) and $params['market_type']) {
            if (is_array($params['market_type']) && count($params['market_type'])) {
                $main_select->where('mn.market_type_id IN (?)', $params['market_type']);
            } elseif (is_numeric($params['market_type'])) {
                $main_select->where('mn.market_type_id = ?', intval($params['market_type']));
            } else {
                $main_select->where('1=0', 1);
            }
        }

        // Filter Market Name
        if (isset($params['market_name']) && $params['market_name'])
            $main_select->where('mn.name LIKE ?', '%'.$params['market_name'].'%');

        // Filter Sale From
        if (isset($params['sales_from']) && $params['sales_from'])
            $main_select->having('COALESCE(B.total_quantity,0) >= ?', intval($params['sales_from']));

        // Filter Sale to
        if (isset($params['sales_to']) && $params['sales_to'])
            $main_select->having('COALESCE(B.total_quantity,0) <= ?', intval($params['sales_to']));

        // Add AM Permission
        if ( isset($params['am']) && $params['am'] ) {
            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            if (count($list_org) > 0)
                $main_select->where( 's.org_dealer IN (?)', $list_org);
            else
                $main_select->where('1=0', 1);
        }

        // Adding Filter Code by Sak : End

        $main_select->order('total_quantity DESC');
        

        if ($limit && $params['export'] != 2)
            $main_select->limitPage($page, $limit);

        //print_r($params);
        //echo "<br>".$main_select;//die;

        $result = $db->fetchAll($main_select);

        if ((!isset($params['export']) || !$params['export']))
            $total = $db->fetchOne("SELECT FOUND_ROWS()");

        return $result;
    }

    public function getKpiType($staff_id, $from, $to)
    {
        $pg = $sale = $leader = 0;



        $where = array();
        $where[] = $this->getAdapter()->quoteInto('pg_id = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('pg_id <> sale_id', 1);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) >= ?', $from);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) <= ?', $to);
        $result = $this->fetchRow($where);

        if ($result) $pg = 1;

        $where = array();
        $where[] = $this->getAdapter()->quoteInto('sale_id = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) >= ?', $from);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) <= ?', $to);
        $result = $this->fetchRow($where);

        if ($result) $sale = 1;

        $where = array();
        $where[] = $this->getAdapter()->quoteInto('leader_id = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) >= ?', $from);
        $where[] = $this->getAdapter()->quoteInto('DATE(timing_date) <= ?', $to);
        $result = $this->fetchRow($where);

        if ($result) {
            $pg = 0;
            $leader = 1;
        }

        return array(
            'pg'     => $pg,
            'sale'   => $sale,
            'leader' => $leader,
        );
    }


     public function GetShopSelloutForWebService($params){
        $db = Zend_Registry::get('db');
         $select = $db->select()
            ->from(array('t'=>$this->_name),array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT store_id'),
                'store_id',
                '@one'   => 'COUNT(DISTINCT CASE WHEN t.activation_date >= "'.$params['one'].'" AND t.activation_date <= "'.$params['one_end'].'" THEN t.imei_sn ELSE NULL END)',
                '@two'   => 'COUNT(DISTINCT CASE WHEN t.activation_date >= "'.$params['two'].'" AND t.activation_date <= "'.$params['two_end'].'" THEN t.imei_sn ELSE NULL END)',
                '@three' => 'COUNT(DISTINCT CASE WHEN t.activation_date >= "'.$params['three'].'" AND t.activation_date <= "'.$params['three_end'].'" THEN t.imei_sn ELSE NULL END)'
            ))
            ->group('t.store_id')
            ->where('t.area_id = ?', $params['area_id']);

            // if(isset($params['limited_3_month']) and $params['limited_3_month'])
            // {
            //     $select->where('@one <> 0', null)
            //     ->where('@two <> 0',null)
            //     ->where('@three <> 0',null);
            // }

            if(isset($params['store_id']) and $params['store_id'])
            {
                $select->where('t.store_id  = ? ', intval($params['store_id']));
            }

        $total   = $db->fetchOne("select FOUND_ROWS()");
        $sellout = $db->fetchAll($select);
        return $sellout;


    }
}