<?php
/**
* @author buu.pham
*/
class My_Staff_Log
{
    public static function getValue($input = 0, $type = null)
    {
        $output = '';

        if ($input && $type) {

            switch ($type) {
                case My_Staff_Info_Type::Area:
                    $QModel = new Application_Model_Area();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Region:
                    $QModel = new Application_Model_RegionalMarket();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Group:
                    $QModel = new Application_Model_Group();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Team:
                    $QModel = new Application_Model_Team();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Department:
                    $QModel = new Application_Model_Department();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Company:
                    $QModel = new Application_Model_Company();
                    $cache = $QModel->get_cache();
                    if ( isset( $cache[ $input ] ) ) $output = $cache[ $input ];
                    break;
                case My_Staff_Info_Type::Status:
                    if ( My_Staff_Status::get( $input ) ) $output = My_Staff_Status::get( $input );
                    break;

                default:
                    break;
            } // END switch
        } // END big if


        return $output;
    }

    /**
     * Log bình thường, mỗi khi edit staff
     * @param  [type] $staff_id [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public static function write($staff_id, array $data)
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (!$userStorage)
            throw new Exception("Invalid user. Must login to use this action.");

        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];

        if (empty($ip))
            throw new Exception("Invalid IP.");

        $before = isset( $data['before'] ) ? $data['before'] : array();
        $after = isset( $data['after'] ) ? $data['after'] : array();

        if ($after || !count($after))
            throw new Exception("New data may be wrong, cannot blank, please check again.");

        $QLog = new Application_Model_StaffLog();

        $log_id = $QLog->insert( array(
            'object'     => $staff_id,
            'before'     => serialize($before),
            'after'      => serialize($after),
            'user_id'    => $userStorage->id,
            'ip_address' => $ip,
            'time'       => date('Y-m-d H:i:s'),
        ) );

        return $log_id;
    }

    /**
     * Transfer of work - lưu theo trường
     * @param  [type] $staff_id [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */

    public static function transfer($staff_id, $transfer_data, $option = 'add')
    {
        $userStorage     = Zend_Auth::getInstance()->getStorage()->read();
        $db              = Zend_Registry::get('db');
        $QStaff          = new Application_Model_Staff();
        $QStaffLogDetail = new Application_Model_StaffLogDetail();
        $QStaffTransfer  = new Application_Model_StaffTransfer();

        if (!$userStorage){
            throw new Exception("Invalid user. Must login to use this action.");
        }

        if (isset($transfer_data['from_date']) && strtotime($transfer_data['from_date'])){
            $from_date = $transfer_data['from_date'];
        }
        else{
            throw new Exception("Invalid date.");
        }

        // Data for new transfer
        $data = array(
              'staff_id'   => $staff_id,
              'created_at' => date('Y-m-d H:i:s'),
              'created_by' => $userStorage->id,
              'from_date'  => date('Y-m-d',strtotime($from_date)),
        );
        $time       = strtotime($from_date);
        $object     = $staff_id;
        $info       = $transfer_data['info'];//info_type => value
        $db->beginTransaction();
        try{
            if($option == 'update'){

                // update to_date time for old transfer
                $selectOldTransfer = $QStaffTransfer->select()
                    ->where('staff_id = ?',$staff_id)
                    ->where('to_date IS NOT NULL')
                    ->order('created_at DESC')
                    ->limit(1);
                ;
                $rowOld = $QStaffTransfer->fetchRow($selectOldTransfer);
                if($rowOld){
                    $rowOld->to_date = $from_date;
                    $rowOld->save();
                }

                //update current transfer
                $whereUpdateTransfer  = $QStaffTransfer->getAdapter()->quoteInto('id = ?',$transfer_data['transfer_id']);
                $data                 = array('from_date'=> date('Y-m-d',strtotime($from_date)));
                $QStaffTransfer->update($data,$whereUpdateTransfer);
                $transfer_id = $transfer_data['transfer_id'];

                // tạm thời không check duplicate trong trường hợp update
                foreach($info as $key => $value){
                    $where = $QStaffLogDetail->select()
                        ->where('object = ?',$object)
                        ->where('info_type = ?',$key)
                        ->where('transfer_id = ?',$transfer_id);
                    $row = $QStaffLogDetail->fetchRow($where);
                    $row->current_value = ($key == My_Staff_Info_Type::Title) ? trim($value) : intval($value);
                    $row->save();
                }

                //Update staff table
                if($row->to_date == NULL){//nếu là transfer mới nhất
                    $dataStaff = array(
                        'company_id'      => $info[My_Staff_Info_Type::Company],
                        'department'      => $info[My_Staff_Info_Type::Department],
                        'team'            => $info[My_Staff_Info_Type::Team],
                        'regional_market' => $info[My_Staff_Info_Type::Region],
                        'title'           => $info[My_Staff_Info_Type::Title],
                        'group_id'        => $info[My_Staff_Info_Type::Group],
                        'updated_at'      => date('Y-m-d H:i:s'),
                        'updated_by'      => $userStorage->id,
                    );

                    if ($info[My_Staff_Info_Type::Title] == PGPB_TITLE)
                        $dataStaff['has_email'] = 0;

                    self::updateStaffTable($dataStaff,$staff_id);
                }
            }else{

                //Kiểm tra lần đầu :D
                $selectNew = $QStaffTransfer->select()
                    ->where('staff_id = ?',$staff_id);
                $new = $QStaffTransfer->fetchAll($selectNew);
                if(!count($new)){
                    $selectCurrentStaffInfo = $db->select()
                            ->from(array('s'=>'staff'),array(
                                's.company_id',
                                's.group_id',
                                's.department',
                                's.team',
                                'region' => 'r.id',
                                'area'   => 'r.area_id',
                                's.title',
                                'joined_at'
                            ))
                            ->join(array('r'=>'regional_market'),'s.regional_market = r.id',array())
                            ->where('s.id = ?',$staff_id)
                    ;
                    $currentStaffInfo = $db->fetchRow($selectCurrentStaffInfo);

                    $basicTransferData = array(
                        'note'       => 'initFirstData',
                        'staff_id'   => $staff_id,
                        'created_at' => $from_date,
                        'created_by' => $userStorage->id,
                        'from_date'  => $currentStaffInfo['joined_at'],
                        'to_date'    => NULL
                    );
                    $basicTransferId = $QStaffTransfer->insert($basicTransferData);

                    $arrBasicInfoType = array(
                        My_Staff_Info_Type::Company    => $currentStaffInfo['company_id'],
                        My_Staff_Info_Type::Department => $currentStaffInfo['department'],
                        My_Staff_Info_Type::Team       => $currentStaffInfo['team'],
                        My_Staff_Info_Type::Group      => $currentStaffInfo['group_id'],
                        My_Staff_Info_Type::Area       => $currentStaffInfo['area'],
                        My_Staff_Info_Type::Region     => $currentStaffInfo['region'],
                        My_Staff_Info_Type::Title      => $currentStaffInfo['title']

                    );

                    foreach($arrBasicInfoType as $key => $value){
                        $arrTmp = array(
                            'object'        => $staff_id,
                            'info_type'     => $key,
                            'current_value' => $value,
                            'transfer_id'   => $basicTransferId,
                            'from_date'     => strtotime($currentStaffInfo['joined_at']),
                            'to_date'       => NULL,
                        );
                        $QStaffLogDetail->insert($arrTmp);
                    }

                }// End check first time

                //update  date for old transfer
                $selectOldTransfer = $QStaffTransfer->select()
                    ->where('staff_id = ?',$staff_id)
                    ->where('to_date IS NULL')
                ;
                $rowOld = $QStaffTransfer->fetchRow($selectOldTransfer);
                if($rowOld){
                    $rowOld->to_date = date('Y-m-d',strtotime($from_date));
                    $rowOld->save();
                }

                // Add new transfer
                $transfer_id = $QStaffTransfer->insert($data);
                $checkDuplicate = true;
                foreach($info as $key => $value){
                    //update old staff log detail
                    $where = $QStaffLogDetail->select()
                        ->where('object = ?',$object)
                        ->where('info_type = ?',$key)
                        ->where('transfer_id = ?',$rowOld->id)
                    ;
                    $row = $QStaffLogDetail->fetchRow($where);//old staff log detail
                    $old_value = null;
                    if($row){//nếu có giá trị cũ
                        $row->to_date = $time;
                        $row->save();
                        $old_value  = $row->current_value;
                    }

                    // data for new row
                    $data = array(
                        'transfer_id'   => $transfer_id,
                        'object'        => $object,
                        'info_type'     => $key,
                        'old_value'     => ($key == My_Staff_Info_Type::Title) ? trim($old_value) : intval($old_value),
                        'current_value' => ($key == My_Staff_Info_Type::Title) ? trim($value) : intval($value),
                        'from_date'     => $time,
                    );

                    if($old_value != $value){
                        $checkDuplicate = false;
                    }

                    if($row){
                        if(!self::_check_transfer_time($object,$key,$time)){
                            exit(
                            json_encode(array('status'=>0,'message'=>'Time transfer is wrong'))
                            );
                        }
                    }

                    $QStaffLogDetail->insert($data);



                    if($key == My_Staff_Info_Type::Title){
                        if(in_array($old_value,array(PGPB_TITLE,SALES_TITLE,LEADER_TITLE))){
                            My_Staff::removeStoreForTransfer($object,$old_value,date('Y-m-d',$time));
                        }
                    }

                }//End foreach

                if($checkDuplicate == true){
                    exit(
                    json_encode(array('status'=>0,'message'=>'New transfer must be different'))
                    );
                }

                //data staff table and log
                $dataStaff = array(
                    'company_id'      => $info[My_Staff_Info_Type::Company],
                    'department'      => $info[My_Staff_Info_Type::Department],
                    'team'            => $info[My_Staff_Info_Type::Team],
                    'regional_market' => $info[My_Staff_Info_Type::Region],
                    'title'           => $info[My_Staff_Info_Type::Title],
                    'group_id'        => $info[My_Staff_Info_Type::Group],
                    'updated_at'      => date('Y-m-d H:i:s'),
                    'updated_by'      => $userStorage->id,
                );

                if ($info[My_Staff_Info_Type::Title] == PGPB_TITLE)
                    $dataStaff['has_email'] = 0;

                self::updateStaffTable($dataStaff,$staff_id);
            }
            $db->commit();

        }catch (Exception $e){
            $db->rollBack();
            exit(
                json_encode(array('status'=>0,'message'=>$e->getMessage()))
            );
            return false;
        }
        return true;
    }

    /**
     * Check xem thời gian transfer có bị chồng lấn hay không
     * @param  [type] $staff_id [description]
     * @param  [type] $type     [description]
     * @param  [type] $from     [description]
     * @return [type]           [description]
     */
    private static function _check_transfer_time($staff_id, $type, $from)
    {
        $QStaffLogDetail = new Application_Model_StaffLogDetail();
        $where = array();
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('object = ?', $staff_id);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('info_type = ?', $type);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('from_date <= ?', $from);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('to_date IS NOT NULL AND to_date > ?', $from);
        $log = $QStaffLogDetail->fetchRow($where);
        if ($log) return false;
        return true;
    }

    private static function updateStaffTable($dataStaff,$staff_id){
        $QStaff = new Application_Model_Staff();

        //get old data
        $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id);
        $s          = $QStaff->fetchRow($whereStaff);
        $QStaff->update($dataStaff,$whereStaff);
        //get new data;
        $s_after    = $QStaff->fetchRow($whereStaff);

        // check and add staff to dashboard
        My_Dashboard_Staff::checkDashboardCondition($s_after);

        Log::w($s->toArray(), $s_after->toArray(), $staff_id, LogGroup::Staff, LogType::Update);
    }

    public static function delete($transfer_id){
        if(!$transfer_id){
            $arrResult = array(
                'code'    => 1,
                'message' => 'Error: Please select transfer to delete'
            );
            return $arrResult;
        }
        $db = Zend_Registry::get('db');
        $QStaffTransfer = new Application_Model_StaffTransfer();
        $QStaffLogDetail = new Application_Model_StaffLogDetail();
        $db->beginTransaction();
        try{
            $row = $QStaffTransfer->find($transfer_id)->current();
            $staff_id = $row->staff_id;
            $row->delete();
            $whereStaffLog  = $QStaffLogDetail->getAdapter()->quoteInto('transfer_id = ?',$transfer_id);
            $QStaffLogDetail->delete($whereStaffLog);

            //update to_date NULL
            $select = $QStaffTransfer->select()
                ->where('staff_id = ?',$staff_id)
                ->order('created_at DESC')
                ->limit(1);
            $row = $QStaffTransfer->fetchRow($select);
            if($row){
                $row->to_date = NULL;
                $row->save();
            }
            $db->commit();
        }catch (Exception $e) {
            $db->rollBack();
            $arrResult = array(
                'code'    => 1,
                'message' => $e->getMessage()
            );
            return $arrResult;

        }
        $arrResult = array(
            'code'    => 2,
            'message' => 'Done'
        );
        return $arrResult;

    }
}