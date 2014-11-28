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

//        $receiveType = array('text', 'image');
//        $receiveEvent = array('subscribe', 'unsubscribe');
//        if (!in_array($RX_TYPE, $receiveType)) {
//            $RX_EVENT = trim($data['Event']);
//            if (!in_array($RX_EVENT, $receiveEvent)) {
//                $ch = curl_init($wxinfo['weixin_dispatchurl']);
//                curl_setopt($ch, CURLOPT_MUTE, 1);
//                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//                curl_setopt($ch, CURLOPT_POST, 1);
//                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
//                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//                $output = curl_exec($ch);
//                curl_close($ch);
//                echo $output ;
//                exit;
//            }
//        }
        
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
        $this->_wechat->response($result);
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
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>$this->_token/*(string)$data['ToUserName']*/, 'picUrl'=>(string)$data['PicUrl'], 'mediaId'=>(string)$data['MediaId']);
        $check_print = $this->_checkfree($post);
        if (!$check_print) {
            return '您的免费打印次数已用完';
        }
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post);
        if ($resourceid) {
            return '照片已收到，可以<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php/zoom/'.$post['fromUserName']."/?picurl=".$post['picUrl'].'">点击这里</a>对图片进行剪裁，也可直接回复消费码开始打印';
        } else {
            return '照片发送失败，请重新发送';
        }
    }

    public function receiveVideo($data){
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>$this->_token/*(string)$data['ToUserName']*/, 'mediaId'=>(string)$data['MediaId'], 'thumbMediaId'=>(string)$data['ThumbMediaId']);
        $check_print = $this->_checkfree($post);
        if (!$check_print) {
            return '您的免费打印次数已用完';
        }
        $resource = new \Admin\Model\ResourceModel();
        $resourceid = $resource->insertResource($post, 1);
        return '视频已收到';
    }

    public function receiveText($data){
        $post = array('fromUserName'=>(string)$data['FromUserName'], 'toUserName'=>$this->_token/*(string)$data['ToUserName']*/, 'content'=>(string)$data['Content']);
        $check_print = $this->_checkfree($post);
        if (!$check_print) {
            return '您的免费打印次数已用完';
        }
        $resource = new \Admin\Model\ResourceModel();
        $result = $resource->updateResourceCode($post);
        if ($result == 'a') {
            return '没有上传资源或上传资源正在审核中...';
        } elseif ($result == 'b') {
            return '消费码错误';
        } else {
            return '开始打印，请在打印机前稍候';
        }
    }

    public function receiveEvent($data) {
        $fromUserName = (string)$data['FromUserName'];//用户微信token
        $toUserName = $this->_token;//(string)$data[ToUserName]微信公众号
        $eventType = (string)$data['Event'];//subscribe(订阅)、unsubscribe(取消订阅)
        $userobj = new \Admin\Model\UserModel();
        $insert['user_id'] = $fromUserName;
        $insert['user_follow'] = $eventType;
        $userInfo = $userobj->getUserByIdWeixin($insert['user_id'], $toUserName);
        if ($userInfo) {
            $userobj->where('user_id = "'.$insert['user_id'].'" and user_weixin = "'.$toUserName.'"')->setField('user_follow', $insert['user_follow']);
        } else {
            $insert['user_regdate'] = date('Y-m-d H:i:s');
            $insert['user_weixin'] = $toUserName;
            $insert['user_name'] = '普通微信用户';
            $userobj->add($insert);
        }
        return '关注成功';
    }

    private function _checkfree($data) {
        $weixin = new \Admin\Model\WeixinModel();
        $resource = new \Admin\Model\ResourceModel();
        $wxinfo = $weixin->getWeixinByToken($this->_token);
        $userResourceCount = $resource->countResourceByUserid($data['fromUserName'], $this->_token);
        if ($userResourceCount >= $wxinfo['weixin_freeprint']) {
            return false;
        } else {
            return true;
        }
    }

    public function zoomAction() {
        $uid = I('get.uid');
        $picurl = $_GET["picurl"];
        $fileSaveName = date("YmdHis").rand(1000,9999).'.jpg';
        $fileSavePath = $_SERVER['DOCUMENT_ROOT']."/upload/";
        $fileContents = file_get_contents($picurl);
        $fileResource = fopen($fileSavePath.$fileSaveName, 'a');
        fwrite($fileResource, $fileContents);
        fclose($fileResource);
        list($img_width, $img_height, $type, $attr) = getimagesize($fileSavePath.$fileSaveName);
        $sxbl = 1;
        if($img_width>300){
            $sxbl = floatval($img_width/300);
            $width = 300;
        }
        $picinfo=array("img_width"=>$img_width, "sxbl"=>$sxbl, "width"=>$width, "imagename"=>$fileSaveName, "picurl"=>$picurl);
        $this->assign('picinfo', $picinfo);
        $this->assign('uid', $uid);
        $this->display();
    }

    public function cropAction() {
        $uid = $_GET["uid"];
        $src = I('post.src');
        $x = I('post.x1');
        $y = I('post.y1');
        $cropwidth = I('post.cropwidth');
        $cropheight = I('post.cropheight');
        $sxbl = I('post.sxbl');
        $src = trim($src);
        if(!$src) die();

        //根据缩小比例计算所选区域在原图上的真实坐标及真实宽高
        $x = intval($x * $sxbl);
        $y = intval($y * $sxbl);
        $width = intval($cropwidth * $sxbl);
        $height = intval($cropheight * $sxbl);

        $fileSavePath = $_SERVER['DOCUMENT_ROOT']."/upload/";
        $imgobj = new \Think\Image();
        $imgobj = $imgobj->open($fileSavePath.$src)->crop($width, $height, $x, $y, 262, 270)->save($fileSavePath.$src);

        $resource = new \Admin\Model\ResourceModel();
        $weixin = new \Admin\Model\WeixinModel();
        $resinfo = $resource->getUserNoPrintResource($uid);
        if ($resinfo) {
            $weixinobj = $weixin->getWeixinByToken($resinfo['resource_weixin']);
            if (!$weixinobj['weixin_copyright']) {
                $copyright_img = imagecreatefromjpeg($fileSavePath.'copyright/banquan.jpg');//copyright image default 262x100
            } else {
                $copyright_img = imagecreatefromjpeg($fileSavePath.'copyright/'.$weixinobj['weixin_copyright']);
            }
            $user_img = imagecreatefromjpeg($fileSavePath.$src);
            $background = imagecreatetruecolor(262,370);
            $color = imagecolorallocate($background, 202, 201, 201);
            imagefill($background, 0, 0, $color);
            imageColorTransparent($background, $color); 
            imagecopyresized($background, $user_img, 0, 0, 0, 0, 262, 270, 262, 270);
            imagecopyresized($background, $copyright_img, 0, 271, 0, 0, 262, 100, 262, 100);
            imagejpeg($background, $fileSavePath.$src);
            imagedestroy($copyright_img);
            imagedestroy($user_img);
            imagedestroy($background);
        
            $isok = $resource->updateResourceContent($resinfo['resource_id'], 'http://'.$_SERVER['SERVER_NAME'].'/upload/'.$src);
            if ($isok) {
                echo $src;
            } else {
                echo 'error';
            }
        } else {
            echo 'error';
        }
    }
}