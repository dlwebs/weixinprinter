<?php
namespace Home\Controller;

class WeixinController extends BaseController {

    public function receiveimgAction(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post);
        return '照片已收到，回复消费码开始制作打印';
    }

    public function receivevideoAction(){
        $post = I('post.');
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        return '视频已收到';
    }

    public function receivetextAction(){
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

    public function eventAction() {
        $fromUserName = I('get.from');//用户微信token
        $toUserName = I('get.to');//微信公众号
        $event = I('get.event');//subscribe(订阅)、unsubscribe(取消订阅)
        $userobj = new \Admin\Model\UserModel();
        $insert = array('user_id'=>$fromUserName, 'user_regdate'=>date('Y-m-d H:i:s'), 'user_weixin'=>$toUserName);
        if ($event == 'subscribe') {
            $insert['user_follow'] = 1
        } elseif ($event == 'unsubscribe') {
            $insert['user_follow'] = 0
        }
        $userobj->add($insert);
    }

    public function getcodeAction() {
        $pid = I('get.printerid');
    }

    public function createcodeAction() {
        $pid = I('get.printerid');
    }
}