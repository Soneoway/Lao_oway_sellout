<?php 
/**
* 
*/
class My_Notification_Type extends My_Type_Enum
{
    const Normal = 1;
    const MassUpload = 2;
    const SystemCreate = 3;

    const MassUpload_Expire = 30;
}