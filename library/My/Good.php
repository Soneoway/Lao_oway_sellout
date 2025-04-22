<?php 
class My_Good {

    /**
     * Lấy giá của model/màu theo thời gian - được lưu trong bảng good_price_log
     * @param  int        - $good_id    - ID model
     * @param  int        - $color_id   - ID màu
     * @param  MySQL date - $date - ngày kiểm tra giá
     * @return mixed      - int price / null
     */
    public static function getPrice($good_id, $color_id, $date)
    {
        $QPrice = new Application_Model_GoodPriceLog();

        if (empty($good_id) || !intval($good_id))
            return null;

        if (empty($color_id) || !intval($color_id))
            return null;

        if (!$date || !strtotime($date))
            return null;

        $where = $QPrice->getAdapter()->quoteInto('good_id = ?', $good_id)
            . " AND " .$QPrice->getAdapter()->quoteInto('from_date <= ?', $date)
            . " AND 
                ( "
                . $QPrice->getAdapter()->quoteInto('to_date IS NULL', 1)
                . " OR "
                . $QPrice->getAdapter()->quoteInto('to_date > ?', $date)
            . " ) ";
        
        // Kiểm tra các ngoại lệ trước
        // Ngoại lệ là các model có giá tùy thuộc vào màu sắc
        $check_by_color = " AND " .$QPrice->getAdapter()->quoteInto('color_id = ?', $color_id);
        $result = $QPrice->fetchRow($where . $check_by_color);

        if ( $result && isset($result['price']) )
            return $result['price'];

        // các trường hợp còn lại
        // giá như nhau cho tất cả các màu
        $check_all_model = " AND " .$QPrice->getAdapter()->quoteInto('color_id IS NULL', 1);
        $result = $QPrice->fetchRow($where . $check_all_model);

        if ( $result && isset($result['price']) )
            return $result['price'];

        return null;
    }
}