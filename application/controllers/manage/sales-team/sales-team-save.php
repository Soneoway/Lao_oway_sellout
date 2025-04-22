<?php
if ($this->getRequest()->getMethod() == 'POST'){

    $QSalesTeam = new Application_Model_SalesTeam();

    $QSalesTeamStaff = new Application_Model_SalesTeamStaff();

    $QSalesTeamStaffLog = new Application_Model_SalesTeamStaffLog();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $area_id = $this->getRequest()->getParam('area_id');
    $regional_market = $this->getRequest()->getParam('regional_market');
    $pic = $this->getRequest()->getParam('pic');
    $pic1 = $this->getRequest()->getParam('pic1');

    $data = array(
        'name' => $name,
        'area_id' => $area_id,
        'regional_market' => $regional_market,
    );

    $list_leader = $pic1 ? explode(',', $pic1) : array();

    $list_leader = array_filter($list_leader);

    $list_member = $pic1 ? explode(',', $pic) : array();

    $list_member = array_filter($list_member);

    //check leader
    if ($list_leader) {

        if (count($list_leader) > 1){
            $error_msg = 'Leader Number is only one!';
            echo '<script>
                                parent.palert("'.$error_msg.'");
                                parent.alert("'.$error_msg.'");
                            </script>';
            exit;
        } elseif (count($list_leader) == 0) {
            $error_msg = 'Leader cannot blank!';
            echo '<script>
                                parent.palert("'.$error_msg.'");
                                parent.alert("'.$error_msg.'");
                            </script>';
            exit;
        }

        $QStaff = new Application_Model_Staff();
        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('id IN (?)', $list_leader);
        $where[] = $QStaff->getAdapter()->quoteInto('group_id  <> ? ', SALES_ID);

        $not_leaders = $QStaff->fetchAll($where);

        if ($not_leaders->count()){
            $error_msg = $error_msg2 = '';
            foreach ($not_leaders as $k=>$item){
                $error_msg .= ( $k > 0 ? "\n" : '' ). $item->firstname .' '. $item->lastname . ' | ' . $item->email . ' is not Sales' ;
                $error_msg2 .= ( $k > 0 ? "<br>" : '' ). $item->firstname .' '. $item->lastname . ' | ' . $item->email . ' is not Sales' ;
            }

            echo "<script>parent.palert(\"$error_msg2\")</script>";


            $patterns = array("/\\\\/", '/\n/', '/\r/', '/\t/', '/\v/', '/\f/');
            $replacements = array('\\\\\\', '\n', '\r', '\t', '\v', '\f');
            $error_msg = preg_replace($patterns, $replacements, $error_msg);


            echo "<script>alert(\"$error_msg\")</script>";
            exit;
        }
    } else {
        $error_msg = 'Leader cannot blank!';
        echo '<script>
                            parent.palert("'.$error_msg.'");
                            parent.alert("'.$error_msg.'");
                        </script>';
        exit;
    }

    if ($list_member){

        $QStaff = new Application_Model_Staff();

        //check PG is existed in another sales team
        $QSalesTeamStaff = new Application_Model_SalesTeamStaff();
        $where = array();
        $where[] = $QSalesTeamStaff->getAdapter()->quoteInto('staff_id IN (?)', $list_member);

        if ($id)
            $where[] = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id <> ?', $id);

        $existed = $QSalesTeamStaff->fetchAll($where);


        if ($existed->count()){

            $error_msg = $error_msg2 = '';

            foreach ($existed as $k=>$item){
                $where = $QStaff->getAdapter()->quoteInto('id = ?', $item->staff_id);

                $staff = $QStaff->fetchRow($where);


                $where = $QSalesTeam->getAdapter()->quoteInto('id = ?', $item->sales_team_id);

                $sales_team = $QSalesTeam->fetchRow($where);

                $error_msg .= ( $k > 0 ? "\n" : '' ). $staff['firstname'] .' '. $staff['lastname'] . ' | ' . $staff['email'] . ' is existed in Team: ' . $sales_team['name'] ;
                $error_msg2 .= ( $k > 0 ? "<br>" : '' ). $staff['firstname'] .' '. $staff['lastname'] . ' | ' . $staff['email'] . ' is existed in Team: ' . $sales_team['name'] ;
            }

            echo "<script>parent.palert(\"$error_msg2\")</script>";


            $patterns = array("/\\\\/", '/\n/', '/\r/', '/\t/', '/\v/', '/\f/');
            $replacements = array('\\\\\\', '\n', '\r', '\t', '\v', '\f');
            $error_msg = preg_replace($patterns, $replacements, $error_msg);


            echo "<script>alert(\"$error_msg\")</script>";
            exit;
        }



        $where = array();
        $where[] = $QStaff->getAdapter()->quoteInto('id IN (?)', $list_member);
        $where[] = $QStaff->getAdapter()->quoteInto('group_id  <> ? ', PGPB_ID);

        $not_pgs = $QStaff->fetchAll($where);

        if ($not_pgs->count()){
            $error_msg = $error_msg2 = '';
            foreach ($not_pgs as $k=>$item){
                $error_msg .= ( $k > 0 ? "\n" : '' ). $item->firstname .' '. $item->lastname . ' | ' . $item->email . ' is not PG/PB' ;
                $error_msg2 .= ( $k > 0 ? "<br>" : '' ). $item->firstname .' '. $item->lastname . ' | ' . $item->email . ' is not PG/PB' ;
            }

            echo "<script>parent.palert(\"$error_msg2\")</script>";


            $patterns = array("/\\\\/", '/\n/', '/\r/', '/\t/', '/\v/', '/\f/');
            $replacements = array('\\\\\\', '\n', '\r', '\t', '\v', '\f');
            $error_msg = preg_replace($patterns, $replacements, $error_msg);


            echo "<script>alert(\"$error_msg\")</script>";
            exit;
        }
    }

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    if ($id){
        $where = $QSalesTeam->getAdapter()->quoteInto('id = ?', $id);

        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $userStorage->id;

        $QSalesTeam->update($data, $where);


        $where = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id = ?', $id);

        $old = $QSalesTeamStaff->fetchAll($where);

        $old_ids = $old_member_ids = $old_leader_ids = array();

        if ($old->count())
            foreach ($old as $item){
                $old_ids[] = $item->staff_id;
                if ($item->is_leader==1)
                    $old_leader_ids[] = $item->staff_id;
                else
                    $old_member_ids[] = $item->staff_id;
            }


        $joined_leader = array_diff($list_leader, $old_leader_ids);

        $joined_leader = is_array($joined_leader) ? array_filter($joined_leader) : null;

        $released_leader = array_diff($old_leader_ids, $list_leader);

        $released_leader = is_array($released_leader) ? array_filter($released_leader) : null;

        $joined_member = array_diff($list_member, $old_member_ids);

        $joined_member = is_array($joined_member) ? array_filter($joined_member) : null;

        $released_member = array_diff($old_member_ids, $list_member);

        $released_member = is_array($released_member) ? array_filter($released_member) : null;


        if ($released_leader){

            foreach ( $released_leader as $staff_id ){

                $where = array();
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('is_leader = ?', 1);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('sales_team_id = ?', $id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => date('Y-m-d H:i:s'),
                );

                $QSalesTeamStaffLog->update($data, $where);

                $where = array();
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('is_leader = ?', 1);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('sales_team_id = ?', $id);

                $QSalesTeamStaff->delete($where);
            }
        }

        if ($joined_leader){

            foreach ( $joined_leader as $staff_id ){

                $data = array(
                    'staff_id' =>$staff_id,
                    'is_leader' =>1,
                    'sales_team_id' =>$id,
                    'joined_at' => date('Y-m-d H:i:s'),
                );

                $QSalesTeamStaffLog->insert($data);

                unset($data['joined_at']);
                $QSalesTeamStaff->insert($data);
            }

        }

        if ($released_member){

            foreach ( $released_member as $staff_id ){

                $where = array();
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('is_leader = ?', 0);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('sales_team_id = ?', $id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('released_at IS NULL', 1);

                $data = array(
                    'released_at' => date('Y-m-d H:i:s'),
                );

                $QSalesTeamStaffLog->update($data, $where);

                $where = array();
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('is_leader = ?', 0);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
                $where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('sales_team_id = ?', $id);

                $QSalesTeamStaff->delete($where);
            }
        }

        if ($joined_member){

            foreach ( $joined_member as $staff_id ){

                $data = array(
                    'staff_id' =>$staff_id,
                    'is_leader' =>0,
                    'sales_team_id' =>$id,
                    'joined_at' => date('Y-m-d H:i:s'),
                );

                $QSalesTeamStaffLog->insert($data);

                unset($data['joined_at']);
                $QSalesTeamStaff->insert($data);
            }

        }

        $info = "SALES TEAM - Update (".$id.") - Data (".serialize($data).")";

    } else {

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userStorage->id;

        $id = $QSalesTeam->insert($data);

        //add member
        if ($list_member) {
            if (is_array($list_member)){
                foreach($list_member as $staff_id){
                    $QSalesTeamStaff->insert(array(
                        'sales_team_id' => $id,
                        'staff_id' => $staff_id,
                    ));

                    $data = array(
                        'staff_id' =>$staff_id,
                        'is_leader' =>0,
                        'sales_team_id' =>$id,
                        'joined_at' => date('Y-m-d H:i:s'),
                    );

                    $QSalesTeamStaffLog->insert($data);
                }
            }

        }

        //add leader
        if ($list_leader) {
            if (is_array($list_leader)){
                foreach($list_leader as $staff_id){
                    $QSalesTeamStaff->insert(array(
                        'sales_team_id' => $id,
                        'staff_id' => $staff_id,
                        'is_leader' => 1,
                    ));

                    $data = array(
                        'staff_id' =>$staff_id,
                        'is_leader' =>1,
                        'sales_team_id' =>$id,
                        'joined_at' => date('Y-m-d H:i:s'),
                    );

                    $QSalesTeamStaffLog->insert($data);
                }

            }
        }

        $info = "SALES TEAM - Add (".$id.") - Data (".serialize($data).")";
    }


    $QLog = new Application_Model_Log();

    $ip = $this->getRequest()->getServer('REMOTE_ADDR');
    //todo log
    $info .= " - New Members (".$pic.")"." - New Leaders (".$pic1.")";
    $QLog->insert( array (
        'info' => $info,
        'user_id' => $userStorage->id,
        'ip_address' => $ip,
        'time' => date('Y-m-d H:i:s'),
    ) );

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('sales_team_cache');

    $cache->remove('sales_team_staff_sale_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'.( $back_url ? $back_url : HOST.'manage/sales-team' ).'"</script>';

exit;