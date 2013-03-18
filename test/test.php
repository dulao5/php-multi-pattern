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


