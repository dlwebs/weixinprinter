<?php
namespace Home\Controller;

use Think\Controller\RestController;

class ServiceController extends RestController {

    public function regprinter_post(){
        $activecode = I('post.activecode');
        $printobj = new \Admin\Model\PrinterModel();
        $result = $printobj->activePrinter($activecode);
        if ($result == 1) {
            $this->response(array('message'=>'打印机激活成功'), 'json');
        } else {
            $this->response(array('message'=>$result), 'json');
        }
    }

    public function getimage_get(){
        $printer_id = I('get.pid');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterInfo($printer_id);
        if ($printerInfo) {
            $resourceobj = new \Admin\Model\ResourceModel();
            $resourceinfo = $resourceobj->getResourceByPrinter($printerInfo['printer_code']);
            $this->response($resourceinfo, 'json');
        } else {
            $this->response(array('message'=>'未知打印机'), 'json');
        }
    }

    public function upres_put() {
        $resource_id = I('put.rid');
        $resourceobj = new \Admin\Model\ResourceModel();
        $isok = $resourceobj->updateResourcePrintStatus($resource_id, '2');
        if ($isok) {
            $this->response(array('message'=>'更新成功'), 'json');
        } else {
            $this->response(array('message'=>'更新失败'), 'json');
        }
    }
}