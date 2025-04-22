<?php
class Application_Model_FileUploadLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'file_upload_log';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['real_file_name']) and $params['real_file_name'])
            $select->where('p.real_file_name LIKE ?', '%'.$params['real_file_name'].'%');

        if (isset($params['from']) and $params['from']){
            $date = explode('/', $params['from']);
            $select->where('uploaded_at >= ?', strtotime($date[2].'-'.$date[1].'-'.$date[0]) );
        }

        if (isset($params['to']) and $params['to']){
            $date = explode('/', $params['to']);
            $select->where('uploaded_at <= ?', strtotime($date[2].'-'.$date[1].'-'.$date[0]) );
        }

        $order_str = $collate = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = '';
            
            if (in_array($params['sort'], array('real_file_name')))
                $collate .= ' COLLATE utf8_unicode_ci ';

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            
            $order_str .= $params['sort'] . $collate . $desc;

            $select->order(new Zend_Db_Expr($order_str));
        }

        $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}