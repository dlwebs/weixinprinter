<?php
namespace Admin\Model;

class ResourceModel extends BaseModel {

    public function getResourceInfo($resourceid) {
        $data['resource_id'] = $resourceid;
        $data['resource_print'] = 2;
		$data['resource_status'] = 2;
        $resourceInfo = $this->where($data)->find();
        return $resourceInfo;
    }
	 public function getResourceList($where='1') {
		$count = $this->join(' wxp_printer p on wxp_resource.resource_printer=p.printer_id')->where($where)->count();
		$page = new \Think\Page($count, 10);
		$grouplist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$pageinfo = $page->show();
        return array('data' => $grouplist, 'page' => $pageinfo);
    }
	public function updatePrinter($data = array()) {
		foreach($data as $key=>$value){
			if($key != "id"){
				$insert[$key] = $value;
			}
		}
        return $this->where('resource_id ="'.$data['resource_id'].'"')->save($insert);
    }
}