<?php
/**
*
*/
class My_Kpi_Object
{
    const Pg       = 1;
    const Sale     = 2;
    const Leader   = 3;
    const Area     = 4;
    const District = 5;
    const Province = 6;
    const Store    = 7;
    const Dealer   = 8;
    const Product  = 9;

    public static $name = array(
        self::Pg => 'PC',
        self::Sale => 'Sale',
        self::Leader => 'Leader',
    );
}