<?php
namespace Home\Controller;

use Think\Controller\RestController;

class WeixinController extends RestController {

    public function receiveimgAction_post(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post);
        return '照片已收到，回复消费码开始制作打印';
    }

    public function receivevideoAction_post(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        return '视频已收到';
    }

    public function receivetextAction_post(){
        $post = I('post.');
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

    public function eventAction_post() {
        $fromUserName = I('post.fromUserName');//用户微信token
        $toUserName = I('post.toUserName');//微信公众号
        $eventType = I('post.eventType');//subscribe(订阅)、unsubscribe(取消订阅)
        $userobj = new \Admin\Model\UserModel();
        $insert['user_id'] = $fromUserName;
        if ($eventType == 'subscribe') {
            $insert['user_follow'] = '1';
        } elseif ($eventType == 'unsubscribe') {
            $insert['user_follow'] = '0';
        }
        $userInfo = $userobj->getUserById($insert['user_id']);
        if ($userInfo) {
            $userobj->where('user_id = "'.$insert['user_id'].'"')->setField('user_follow', $insert['user_follow']);
        } else {
            $insert['user_regdate'] = date('Y-m-d H:i:s');
            $insert['user_weixin'] = $toUserName;
            $insert['user_name'] = '微信用户';
            $insert['user_pw'] = '';
            $insert['user_status'] = '1';
            $userobj->add($insert);
        }
        $this->response(I('post.'), 'json');
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