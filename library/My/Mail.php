<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @see Zend_Mail
 */
require_once 'Zend/Mail.php';

/**
 * Class for sending an email.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class My_Mail extends Zend_Mail
{

    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE, $preload_images = true)
    {
        if ($preload_images)
        {
            $this->setType(Zend_Mime::MULTIPART_RELATED);

            $dom = new DOMDocument(null, $this->getCharset());
            @$dom->loadHTML($html);

            $images = $dom->getElementsByTagName('img');

            $ats = $reps = array();

            for ($i = 0; $i < $images->length; $i++)
            {
                $img = $images->item($i);
                $url = $img->getAttribute('src');

                $image_http = new Zend_Http_Client($url);
                $response = $image_http->request();

                if ($response->getStatus() == 200)
                {

                    $image_content = $response->getBody();

                    $pathinfo = pathinfo($url);
                    $mime_type = $response->getHeader('Content-Type');

                    $at = $this->createAttachment($image_content);
                    $at->type = $mime_type;
                    $at->disposition = Zend_Mime::DISPOSITION_INLINE;
                    $at->encoding = Zend_Mime::ENCODING_BASE64;
                    $at->filename = $pathinfo['basename'];
                    $at->id = 'cid_' . md5_file($url);

                    $reps[] = $url;
                    $ats[] = 'cid:' . $at->id;

                }
            }
        }

        return parent::setBodyHtml(str_replace( $reps,
                                                $ats,
                                                $html)
            , $charset, $encoding);
    }
}
