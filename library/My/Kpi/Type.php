<?php 
/**
* 
*/
class My_Kpi_Type
{
    const SellOut    = 1;
    const Activation = 2; // đếm số có ngày active trong khoảng thời gian
    const Value      = 3; // money
    const Activated  = 4; // Đếm số sell out, xong đếm số đã có ngày active trong mớ đó
    const Not_Activated  = 5; // Đếm số sell out, xong đếm số đã CHƯA CÓ ngày active trong mớ đó
}