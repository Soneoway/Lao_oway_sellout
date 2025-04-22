<?php
/**
 * Generate full url
 * example:
 * echo site_url('news.list');
 *
 * output:
 * http://yoursite.com/index.php?act=news.list
 *
 * @param string $url
 * @return string
 */
function site_url($url = '') {
	if (!empty($url)) {
		return _BASE_URL . 'index.php?act=' . $url;
	}
	return _BASE_URL;
}

/**
 * Easy redirect
 * example:
 * redirect('news.list');
 *
 * @param string $url
 */
function redirect($url) {
	$url = site_url($url);
	header("Location: $url");
	die;
}

/* End of functions.php */