<?php
function kwdFilter($kwd) {
	$kwd_len_arr = $this->_getKwdLen($kwd);
	if (($kwd_len_arr[0] > 10) || ($kwd_len_arr[0] > 1 && $kwd_len_arr[1] == 'en')) {
		return '';
	} else if ($kwd_len_arr[1] == 'en') {
		$res = preg_split("/ /", $kwd);
    foreach($res as $val) {
      if (mb_strlen($val) > 20) {
				return '';
      }
    }
	}

	$vowels = ['www.','.com','.org','.co','.tw','.info','.net','.cc','.com.tw','.cn','.io','.xyz','.club','.com.cn','.today','.online','.asia','.tech','.company','.life','.rocks','.tv','.solutions','.website','.video','.space','.city','.biz','.cool','.email','.mobi','.site','.store','.cloud','.style','.guru','.center','.design','.ninja','.tips','.me','.house','.love','.cafe','.global','.us','.idv.tw','.co.uk','.com.au','.de','.in','.ca','.jp'];
	$check_url_kwd = str_replace($vowels, '', $kwd);
	if ($check_url_kwd != $kwd) {
		return '';
	}

	$vowels = array('​', '×', '€', '│', "←", "〉", "·", "§", "♂", "♀", "㊣", "$", "-", "※", "●", "", "∣", "◢", "︱", "█", "╚", "╠", "▲", "☆", "▃", "／", "▌", "◤", "▅", '－', '’', '–', ',', '²', '！', '!', '@', '＠', '%', '*', '?', '？', '~', '～', '»', '<', '>', '\\', '=', '^', '`', '(', ')', '|', '{', '}', '｛', '｝', ';', '；', '"', '．', '‧', '、', '®', '™', '》', '《', '（', '）','〔', '〕', '＃', '，', '﹒', '°', '：', '】', '【', '。', '＋', '＆', '­', '℃', '•', '˙', '　', '」', '「', '『', '』', '╳', '︿', '＿', '＋', '＝', '＊', '＄', '％', '｜', '＼', '＜', '＞', '＂', '♥', '​', '→', '║', '|', '』', '『','◎','★','►','↑','↗','■','○','├','↓','└','⊙','✔','┌','▼','》','▪','─', '◆', '∥', '↘', 'ⅲ', 'Ⅲ', 'ⅱ', 'Ⅱ', '∟', '◇', '┼', '十', '┘', '╚', '╠', '▶', '+', 'Ⅰ', '±', '♪', '╘', '—', '∮', '′', '≦', '≧', '…', '�', '÷', '╱', '', '
', 'favicon.ico', '[', ']', '´', '«', '\/', '/', '#','&');

	$kwd = html_entity_decode($kwd);
  $kwd = (strip_tags($kwd));//htmlspecialchars
  $kwd = strtolower(trim(rtrim($kwd)));
	$kwd = str_replace($vowels, '', $kwd);
	$vowels = array('／');
	$kwd = str_replace($vowels, '/', $kwd);
	$kwd = $this->font_width_change($kwd);

	$res = preg_split("/[ ]+/", $kwd);
	$arr_len = count($res);
	if ($res[$arr_len-1] == "") {
		unset($res[$arr_len-1]);
		$arr_len--;
	}
	if (isset($res[0]) && $res[0] == "") {
		unset($res[0]);
		$arr_len--;
	}
	$arr_len--;
		
	$kwd = '';
	$res = array_values($res);
	foreach($res as $index => $val) {
		$kwd_len_res = $this->_getKwdLen($val);
		if( $kwd_len_res[0] <= 20 ){
			$kwd .= $val;
			if ($index != $arr_len) {
				$kwd .= " ";
	}}}
	return $kwd;
}

function _getKwdLen( $str='' ) {
	if( trim($str) == ''){
		return 0;
	}
	$m = mb_strlen($str,'utf-8');
	$s = strlen($str);
	if ( $s == $m ) {
		//english
		$en_len = $this->_getEnStrLen($str);
		return array($en_len, 'en');
	}
	
	if ( ($s%$m == 0) && ($s%3 == 0) ) {
		//chinese
		return array($m, 'ch');
	}
	
	$count = 0;
	$res = preg_split("/([a-z0-9A-Z\s])/", $str);
	foreach ($res as $val) {
		if ($val != '') {
			$count += mb_strlen($val,'UTF-8');
			$str = str_replace($val, '', $str);
		}
	}
	$count += $this->_getEnStrLen($str);
	//mix
	return array($count, 'mix');
}

function _getEnStrLen($str) {
	$res = preg_split("/ /", $str);
	$count = 0;
	foreach($res as $val) {
		if ($val != '') {
			$count++;
		}
	}
	return $count;
}

function font_width_change($strin, $h2f=false) {
	$HalfWidthChar = array(
		"0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j","k", "l", "m", "n", "o", "p", "q", "r", "s", "t","u", "v", "w", "x", "y", "z",
		"A", "B", "C", "D", "E", "F", "G", "H", "I", "J","K", "L", "M", "N", "O", "P", "Q", "R", "S", "T","U", "V", "W", "X", "Y", "Z"
	);
	$FullWidthChar = array(
		"０", "１", "２", "３", "４", "５", "６", "７", "８", "９",
		"ａ", "ｂ", "ｃ", "ｄ", "ｅ", "ｆ", "ｇ", "ｈ", "ｉ", "ｊ","ｋ", "ｌ", "ｍ", "ｎ", "ｏ", "ｐ", "ｑ", "ｒ", "ｓ", "ｔ","ｕ", "ｖ", "ｗ", "ｘ", "ｙ", "ｚ",
		"Ａ", "Ｂ", "Ｃ", "Ｄ", "Ｅ", "Ｆ", "Ｇ", "Ｈ", "Ｉ", "Ｊ","Ｋ", "Ｌ", "Ｍ", "Ｎ", "Ｏ", "Ｐ", "Ｑ", "Ｒ", "Ｓ", "Ｔ","Ｕ", "Ｖ", "Ｗ", "Ｘ", "Ｙ", "Ｚ"
	);

	if ($h2f) {
		$strtemp = str_replace($HalfWidthChar, $FullWidthChar, $strin);
	} else {
		$strtemp = str_replace($FullWidthChar, $HalfWidthChar, $strin);
	}
	return $strtemp;
}
