<?php
/**
* @author buu.pham
*/
class My_Staff_Group extends My_Type_Enum
{
    const ASM         = 5;
    const ASM_STANDBY = 16;
    const LEADER      = 14;
    const SALES       = 9;
    const PG          = 4;
    const SALES_ADMIN = 12;
    const TRAINING    = 17;
    const TRAINING_LEADER = 36;
    const BOARD       = 8;
    const AM          = 27;
    const RM          = 28;
    const RM_STANDBY  = 50;
    const ABM         = 32;
    const TMS         = 37;
    const TMS_LEADER  = 38;


    public static $allow_in_area_view = array(
        My_Staff_Group::ASM,
        My_Staff_Group::ASM_STANDBY,
        My_Staff_Group::SALES_ADMIN,
        My_Staff_Group::TRAINING,
        My_Staff_Group::RM,
        My_Staff_Group::RM_STANDBY,
        My_Staff_Group::ABM,
        My_Staff_Group::TRAINING_LEADER,
        My_Staff_Group::TMS,
        My_Staff_Group::TMS_LEADER,
    );

    public static $targetName = array(
        self::PG => 'PG',
        self::SALES => 'Sale',
    );
}