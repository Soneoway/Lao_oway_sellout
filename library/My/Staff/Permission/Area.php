<?php
/**
*  
*/
class My_Staff_Permission_Area
{
    
    public static function view_all($staff_id)
    {
        if (is_null($staff_id))
            return false;

        $QAll = new Application_Model_AllArea();
        $all = $QAll->get_cache();

        if (!$all || !is_array($all) || !count($all) || !in_array($staff_id, $all))
            return false;

        return true;
    }
}