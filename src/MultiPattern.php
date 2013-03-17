<?php

/****
 * @class : MultiPattern is a php class library of Multiple String-Pattern Matching , it uses the php-pcre.
 * 		
 * @case 1. match the multiple-keywords :
 *          eg.
 *          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
 *          $result = $mp->match('xxxx abc def', $matches);     // $result === 1 ; $matches === 'abc'
 *
 * @case 2. match all multiple-keywords in text :
 *          eg.
 *          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
 *          $result = $mp->matchAll('xxxx abc def', $matches);     // $result === 2 ; $matches === array('abc', 'def')
 *
 * @case 3. build multiple-keywords as a Regular expressions
 *          eg.
 *          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
 *          $regex = $mp->getFullRegex('xxxx abc def', $matches);     // $regex === '/a(bc?|xy)|def/i'
 *
 * */
class MultiPattern {

	protected static $DEFAULT_OPTIONS = array(
			);

	public function __construct($words , $opts=array()) {
		$this->wtree = self::createWordTree($words);
		$this->opts = $opts + self::$DEFAULT_OPTIONS;
	}

	public function match($str, &$match) {
		$match = null;
		foreach($this->getRegexList() as $regex) {
			$r = preg_match($regex, $str, $m);
			if($r === false) {
				$errno = preg_last_error();
				throw new MultiPatternException($errno , "regex[ $regex ] throw the preg-error : $errno ");
			} else if($r) {
				$match = $m[0];
				return $r;
			}
		}
		return false;
	}

	public function matchAll($str, &$matches) {
		$result = 0;
		$matches = array();
		foreach($this->getRegexList() as $regex) {
			$r = preg_match_all($regex, $str, $m);
			if($r === false) {
				$errno = preg_last_error();
				throw new MultiPatternException($errno , "regex[ $regex ] throw the preg-error : $errno ");
			} else if($r) {
				$result += $r;
				$matches = array_merge($matches , $m[0]);
			}
		}
		return $result;
	}

	public function getFullRegex($delimiters='/', $modifiers='i') {
		$isTopLevel = true;
		return $delimiters . self::makeRegex($this->wtree, $isTopLevel) . $delimiters . $modifiers;
	}

	public function getRegexList() {
		if($this->regexList) return $this->regexList;
		return $this->regexList = self::generat($this->wtree);
	}

	const REGEX_SUB_PATTEM_LIMIT = 200;
	const REGEX_DELIMITERS = 0;
	const REGEX_MODIFIERS = 1;

	protected $wtree = null;
	protected $opts = null;

	protected static function generat($wordtree, $delimiters='/', $modifiers='i'){
		$regs = self::makeRegexList($wordtree);
		$new_regs = array();
		foreach($regs as $reg){
			$new_regs[] = $delimiters. $reg . $delimiters . $modifiers;
		}
		return $new_regs;
	}

	/* 
	 * [abc,ade,adb,ab] 
	 * 		and the array: array(
	 *			'a'=>array(
	 *				'b'=>array("NULL"=>NULL, 'c'=>array("NULL"=>NULL)),
	 *				'd'=>array(
	 *					'e'=>array("NULL"=>NULL),
	 *					'b'=>array("NULL"=>NULL)
	 *				)
	 *			)
	 * 		)
	 *              the tree for the regex : a(bc?|d(e|b))
	 * **/
	protected static function createWordTree($words){
		$tree = array();

		foreach($words as $word){
			$len = strlen($word);
			$ref = &$tree;
			for($i=0; $i<$len; $i++){
				$tmpref = &$ref;
				$c = $word{$i};
				if(!isset($tmpref["".$c])){
					$tmpref["".$c] = array();
				}
				unset($ref);
				$ref = &$tmpref["".$c];
				unset($tmpref);
			}
			$ref["NULL"] = NULL;
			unset($ref);
		}
		return $tree;
	}
	/*
	 * [abc,ade,adb,ab] to a(bc?|d(e|b))
	 * */
	protected static function makeRegex($wtree, $isTopLevel=false){
		$arr = array();
		$num = 0;
		foreach($wtree as $key=>$value){
			if($key==='NULL' && $value === NULL)continue;
			$tmp = self::makeRegex($value);
			$rkey = self::escpRegexStr("$key");
			$arr[] = $rkey.$tmp;
			$num++;
		}
		$reg = implode('|', $arr);
		if($num>1 && !$isTopLevel) $reg = "($reg)";
		if(in_array(NULL, $wtree)&& count($wtree)>1){
			if(strlen($reg)==1 ){
				$reg = "$reg?";
			}
			else{
				$reg = "($reg)?";
			}
		}
		return $reg;
	}

	protected static function makeRegexList($wtree){
		$regs = array();
		foreach($wtree as $key=>$value){
			if(!is_array($value))continue;
			$rkey = self::escpRegexStr("$key");
			$r = self::makeRegex($value);
			if(self::validateRegex($r)){
				$regs[] = "$rkey$r";
			}
			else{
				$subregs = self::makeRegexList($value);
				$subregs = self::optimMultiSubPattem($subregs);
				foreach($subregs as $subreg){
					$regs[] = "$rkey$subreg";
				}
			}
		}
		return $regs;
	}
	protected static function optimMultiSubPattem($regs){
		$this_limit = self::REGEX_SUB_PATTEM_LIMIT / 2;
		$res = array();
		$tmp = array();
		$count = 0;
		foreach($regs as $reg){
			$c = self::countSubPattem($reg);
			if($c < $this_limit){
				$tmp[] = $reg;
				$count += $c;
				if($count > $this_limit){
					$mreg = implode('|', $tmp);
					if(count($tmp)>1) $mreg = "($mreg)";
					$res[] = $mreg;
					$tmp = array();
					$count = 0;
				}
			}
			else{
				$res[] = $reg;
			}
		}
		if(count($tmp)){
			$mreg = implode('|', $tmp);
			if(count($tmp)>1) $mreg = "($mreg)";
			$res[] = $mreg;
		}
		return $res;
	}


	protected static function validateRegex($reg){
		return ( self::countSubPattem($reg) < self::REGEX_SUB_PATTEM_LIMIT ) ;
	}
	protected static function countSubPattem($reg){
		$c=0;
		$len = strlen($reg);
		for($i=0; $i<$len; $i++){
			if('('==$reg{$i})$c++;
		}
		return $c;
	}
	protected static function escpRegexStr($char){
		$arr = array();
		$len = strlen($char);
		for($i=0; $i<$len; $i++){
			$o = ord($char{$i});
			if(($o >= ord('0') ) && ($o <= ord('9'))){
				$arr[] = $char{$i};
			}
			elseif(($o >= ord('a')) && ($o <= ord('z'))){
				$arr[] = $char{$i};
			}
			elseif(($o >= ord('A')) && ($o <= ord('Z'))){
				$arr[] = $char{$i};
			}
			else{
				$arr[] = sprintf("\\x%X", ord($char{$i}));
			}
		}
		return implode('',$arr);
	}
};

Class MultiPatternException extends Exception {};
