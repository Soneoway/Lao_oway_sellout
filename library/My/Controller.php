<?php
/**
*   
*/
class My_Controller
{
    
    public static function palert($msg = '')
    {
        exit('<script>parent.palert("'.$msg.'");</script>');
    }

    public static function redirect($url = null)
    {
        if (!$url) exit;

        exit('<script>parent.window.location="'.$url.'"</script>');
    }
}