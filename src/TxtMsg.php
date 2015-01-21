<?php
/**
 * Created by PhpStorm.
 * User: soooldier
 * Date: 1/21/15
 * Time: 14:41
 */
namespace rongCloud\server;

class TxtMsg extends Msg
{
    /**
     * @var string 消息类型
     */
    protected $objectName = 'RC:TxtMsg';

    /**
     * @var string 主体内容
     */
    public $content;

    /**
     * @ignore
     * @param string $content
     * @param string $extra
     */
    public function __construct($content, $extra = "")
    {
        parent::__construct($extra);
        $this->content = $content;
    }

    /**
     * @inheritdoc
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), ['content']);
    }

    /**
     * @inheritdoc
     * @return array
     */
    protected function rules()
    {
        return array_merge(parent::rules(), [
            ['content', 'required'],
        ]);
    }
}