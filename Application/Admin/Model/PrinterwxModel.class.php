<?php
namespace Admin\Model;

class PrinterwxModel extends BaseModel {
    
    public function countPrinterNumber($weixin = array()) {
        if (is_array($weixin) && count($weixin)) {
            $weixinwhere = implode('","', $weixin);
            $where = 'printerwx_weixin in ("'.$weixinwhere.'")';
        } else {
            $where = 'printerwx_weixin = "'.$weixin.'"';
        }
        $number = $this->query('select  count( DISTINCT (printerwx_printer) ) as printernum from wxp_printerwx where '.$where);
        if (isset($number[0]['printernum'])) {
            return $number[0]['printernum'];
        } else {
            return 0;
        }
    }
    
    public function getPrinterByWx($weixin = array()) {
        if (is_array($weixin) && count($weixin)) {
            $where['printerwx_weixin'] = array('in', $weixin);
        } else {
            $where['printerwx_weixin'] = $weixin;
        }
        return $this->distinct(true)->field('printerwx_printer')->where($where)->select();
    }
    
    public function getWeixinByPrinter($pid = array()) {
        if (is_array($pid) && count($pid)) {
            $where['printerwx_printer'] = array('in', $pid);
        } else {
            $where['printerwx_printer'] = $pid;
        }
        return $this->distinct(true)->field('printerwx_weixin')->where($where)->select();
    }
    
    public function getPrinterWxInfoByCondition($where) {
        $printerwxInfo = $this->where($where)->select();
        return $printerwxInfo;
    }
    
    public function getPrinterWxInfoByWeixin($data) {
        if($data["printerwx_printer"]){
            $where["printerwx_printer"] = $data["printerwx_printer"];
        }
        if($data["printerwx_weixin"]){
            $where["printerwx_weixin"] = $data["printerwx_weixin"];
        }
        $printerwxInfo = $this->where($where)->select();
        return $printerwxInfo;
    }    
    
    public function getPrinterWxInfoById($printerwx_id) {
        $data['printerwx_id'] = $printerwx_id;
        $printerwxInfo = $this->where($data)->find();
        return $printerwxInfo;
    }
    
    public function delPrinterWx($data = array()){
        return $this->where($data)->delete();
    }
    
    public function addPrinterWx($data = array()) {
        foreach ($data as $key => $value) {
            $insert[$key] = $value;
        }
        return $this->add($insert);
    }
    
    public function updatePrinterWx($data = array()) {
        foreach ($data as $key => $value) {
            $insert[$key] = $value;
        }
        if (count($insert)) {
            $array = $this->getPrinterWxInfoByWeixin($insert);
            if ($array[0]["printerwx_id"]) {
                return $this->where('printerwx_id = "' . $array[0]["printerwx_id"] . '"')->save($insert);
            } else {
                $this->addPrinterWx($insert);
            }
        }
    }
}