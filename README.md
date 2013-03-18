php-multi-pattern
=================

php-multi-pattern 是一个php实现的 *多模式匹配* 类。

其实原理很简单，核心思想是将 **多个关键词** 转化成pcre风格的 **正则表达式** 。

因此这个项目只有两百多行PHP代码，并不是一个C写的PHP扩展。

我自己曾经用它过滤过上万个关键词，性能还可以。

php-multi-pattern is a php class library of Multiple String-Pattern Matching , it uses the php-pcre.

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
