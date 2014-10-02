<?php
namespace Home\Controller;

class WeixinController extends BaseController {

    public function receiveimgAction(){
        $post = I('post.');
        $fromUserName = 
        $param = array('fromUserName'=>$object->FromUserName, 'toUserName'=>$object->ToUserName, 'picUrl'=>$object->PicUrl, 'mediaId'=>$object->MediaId);
        return '照片已收到，回复消费码开始制作打印';
    }

    public function receivevideoAction(){
        $post = I('post.');
        $fromUserName = 
        $param = array('fromUserName'=>$object->FromUserName, 'toUserName'=>$object->ToUserName, 'picUrl'=>$object->PicUrl, 'mediaId'=>$object->MediaId);
        return '照片已收到，回复消费码开始制作打印';
    }

    public function eventAction() {
        $fromUserName = I('get.from');
        $toUserName = I('get.to');
        $event = I('get.event');
        if ($event == 'subscribe') {
            
        } elseif ($event == 'unsubscribe') {
            
        }
    }
}