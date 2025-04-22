<?php
/**
* @author buu.pham
*/
class My_Staff_Status_Off extends My_Type_Enum
{
    const Normal_Unfinished = 1;
    const Normal_Finished = 2;
    const Abnormal_Unfinished = 3;
    const Abnormal_Finished = 4;

    public static $name = array(
        1 => 'Nghỉ thường - Chưa hoàn tất hồ sơ',
        2 => 'Nghỉ thường - Đã hoàn tất hồ sơ',
        3 => 'Nghỉ bất thường - Chưa hoàn tất hồ sơ',
        4 => 'Nghỉ bất thường - Đã hoàn tất hồ sơ',
        );
}