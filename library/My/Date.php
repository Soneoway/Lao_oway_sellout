<?php
/**
* 
*/
class My_Date
{
    
    public static function normal_to_mysql($input)
    {
        $arr = explode('/', $input);

        if (!is_array($arr))
            return null;

        elseif (count($arr) == 2)
            return $arr[1].'-'.$arr[0].'-01';

        elseif (count($arr) == 3)
            return $arr[2].'-'.$arr[1].'-'.$arr[0];

        else
            return null;

    }
}