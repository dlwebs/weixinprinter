<?php
namespace Admin\Controller;

class ResourceController extends BaseController {

    public function listAction(){
        $resourceobj = D('resource');
        $searchArray = I('post.');
        if(count($searchArray)){
			$condition = " 1 ";
			if($searchArray["search_weixin"]){
				$condition = $condition." AND resource_weixin = '".$searchArray["search_weixin"]."'";
				$this->assign('search_weixin', $searchArray["search_weixin"]);
			}
			if($searchArray["search_printer"]){
				$condition = $condition." AND resource_printer = '".$searchArray["search_printer"]."'";
				$this->assign('search_printer', $searchArray['search_printer']);
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