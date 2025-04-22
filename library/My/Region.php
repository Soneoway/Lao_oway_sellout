<?php
/**
* @author buu.pham
*/
class My_Region extends My_Type_Enum
{
    const Region   = 1;
    const Area     = 2;
    const Province = 3;
    const District = 4;

    // types
    const Name = 0;
    const ID = 1;

    /**
     * Lấy tên quận/huyện, tỉnh/thành, khu vực từ ID district được truyền vào
     *
     * @author buu.pham
     * @param  int $district District ID
     * @param  int $level District or Province or Area as defined constants
     * @param  int type - 0=name, 1=id
     * @return string district/province/area name
     */
    public static function getValue($district_id, $level, $type = self::Name)
    {
        if ($level <= self::District) {
            $QRegion = new Application_Model_RegionalMarket();
            $districts = $QRegion->get_district_cache();

            if (isset($districts[ $district_id ]))
                $district_name = $districts[ $district_id ]['name'];
            else
                return false;

            if ($level == self::District) {
                if ($type == self::ID) return $district_id;
                return $district_name;
            }

            $province_id = $districts[ $district_id ]['parent'];
        }

        if ($level <= self::Province) {
            $provinces = $QRegion->get_cache_all();

            if (isset($provinces[ $province_id ]))
                $province_name = $provinces[ $province_id ]['name'];
            else
                return false;

            if ($level == self::Province) {
                if ($type == self::ID) return $province_id;
                else return $province_name;
            }

            $area_id = $provinces[ $province_id ]['area_id'];
        }

        if ($level == self::Area) {
            $QArea = new Application_Model_Area();
            $areas = $QArea->get_cache();

            if (isset($areas[ $area_id ]))
                $area_name = $areas[ $area_id ];
            else
                return false;

            if ($level == self::Area) {
                if ($type == self::ID) return $area_id;
                else return $area_name;
            }
        }
    }
}