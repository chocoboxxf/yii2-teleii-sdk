# yii2-teleii-sdk
基于Yii2实现的啦米小智商务号API SDK（目前开发中）

环境条件
--------
- >= PHP 5.4
- >= Yii 2.0
- >= GuzzleHttp 5.0

安装
----

添加下列代码在``composer.json``文件中并执行``composer update --no-dev``操作

```json
{
    "require": {
       "chocoboxxf/yii2-teleii-sdk": "dev-master"
    }
}
```

设置方法
--------

```php
// 全局使用
// 在config/main.php配置文件中定义component配置信息
'components' => [
  .....
  'teleii' => [
    'class' => 'chocoboxxf\Teleii\Teleii',
    'id' => '123', // 接入商id
    'key' => 'ABCXYZ1234567', // 接入商key
    'host' => '127.0.0.1', // API地址
    'port' => '8000', // API端口
  ]
  ....
]
// 代码中调用
$result = Yii::$app->teleii->bindMobile('14012345678', '13000000000');
....
```

```php
// 局部调用
$teleii = Yii::createObject([
    'class' => 'chocoboxxf\Teleii\Teleii',
    'id' => '123', // 接入商id
    'key' => 'ABCXYZ1234567', // 接入商key
    'host' => '127.0.0.1', // API地址
    'port' => '8000', // API端口
]);
$result = $teleii->bindMobile('14012345678', '13000000000');
....
```

使用示例
--------

商务号绑定接口

```php
$result = Yii::$app->teleii->bindMobile('14012345678', '13000000000');
if ($result['result'] !== 0) {
    // 出错情况
    return $result['error']; // 返回出错信息
    ....
}
// 正常情况
....
```