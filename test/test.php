<?php

include(dirname(__FILE__).'/../src/MultiPattern.php');

//@case 1. match the multiple-keywords :
$mp = new MultiPattern(array('abc', 'axy', 'def'));
$result = $mp->match('xxxx abc def', $match);     // $result === 1 ; $match === 'abc'
assert(1 === $result);
assert('abc' === $match);
 
//@case 2. match all multiple-keywords in text :
$mp = new MultiPattern(array('abc', 'axy', 'def'));
$result = $mp->matchAll('xxxx abc def', $matches);     // $result === 2 ; $matches === array('abc', 'def')
assert(2 === $result);
assert(array('abc', 'def') == $matches);
 
//@case 3. build multiple-keywords to a Regular expressions
$mp = new MultiPattern(array('ab', 'abc', 'axy', 'def' ));
$regex = $mp->getFullRegex();     // $regex === '/a(bc?|xy)|def/i'
assert('/a(bc?|xy)|def/i' === $regex);



//@case 4. multi-match 10000 keywords from 10000 texts

echo "multi-match 10000 keywords from 10000 texts\n";

$keywords = array();
while(1){
	$keywords[ random_word() ] = 1;
	if(count($keywords) == 10000) break;
}
$keywords = array_keys($keywords);

$mp = new MultiPattern($keywords);

$texts = array();
for($i=0; $i<10000; $i++) {
	$texts[] = base64_encode(file_get_contents('/dev/urandom', NULL, NULL, 0, 1024)) . " " . $keywords[$i];
}

$begin_time = microtime(true);
for($i=0; $i<10000; $i++) {
	$r = $mp->matchAll($texts[$i], $match);
	if($i == 0) {
		$first_time = microtime(true);
		echo "first time: ". ($first_time - $begin_time) ." Sec. \n";
	}
	assert($match[0] == $keywords[$i]); 	//should be $keywords[$i]
}
$end_time = microtime(true);
echo "10000th matched\n";
echo "total: ". ($end_time - $begin_time) ." Sec.\n";
echo "10000 times mean: ". (($end_time - $begin_time)/10000) ." Sec.\n";


function random_word() {
	$res = "";
	for($i=0; $i<10; $i++) {
		$res .= chr( ord('a') + mt_rand() % 26 ) ;
	}
	return $res;
}
