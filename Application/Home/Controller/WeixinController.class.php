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
                $result = $this->valid();
                break;
        }
        $this->_wechat->response($result, 'html');
    }

    public function valid() {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            return $echoStr;
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
            return '照片已收到，回复消费码开始制作打印';
        } else {
            return '照片发送失败，请重新发送';
        }
    }

    public function receiveVideo(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        $this->response('视频已收到', 'html');
    }

    public function receiveText(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $result = $resource->updateResourceCode($post);
        if ($result == 'a') {
            $this->response('请先上传资源', 'html');
        } elseif ($result == 'b') {
            $this->response('消费码错误', 'html');
        } else {
            $this->response('开始打印，请在打印机前稍候', 'html');
        }
    }

    public function receiveEvent() {
        $fromUserName = I('post.fromUserName');//用户微信token
        $toUserName = I('post.toUserName');//微信公众号
        $eventType = I('post.eventType');//subscribe(订阅)、unsubscribe(取消订阅)
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
        $this->response('关注成功', 'html');
    }

    public function getcode() {
        $pid = I('get.printerid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($pid);
        if ($printerInfo) {
            $printcode = D('printcode');
            $code = $printcode->getCode($printerInfo['printer_code']);
            return $code;
        }
    }

    public function createcode() {
        $pid = I('get.printerid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($pid);
        if ($printerInfo) {
            $printcode = D('printcode');
            $code = $printcode->createCode($printerInfo['printer_code']);
            return $code;
        }
    }
}