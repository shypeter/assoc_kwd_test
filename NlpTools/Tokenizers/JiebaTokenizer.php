<?php
namespace NlpTools\Tokenizers;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;

class JiebaTokenizer implements TokenizerInterface {
	public function tokenize($str) {
		ini_set('memory_limit', '600M');
		Jieba::init(array('mode'=>'default','dict'=>'big'));
		Finalseg::init();
		$seg_list = Jieba::cut($str);
var_dump($seg_list); exit;
		return [];
	}
}
