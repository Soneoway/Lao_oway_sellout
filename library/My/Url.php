<?php 
/**
* 
*/
class My_Url
{
    public static function refer($default = null)
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] 
            : ( $default ? HOST . str_replace(HOST, '', $default) : HOST );
    }
}