<?php
namespace Admin\Model;

class PrinterModel extends BaseModel {

    public function getPrinterInfo($printer_id) {
        $data['printer_id'] = $group_id;
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

    public function deletePrinterById($printerid = '') {
        return $this->where('printer_id = "'.$printerid.'"')->delete();
    }
}