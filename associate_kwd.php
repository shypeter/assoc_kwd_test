<?php
ini_set('memory_limit', '1024M');
require_once __DIR__."/src/vendor/multi-array/MultiArray.php";
require_once __DIR__."/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once __DIR__."/src/class/Jieba.php";
require_once __DIR__."/src/class/Finalseg.php";
require_once __DIR__."/KwdFilter.php";
require_once __DIR__."/Tokenizer.php";
require_once __DIR__."/bootstrap.php";

use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Documents\TokensDocument;
use NlpTools\Documents\TrainingSet;
use NlpTools\Models\Lda;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();
$content = file_get_contents("fileText.txt");

$tok = new Tokenizer();
$res = $tok->segmentation($content);
$res = array_flip($res);
var_dump($res);
exit;

$words_arr = Jieba::cut($content);
$words_arr = check_kwds($words_arr);
//foreach($words_arr as $index => $val) {
//	if(!isset($res[$val])){
//		unset($words_arr[$index]);
//	}
//}

$tset = new TrainingSet();
$tset->addDocument(
    '', // the class is not used by the lda model
    new TokensDocument(
			$words_arr
    )
);
 
$lda = new Lda(
    new DataAsFeatures(), // a feature factory to transform the document data
    1, // the number of topics we want
    1, // the dirichlet prior assumed for the per document topic distribution
    1  // the dirichlet prior assumed for the per word topic distribution
);
 
// run the sampler 50 times
$lda->train($tset,50);
 
print_r(
    // $lda->getPhi(10)
    // just the 10 largest probabilities
    $lda->getWordsPerTopicsProbabilities(10)
);

function check_kwds($words_arr) {
	foreach ($words_arr as $index => $val) {
		if (kwdFilter($val) == '')
			unset($words_arr[$index]);
	}
	return $words_arr;
}
