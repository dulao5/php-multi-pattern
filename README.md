php-multi-pattern
=================

php-multi-pattern is a php class library of Multiple String-Pattern Matching , it uses the php-pcre.

- case 1. match the multiple-keywords :
```php
          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
          $result = $mp->match('xxxx abc def', $matches);     // $result === 1 ; $matches === 'abc'
```

- case 2. match all multiple-keywords in text :
```php
          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
          $result = $mp->matchAll('xxxx abc def', $matches);     // $result === 2 ; $matches === array('abc', 'def')
```
 
- case 3. build multiple-keywords as a Regular expressions
```php
          $mp = new MultiPattern(array('ab', 'abc', 'axy', 'def'));
          $regex = $mp->getFullRegex('xxxx abc def', $matches);     // $regex === '/a(bc?|xy)|def/i'
```
