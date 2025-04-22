<?php
/**
* Xử lý kiểu Enum
*/
class My_Type_Enum
{
    const __default = self::Unknown;
    const Unknown = 0;

    function __construct()
    {
        
    }

    /**
     * @param int $x - giá trị hằng số
     * @return string - tên hằng số tương ứng
     */
    public static function get($x = 0)
    {
        $fooClass = new ReflectionClass( get_called_class() );
        $constants = $fooClass->getConstants();

        $constName = null;
        foreach ( $constants as $name => $value )
        {
            if ( $value == $x )
            {
                $constName = $name;
                break;
            }
        }

        return $constName;
    }
}