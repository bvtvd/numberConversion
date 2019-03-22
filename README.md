- install
```
composer require bvtvd/number-conversion
```

- quick start
```
$conv = new bvtvd\NumberConversion();

// 数字转中文
$conv->numberToChinese(1001);    // 一千零一

// 中文转数字
$conv->chineseToNumber('一百');     // 100

```

```
NumberConversion($uppercase = false, $simple = true)
```
> 参数:  
> - $uppercase: 是否使用大写中文数字
> - $simple: 数字简化, 会将 '一十一' 简化为 '十一', '五百二十' 简化为 '五百二'

