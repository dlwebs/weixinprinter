<?php
namespace Admin\Model;

class PrinterModel extends BaseModel {

    public function getPrinterInfo($printer_id) {
        $data['printer_id'] = $printer_id;
        $printerInfo = $this->where($data)->find();
        return $printerInfo;
    }

    public function getPrinterList($show = '', $where='') {
        if ($show == 'all') {
            $printerlist = $this->where($where)->select();
            $pageinfo = array();
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $printerlist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $printerlist, 'page' => $pageinfo);
    }

    public function getPrinterByName($printername = '') {
        return $this->where('printer_name = "'.$printername.'"')->find();
    }

    public function getPrinterByCode($printercode = '') {
        return $this->where('printer_code = "'.$printercode.'"')->find();
    }
    public function getPrinterByWeixin($printerweixin = '') {
        return $this->where('printer_weixin = "'.$printerweixin.'"')->find();
    }

    public function getPrinterById($printerid = '') {
        return $this->where('printer_id = "'.$printerid.'"')->find();
    }
    
    public function getPrinterByActiveCode($atcode = '') {
        return $this->where('printer_activecode = "'.$atcode.'"')->find();
    }
    
    public function getPrinterByTpl($tplid = '') {
        return $this->where('printer_template = "'.$tplid.'"')->select();
    }

    public function addPrinter($data = array()) {
        foreach($data as $key=>$value){
            $insert[$key] = $value;
        }
        return $this->add($insert);
    }

    public function updatePrinter($data = array()) {
        foreach($data as $key=>$value){
            if($key != "id"){
                $insert[$key] = $value;
            }
        }
        return $this->where('printer_id ="'.$data['printer_id'].'"')->save($insert);
    }

    public function updatePrinterTpl($template_id) {
        return $this->where('printer_template = "'.$template_id.'"')->setField('printer_template', 0);
    }

    public function deletePrinterById($printerid = '') {
        return $this->where('printer_id = "'.$printerid.'"')->delete();
    }

    public function deletePrinterByWx($printer_weixin = '') {
        return $this->where('printer_weixin = "'.$printer_weixin.'"')->delete();
    }
    
    public function countPrinterNumber($weixin = array()) {
        if (is_array($weixin) && count($weixin)) {
            $where['printer_weixin'] = array('in', $weixin);
        } else {
            $where['printer_weixin'] = $weixin;
        }
        $number = $this->where($where)->count();
        if ($number) {
            return $number;
        } else {
            return 0;
        }
    }
    
    public function activePrinter($activecode) {
        $printer = $this->where('printer_activecode = "'.$activecode.'"')->find();
        if (!$printer) {
            return '没有找到激活码';
        } else {
            if ($printer['printer_status']) {
                return '激活码已被使用';
            } else {
                $isok = $this->where('printer_activecode = "'.$activecode.'"')->setField('printer_status', '1');
                if ($isok) {
                    return 1;
                } else {
                    return '设备激活失败';
                }
            }
        }
    }
}