<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 1/21/15
 * Time: 13:59
 */
namespace rongCloud\server;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\Json;

abstract class Msg
{
    /**
     * @var string 消息类型
     */
    protected $objectName;

    /**
     * @var string 自定义消息，定义显示的 Push 内容
     */
    protected $pushContent;

    /**
     * @var string 针对 iOS 平台，Push 通知附加的 payload 字段，字段名为 appData
     */
    protected $pushData;

    /**
     * @var string 消息额外信息
     */
    public $extra;

    /**
     * @ignore
     * @param string $extra
     */
    public function __construct($extra = "")
    {
        $this->extra = $extra;
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return [
            'extra',
        ];
    }

    /**
     * 针对消息格式各个字段的过滤规则
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getContent()
    {
        if ($this->rules()) {
            $data = [];
            $fields = $this->fields();
            if ($fields) {
                foreach ($fields as $val) {
                    if (property_exists($this, $val)) {
                        $data[$val] = $this->$val;
                    }
                }
            }
            $model = DynamicModel::validateData($data, $this->rules());
            if ($model->hasErrors()) {
                // todo: 记录错误日志
                return false;
            } else {
                return Json::encode($data);
            }
        }
    }

    /**
     * 获取objectName
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * 设置pushContent
     * @param string
     */
    public function setPushContent($content)
    {
        $this->pushContent = $content;
        return ;
    }

    /**
     * 获取pushContent
     * @return string
     */
    public function getPushContent()
    {
        return $this->pushContent;
    }

    /**
     * 设置pushData
     * @param string
     */
    public function setPushData($data)
    {
        $this->pushData = $data;
        return ;
    }

    /**
     * 获取pushData
     * @return string
     */
    public function getPushData()
    {
        return $this->pushData;
    }
}