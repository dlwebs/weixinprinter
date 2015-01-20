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
          //  return '照片已收到，可以<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php/zoom/'.$post['fromUserName']."/?picurl=".$post['picUrl'].'">点击这里</a>对图片进行剪裁，也可直接回复消费码开始打印';

            $this->_wechat->replyNews(
                array("图片上传成功,请选择下面裁剪方式对图片进行裁剪","请先裁剪图片，然后即可打印","http://".$_SERVER['SERVER_NAME']."/index.php/zoom/".$post['fromUserName']."/?picurl=".$post['picUrl'],$post['picUrl']),
                array("普通裁剪图片","普通裁剪图片，然后即可打印","http://".$_SERVER['SERVER_NAME']."/index.php/zoom/".$post['fromUserName']."/?picurl=".$post['picUrl'],$post['picUrl']),
                array("使用高级模版生成打印图片","使用高级模版生成打印图片","http://".$_SERVER['SERVER_NAME']."/index.php/zoom2/".$post['fromUserName']."/?picurl=".$post['picUrl'],$post['picUrl']),
                array("使用带文字和音频留言机的模版打印图片","使用带文字和音频留言机的模版打印图片","http://".$_SERVER['SERVER_NAME']."/index.php/zoom3/".$post['fromUserName']."/?picurl=".$post['picUrl'].'&wxtoken='.$this->_token,$post['picUrl'])
            );
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

    public function zoom2Action() {
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
    
    public function zoom3Action() {
        $uid = I('get.uid');
        $wxtoken = $_GET["wxtoken"];
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
        $this->assign('wxtoken', $wxtoken);
        $this->assign('uid', $uid);
        
        $weixin = new \Admin\Model\WeixinModel();
        $wxinfo = $weixin->getWeixinByToken($wxtoken);
        require_once  APP_PATH."Common/Common/jssdk.php";
        $jssdk = new \JSSDK($wxinfo['weixin_appid'], $wxinfo['weixin_appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $this->assign('wxinfo', $wxinfo);
        $this->display();
    }
    public function zoom4Action() {
        $uid = I('get.uid');
        $picurl = $_GET["picurl"];

        $picinfo=array( "picurl"=>$picurl);
        $this->assign('picinfo', $picinfo);

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

    public function crop2Action() {
        $uid = $_GET["uid"];
        $x = I('post.offsetx');
        $y = I('post.offsety');
        $sxbl = I('post.sfbl');
        $backpic = I('post.temppic');
        $src = I('post.originpic');
        $newwidth = I('post.canvesw');
        $newheight = I('post.canvesh');
        $src = trim($src);
        if(!$src) die('error');

        //根据缩小比例计算所选区域在原图上的真实坐标及真实宽高
        $fileSavePath = $_SERVER['DOCUMENT_ROOT']."/upload/";
        $resizeimage = date("YmdHis").rand(100,999).'.jpg';
        list($origwidth, $origheight) = getimagesize($fileSavePath.$src);
        $width = round($origwidth * $sxbl);
        $height = round($origheight * $sxbl);
        $imagetype = array_pop(explode('.', $src));
        if ($imagetype == 'jpg') {
            $imageobj = imagecreatefromjpeg($fileSavePath.$src);
        } elseif ($imagetype == 'png') {
            $imageobj = imagecreatefrompng($fileSavePath.$src);
        } elseif ($imagetype == 'gif') {
            $imageobj = imagecreatefromgif($fileSavePath.$src);
        }
        if (!$imageobj) {
            echo 'error';exit;
        }
        $newimg = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($newimg, 255, 255, 255);
        imagefill($newimg, 0, 0, $color);
        imagecopyresampled($newimg, $imageobj, 0, 0, 0, 0, $width, $height, $origwidth, $origheight);
        if ($imagetype == 'jpg') {
            imagejpeg($newimg, $fileSavePath.$resizeimage);
        } elseif ($imagetype == 'png') {
            imagepng($newimg, $fileSavePath.$resizeimage);
        } elseif ($imagetype == 'gif') {
            imagegif($newimg, $fileSavePath.$resizeimage);
        }
        $src = $resizeimage;

        $resource = new \Admin\Model\ResourceModel();
        $weixin = new \Admin\Model\WeixinModel();
        $resinfo = $resource->getUserNoPrintResource($uid);
        if ($resinfo) {

            $saveimage = date("YmdHis").rand(10000,99999).'.jpg';
            $png = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].$backpic);
            $jpeg = imagecreatefromjpeg($fileSavePath.$src);
            $outimage = imagecreatetruecolor($newwidth, $newheight);
            if ($x < 0 && $y < 0) {
                $x = abs($x);
                $y = abs($y);
                imagecopyresampled($outimage, $jpeg, 0, 0, $x, $y, $newwidth, $newheight, $newwidth, $newheight);
            } elseif ($x >= 0 && $y >= 0) {
                imagecopyresampled($outimage, $jpeg, $x, $y, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
            } else {
                if ($x > 0) {
                    $y = abs($y);
                    imagecopyresampled($outimage, $jpeg, $x, 0, 0, $y, $newwidth, $newheight, $newwidth, $newheight);
                } elseif ($y > 0) {
                    $x = abs($x);
                    imagecopyresampled($outimage, $jpeg, 0, $y, $x, 0, $newwidth, $newheight, $newwidth, $newheight);
                }
            }
            imagecopyresampled($outimage, $png, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
            imagejpeg($outimage, $fileSavePath.$saveimage);
            imagedestroy($png);
            imagedestroy($jpeg);
            imagedestroy($outimage);
            $isok = $resource->updateResourceContent($resinfo['resource_id'], 'http://'.$_SERVER['SERVER_NAME'].'/upload/'.$saveimage);
            if ($isok) {
                echo $saveimage;
            } else {
                echo 'error';
            }
        } else {
            echo 'error';
        }
    }
    
    public function crop3Action() {
        $uid = $_GET["uid"];
        $src = I('post.src');
        $x = I('post.x1');
        $y = I('post.y1');
        $cropwidth = I('post.cropwidth');
        $cropheight = I('post.cropheight');
        $sxbl = I('post.sxbl');
        $src = trim($src);
        if(!$src) die();
        $wxtext = I('post.wxtext');
        $media_id = I('post.media_id');
        $wxtoken = I('post.wxtoken');
        $weixin = new \Admin\Model\WeixinModel();
        $wxinfo = $weixin->getWeixinByToken($wxtoken);

        //拉取音频文件
        $access_token_file = $wxinfo['weixin_appid'].'_'.$wxinfo['weixin_appsecret'].'_access_token.json';
        $access_token_data = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/upload/jssdk/'.$access_token_file));
        $access_token = $access_token_data->access_token;
        if ($access_token_data->expire_time < time()) {
            $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxinfo['weixin_appid'].'&secret='.$wxinfo['weixin_appsecret'];
            $res = json_decode(getPage($access_token_url));
            $access_token = $res->access_token;
            if ($access_token) {
                $access_token_data->expire_time = time() + 7000;
                $access_token_data->access_token = $access_token;
                $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/jssdk/'.$access_token_file, "w");
                fwrite($fp, json_encode($access_token_data));
                fclose($fp);
            }
        }
        $dl_media_url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$media_id;
        $audio_save_path = $_SERVER['DOCUMENT_ROOT'].'/upload/audio/';
        $audio_save_file = $wxtoken.'__'.$media_id;
        saveWeixinMedia($dl_media_url, $audio_save_path, $audio_save_file);
//        $weixinFileInfo = downloadWeixinFile($dl_media_url);
//        saveWeixinFile($audio_save_path.$audio_save_file.$audio_save_filetype, $weixinFileInfo["body"]);

 
        //生成音频文件地址二维码
        Vendor("phpqrcode.phpqrcode");
        $data = 'http://'.$_SERVER['SERVER_NAME'].'/index.php/playaudio/'.$audio_save_file;
        $level = 'Q';
        $size = 4;
        $fileName = $wxtoken.$level.$size.'_'.date('YmdHis').'.png';
        \QRcode::png($data, $_SERVER['DOCUMENT_ROOT'].'/upload/qrcode/'.$fileName, $level, $size);

 
        //resize音频二维码到100x100的大小
        $qrcode_dir = $_SERVER['DOCUMENT_ROOT'].'/upload/qrcode/';
        $qrcode_resize_name = $wxtoken.$level.$size.'_'.date('YmdHis').'_resize.png';
        list($origwidth, $origheight) = getimagesize($qrcode_dir.$fileName);
        $imageobj = imagecreatefrompng($qrcode_dir.$fileName);
        $newimg = imagecreatetruecolor(100, 100);
        $color = imagecolorallocate($newimg, 255, 255, 255);
        imagefill($newimg, 0, 0, $color);
        imagecopyresampled($newimg, $imageobj, 0, 0, 0, 0, 100, 100, $origwidth, $origheight);
        imagepng($newimg, $qrcode_dir.$qrcode_resize_name);
        $audio_resize_qrcode = imagecreatefrompng($qrcode_dir.$qrcode_resize_name);


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
//            if (!$weixinobj['weixin_copyright']) {
//                $copyright_img = imagecreatefrompng($fileSavePath.'copyright/minilogo.png');//copyright image default 162x100
//            } else {
//                $copyright_img = imagecreatefromjpeg($fileSavePath.'copyright/'.$weixinobj['weixin_minicopyright']);
//            }
            if (!$wxtext) {
                $wxtext = '微谷云微信打印机';
            }
            $text_image_file = $fileSavePath.'copyright/'.$resinfo['resource_weixin'].'_'.date('YmdHis').'_text.png';
            $copyright_img = imagecreatetruecolor(162,100);
            $color = imagecolorallocate($copyright_img, 255, 255, 255);
            imagefill($copyright_img, 0, 0, $color);
            imagepng($copyright_img, $text_image_file);
            $imgobj = new \Think\Image();
            $imgobj = $imgobj->open($text_image_file)->text($wxtext, '/usr/share/fonts/truetype/XHei_Ubuntu.ttc', 12, '#000000', \Think\Image::IMAGE_WATER_WEST)->save($text_image_file);
            $copyright_img = imagecreatefrompng($text_image_file);


            $user_img = imagecreatefromjpeg($fileSavePath.$src);
            $background = imagecreatetruecolor(262,370);
            $color = imagecolorallocate($background, 202, 201, 201);
            imagefill($background, 0, 0, $color);
            imageColorTransparent($background, $color); 
            imagecopyresized($background, $user_img, 0, 0, 0, 0, 262, 270, 262, 270);
            imagecopyresized($background, $copyright_img, 100, 271, 0, 0, 162, 100, 162, 100);
            imagecopyresized($background, $audio_resize_qrcode, 0, 271, 0, 0, 100, 100, 100, 100);
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

    public function playaudioAction() {
        $audio = I('get.audio');
        $audioinfo = explode('__', $audio);

        $weixin = new \Admin\Model\WeixinModel();
        $wxinfo = $weixin->getWeixinByToken($audioinfo[0]);
        require_once  APP_PATH."Common/Common/jssdk.php";
        $jssdk = new \JSSDK($wxinfo['weixin_appid'], $wxinfo['weixin_appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $this->assign('wxinfo', $wxinfo);
        $this->assign('media_id', $audioinfo[1]);
        $this->display();
    }
}