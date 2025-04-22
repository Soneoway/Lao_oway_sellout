<?php

class Zend_View_Helper_Sorting {
	function sorting(array $columns, $url = '', $current_col = '', $is_desc = 0) {
		$header = '';

		// kiểm tra đã có dấu ? phân chia url với tham số chưa
		$url .= preg_match('/\?/', $url) ? '' : '?';

		// duyệt qua tất cả các cột
		foreach ($columns as $key => $value) {
			if (preg_match('/^[0-9]+$/', $key)) {
				$header .= '<th>'.$value.'</td>';
				continue;
			}

			$p_url = $url; // biến tạm

			// kiểm tra đã có tham số sort chưa
			if ( preg_match('/sort=[a-z_]*\&/', $p_url) ) {
				// thay giá trị ứng với cột hiện tại
				$p_url = preg_replace('/sort=[a-z_]*\&/', 'sort='.$key.'&', $p_url);
				
			} else {
				// thêm vào giá trị ứng với cột hiện tại
				$p_url .= '&sort='.$key;
			}

			// class để định dạng hiển thị nút sắp xếp
			$class_attr = '';

			// kiểm tra xem có phải là cột đang sắp xếp không
			if ($current_col == $key) {
				// đánh dấu đang sắp xếp theo cột này - class current_col
				// $class_attr .= ' current_col ';

				// nếu đang sắp xếp giảm dần - class desc
				if ($is_desc == 1) {
					$class_attr .= ' icon-chevron-down ';
				} else {
					$class_attr .= ' icon-chevron-up ';
				}

				// kiểm tra cột tăng hay giảm
				if ( preg_match('/desc=0\&/', $p_url) ) {
					$p_url = preg_replace('/desc=0\&/', 'desc=1&', $p_url);
				} else if ( preg_match('/desc=1\&/', $p_url) ) {
					$p_url = preg_replace('/desc=1\&/', 'desc=0&', $p_url);
				}
			} else { // trường hợp các cột còn lại
				if ( preg_match('/desc=[01]*\&/', $p_url) ) {
					$p_url = preg_replace('/desc=[01]*\&/', 'desc=0&', $p_url);
				} else {
					$p_url .= '&desc=0';
				}
			}

			$p_url = preg_replace('/\&\&/', '&', $p_url);

			$header .= '<th><a class="sortable" href="'.$p_url.'">'.$value.'</a>'.
				($class_attr != '' ? ('<span class="' . trim($class_attr) . '"></span>') : '') .
				'</td>';
		}

		return $header;
	}
}