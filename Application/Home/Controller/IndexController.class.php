<?php
namespace Home\Controller;

class IndexController extends BaseController {

    public function indexAction(){
        $printer_id = I('get.pid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($printer_id);
        if ($printerInfo) {
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
                    $this->assign('video'.$value['printertpl_num'], '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" 
                                                                                                                        codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" height="120" width="190"> 
                                                                                                                        <param name="movie" 
                                                                                                                        value="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'"> 
                                                                                                                        <param name="quality" value="high"> 
                                                                                                                        <param name="allowFullScreen" value="true" /> 
                                                                                                                        <embed 
                                                                                                                        src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'" 
                                                                                                                        quality="high" 
                                                                                                                        pluginspage="http://www.macromedia.com/go/getflashplayer" 
                                                                                                                        type="application/x-shockwave-flash" width="320" height="240"> 
                                                                                                                        </embed> 
                                                                                                                    </object>');
                } elseif ($value['printertpl_type'] == 'image') {
                    $image[] = $value['printertpl_content'];
                    $this->assign('image'.$value['printertpl_num'], '<img src="http://'.$_SERVER['SERVER_NAME'].'/upload/'.$value['printertpl_content'].'" width="100%" height="100%">');
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