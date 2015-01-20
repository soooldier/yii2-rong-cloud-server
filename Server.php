<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 1/20/15
 * Time: 20:29
 */
namespace rongCloud\server;

use Yii;
use yii\base\Component;
use anlutro\cURL\cURL;
use yii\base\NotSupportedException;

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function  getToken($userId, $userName, $avatar)
    {
        if (empty($userId)) {
            throw new InvalidParamException("Miss userId.");
        }
        if (empty($userName)) {
            $userName = $this->$defaultUserName;
        }
        if (empty($avatar)) {
            $avatar = $this->defaultAvatar;
        }
        $response = $this->_request('/user/getToken', ['userId' => $userId, 'name' => $userName, 'portraitUri' => $avatar]);
        print_r($response);
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
        return $request->setOptions([
            'RC-App-Key:'.$this->key,
            'RC-Nonce:'.$nonce,
            'RC-Timestamp:'.$timeStamp,
            'RC-Signature:'.sha1($this->secret.$nonce.$timeStamp),
        ]);
    }
}