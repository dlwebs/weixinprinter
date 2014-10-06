<?php
namespace Home\Controller;

use Think\Controller\RestController;

class WeixinController extends RestController {

    public function receiveimgAction_post(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post);
        if ($resourceid) {
            $this->response('照片已收到，回复消费码开始制作打印', 'html');
        } else {
            $this->response('照片发送失败，请重新发送', 'html');
        }
    }

    public function receivevideoAction_post(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        $this->response('视频已收到', 'html');
    }

    public function receivetextAction_post(){
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

    public function eventAction_post() {
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

    public function getcodeAction_get() {
        $pid = I('get.printerid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($pid);
        if ($printerInfo) {
            $printcode = D('printcode');
            $code = $printcode->getCode($printerInfo['printer_code']);
            return $code;
        }
    }

    public function createcodeAction_get() {
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