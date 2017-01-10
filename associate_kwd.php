<?php
date_default_timezone_set("Asia/Taipei");
require_once __DIR__."/Tokenizer.php";
require_once __DIR__."/bootstrap.php";
require_once __DIR__."/Utilities.php";
require_once __DIR__."/Content.php";
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;

$time_stamp = time();
$dateTime = date("Y-m-d H:i:s", $time_stamp);
$date = date("Y-m-d", $time_stamp);

$conn = db_init();
start();
$conn->close();

function start() {
	global $date;
	$kwds = get_log_kwd($date);
	foreach ($kwds as $kwd)
		gen_kwd($kwd);
}

function gen_kwd($kwd) {
	echo $kwd."\n";
	global $dateTime;
	$content = get_web_page(SERP_TEN.rawurlencode($kwd));
	$str_len = mb_strlen($content);
	if ($str_len < 10000) {
		echo "Pass, str_len = " . $str_len . "\n";
		return;
	}
	
	$content = str_replace($kwd, "", $content);
	$tok = new Tokenizer();
	$res = $tok->segmentation($content);
	$res = kwds_2_earth($res);
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
	get_horizontal($ptw, $words_in_topic);
}

function get_horizontal($ptw, $words_in_topic) {
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
}

function kwds_2_earth($kwds_res) {
	global $time_stamp;
	$res = [];
	$data = ["keywords" => json_encode($kwds_res)];
	$content = get_web_page(LAK, $data);
	$kwds_res = json_decode($content, true);
	foreach ($kwds_res['result'] as $kwd => $val) {
		if (isset($val["fa"]["updated_at"])) {
			$u_at = strtotime($val["fa"]["updated_at"]);
			if ($u_at > ($time_stamp-(86400*30*6)))
				$res[] = $kwd;
		}
	}
	return $res;
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
