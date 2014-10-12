<?php
namespace Admin\Controller;

class ResourceController extends BaseController {

    public function listAction(){
        $resourceobj = D('resource');
        $searchArray = I('post.');
		$condition = " resource_status='2' AND resource_print='2' ";
        if(count($searchArray)){
			if($searchArray["search_weixin"]){
				$condition = $condition." AND resource_weixin = '".$searchArray["search_weixin"]."'";
				$this->assign('search_weixin', $searchArray["search_weixin"]);
			}
			if($searchArray["search_printer"]){
				$condition = $condition." AND resource_printer = '".$searchArray["search_printer"]."'";
				$this->assign('search_printer', $searchArray['search_printer']);
			}
        }
		if ($group_id != 1) {
			$wxobj = D('weixin');
			$wxdata = $wxobj->getWeixinList('', 'weixin_userid = "'.$user_id.'"');
			$array = $wxdata["data"];
			for($i = 0; $i < count($array); $i ++){
				$tokenArray[] = $array[$i]["weixin_token"];
			}
			if(count($tokenArray)){
				$condition = $condition." AND resource_weixin in ('".implode("','", $tokenArray)."')";
				$resourcedata = $resourceobj->getResourceList('', $condition);
			}else{
				$resourcedata = "";
			}
		}else{
			$resourcedata = $resourceobj->getResourceList('', $condition);
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