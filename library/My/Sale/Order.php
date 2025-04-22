<?php 
/**
* @author buu.pham
*/
class My_Sale_Order
{
    /**
     * Tạo Order SN theo cấu trúc chung
     * @author buu.pham
     * @return [type] [description]
     */
    public static function generateSn()
    {
        return date('YmdHis') . substr(microtime(), 2, 4);
    }

    /**
     * Kiểm tra nếu SN đơn hàng có tag là stock_tet_2015 thì trả về note là 'Stock TET'
     * - Dinh @ Kế Toán
     * @author buu.pham
     * @param  bigint $order_sn Order SN
     * @return string 'Stock TET' if tag stock_tet_2015
     */
    public static function getStockTetNote($order_sn)
    {
        $note = '';

        if (!$order_sn) return $note;

        $QTagObject = new Application_Model_TagObject();
        $where = array();
        $where[] = $QTagObject->getAdapter()->quoteInto('tag_id = ?', 3447); // ID của tag stock_tet_2015
        $where[] = $QTagObject->getAdapter()->quoteInto('object_id = ?', $order_sn);
        $tag = $QTagObject->fetchRow($where);

        if ($tag) $note = 'Stock TET';

        return $note;
    }
}