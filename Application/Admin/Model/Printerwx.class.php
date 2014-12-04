<?php
namespace Admin\Model;

class PrinterwxModel extends BaseModel {
    
    public function countPrinterNumber($weixin = array()) {
        if (is_array($weixin) && count($weixin)) {
            $where['printerwx_weixin'] = array('in', $weixin);
        } else {
            $where['printerwx_weixin'] = $weixin;
        }
        $number = $this->distinct(true)->field('printerwx_printer')->where($where)->count();
        if ($number) {
            return $number;
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
}