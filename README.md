php-multi-pattern
=================


## Description ##

php-multi-pattern is a Multiple String-Pattern Matching algorithm implementation, the PHP library using php-pcre. It is very simple, but available and fast.

- case 1. match the multiple-keywords :
```php
$mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
$result = $mp->match('xxxx abc def', $matches);     
// $result === 1 ; $matches === 'abc'
```

- case 2. match all multiple-keywords in text :
```php
$mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
$result = $mp->matchAll('xxxx abc def', $matches);     
// $result === 2 ; $matches === array('abc', 'def')
```
 
- case 3. build multiple-keywords as a Regular expressions
```php
$mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
$regex = $mp->getFullRegex('xxxx abc def', $matches);     
// $regex === '/a(bc?|xy)|def/i'
```

- case 4. multi-match 10000 keywords from 10000 texts
```php
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
```

## 中文简介 ##

php-multi-pattern 是一个php实现的 *多模式匹配* 类。

其实原理很简单，核心思想是将 **多个关键词** 转化成pcre风格的 **正则表达式** 。

因此这个项目只有两百多行PHP代码，并不是一个C写的PHP扩展。

我自己曾经用它过滤过上万个关键词，性能还可以。
