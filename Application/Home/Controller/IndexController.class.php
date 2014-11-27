<?php
namespace Home\Controller;

class IndexController extends BaseController {

    public function indexAction(){
        $printer_id = I('get.pid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($printer_id);
        if ($printerInfo) {
            if (!$printerInfo['printer_status']) {
                echo '设备未激活';exit;
            }
            if ($printerInfo['printer_template']) {
                $template = new \Admin\Model\TemplateModel();
                $tpl = $template->getTemplateById($printerInfo['printer_template']);
                $showpage = $tpl['template_code'];
            } else {
                $showpage = 'index';
            }
            $weixin = new \Admin\Model\WeixinModel();
            $wxinfo = $weixin->getWeixinByToken($printerInfo['printer_weixin']);

            $printcode = D('printcode');
            $code = $printcode->getCode($printerInfo['printer_code']);

            $printertpl = new \Admin\Model\PrintertplModel();
            $printer_content = $printertpl->getPrintertplById($printer_id);
            $video = array();
            $image = array();
            $word = array();
            foreach ($printer_content as $value) {
                if ($value['printertpl_type'] == 'video') {
                    $video[] = $value['printertpl_content'];
                    if (strpos($value['printertpl_content'], 'http://') === false) {
                        $object_str = '<video id="video'.$value['printertpl_num'].'" class="video-js vjs-default-skin" width="640" height="450" data-setup=\'{"controls" : false, "autoplay" : true, "preload" : "auto", "loop": true}\'>
                                                  <source src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'" type="video/x-flv"></video>';
                    } else {
                        $object_str = '<video id="video'.$value['printertpl_num'].'" class="video-js vjs-default-skin" width="640" height="450" data-setup=\'{"controls" : false, "autoplay" : true, "preload" : "auto", "loop": true}\'>
                                                  <source src="'.$value['printertpl_content'].'" type="video/x-flv"></video>';
                    }
                    $this->assign('video'.$value['printertpl_num'], $object_str);
                } elseif ($value['printertpl_type'] == 'image') {
                    $image[] = $value['printertpl_content'];
                    if (strpos($value['printertpl_content'], 'http://') === false) {
                        $this->assign('image'.$value['printertpl_num'], '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'" width="100%" height="100%">');
                    } else {
                        $this->assign('image'.$value['printertpl_num'], '<img src="'.$value['printertpl_content'].'" width="100%" height="100%">');
                    }
                } else {
                    $word[] = $value['printertpl_content'];
                    $this->assign('word'.$value['printertpl_num'], $value['printertpl_content']);
                }
            }
            $this->assign('printer', $printerInfo);
            $this->assign('imgcode', '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$wxinfo['weixin_imgcode'].'" width="100%" height="100%">');
            $this->assign('code', $code);
            $this->assign('video', $video);
            $this->assign('image', $image);
            $this->assign('word', $word);
            $this->display($showpage);
        } else {
            echo '未知设备';exit;
        }
    }
}