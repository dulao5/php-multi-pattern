<?php

include(dirname(__FILE__).'/../src/MultiPattern.php');

//@case 1. match the multiple-keywords :
$mp = new MultiPattern(array('abc', 'axy', 'def'));
$result = $mp->match('xxxx abc def', $match);     // $result === 1 ; $matches === 'abc'
var_dump($result, $match);
 
//@case 2. match all multiple-keywords in text :
$mp = new MultiPattern(array('abc', 'axy', 'def'));
$result = $mp->matchAll('xxxx abc def', $matches);     // $result === 2 ; $matches === array('abc', 'def')
var_dump($result, $matches);
 
//@case 3. build multiple-keywords to a Regular expressions
$mp = new MultiPattern(array('ab', 'abc', 'axy', 'def' ));
$regex = $mp->getFullRegex();     // $regex === '/a(bc?|xy)|def/i'
var_dump($regex);
