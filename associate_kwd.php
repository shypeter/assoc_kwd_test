<?php
ini_set('memory_limit', '1024M');
require_once __DIR__."/src/vendor/multi-array/MultiArray.php";
require_once __DIR__."/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once __DIR__."/KwdFilter.php";
require_once __DIR__."/Tokenizer.php";
require_once __DIR__."/bootstrap.php";
//require_once __DIR__."/src/class/Jieba.php";
//require_once __DIR__."/src/class/Finalseg.php";

use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;
//use Fukuball\Jieba\Jieba;
//use Fukuball\Jieba\Finalseg;
define("KWD", "雞尾酒");
//$defaults = array(
//	'mode'=>'default',
//	'dict'=>'big'
//);
//Jieba::init($defaults);
//Finalseg::init();

$content = file_get_contents("fileText.txt");
$content = str_replace(KWD, "", $content);
//$words_arr = Jieba::cut($content);
//$words_arr = check_kwds($words_arr);
$tok = new Tokenizer();
$res = $tok->segmentation($content);
$words_arr = strpos_array($content, $res);
ksort($words_arr);
$tset = new TrainingSet();
$tset->addDocument(
    '', // the class is not used by the lda model
    new TokensDocument(
			$words_arr
    )
);
 
$lda = new Lda(
    new DataAsFeatures(), // a feature factory to transform the document data
    3, // the number of topics we want
    1, // the dirichlet prior assumed for the per document topic distribution
    1  // the dirichlet prior assumed for the per word topic distribution
);
 
// run the sampler 50 times
$lda->train($tset, 50);

// just the 10 largest probabilities
list($ptw, $words_in_topic) = $lda->getWordsPerTopicsProbabilities(10);
//$conn = db_init();
$kwd_hash = [];//get_keyword_hash($conn);
list($unknow_kwd) = get_horizontal($kwd_hash, $ptw, $words_in_topic);
//insert_unknow_kwd($conn, $unknow_kwd);
//$conn->close();



function get_horizontal($kwd_hash, $ptw, $words_in_topic) {
	$unknow_kwd = [];
//	foreach ($ptw as $idx => $topic) {
//		foreach ($topic as $keyword => $val) {
//			if (!isset($kwd_hash[$keyword])) {
//				$unknow_kwd[$keyword] = 1;
//				unset($ptw[$idx][$keyword]);
//			}
//		}
//	}
var_dump($ptw);
var_dump($words_in_topic);
	return [$unknow_kwd];
}

function get_keyword_hash($conn) {
	$kwd_hash = [];
	$query = "SELECT keyword, speech FROM speech";
	$res = $conn->query($query);
	while($row = $res->fetch_array(MYSQLI_BOTH)) {
		$kwd_hash[$row['keyword']] = $row['speech'];
	}
	return $kwd_hash;
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

function check_kwds($words_arr) {
	foreach ($words_arr as $index => $val) {
		if (kwdFilter($val) == '')
			unset($words_arr[$index]);
	}
	return $words_arr;
}

function strpos_array($haystack, $needles) {
	$res = [];
	if (is_array($needles)) {
		$str_len = mb_strlen($haystack, "utf-8");
		foreach ($needles as $needle) {
			$offset = 0;
			$needle_len = mb_strlen($needle, "utf-8");
			$pos = true;
			while($pos) {
				$pos = mb_strpos($haystack, $needle, $offset, "utf-8");
				if ($pos) {
					$res[$pos] = $needle;
					$offset = ($pos + $needle_len);
				}
			}
		}
		return $res;
	} else
		return false;
}

function insert_unknow_kwd($conn, $unknow_kwd) {
	if ($unknow_kwd) {
		$query = "";
		$query_head = "INSERT INTO `unknow` (`idx`, `keyword`, `created_at`) VALUES ";
		foreach ($unknow_kwd as $kwd => $val) {
			$query .= " (NULL, '".$kwd."', NOW()),";
		}
		$query = rtrim($query, ",");
		$query = $query_head.$query;
		$res = $conn->query($query);
	}
}
