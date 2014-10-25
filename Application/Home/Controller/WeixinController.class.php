<?php
namespace Home\Controller;

use Com\Wechat;

class WeixinController extends BaseController {

    private $_token;

    private $_wechat;

    public function indexAction() {
        $this->_token = I('get.token');

        $weixin = new \Admin\Model\WeixinModel();
        $wxinfo = $weixin->getWeixinByToken($this->_token);
        if(!$wxinfo){
            exit('error token');
        }

        $this->_wechat = new Wechat($this->_token);
        $data = $this->_wechat->request();
        $RX_TYPE = trim($data['MsgType']);
        switch($RX_TYPE){
            case Wechat::MSG_TYPE_TEXT:
                $result = $this->receiveText($data);
                break;
            case Wechat::MSG_TYPE_IMAGE:
                $result = $this->receiveImage($data);
                break;
            case Wechat::MSG_TYPE_EVENT:
                $result = $this->receiveEvent($data);
                break;
            case Wechat::MSG_TYPE_VIDEO:
                $result = $this->receiveVideo($data);
                break;
            default:
                $this->valid();
                break;
        }
        if (is_array($result)) {
            $this->_wechat->response($result, Wechat::MSG_TYPE_IMAGE);
        } else {
            $this->_wechat->response($result);
        }
    }

    public function valid() {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->_token;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }



    public function receiveImage($data){
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>(string)$data['ToUserName'], 'picUrl'=>(string)$data['PicUrl'], 'mediaId'=>(string)$data['MediaId']);
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post);
        if ($resourceid) {
            return array('item' => array("Title"=>"图片上传成功",  "Description"=>"照片已收到，可以点击图片进行剪裁，也可回复消费码直接开始制作打印", "PicUrl"=>$post['picUrl'], "Url"=>'http://'.$_SERVER['SERVER_NAME'].'/index.php/zoom/'.urlencode($post['fromUserName'])."/?picurl=".$post['picUrl']));
        } else {
            return '照片发送失败，请重新发送';
        }
    }

    public function receiveVideo($data){
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>(string)$data['ToUserName'], 'mediaId'=>(string)$data['MediaId'], 'thumbMediaId'=>(string)$data['ThumbMediaId']);
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        return '视频已收到';
    }

    public function receiveText($data){
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>(string)$data['ToUserName'], 'content'=>(string)$data['Content']);
        $resource = new \Admin\Model\ResourceModel();
        $result = $resource->updateResourceCode($post);
        if ($result == 'a') {
            return '请先上传资源';
        } elseif ($result == 'b') {
            return '消费码错误';
        } else {
            return '开始打印，请在打印机前稍候';
        }
    }

    public function receiveEvent($data) {
        $fromUserName = (string)$data[FromUserName];//用户微信token
        $toUserName = (string)$data[ToUserName];//微信公众号
        $eventType = (string)$data[Event];//subscribe(订阅)、unsubscribe(取消订阅)
        $userobj = new \Admin\Model\UserModel();
        $insert['user_id'] = $fromUserName;
        $insert['user_follow'] = $eventType;
        $userInfo = $userobj->getUserById($insert['user_id']);
        if ($userInfo) {
            $userobj->where('user_id = "'.$insert['user_id'].'"')->setField('user_follow', $insert['user_follow']);
        } else {
            $insert['user_regdate'] = date('Y-m-d H:i:s');
            $insert['user_weixin'] = $toUserName;
            $insert['user_name'] = '微信用户';
            $userobj->add($insert);
        }
        return '关注成功';
    }

    public function getcodeAction() {
        $pid = I('get.printerid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($pid);
        if ($printerInfo) {
            $printcode = D('printcode');
            $code = $printcode->getCode($printerInfo['printer_code']);
            if ($code) {
                echo $code;
            } else {
                $code = $printcode->createCode($printerInfo['printer_code']);
                echo $code;
            }
        } else {
            echo 'No printer';
        }
    }

    public function createcodeAction() {
        $pid = I('get.printerid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($pid);
        if ($printerInfo) {
            $printcode = D('printcode');
            $code = $printcode->createCode($printerInfo['printer_code']);
            return $code;
        } else {
            return 'No printer';
        }
    }
}