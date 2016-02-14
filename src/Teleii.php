<?php
/**
 * 啦米小智商务号SDK
 * User: chocoboxxf
 * Date: 16/2/13
 * Time: 下午10:33
 */
namespace chocoboxxf\Teleii;

use GuzzleHttp\Client;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Teleii extends Component
{
    /**
     * result参数
     */
    const RESULT_SUCCESS = 0; // 成功
    const RESULT_ERROR_UNKNOWN = -1; // 未知错误
    const RESULT_ERROR_SERVICE_PAUSED = -2; // 服务暂停
    const RESULT_ERROR_INVALID_PARAMS = -3; // 无效参数
    const RESULT_ERROR_MISSING_PARAMS = -4; // 缺失参数
    const RESULT_ERROR_UNAUTHORIZED_IP = -5; // 未授权的IP地址
    const RESULT_ERROR_UNAUTHORIZED_ID = -6; // 未授权的Id帐号
    const RESULT_ERROR_INVALID_ID = -7; // Id无效
    const RESULT_ERROR_ABNORMAL_ID = -8; // Id帐号状态异常，联系管理员
    const RESULT_ERROR_INVALID_SIGNATURE = -9; // 签名错误
    const RESULT_ERROR_RATE_LIMIT_EXCEEDED = -10; // 并发请求超过限制的范围
    const RESULT_ERROR_UNAUTHORIZED_VIRTUAL_MOBILE = -11; // 未授权的商务号
    const RESULT_ERROR_INVALID_TIMESTAMP = -12; // 时间戳格式错误
    const RESULT_ERROR_EXPIRED_TIMESTAMP = -13; // 时间戳过期错误（冗余的时间范围前后12小时）
    const RESULT_ERROR_INVALID_BIND_MOBILE = -14; // bindMobile格式错误，非正确的号码
    const RESULT_ERROR_BIND_MOBILE_VIRTUAL = -15; // bindMobile不能为商务号
    const RESULT_ERROR_INVALID_BIND_TIME = -16; // bindTime格式错误
    const RESULT_ERROR_FM_VIRTUAL = -17; // fm不能为商务号
    const RESULT_ERROR_TM_VIRTUAL = -18; // tm不能为商务号
    const RESULT_ERROR_NOT_ENOUGH_SMS = -19; // 短信余额不足
    const RESULT_ERROR_NOT_ENOUGH_VOICE = -20; // 语音余额不足
    const RESULT_ERROR_MOBILE_NOT_BIND = -21; // 未绑定号码
    const RESULT_ERROR_VIRTUAL_MOBILE_UNAVAILABLE = -22; // 无法获取商务号
    const RESULT_ERROR_FIX_TIME_LARGER_THAN_BIND_TIME = -23; // fixTime不能大于bindTime
    const RESULT_ERROR_NOT_BIND = -24; // 不存在的绑定关系，或关系已经解除
    const RESULT_ERROR_NOT_AUTHORIZED_METHOD = -25; // 未授权的操作
    const RESULT_ERROR_ALREADY_BIND = -26; // 存在绑定关系，无法重复绑定，解绑后可操作
    const RESULT_ERROR_SERVICE_ABNORMAL = -99; // 服务异常

    /**
     * 各接口路径
     */
    const PATH_BIND_MOBILE = '/bindMobile.do'; // 商务号绑定接口
    const PATH_UNBIND_MOBILE = '/unbindMobile.do'; // 商务号解绑接口

    /**
     * 接入商id，由平台方提供
     * @var string
     */
    public $id;
    /**
     * 接入商key，由平台方提供
     * @var string
     */
    public $key;
    /**
     * API地址
     * @var string
     */
    public $host;
    /**
     * API端口
     * @var string
     */
    public $port;

    /**
     * API路径前缀
     * @var string
     */
    protected $apiBaseUrl;
    /**
     * HTTP Client
     * @var \GuzzleHttp\Client
     */
    protected $apiClient;

    public function init()
    {
        parent::init();
        if (!isset($this->id)) {
            throw new InvalidConfigException('请先配置接入商id');
        }
        if (!isset($this->key)) {
            throw new InvalidConfigException('请先配置接入商key');
        }
        if (!isset($this->host)) {
            throw new InvalidConfigException('请先配置API地址');
        }
        if (!isset($this->port)) {
            throw new InvalidConfigException('请先配置API端口');
        }
        $this->apiBaseUrl = 'http://' . $this->host . ':' . $this->port;
        $this->apiClient = new Client([
            'base_url' => [
                $this->apiBaseUrl,
                [],
            ],
            'defaults' => [
            ]
        ]);
    }

    /**
     * 商务号绑定接口
     * @param string $bindMobile 需要绑定的号码，即呼叫商务号后最终转移到的号码
     * @param string $virtualMobile 商务号预先分配给接入商，可填写此参数指定绑定的商务号
     * @param int $bindTime 当设置为X时（X大于0且小于1440），绑定时间X分钟，超过指定时间后将自动释放
     * 当设置为小于等于零时为长期绑定
     * @return array
     */
    public function bindMobile($bindMobile, $virtualMobile, $bindTime = -1)
    {
        $timestamp = $this->getTimestamp();
        mt_srand($timestamp);
        $seqId = strval(mt_rand(0, 100000));
        $params = $this->key.$this->id.$seqId.$timestamp.$bindMobile.$virtualMobile.$bindTime;
        $sign = md5($params);
        try {
            $data = [
                'id' => $this->id,
                'seqId' => $seqId,
                'timestamp' => $timestamp,
                'bindMobile' => $bindMobile,
                'virtualMobile' => $virtualMobile,
                'bindTime' => $bindTime,
                'sign' => $sign,
            ];
            $response = $this->apiClient->post(
                self::PATH_BIND_MOBILE,
                [
                    'body' => $data
                ]
            );
            $result = $response->json();
        } catch (\Exception $ex) {
            $result = [
                'result' => self::RESULT_ERROR_UNKNOWN,
                'error' => $ex->getMessage(),
            ];
        }
        return $result;
    }

    /**
     * 商务号解绑接口
     * @param string $bindMobile 实际绑定的号码，如与实际绑定号码不符合，则忽略操作
     * @param string $virtualMobile 解除绑定的商务号
     * @return array
     */
    public function unbindMobile($bindMobile, $virtualMobile)
    {
        $timestamp = $this->getTimestamp();
        $params = $this->key.$this->id.$timestamp.$virtualMobile;
        $sign = md5($params);
        try {
            $data = [
                'id' => $this->id,
                'timestamp' => $timestamp,
                'virtualMobile' => $virtualMobile,
                'bindMobile' => $bindMobile,
                'sign' => $sign,
            ];
            $response = $this->apiClient->post(
                self::PATH_UNBIND_MOBILE,
                [
                    'body' => $data
                ]
            );
            $result = $response->json();
        } catch (\Exception $ex) {
            $result = [
                'result' => self::RESULT_ERROR_UNKNOWN,
                'error' => $ex->getMessage(),
            ];
        }
        return $result;
    }

    /**
     * 获取时间戳，精确到毫秒
     * @return string
     */
    protected function getTimestamp()
    {
        return intval(microtime(true)*1000);
    }
}