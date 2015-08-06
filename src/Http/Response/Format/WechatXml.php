<?php

namespace Dingo\Api\Http\Response\Format;

use Illuminate\Contracts\Support\Arrayable;

class WechatXml extends Format
{
    public $request = array();    
    protected $funcflag = false;

    /**
     * Format an Eloquent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function formatEloquentModel($model)
    {
        $key = str_singular($model->getTable());

        if (! $model::$snakeAttributes) {
            $key = camel_case($key);
        }

        return $this->encode([$key => $model->toArray()]);
    }

    /**
     * Format an Eloquent collection.
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     *
     * @return string
     */
    public function formatEloquentCollection($collection)
    {
        if ($collection->isEmpty()) {
            return $this->encode([]);
        }

        $model = $collection->first();
        $key = str_plural($model->getTable());

        if (! $model::$snakeAttributes) {
            $key = camel_case($key);
        }

        return $this->encode([$key => $collection->toArray()]);
    }

    /**
     * Format an array or instance implementing Arrayable.
     *
     * @param array|\Illuminate\Contracts\Support\Arrayable $content
     *
     * @return string
     */
    public function formatArray($content)
    {
        $content = $this->morphToArray($content);

        array_walk_recursive($content, function (&$value) {
            $value = $this->morphToArray($value);
        });
        $this->getWechatXml($content['detail']);
        // print_r($this->request);
        if ($content['type'] == 'text')
        {
            return $this->replyText($content='wocao');
        }

        return $this->encode([]);
    }
    
    //获取微信发来带有用户消息的XML
    public function getWechatXml($xml='')
    {
        $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $request = (array)$postObj;

        // 可以解析信息的详细信息
        // $fromUsername = $postObj->FromUserName;
        // $toUsername = $postObj->ToUserName;
        // $Message = trim($postObj->Content);
        // $MsgType = trim($postObj->MsgType);
        
        // $Location_X = $postObj->Location_X;
        // $Location_Y = $postObj->Location_Y;
        // $Scale = $postObj->Scale;
        // $Label = $postObj->Label;
        // $PicUrl = $postObj->PicUrl;
        // $MsgId  = $postObj->MsgId;
        // $Url = $postObj->Url;
        // $Event = $postObj->Event;
        // $Latitude = $postObj->Latitude;
        // $Longitude = $postObj->Longitude;
        // $Precision = $postObj->Precision;
        // $EventKey = $postObj->EventKey;

        $this->request = $request;
    }

    //星标消息
    public function set_funcflag()
    {
        $this->funcflag = true;
    }

    //回复文本
    public function replyText($message)
    {
        $textTpl = <<<eot
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[%s]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>%d</FuncFlag>
</xml>
eot;
        $req = $this->request;
        return sprintf($textTpl, $req['FromUserName'], $req['ToUserName'], time(), 'text', $message, $this->funcflag ? 1 : 0);
    }

    /**
     * Get the response content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/plain';
    }

    /**
     * Morph a value to an array.
     *
     * @param array|\Illuminate\Contracts\Support\Arrayable $value
     *
     * @return array
     */
    protected function morphToArray($value)
    {
        return $value instanceof Arrayable ? $value->toArray() : $value;
    }

    /**
     * Encode the content to its JSON representation.
     *
     * @param string $content
     *
     * @return string
     */
    protected function encode($content)
    {
        return json_encode($content);
    }
}
