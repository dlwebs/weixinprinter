<?php
namespace Home\Controller;

use Think\Controller\RestController;

class ServiceController extends RestController {

    public function regprinterAction_get(){
        $activecode = I('get.activecode');
        $printobj = new \Admin\Model\PrinterModel();
        $result = $printobj->activePrinter($activecode);
        if ($result == 1) {
            $this->response(array('message'=>$activecode, 'msgcode'=>1), 'json');
        } else {
            $this->response(array('message'=>$activecode, 'msgcode'=>0), 'json');
        }
    }

    public function getimageAction_get(){
        $activecode = I('get.activecode');
        $printobj = new \Admin\Model\PrinterModel();
        $printerInfo = $printobj->getPrinterByActiveCode($activecode);
        if ($printerInfo) {
            $resourceobj = new \Admin\Model\ResourceModel();
            $resourceinfo = $resourceobj->getResourceByPrinter($printerInfo['printer_code']);
            $this->response($resourceinfo, 'json');
        } else {
            $this->response(array('message'=>'未知打印机'), 'json');
        }
    }

    public function upresAction_get() {
        $print_result = I('get.result');
        if ($print_result) {
            $resource_id = I('get.rid');
            $resourceobj = new \Admin\Model\ResourceModel();
            $isok = $resourceobj->updateResourcePrintStatus($resource_id, '2');
            if ($isok) {
                $this->response(array('message'=>'更新成功'), 'json');
            } else {
                $this->response(array('message'=>'更新失败'), 'json');
            }
        } else {
            $this->response(array('message'=>'打印失败'), 'json');
        }
    }
}