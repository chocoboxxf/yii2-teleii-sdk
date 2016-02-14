<?php
/**
 * 绑定商务号接口测试
 * User: chocoboxxf
 * Date: 16/2/13
 * Time: 下午11:44
 */
namespace chocoboxxf\Teleii\Tests;

use Yii;

class BindMobileTest extends \PHPUnit_Framework_TestCase
{
    public function testNoExpireTime()
    {
        // 请在phpunit.xml.dist中设置平台信息
        $teleii = Yii::createObject([
            'class' => 'chocoboxxf\Teleii\Teleii',
            'id' => isset($_ENV['API_ID']) ? $_ENV['API_ID'] : '',
            'key' => isset($_ENV['API_KEY']) ? $_ENV['API_KEY'] : '',
            'host' => isset($_ENV['API_HOST']) ? $_ENV['API_HOST'] : '',
            'port' => isset($_ENV['API_PORT']) ? $_ENV['API_PORT'] : '',
        ]);
        $ret = $teleii->bindMobile('13000000001', '13000000000');
        var_dump($ret);
    }

}
