<?php
$str = "維基百科，自由的百科全書關島（英文：The Territory of Guam；查莫羅文：Guåhan）是位於西太平洋的島嶼，美國的非併入屬地。本島原住民是查莫羅人，約在3500年前在此定居。首府是阿加尼亞（Hagåtña，舊名Agana）。美軍基地佔地約全島的1/4。 關島自由行怎麼玩就看 - 關島之家部落格喜歡關島或準備前往關島旅遊的朋友，部落格記載阿物與作者群對關島的熱情與旅遊經驗，還有許多關島自由行、關島必買好物、關島必吃美食小撇步唷～喔耶！ Guam 關島觀光局歡迎來到關島 Håfa Adai！歡迎來到關島觀光局網站。我們提供各式關島旅遊資訊，幫助您計畫豐富行程。唯有關島，您才可同時找到熱情好客的居民、星砂海灘、碧海藍天及舉世著名的日落。";
var_dump(strpos_array($str, ["關島", "Guam"]));

//function strpos_array($haystack, $needles, $offset = 0) {
//	if (is_array($needles)) {
//		foreach ($needles as $needle) {
//			$pos = strpos_array($haystack, $needle);
//			if ($pos !== false) {
//				return $pos;
//			}
//		}
//		return false;
//	} else {
//		return mb_strpos($haystack, $needles, $offset);
//	}
//}


function strpos_array($haystack, $needles) {
	$res = [];
	if (is_array($needles)) {
		$str_len = mb_strlen($haystack, "utf-8");
		$offset = 0;
		foreach ($needles as $needle) {
			$needle_len = mb_strlen($needle, "utf-8");
			$pos = true;
			while($pos) {
				$pos = mb_strpos($haystack, $needle, $offset, "utf-8");
				if ($pos) {
					$res[$pos] = $needle;
					$offset = ($pos + $needle_len);
				}
			}
			var_dump($res);
			exit;
		}
		return true;
	} else {
		return mb_strpos($haystack, $needles, $offset, "utf-8");
	}
}
