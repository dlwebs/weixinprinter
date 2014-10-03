<?php
namespace Admin\Controller;

class ResourceController extends BaseController {

    public function listAction(){
        $resourceobj = D('resource');
        $searchArray = I('post.');
        if(count($searchArray)){
			$condition = " 1 ";
			if($searchArray["search_type"]){
				$condition = $condition." AND resource_type = '".$searchArray["search_type"]."'";
				$this->assign('search_type', $searchArray["search_type"]);
			}
			if($searchArray["search_print"]){
				$condition = $condition." AND resource_print = '".$searchArray["search_print"]."'";
				$this->assign('search_print', $searchArray['search_print']);
			}
			if($searchArray["search_status"]){
				$condition = $condition." AND resource_status = '".$searchArray["search_status"]."'";
				$this->assign('search_status', $searchArray['search_status']);
			}
			if($searchArray["search_user"]){
				$condition = $condition." AND resource_user like '%".$searchArray["search_user"]."%'";
				$this->assign('search_user', $searchArray['search_user']);
			}
        }
		if($condition){
			$resourcedata = $resourceobj->getResourceList($condition);
		}else{
            $resourcedata = $resourceobj->getResourceList();
        }
        $this->assign('resourcelist', $resourcedata['data']);
        $this->assign('page', $resourcedata['page']);
        $this->display();
    }

    public function detailAction(){
		$resource_id = I("get.resourceid");
        $resourceobj = D('resource');
        $resourceinfo = $resourceobj->getResourceInfo($resource_id);
        $this->assign('resourceinfo', $resourceinfo);
        $this->display();
    }
}