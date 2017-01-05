<?php
ini_set('memory_limit', '1024M');
require_once __DIR__."/src/vendor/multi-array/MultiArray.php";
require_once __DIR__."/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once __DIR__."/KwdFilter.php";
require_once __DIR__."/Tokenizer.php";
require_once __DIR__."/bootstrap.php";
require_once __DIR__."/Utilities.php";

use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;

date_default_timezone_set("Asia/Taipei");
$dateTime = date("Y-m-d H:i:s");
$conn = db_init();
start();
$conn->close();

function start() {
	$kwds = get_log_kwd();
	foreach ($kwds as $kwd) {
		gen_kwd($kwd);
	}
}

function gen_kwd($kwd) {
	global $dateTime;
	$content = get_web_page("https://www.awoo.org/kelo/api/get_10_pages_gyserp/".rawurlencode($kwd));
	$str_len = mb_strlen($content);
	if ($str_len < 10000) {
		var_dump("$kwd : pass ".$str_len);
		return;
	}
	
	$content = str_replace($kwd, "", $content);
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
	    5, // the number of topics we want
	    1, // the dirichlet prior assumed for the per document topic distribution
	    1  // the dirichlet prior assumed for the per word topic distribution
	);
	 
	// run the sampler 50 times
	$lda->train($tset, 50);
	
	// just the 10 largest probabilities
	list($ptw, $words_in_topic) = $lda->getWordsPerTopicsProbabilities(5);
	$kwd_hash = get_keyword_hash();
	list($unknow_kwd) = get_horizontal($kwd_hash, $ptw, $words_in_topic);
	insert_unknow_kwd($unknow_kwd, $dateTime);
}

function get_horizontal($kwd_hash, $ptw, $words_in_topic) {
	$unknow_kwd = [];
	foreach ($ptw as $idx => $topic) {
		foreach ($topic as $keyword => $val) {
			if (!isset($kwd_hash[$keyword])) {
				$unknow_kwd[$keyword] = 1;
				unset($ptw[$idx][$keyword]);
			}
		}
	}

	$res = [];
	arsort($words_in_topic);
	foreach ($words_in_topic as $topic_idx => $topic) {
		foreach ($ptw[$topic_idx] as $keyword => $val) {
			if (!isset($ptw[$topic]))
				$res[$keyword] = $val;
		}
	}
	arsort($res);
	var_dump($res);
	return [$unknow_kwd];
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
