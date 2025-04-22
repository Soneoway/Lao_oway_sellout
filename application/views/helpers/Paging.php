<?php
/**
 * Created by PhpStorm.
 * User: thong
 * Date: 10/29/13
 * Time: 3:37 PM
 */
class Zend_View_Helper_Paging extends Zend_View_Helper_Abstract
{
    public function paging($intTotal, $intLimit, $intOffset, $strUrl = '', $symbol=1, $intDisplayPage = 10, $isAjax=false, $strContainer='')
    {
        $paging = new Paging($intTotal, $intLimit, $intOffset, $strUrl, $symbol, $intDisplayPage);
        if ($isAjax)
            return $paging->getAjax($strContainer);
        else
            return $paging->get();
    }
}

class Paging
{
    var $page 	= 0;
    var $limit 	= 0;
    var $total 	= 0;
    var $symbol = 1;/* 1: &; 2: / */
    var $url 	= '';

    public function __construct($intTotal, $intLimit, $intOffset, $strUrl = '', $symbol=1, $intDisplayPage = 10)
    {
        $this->total 	= $intTotal;
        $this->page  	= ($intOffset / $intLimit) + 1;
        $this->limit	= $intLimit;
        $this->offset 	= $intOffset;
        $this->url      = $strUrl;
        $this->symbol	= $symbol;
        $this->intDisplayPage	= $intDisplayPage;
    }

    public function get()
    {
        $strNav = '';
        $intPageTotal 	= ceil($this->total / $this->limit);
        $intDelta		= ceil($this->intDisplayPage / 2);
        if ($intPageTotal > $this->intDisplayPage) {
            if ($this->page <= $intDelta) {
                $intStartLoop 	= 1;
                $intEndLoop		= $this->intDisplayPage;
            } elseif ($this->page >= $intPageTotal - $intDelta) {
                $intStartLoop 	= $intPageTotal - $this->intDisplayPage + 1;
                $intEndLoop		= $intPageTotal;
            } else {
                $intStartLoop 	= $this->page - $intDelta + 1;
                $intEndLoop		= $this->page + $intDelta;
            }
        } else {
            $intStartLoop = 1;
            $intEndLoop = $intPageTotal;
        }

        $html = '';

        $slash = '=';

        if ($this->symbol==2) $slash = '/';

        if ($intEndLoop > 1) {
            for ($i = $intStartLoop; $i <= $intEndLoop; $i++) {

                if ($i == $this->page) {
                    $strNav .= "<li class=\"disable active\"><a class=\"paging-link\" href='" . $this->url . "page{$slash}$i'>$i</a></li>";
                } else {
                    $strNav .= "<li><a class=\"paging-link\" href='" . $this->url . "page{$slash}$i'>$i</a></li>";
                }
            }
            if ($this->page > 1) {
                $strFirst = "<li><a class=\"paging-link\" href='" . $this->url . "page{$slash}1'>&laquo;</a></li>";
                $strPrev  = "<li><a class=\"paging-link\" href='" . $this->url . "page{$slash}" . ($this->page - 1) . "'>&lsaquo;</a></li>";
            } else {
                $strFirst = '';
                $strPrev  = '';
            }
            if ($this->page < $intPageTotal) {
                $strLast = "<li><a class=\"paging-link\" href='" . $this->url . "page{$slash}" . $intPageTotal . "'>&raquo;</a></li>";
                $strNext = "<li><a class=\"paging-link\" href='" . $this->url . "page{$slash}" . ($this->page + 1) . "'>&rsaquo;</a></li>";
            } else {
                $strLast = '';
                $strNext = '';
            }
            $html = "<div class=\"pagination\"><strong class='count'>{$this->total}</strong><ul>$strFirst $strPrev $strNav $strNext $strLast</ul></div>";
        }
        return $html;
    }

    public function getAjax($strContainer = '')
    {
        $strNav = '';
        $intPageTotal 	= ceil($this->total / $this->limit);
        $intDelta		= ceil($this->intDisplayPage / 2);
        if ($intPageTotal > $this->intDisplayPage) {
            if ($this->page <= $intDelta) {
                $intStartLoop 	= 1;
                $intEndLoop		= $this->intDisplayPage;
            } elseif ($this->page >= $intPageTotal - $intDelta) {
                $intStartLoop 	= $intPageTotal - $this->intDisplayPage + 1;
                $intEndLoop		= $intPageTotal;
            } else {
                $intStartLoop 	= $this->page - $intDelta + 1;
                $intEndLoop		= $this->page + $intDelta;
            }
        } else {
            $intStartLoop = 1;
            $intEndLoop = $intPageTotal;
        }
        $html = '';

        $slash = '=';

        if ($this->symbol==2) $slash = '/';

        if ($intEndLoop > 1) {
            for ($i = $intStartLoop; $i <= $intEndLoop; $i++) {
                if ($i == $this->page) {
                    $strNav .= "<li class=\"disable active\"><a href=\"javascript:void(0);\">$i</a></li>";
                } else {
                    $strNav .= "<li><a href=\"javascript:void(0);\" onclick=\"paging('{$this->url}page{$slash}{$i}', '{$strContainer}')\">$i</a></li>";
                }
            }
            if ($this->page > 1) {
                $strFirst = "<li><a href=\"javascript:void(0);\" onclick=\"paging('{$this->url}page{$slash}{$intPageTotal}', '{$strContainer}');\">&laquo;</a></li>";
                $strPrev  = "<li><a href=\"javascript:void(0);\" onclick=\"paging('{$this->url}page{$slash}".($this->page - 1)."', '{$strContainer}');\">&lsaquo;</a></li>";
            } else {
                $strFirst = '';
                $strPrev  = '';
            }
            if ($this->page < $intPageTotal) {
                $strLast = "<li><a href=\"javascript:void(0);\" onclick=\"paging('{$this->url}page{$slash}{$intPageTotal}', '{$strContainer}');\">&raquo;</a></li>";
                $strNext  = "<li><a href=\"javascript:void(0);\" onclick=\"paging('{$this->url}page{$slash}".($this->page + 1)."', '{$strContainer}');\">&rsaquo;</a></li>";
            } else {
                $strLast = '';
                $strNext = '';
            }
            $html = "<div class=\"pagination\"><ul>$strFirst $strPrev $strNav $strNext $strLast</ul></div>";
        }
        return $html;
    }
}