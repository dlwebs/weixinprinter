<?php
namespace Home\Controller;

class IndexController extends BaseController {

    public function indexAction(){
        $printer_atcode = I('get.atcode');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterByActiveCode($printer_atcode);
        if ($printerInfo) {
            if (!$printerInfo['printer_status']) {
                echo '设备未激活';exit;
            }
            $printer_id = $printerInfo['printer_id'];
            if ($printerInfo['printer_template']) {
                $template = new \Admin\Model\TemplateModel();
                $tpl = $template->getTemplateById($printerInfo['printer_template']);
                $showpage = $tpl['template_code'];
            } else {
                $showpage = 'index';
            }
            $printerwxobj = new \Admin\Model\PrinterwxModel();
            $printerwx = $printerwxobj->getWeixinByPrinter($printer_id);
            $weixin = new \Admin\Model\WeixinModel();
            $weixinlist = array();
            foreach ($printerwx as $key => $pwx) {
                $qrcodenum = $key + 1;
                $wxinfo = $weixin->getWeixinByToken($pwx['printerwx_weixin']);
                $weixinlist[] = array('imgcode'=>'http://'.$_SERVER['SERVER_NAME'].'/upload/'.$wxinfo['weixin_imgcode'], 'name'=>$wxinfo['weixin_name']);
//                $this->assign('qrcode'.$qrcodenum, '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$wxinfo['weixin_imgcode'].'" alt="'.$wxinfo['weixin_name'].'">');
            }
            if (count($weixinlist)) {
                $this->assign('qrcode', json_encode($weixinlist));
            } else {
                $this->assign('qrcode', '');
            }

            $printcode = D('printcode');
            $code = $printcode->getCode($printerInfo['printer_code']);

            $printertpl = new \Admin\Model\PrintertplModel();
            $printer_content = $printertpl->getPrintertplById($printer_id);
            foreach ($printer_content as $value) {
                if ($value['printertpl_type'] == 'video') {
                    $source_type = 'video/x-flv';
                    $video_ext = array_pop(explode('.', $value['printertpl_content']));
                    if ($video_ext == 'mp4') {
                        $source_type = 'video/mp4';
                    } elseif ($video_ext == 'ogg') {
                        $source_type = 'video/ogg';
                    } elseif ($video_ext == 'webm') {
                        $source_type = 'video/webm';
                    }
                    if (strpos($value['printertpl_content'], 'http://') === false) {
                        $object_str = '<video id="video'.$value['printertpl_num'].'" class="video-js vjs-default-skin" width="100%" height="100%" data-setup=\'{"controls" : false, "autoplay" : true, "preload" : "auto", "loop": true}\'>
                                       <source src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'" type="'.$source_type.'"></video>';
                    } else {
                        $object_str = '<video id="video'.$value['printertpl_num'].'" class="video-js vjs-default-skin" width="100%" height="100%"  data-setup=\'{"controls" : false, "autoplay" : true, "preload" : "auto", "loop": true}\'>
                                       <source src="'.$value['printertpl_content'].'" type="'.$source_type.'"></video>';
                    }
                    $this->assign('video'.$value['printertpl_num'], $object_str);
                } elseif ($value['printertpl_type'] == 'image') {
                    if (strpos($value['printertpl_content'], 'http://') === false) {
                        $this->assign('image'.$value['printertpl_num'], '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'">');
                    } else {
                        $this->assign('image'.$value['printertpl_num'], '<img src="'.$value['printertpl_content'].'">');
                    }
                } else {
                    $this->assign('word'.$value['printertpl_num'], $value['printertpl_content']);
                }
            }
            $this->assign('printer_name', $printerInfo['printer_name']);
            $this->assign('atcode', $printer_atcode);
            $this->assign('code', '<span id="printercode">'.$code.'</span>');
            
            $resourceobj = new \Admin\Model\ResourceModel();
            $current_image = $resourceobj->getResourceByPrinter($printerInfo['printer_code']);
            if ($current_image) {
                $this->assign('current_image', '<img id="current_image" src="'.$current_image['resource_content'].'">');
            } else {
                $this->assign('current_image', '<img id="current_image" src="" alt="暂无图片打印">');
            }
            $this->display($showpage);
        } else {
            echo '未知设备';exit;
        }
    }
}
