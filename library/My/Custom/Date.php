<?php
/**
 * Tổng hợp một số hàm xử lý về ngày tháng (tất nhiên là hàm tự viết/sưu tầm thêm)
 */
class My_Custom_Date {
	/**
	 * Kiểm tra xem $date và $date+$d, một trong hay thời gian này có nằm giữa $from với $to hay không
	 * 		Nói cách khác là 2 khoảng thời gian này có chồng lên nhau hay không
	 * @param  datetime  $from   ngày bắt đầu
	 * @param  datetime  $to     ngày kết thúc
	 * @param  datetime  $date   ngày để check
	 * @param  integer $d        khoảng thời gian, tính bằng ngày
	 * @return boolean true      2 khoảng thời gian này có chồng lên nhau
	 * @return boolean false     2 khoảng thời gian này không chồng lên nhau
	 */
    public static function is_between($from, $to, $date, $d = 0) {
        $from   = strtotime($from);
        $to     = strtotime($to);
        $date   = strtotime($date);
        $date_d = $date + $d*24*3600;

        if ( ($date >= $from && $date <= $to) || ($date_d >= $from && $date_d <= $to) ) {
            return true;
        } else {
            return false;
        }
    }

	public static function time_span_overlap($data_from, $data_to, $check_from, $check_to = NULL) {
        $data_from  = strtotime($data_from);
        $data_to    = strtotime($data_to);
        $check_from = strtotime($check_from);
        $check_to   = strtotime($check_to);

        if ( ( is_null($data_to) && $check_to >= $data_from )
                || ($check_from >= $data_from && $check_from <= $data_to) 
                || ($check_to >= $data_from && $check_to <= $data_to) ) {
			return true;
		} else {
			return false;
		}
	}
}