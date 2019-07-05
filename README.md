# WAYA Pay PHP SDK

使用说明
## 初始化
经过SDK的封装，初始化非常简单，在程序启动后添加以下代码进行初始化

需要引用``waya_pay_sdk.php``
```php
include_once 'waya_pay_sdk.php';
Config::build('Yd3rSG7ync', '1b7a48935d71b27dbc74a03e6b98d780');
```
## 调用接口
使用``HttpModule``接口，非常简化了调用以及新增接口

在调用接口时会自动判断是否有token及token是否过期而进行自动更新

通过``HttpClient``构造``HttpModule``
>以创建微信交易订单举例

```php
$module = HttpClient::module_order_wxpay();
$module->parameter(array(
  'fee'=>1,
  'body'=>'e=test pay',
  'notifyUrl'=>'http://pay.wayakeji.com/api/testnotify'
));
$res = $module->execute();
var_dump(json_encode($res));
if($res['code'] == 0) {
  // success
}else {
  // error
}
```

### 所有接口均使用此流程进行调用

1. 构造``HttpModule``
2. 给``HttpModule``设置参数
3. 执行
4. 判断code是否为0

您也可以在``dome.php``里面发现所有接口的测试
