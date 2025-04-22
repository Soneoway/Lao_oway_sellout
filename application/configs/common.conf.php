<?php
define('HOST', 'https://sellout.oway-la.com/');
define('TRADE_URI' , '');
define('API_URI', '');
define('WAREHOUSE_DB', 'warehouse');
define('HR_DB', 'hr');
define('HROPPO_DB', 'hr');
define('VERSION', "2.2.1");
define('LIMITATION', 10);
define('SUPERADMIN_ID', 644);
define('ADMINISTRATOR_ID', 1);
define('CHECKTIMING_ID', 45);
define('EMPLOYEE_ID', 6);
define('ASM_ID', 5);
define('PGPB_ID', 4);
define('PCDB_ID', 64);
define('HR_ID', 7);
define('BOARD_ID', 8);
define('SALES_ID', 9);
define('SALES_ADMIN_ID', 12);
define('SALES_EXT_ID', 11);
define('HR_EXT_ID', 10);
define('LEADER_ID', 14);
define('ASMSTANDBY_ID', 16);
define('TRAINING_TEAM_ID', 17);
define('TRADE_MARKETING_ID', 20);
define('DIGITAL_ID', 19);
define('TRADE_TEAM', 131);
define('PGPB_TEAM', 16);
define('HR_RECRUITMENT', 26);
define('PCM_ID', 25);
define('AM_ID', 27);
define('RM_ID', 28);
define('BM_ID', 30);
define('RMSTANDBY_ID', 50);
define('ABM_ID', 32);

/* CONFIG by Title */
define('PGPB_TITLE', 182);
define('SALES_TITLE', 183);
define('LEADER_TITLE', 190);
define('SALES_ACCESSORIES_TITLE', 183);
define('SALES_ACCESSORIES_LEADER_TITLE', 162);
define('SALES_ADMIN_TITLE', 191);
define('SALES_LEADER_TITLE', 190);
/* End of CONFIG by Title */

define('SALES_TEAM', 75);
define('SALES_ADMIN_TEAM', 119);
define('TRAINING_TEAM', 133);

define('TRAINING_TEAM_GROUP_ID', 17);
define('TRADE_MARKETING_GROUP_ID', 20);

define('HCMC1', 24);
define('HCMC2', 25);
define('HCMC3', 26);
define('HCMC4', 1);
define('HCMC5', 34);

define('HN1', 10);
define('HN2', 31);
define('HN3', 32);
define('HN4', 33);

define('AG', 28);
define('VINH', 14);

define('TIME_LIMIT_TIMING', 24*3600*100 );
define('TIME_LIMIT_DEL_TIMING', 24*3600*100 );
define('EMAIL_SUFFIX', '@oppo.in.th' );

define('SALES_KPI', 1 ); // id của dòng tương ứng trong bảng KPI
define('LOG_DASHBOARD', 5 );

define('CSKH_ID', 1 );
define('WARRANTY_CENTER', 161 );
define('CALL_CENTER', 160 );
define('SERVICE_CENTER', 159 );
define('LOGISTICS_ID', 3 );
define('TRAINER_TITLE', 'NHÂN VIÊN TRAINER' );

define('EMAIL_HCM_PGPB', 'hcmpromoter@oppomobile.vn' );
define('EMAIL_CSKH', 'care@oppomobile.vn' );
define('EMAIL_ASM', 'asm@oppomobile.vn' );
define('EMAIL_SALESADMIN', 'saleadmin@oppomobile.vn' );
define('EMAIL_HCM_OFFICE', 'hochiminh@oppomobile.vn' );
define('EMAIL_LOGISTICS', 'vnwarehouse@oppomobile.vn' );
define('EMAIL_TRAINER', 'trainer@oppomobile.vn' );
define('FILENAME_SALT', 'Th0ng!@#' );

define('PHONE_CAT_ID', 11);
define('ACCESS_CAT_ID', 12);
define('IOT_CAT_ID', 15);
define('WSS_MK_URI', 'http://center.dev/wss' );
define('WSS_WH_URI', 'http://warehouse.dev/wss' );

define('DONG_KHUONG', 765);
define('PHONG_LAU', 34);
define('TAG_STAFF', 1);
define('TAG_STAFF_TEMP', 2);


define('HAPPY_TIME_CHECKIN', 1);
define('ACCESSORIES_TEAM', 147);
define('ACCESSORIES_ID', 18);
define('SERVICE_ID', 21);

define('PREVENT_TIMING_AT_TGDD', 1);

define('LOCK_TIMING', 0);
define('PASSWORD_EXPIRE_TIME', 60); // 2 tháng
define('IMEI_ACTIVATION_EXPIRE', 3); // ngày
define('IMEI_ACTIVATION_EXPIRE_KICK_OUT', 7); // ngày

define('STAFF_PRINT_LOG_UPDATE',1);
define('STAFF_PRINT_LOG_INSERT',2);
define('STAFF_PRINT_LOG_PRINT',3);


define('LIMIT_STAFF_WORKING', 20);
define('LABOUR_CONTRACT' , 2);
define('DAY_OFF_BEGIN' , '2015-03-01');

define('CONTRACT_TERM_12_MONTH', 1);
define('CONTRACT_TERM_LABOUR', 2);
define('CONTRACT_TERM_SEASONAL', 3);
define('CONTRACT_TERM_SEASONAL_CHALLENGE', 4);
define('CONTRACT_TERM_NOT_YET', 5);
define('CONTRACT_TERM_32_MONTH', 6);
define('CONTRACT_TERM_UNLIMITED', 7);


/* Check SAlE admin duoc edit ho so khu vuc cua minh thoi */
define('CHECK_USER_EDIT_AREA',  true);
/* Check SAlE admin duoc edit ho so khu vuc cua minh thoi */

/* trainer evaluation */
define('TRAINER_EVALUATION_GOOD',1);
define('TRAINER_EVALUATION_AVERAGE',2);
define('TRAINER_EVALUATION_BELOW_AVERAGE',3);

define('TRAINER_EVALUATION_GOOD_NAME','GOOD');
define('TRAINER_EVALUATION_AVERAGE_NAME','AVERAGE');
define('TRAINER_EVALUATION_BELOW_AVERAGE_NAME','BELOW AVERAGE');

define ("TRAINER_EVALUATION", serialize (array (
    TRAINER_EVALUATION_GOOD                      => TRAINER_EVALUATION_GOOD_NAME,
    TRAINER_EVALUATION_AVERAGE                   => TRAINER_EVALUATION_AVERAGE_NAME,
    TRAINER_EVALUATION_BELOW_AVERAGE             => TRAINER_EVALUATION_BELOW_AVERAGE_NAME
)));

/*Type Reward and warning PG*/
define('TYPE_REWARD_PG',1);
define('TYPE_WARNING_PG',2);
define('TYPE_REWARD_PG_NAME','REWARD');
define('TYPE_WARNING_PG_NAME','WARNING');

define ("TYPE_PG", serialize (array (
    TYPE_REWARD_PG         => TYPE_REWARD_PG_NAME,
    TYPE_WARNING_PG        => TYPE_WARNING_PG_NAME
)));
define('EXPORT_ID', 15);

/*Config team Is Head Office*/
define('DEPARTMENT_SALE',               152); // department
define('DEPARTMENT_WARRANTY_CENTER',    161); // department

define('TEAM_SALE_SALE',                75); // team
define('SALE_SALE_ASM',                 179); // title
define('SALE_SALE_ASM_STANDBY',         181); // title
define('SALE_SALE_PGPB',                182); // title
define('SALE_SALE_SALE',                183); // title
define('SALE_SALE_SALE_LEADER',         190); // title
define('SALE_SALE_SALE_ADMIN',          191); // title
define('SALE_SALE_SALE_TRAINEE',        274); // title
define('SALE_SALE_DELIVERY',            277); // title
define('TEAM_SALE_ACCESSORIES',         147); // team
define('SALE_ACCESSORIES_SALE_LEADER',  162); // title
define('SALE_ACCESSORIES_SALE',         164); // title
define('SALE_ACCESSORIES_SALE_ADMIN',   163); // title

define('TEAM_SALE_DIGITAL',             148); //team
define('SALE_DIGITAL_SALE_LEADER',      165); // title
define('SALE_DIGITAL_SALE',             166); // title
define('SALE_DIGITAL_SALE_ADMIN',       167); // title

define ("CONFIG_NOT_HEAD_OFFICE", serialize (array (
    DEPARTMENT_WARRANTY_CENTER => DEPARTMENT_WARRANTY_CENTER,
    DEPARTMENT_SALE => array(
        TEAM_SALE_SALE => array(
            SALE_SALE_ASM => SALE_SALE_ASM,
            SALE_SALE_ASM_STANDBY => SALE_SALE_ASM_STANDBY,
            SALE_SALE_PGPB => SALE_SALE_PGPB,
            SALE_SALE_SALE => SALE_SALE_SALE,
            SALE_SALE_SALE_LEADER => SALE_SALE_SALE_LEADER,
            SALE_SALE_DELIVERY => SALE_SALE_DELIVERY,
        ),
        TEAM_SALE_ACCESSORIES => array(
            SALE_ACCESSORIES_SALE_LEADER => SALE_ACCESSORIES_SALE_LEADER,
            SALE_ACCESSORIES_SALE => SALE_ACCESSORIES_SALE,
        ),
        TEAM_SALE_DIGITAL => array(
            SALE_DIGITAL_SALE_LEADER => SALE_DIGITAL_SALE_LEADER,
            SALE_DIGITAL_SALE => SALE_DIGITAL_SALE,
        ),
    ),
)));
/*End of Config team Is Head Office*/

define('PGPB_NAME','PGPB');
define('SALE_SALE_SALE_NAME','SALE');

define ("TRAINER_TITLE_SEARCH", serialize (array (
    PGPB_TITLE             => PGPB_NAME,
    SALE_SALE_SALE         => SALE_SALE_SALE_NAME
)));

define("TITLE_TRAINER_LEADER",174);
define("TITLE_TRAINING_MANAGER",176);
define("NOTIFICATION_PRIMARY_TYPE" , 1);

define('PERMIT',1);
define('SICK',2);
define('CHILDBEARING', 3);
define('PERMIT_NAME','NGHỈ PHÉP');
define('SICK_NAME','TẠM NGHỈ');
define('CHILDBEARING_NAME', 'THAI SẢN');
define('CHILDBEARING_KL',4);
define('CHILDBEARING_KL_NAME','NGHỈ THAI SẢN KHÔNG LƯƠNG');
define('OFF_KL',5);
define('OFF_KL_NAME','NGHỈ KHÔNG LƯƠNG');
define('TEMPORARY_OFF',2);
define('OFF_TYPE',serialize(array(
    PERMIT          => PERMIT_NAME,
    SICK            => SICK_NAME,
    CHILDBEARING    => CHILDBEARING_NAME,
    CHILDBEARING_KL => CHILDBEARING_KL_NAME,
    OFF_KL          => OFF_KL_NAME
)));
/*STATUS TYPE OF STAFF*/
define('STATUS_STAFF_ON' , 1);
define('STATUS_STAFF_OFF' , 0);
define('STATUS_STAFF_TEMPORARYOFF' , 2);
define('STATUS_STAFF_CHILDBEARING' , 3);
/*END STATUS*/
define('HAPPY_TIME',serialize(array(
    'from'      => '2015-04-01 00:00:00',
    'to'        => '2015-04-30 23:59:59',
    'editFrom'  => '2015-05-01 00:00:00',
    'editTo'    => '2015-05-04 23:59:59',
)));


/*TYPE TRAINING REPORT*/
define('TRAINING_REPORT_INTERNALLY',    1);
define('TRAINING_REPORT_PARTNERS',      2);
define('TRAINING_REPORT_INTERNALLY_NAME',    'INTERNAL');
define('TRAINING_REPORT_PARTNERS_NAME',      'PARTNER');

define ("TYPE_TRAINING_REPORT", serialize (array (
    TRAINING_REPORT_INTERNALLY       => TRAINING_REPORT_INTERNALLY_NAME,
    TRAINING_REPORT_PARTNERS         => TRAINING_REPORT_PARTNERS_NAME
)));
/*END TYPE TRAINING REPORT*/

/*TYPE LOYALTY_PLAN*/
define('LOYALTY_PLAN_RULE_TYPE_SELL_IN',            1);
define('LOYALTY_PLAN_RULE_TYPE_SELL_OUT',           2);
define('LOYALTY_PLAN_RULE_TYPE_SELL_IN_NAME',       'SELL IN');
define('LOYALTY_PLAN_RULE_TYPE_SELL_OUT_NAME',      'SELL OUT');

define ("LOYALTY_PLAN_RULE_TYPE", serialize (array (
    LOYALTY_PLAN_RULE_TYPE_SELL_IN                  => LOYALTY_PLAN_RULE_TYPE_SELL_IN_NAME,
    LOYALTY_PLAN_RULE_TYPE_SELL_OUT                 => LOYALTY_PLAN_RULE_TYPE_SELL_OUT_NAME,
)));
/*END TYPE LOYALTY_PLAN*/

/*STATUS ORDER TRAINING*/
define('ORDER_STATUS_PENDING_TRAINING',        1);
define('ORDER_STATUS_COMPLETED_TRAINING',      2);
define('ORDER_STATUS_PENDING_TRAINING_NAME',   'PENDING');
define('ORDER_STATUS_COMPLETED_TRAINING_NAME', 'COMPLETED');

define ("ORDER_STATUS_TRAINING", serialize (array (
    ORDER_STATUS_PENDING_TRAINING       => ORDER_STATUS_PENDING_TRAINING_NAME,
    ORDER_STATUS_COMPLETED_TRAINING     => ORDER_STATUS_COMPLETED_TRAINING_NAME
)));
/*END STATUS ORDER TRAINING*/

define('TITLE_TRAINING_ASSISTANT', 178);

/*RIGHTS TRAINER FULL*/
define ("FULL_RIGHTS_TRAINER", serialize (array (
    TITLE_TRAINING_ASSISTANT,
    TITLE_TRAINING_MANAGER
)));
/*END RIGHTS TRAINER FULL*/

define('COMPANY_OPPO' , 1);
define('COMPANY_DIDONGTHONGMINH' , 2);

define('TIME_CHECKIN_LIMITED'  , '08:15:00');
define('TIME_CHECKOUT_LIMITED' , '17:30:00');
define('TIME_NOT_CHECKIN'      , '00:00:00');
define('TITLE_CHECK_IN_FOR_PG_TRAINING', serialize (array(PGPB_TITLE)));
define('SHIFT_CHECK_IN_FOR_PG_TRAINING', 1);
//SIGN TIMING
define('SIGN_TIME_CHECKIN_SUNDAY_FULLDAY' , 'C');
define('SIGN_TIME_CHECKIN_SUNDAY_HALFDAY' , 'D');
define('SIGN_TIME_CHECKIN_WEEKDAY_HALFDAY' , 'H');
define('SIGN_TIME_CHECKIN_WEEKDAY_FULLDAY' , 'X');
define('SIGN_TIME_CHECKIN_SPECIAL_DAY' ,     'L');
define('SIGN_TIME_CHECKIN_OFF_DAY' ,     'P');

define('COMMISSION_RATE', 30);