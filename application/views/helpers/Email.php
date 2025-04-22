<?php

class Zend_View_Helper_Email extends Zend_View_Helper_Abstract
{
    public function email($full_email)
    {
        return str_replace(EMAIL_SUFFIX, '@', $full_email);
    }
}