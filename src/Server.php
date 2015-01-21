<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 1/20/15
 * Time: 20:29
 */
namespace rongCloud\server;

use Yii;
use yii\helpers\Json;
use yii\base\Component;
use yii\base\NotSupportedException;

use anlutro\cURL\cURL;

class Server extends Component
{
    /**
     * @var string 返回格式 json|xml
     */
    public $format = 'json';
    /**
     * @var string 请求服务地址
     */
    public $host;
    /**
     * @var string 第三方key
     */
    public $key;
    /**
     * @var string 第三方密钥
     */
    public $secret;
    /**
     * @var string 默认头像地址
     */
    public $defaultAvatar = "http://rongcloud-web.qiniudn.com/docs_demo_rongcloud_logo.png";
    /**
     * @var string 默认用户名
     */
    public $defaultUserName = "斑马一号";
    /**
     * @var string 错误消息
     */
    public $error = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * 获取用户token，用于发起对话请求
     * @param $userId
     * @param string $userName
     * @param string $avatar
     * @return bool|mixed
     * @throws InvalidParamException
     */
    public function getUserToken($userId, $userName = '', $avatar = '')
    {
        if (empty($userId)) {
            throw new InvalidParamException("Miss userId.");
        }
        if (empty($userName)) {
            $userName = $this->defaultUserName;
        }
        if (empty($avatar)) {
            $avatar = $this->defaultAvatar;
        }
        $response = $this->_request('/user/getToken', 'post', ['userId' => $userId, 'name' => $userName, 'portraitUri' => $avatar]);
        $body = Json::decode($response->body);
        if (isset($body['code']) && $body['code'] != 200) {
            $this->setError($this->formatError($body));
            return false;
        } else {
            return $body;
        }
    }

    /**
     * 刷新用户信息
     * @param $userId
     * @param string $userName
     * @param string $avatar
     * @return bool|mixed
     * @throws InvalidParamException
     */
    public function refreshUser($userId, $userName = '', $avatar = '')
    {
        if (empty($userId)) {
            throw new InvalidParamException("Miss userId.");
        }
        if (empty($userName)) {
            $userName = $this->defaultUserName;
        }
        if (empty($avatar)) {
            $avatar = $this->defaultAvatar;
        }
        $response = $this->_request('/user/refresh', 'post', ['userId' => $userId, 'name' => $userName, 'portraitUri' => $avatar]);
        $body = Json::decode($response->body);
        if (isset($body['code']) && $body['code'] != 200) {
            $this->setError($this->formatError($body));
            return false;
        } else {
            return $body;
        }
    }

    /**
     * 发起请求
     * @param $uri
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws NotSupportedException
     */
    private function _request($uri, $method = 'get', array $data = [])
    {
        $curl = new cURL();
        $url = $this->_createUrl($uri);
        $request = $curl->newRequest($method, $url, $data);
        $response = $this->_createHttpHeader($request)->send();
        // todo: 记录接口日志
        return $response;
    }

    /**
     * 创建请求url
     * @param $uri
     * @return string
     * @throws NotSupportedException
     */
    private function _createUrl($uri)
    {
        if (!in_array($this->format, ['json', 'xml'])) {
            throw new NotSupportedException();
        }
        return $this->host.$uri.'.'.$this->format;
    }

    /**
     * 创建http header参数
     * @return array
     */
    private function _createHttpHeader($request)
    {
        $nonce = mt_rand();
        $timeStamp = time();
        return $request->setHeaders([
            'RC-App-Key:'.$this->key,
            'RC-Nonce:'.$nonce,
            'RC-Timestamp:'.$timeStamp,
            'RC-Signature:'.sha1($this->secret.$nonce.$timeStamp),
        ]);
    }

    /**
     * 格式化错误
     * @param array $body
     * @return string
     */
    protected function formatError(array $body)
    {
        return "URL:[{$body['url']}]CODE:[{$body['code']}]MESSAGE:[{$body['errorMessage']}]";
    }

    /**
     * 重置错误信息
     */
    public function cleanError()
    {
        $this->error = '';
        return ;
    }

    /**
     * 设置错误信息
     * @param $error
     */
    public function setError($error)
    {
        $this->cleanError();
        $this->error = $error;
        return ;
    }

    /**
     * 获取错误
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}