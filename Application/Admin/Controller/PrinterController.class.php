<?php
namespace Admin\Controller;

class PrinterController extends BaseController {

    public function listAction(){
        $printerobj = D('printer');
        $searchArray = I('post.');
		$group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
		$condition = " 1 ";
        if(count($searchArray)){
            if($searchArray["search_name"]){
                $condition = $condition." AND printer_name like '%".$searchArray["search_name"]."%'";
                $this->assign('search_name', $searchArray["search_name"]);
            }
            if($searchArray["search_weixin"]){
                $condition = $condition." AND printer_weixin = '".$searchArray["search_weixin"]."'";
                $this->assign('search_weixin', $searchArray['search_weixin']);
            }
            if($searchArray["search_type"]){
                $condition = $condition." AND printer_type = '".$searchArray["search_type"]."'";
                $this->assign('search_type', $searchArray['search_type']);
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
				$condition = $condition." AND printer_weixin in ('".implode("','", $tokenArray)."')";
				$printerdata = $printerobj->getPrinterList('', $condition);
			}else{
				$printerdata = "";
			}
		}else{
			$printerdata = $printerobj->getPrinterList('', $condition);
		}
        $this->assign('printerlist', $printerdata['data']);
        $this->assign('page', $printerdata['page']);
        $this->display();
    }

    public function addAction(){
        $wxobj = D('weixin');
		$group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
		if ($group_id == 1) {
            $weixindata = $wxobj->getWeixinList();
        } else {
            $weixindata = $wxobj->getWeixinList('', 'weixin_userid = "'.$user_id.'"');
        }
        //$weixindata = $wxobj->getWeixinList('all');
        $this->assign('weixinlist', $weixindata['data']);
        $this->display();
    }

    public function modprinterAction(){
        $printer_id = I('get.printerid');
        $printerobj = D("printer");
        $printerinfo = $printerobj->getPrinterById($printer_id);
        $this->assign('printerinfo', $printerinfo);

        $weixin = D('weixin');
		$group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
		if ($group_id == 1) {
            $weixindata = $weixin->getWeixinList();
        } else {
            $weixindata = $weixin->getWeixinList('', 'weixin_userid = "'.$user_id.'"');
			$array = $weixindata["data"];
			for($i = 0; $i < count($array); $i ++){
				$tokenArray[] = $array[$i]["weixin_token"];
			}
			if(!in_array($printerinfo["printer_weixin"], $tokenArray)){
				$this->error('您没有修改此设备的权限!');
			}
        }
        //$weixindata = $weixin->getWeixinList('all');
        $this->assign('weixinlist', $weixindata['data']);

        $this->display();
    }

    public function delprinterAction(){
        $printer_id = I('get.printerid');
        $printerobj = D("printer");
        $printerinfo = $printerobj->getPrinterById($printer_id);

		$weixin = D('weixin');
		$group_id = $this->userInfo['group_id'];
        $user_id = $this->userInfo['user_id'];
		if ($group_id != 1) {
            $weixindata = $weixin->getWeixinList('', 'weixin_userid = "'.$user_id.'"');
			$array = $weixindata["data"];
			for($i = 0; $i < count($array); $i ++){
				$tokenArray[] = $array[$i]["weixin_token"];
			}
			if(!in_array($printerinfo["printer_weixin"], $tokenArray)){
				$this->error('您没有删除此设备的权限!');
			}
        }

        if ($printerinfo) {
            $isok = $printerobj->deletePrinterById($printer_id);
            if ($isok) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
        $this->error('无此打印机信息');
    }

    public function saveAction() {
        $post = I('post.');
        $printerobj = D('printer');
        if (isset($post['id']) && $post['id']) {
            if (!trim($post['printer_name'])) {
                $this->error("打印机名称不能为空");
            }
            if(strlen(trim($post['printer_code'])) != 3 || !preg_match ("/^[A-Za-z]/",  trim($post['printer_code']))){
                $this->error("打印机消费码不能为空");
            }
            $pcinfo = $printerobj->getPrinterByCode($post['printer_code']);
            if (count($pcinfo) && $pcinfo["printer_id"] != $post['printer_id']) {
                $this->error("打印机消费码已存在");
            }
            if(!$post['printer_weixin']){
                $this->error("公共帐号不能为空");
            }
            $printernumber = $printerobj->updatePrinter($post);
            $id = $post['id'];
        } else {
            if (!trim($post['printer_name'])) {
                $this->error("打印机名称不能为空");
            }
            if (!trim($post['printer_code'])) {
                $this->error("打印机消费码不能为空");
            }
			if(strlen(trim($post['printer_code'])) != 3 || !preg_match ("/^[A-Za-z]/",  trim($post['printer_code']))){
				$this->error("打印机消费码只能是三位");
			}
            $pcinfo = $printerobj->getPrinterByCode($post['printer_code']);
            if ($pcinfo) {
                $this->error("打印机消费码已存在");
            }
            if(!$post['printer_weixin']){
                $this->error("公共帐号不能为空");
            }
            $id = $printerobj->addPrinter($post);
        }
        if ($id) {
            $this->success('保存成功', 'list');
        } else {
            $this->error('保存失败');
        }
    }
}