<?php

class Application_Model_Notification extends Zend_Db_Table_Abstract
{
    protected $_name = 'notification_new';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');
        $title_notification_pg = "Th�ng b�o v? vi?c chuy?n doanh s? b�n";
        $title_delete_imei = "Danh s�ch IMEI b? x�a ng�y";
        $salary = "Phi?u luong";

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['filter']) && $params['filter']) {
            $select_filter = array(
                'c_area' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = a.id AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::AREA)),
                'c_department' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.department AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::DEPARTMENT)),
                'c_team' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.team AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::TEAM)),
                'c_title' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.title AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::TITLE)),
                // 'c_office'     => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.is_officer AND o.type = %d THEN 1 ELSE 1 END)', My_Notification::OFFICER)),
                'c_staff' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.id AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::STAFF)),
                'c_all' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = 1 AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::ALL_STAFF)),
            );

            $select->joinLeft(array('o' => 'notification_object'), 'o.notification_id=p.id', $select_filter)
                ->joinLeft(array('s' => 'staff'), '1=1', array())
                ->join(array('r' => 'regional_market'), 'r.id=s.regional_market', array())
                ->join(array('a' => 'area'), 'a.id=r.area_id', array())
                ->joinLeft(array('nr' => 'notification_read'), 'nr.staff_id = s.id AND nr.notification_id = p.id', array('read' => 'nr.notification_id'));
        }

        if (isset($params['id']) and $params['id'])
            $select->where('p.id = ?', $params['id']);

        if (isset($params['title']) and $params['title'])
            $select->where('p.title LIKE ?', '%' . $params['title'] . '%');

        if (isset($params['content']) and $params['content'])
            $select->where('p.content LIKE ?', '%' . $params['content'] . '%');

        // filter who can see the notification
        if (isset($params['filter']) && $params['filter'])
            $select->having('(c_area > ? AND c_department > ? AND c_team > ? AND c_title > ?) OR c_staff > ? OR c_all > ?', 0);
        ////////////////////////////

        if (isset($params['read'])) {
            if ($params['read'])
                $select->where('nr.notification_id IS NOT NULL', 1);
            else
                $select->where('nr.notification_id IS NULL', 1);
        }

        if (isset($params['created_from']) && $params['created_from']) {
            $select->where('p.created_at >= ?', $params['created_from']);
        }

        if (isset($params['created_to']) && $params['created_to']) {
            $select->where('p.created_at <= ?', $params['created_to']);
        }

        if (isset($params['filter']) && $params['filter']) {
            $today = date('Y-m-d H:i:s');
            $select->where('p.show_from IS NULL OR p.show_from <= ?', $today);
            $select->where('p.show_to IS NULL OR p.show_to >= ?', $today);
        }

        if (isset($params['staff_id']) && $params['staff_id'])
            $select->where('s.id = ?', $params['staff_id']);

        if (isset($params['filter_display']) && $params['filter_display']) {
            $select->where('p.type =  ?', NOTIFICATION_PRIMARY_TYPE);
        }


        if (isset($params['status']) && $params['status'])
            $select->where('p.status = ?', $params['status']);

        if (isset($params['pop_up']) && $params['pop_up'])
            $select->where('p.pop_up = ?', $params['pop_up']);

        $select->group('p.id');

        if (isset($params['sort']) && $params['sort']) {
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            $collate = ' COLLATE utf8_unicode_ci ';
            $order_str = 'p.`' . $params['sort'] . '` ' . $collate . $desc;

            $select->order(new Zend_Db_Expr($order_str));
        } else {
            $select->order('created_at DESC');
        }

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function getNotificationMobile($params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.title', 'p.content' , 'p.created_at', 'p.updated_at'));

        if (isset($params['filter']) && $params['filter']) {
            $select_filter = array(
                'c_area' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = a.id AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::AREA)),
                'c_department' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.department AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::DEPARTMENT)),
                'c_team' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.team AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::TEAM)),
                'c_title' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.title AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::TITLE)),
                // 'c_office'     => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.is_officer AND o.type = %d THEN 1 ELSE 1 END)', My_Notification::OFFICER)),
                'c_staff' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = s.id AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::STAFF)),
                'c_all' => new Zend_Db_Expr(sprintf('SUM(CASE WHEN o.object_id = 1 AND o.type = %d THEN 1 ELSE 0 END)', My_Notification::ALL_STAFF)),
            );

            $select->joinLeft(array('o' => 'notification_object'), 'o.notification_id=p.id', $select_filter)
                ->joinLeft(array('s' => 'staff'), '1=1', array())
                ->join(array('r' => 'regional_market'), 'r.id=s.regional_market', array())
                ->join(array('a' => 'area'), 'a.id=r.area_id', array())
                ->joinLeft(array('nr' => 'notification_read'), 'nr.staff_id = s.id AND nr.notification_id = p.id', array('read' => 'nr.notification_id'));
        }

        if (isset($params['id']) and $params['id'])
            $select->where('p.id = ?', $params['id']);

        if (isset($params['title']) and $params['title'])
            $select->where('p.title LIKE ?', '%' . $params['title'] . '%');

        if (isset($params['content']) and $params['content'])
            $select->where('p.content LIKE ?', '%' . $params['content'] . '%');

        // filter who can see the notification
        if (isset($params['filter']) && $params['filter'])
            $select->having('(c_area > ? AND c_department > ? AND c_team > ? AND c_title > ?) OR c_staff > ? OR c_all > ?', 0);
        ////////////////////////////

        if (isset($params['read'])) {
            if ($params['read'])
                $select->where('nr.notification_id IS NOT NULL', 1);
            else
                $select->where('nr.notification_id IS NULL', 1);
        }

        if (isset($params['created_from']) && $params['created_from']) {
            $select->where('p.created_at >= ?', $params['created_from']);
        }

        if (isset($params['created_to']) && $params['created_to']) {
            $select->where('p.created_at <= ?', $params['created_to']);
        }

        if (isset($params['filter']) && $params['filter']) {
            $today = date('Y-m-d H:i:s');
            $select->where('p.show_from IS NULL OR p.show_from <= ?', $today);
            $select->where('p.show_to IS NULL OR p.show_to >= ?', $today);
        }

        if (isset($params['staff_id']) && $params['staff_id'])
            $select->where('s.id = ?', $params['staff_id']);

        if (isset($params['staff_code']) && $params['staff_code'])
            $select->where('s.code = ?', $params['staff_code']);

        if (isset($params['filter_display']) && $params['filter_display']) {
            $select->where('p.type =  ?', NOTIFICATION_PRIMARY_TYPE);
        }


        if (isset($params['status']) && $params['status'])
            $select->where('p.status = ?', $params['status']);

        if (isset($params['pop_up']) && $params['pop_up'])
            $select->where('p.pop_up = ?', $params['pop_up']);

        $select->group('p.id');

        if (isset($params['sort']) && $params['sort']) {
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            $collate = ' COLLATE utf8_unicode_ci ';
            $order_str = 'p.`' . $params['sort'] . '` ' . $collate . $desc;

            $select->order(new Zend_Db_Expr($order_str));
        } else {
            $select->order('created_at DESC');
        }

//        echo $select;

        $result = $db->fetchAll($select);
        return $result;
    }
}
