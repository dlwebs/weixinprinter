<?php
namespace Admin\Controller;

class ResourceController extends BaseController {

    public function listAction(){
        $resourceobj = D('resource');
        $searchArray = I('post.');
        $searchGetArray = I('get.');
        $condition["resource_status"] = '2';
        $condition["resource_print"] = "2";
        //$condition = " resource_status='2' AND resource_print='2' ";
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
        if(count($searchArray)){
            if($searchArray["search_weixin"]){
                $condition['weixin_name'] = array('like',"%".$searchArray['search_weixin']."%");
                $this->assign('search_weixin', $searchArray["search_weixin"]);
            }
            if($searchArray["search_printer"]){
                $condition['resource_printer'] = $searchArray['search_printer'];
                $this->assign('search_printer', $searchArray['search_printer']);
            }
        }
        if(count($searchGetArray)){
            if($searchGetArray["weixin_name"]){
                $condition['weixin_name'] = array('like',"%".urldecode($searchGetArray['weixin_name'])."%");
                $this->assign('search_weixin', urldecode($searchGetArray["weixin_name"]));
            }
            if($searchGetArray["search_printer"]){
                $condition['resource_printer'] = $searchGetArray['search_printer'];
                $this->assign('search_printer', $searchGetArray['search_printer']);
            }
        }
        //echo $user_id;
        if ($group_id != 1) {
            $wxobj = D('weixin');
            $wxdata = $wxobj->getWeixinList('all', "weixin_userid = '".$user_id."'");
            //print_r($wxdata);
            $array = $wxdata["data"];
            for($i = 0; $i < count($array); $i ++){
                $tokenArray[] = $array[$i]["weixin_token"];
            }
            if(count($tokenArray)){
                $condition['resource_weixin'] = array('in', $tokenArray);
                $resourcedata = $resourceobj->getResourceList($condition);
            }else{
                $resourcedata = "";
            }
        }else{
            $resourcedata = $resourceobj->getResourceList($condition);
        }
        //print_r($condition);
        //print_r($resourcedata);
        $this->assign('resourcelist', $resourcedata['data']);
        $this->assign('page', $resourcedata['page']);
        $this->display();
    }
    
    public function manageAction(){
        $resourceobj = D('resource');
        $searchArray = I('post.');
        $condition = " 1 ";
        $group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
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
            $this->error('您没有查看资源管理的权限!');
        }else{
            $resourcedata = $resourceobj->getResourceList($condition);
        }
        //print_r($resourcedata);
        $this->assign('resourcelist', $resourcedata['data']);
        $this->assign('page', $resourcedata['page']);
        $this->display();
    }
    public function detailAction(){
        $resource_id = I("get.resourceid");
        $resourceobj = D('resource');
        $resourceinfo = $resourceobj->getDetailById($resource_id);
        $this->assign('resourceinfo', $resourceinfo);
        $this->display();
    }
    public function verifyAction(){
        $resource_id = I("post.id");
        $resource_status = I("post.resource_status");
        $resourceobj = D('resource');
        $update["resource_id"] = $resource_id;
        $update["resource_status"] = $resource_status;
        $update["resource_checker"] = $this->userInfo['user_id'];
        $update["resource_checkdate"] = date('Y-m-d H:i:s');
        $resnumber = $resourceobj->updateResource($update);
        if ($resnumber) {
            $this->success('审核保存成功', 'manage');
        } else {
            $this->error('审核保存失败');
        }
    }
}