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
                } elseif ($value['printertpl_type'] == 'image') {
                    $image[] = $value['printertpl_content'];
                } else {
                    $word[] = $value['printertpl_content'];
                }
            }

            $this->assign('printer_id', $printer_id);
            $this->assign('weixin_imgcode', $wxinfo['weixin_imgcode']);
            $this->assign('avalible_code', $code);
            $this->assign('video', $video);
            $this->assign('image', $image);
            $this->assign('word', $word);
            $this->display($showpage);
        } else {
            echo '未知设备';exit;
        }
    }
}