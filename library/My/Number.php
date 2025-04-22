<?php 
/**
* 
*/
class My_Number
{
    const FIX = 0;

    public static function f($number, $fix = self::FIX)
    {
        if ( ! is_numeric($number) ) return false;

        return number_format($number, $fix, ',', '.');
    }

    public static function floatval($num, $fix = self::FIX) {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos > 0 && $commaPos > 0) ? $dotPos : 
            ((($commaPos > $dotPos) && $dotPos > 0 && $commaPos > 0) ? $commaPos : false);
       
        if (!$sep) {
            return round(floatval(preg_replace("/[^0-9]/", "", $num)), $fix);
        } 

        return round(floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
        ), $fix);
    }

    public function PriceToThai($number){
        $txtnum1 = array('ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า','สิบ');
        $txtnum2 = array('','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน');
        $number = str_replace(",","",$number);
        $number = str_replace(" ","",$number);
        $number = str_replace("บาท","",$number);
        $number = explode(".",$number);
        if(sizeof($number)>2){
            return 'ทศนิยมหลายตัว';
            exit;
        }
        $strlen = strlen($number[0]);
        $convert = '';
        for($i=0;$i<$strlen;$i++){
            $n = substr($number[0], $i,1);
            if($n!=0){
                if($i==($strlen-1) and $n==1){ $convert .= 'เอ็ด'; }
                elseif($i==($strlen-2) and $n==2){ $convert .= 'ยี่'; }
                elseif($i==($strlen-2) and $n==1){ $convert .= ''; }
                else{ $convert .= $txtnum1[$n]; }
                $convert .= $txtnum2[$strlen-$i-1];
            }
        }
        $convert .= 'บาท';
        if($number[1]=='0' OR $number[1]=='00' OR $number[1]==''){
            $convert .= 'ถ้วน';
        }else{
            $strlen = strlen($number[1]);
            for($i=0;$i<$strlen;$i++){
                $n = substr($number[1], $i,1);
                if($n!=0){
                    if($i==($strlen-1) and $n==1){$convert .= 'เอ็ด';}
                    elseif($i==($strlen-2) and $n==2){$convert .= 'ยี่';}
                    elseif($i==($strlen-2) and $n==1){$convert .= '';}
                    else{ $convert .= $txtnum1[$n];}
                    $convert .= $txtnum2[$strlen-$i-1];
                }
            }
            $convert .= 'สตางค์';
        }
        return $convert;
    }
    
}