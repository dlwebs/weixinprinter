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
                        $this->assign('image'.$value['printertpl_num'], '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'"  >');
                    } else {
                        $this->assign('image'.$value['printertpl_num'], '<img src="'.$value['printertpl_content'].'"  >');
                    }
                } else {
                    $word[] = $value['printertpl_content'];
                    $this->assign('word'.$value['printertpl_num'], $value['printertpl_content']);
                }
            }
            $this->assign('printer', $printerInfo);
            $this->assign('imgcode', '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$wxinfo['weixin_imgcode'].'" width="100%" height="100%">');
            $this->assign('code', '<span id="printercode">'.$code.'</span>');
            $this->assign('video', $video);
            $this->assign('image', $image);
            $this->assign('word', $word);
            
            $resourceobj = new \Admin\Model\ResourceModel();
            $current_image = $resourceobj->getResourceByPrinter($printerInfo['printer_code']);
            if ($current_image) {
                $this->assign('current_image', '<img width="100%" height="100%" src="'.$current_image['resource_content'].'">');
            } else {
                $this->assign('current_image', '当前无图片');
            }
            $this->display($showpage);
        } else {
            echo '未知设备';exit;
        }
    }
}
