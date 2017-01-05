<?php
function get_keyword_hash() {
	global $conn;
	$kwd_hash = [];
	$query = "SELECT keyword, speech FROM speech";
	$res = $conn->query($query);
	while($row = $res->fetch_array(MYSQLI_BOTH)) {
		$kwd_hash[$row['keyword']] = $row['speech'];
	}
	return $kwd_hash;
}

function get_log_kwd() {
	global $conn;
	$kwds = [];
	$query = "SELECT keyword FROM _log_job_adwords";
	$res = $conn->query($query);
	while($row = $res->fetch_array(MYSQLI_BOTH)) {
		$kwds[] = $row['keyword'];
	}
	return $kwds;
}

function db_init() {
	$servername = "localhost";
	$username = "root";
	$password = "1qaz2wsx3edc";
	$db = "keyword";
	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);
	// Check connection
	if ($conn->connect_error)
		die("Connection failed: " . $conn->connect_error);
	else
		$conn->query("SET NAMES utf8");
	return $conn;
}

function insert_unknow_kwd($unknow_kwd, $dateTime) {
	global $conn;
	if ($unknow_kwd) {
		foreach ($unknow_kwd as $kwd => $val) {
			$query = "INSERT IGNORE INTO unknow SET keyword='".$kwd."', created_at='".$dateTime."';";
			$res = $conn->query($query);
		}
	}
}

function get_web_page($url, $data=array()) {//&$curl_info
	$header = array(
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: zh-tw,zh;q=0.8,en-us;q=0.5,en;q=0.3',
		'Connection: keep-alive',
		'Cache-Control: max-age=0',
	);
				
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.112 Safari/534.30");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	
	if ($data) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	
	$html = curl_exec($ch);
	$_info = curl_getinfo($ch);
	$errno = curl_errno($ch);
	$errmsg = curl_error($ch);
	curl_close($ch);
	$header = substr($html, 0, $_info['header_size']);
	$html = substr($html, $_info['header_size']);
	$header = _httpParseHeaders($header);
	return $html;
}

function _httpParseHeaders($header) {
	$retVal = array();
	$header = preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header);
	$keys = array();
	$blocks = explode("\r\n\r\n", $header);
	foreach ($blocks as $k => $block) {
		if ( trim($block) ) {
			$res = array();
			$fields = explode("\r\n", $block);
			foreach ($fields as $field) {
				$symPos = strpos($field, ':');
				if ($symPos !== false) {
					$key = trim(substr($field, 0, $symPos));
					$val = trim(substr($field, $symPos + 1));
					$res[$key] = $val;
					if ( !in_array($key, $keys) ) $keys[] = $key;
				}
				$blocks[$k] = $res;
			}
		}
		else unset($blocks[$k]);
	}
	foreach ($blocks as $n => $block) {
		foreach ($keys as $key) {
			if (isset($block[$key])) $retVal[$key][$n] = $block[$key];
			else $retVal[$key][$n] = '';
		}
	}
	return $retVal;
}
