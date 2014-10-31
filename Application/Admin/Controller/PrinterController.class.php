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
	public function gettemplatenumAction(){
		$templateId = I('get.templateid');
		$template = D('Template');
		$printertplobj = D('printertpl');
		if(!$templateId){
			$tplinfo["template_video"] = "1";
			$tplinfo["template_image"] = "5";
			$tplinfo["template_word"] = "1";
		}else{
			$tplinfo = $template->getTemplateById($templateId);

		}
		$printer_id = I('get.printer_id');
		if($printer_id){
			$printerobj = D("printer");
			$printerinfo = $printerobj->getPrinterById($printer_id);
			$tplArray = $printertplobj->getPrintertplById($printer_id);
			$resultArray = array();
			for($i = 0; $i < count($tplArray); $i ++){
				if($printerinfo['printer_template'] == $templateId){
					$resultArray[$tplArray[$i]['printertpl_type'].$tplArray[$i]['printertpl_num']] = $tplArray[$i]['printertpl_content'];
				}
			}
			$tplinfo['tplObj'] = $resultArray;		
		}
		echo json_encode($tplinfo);
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
        $template = D('Template');
        $tpldata = $template->getTemplateList();
        $this->assign('tpllist', $tpldata['data']);

        //$weixindata = $wxobj->getWeixinList('all');
        $this->assign('weixinlist', $weixindata['data']);
        $this->display();
    }

    public function modprinterAction(){
        $printer_id = I('get.printerid');
        $printerobj = D("printer");
        $printerinfo = $printerobj->getPrinterById($printer_id);
        $this->assign('printerinfo', $printerinfo);
        
        $template = D('Template');
        $tpldata = $template->getTemplateList();
        $this->assign('tpllist', $tpldata['data']);

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
		$printertplobj = D('printertpl');
		$updatePrintArray = array();
		
        if (isset($post['id']) && $post['id']) {
			//get old info
			$poldinfo = $printerobj->getPrinterByCode($post['printer_id']);
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
			$updatePrintArray["printer_id"] = $post["printer_id"];
			$updatePrintArray["printer_name"] = $post["printer_name"];
			$updatePrintArray["printer_code"] = $post["printer_code"];
			$updatePrintArray["printer_type"] = $post["printer_type"];
			$updatePrintArray["printer_weixin"] = $post["printer_weixin"];
			$updatePrintArray["printer_template"] = $post["printer_template"];
            $printernumber = $printerobj->updatePrinter($updatePrintArray);
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
			$updatePrintArray["printer_name"] = $post["printer_name"];
			$updatePrintArray["printer_code"] = $post["printer_code"];
			$updatePrintArray["printer_type"] = $post["printer_type"];
			$updatePrintArray["printer_weixin"] = $post["printer_weixin"];
			$updatePrintArray["printer_template"] = $post["printer_template"];
            $id = $printerobj->addPrinter($updatePrintArray);
            if ($id) {
                $printcode = new \Home\Model\PrintcodeModel();
                $code_number = $printcode->createCode($post['printer_code']);
            }
        }
		//处理模板里的设定
		if($id){
			$printer_template = $post["printer_template"];
			if(count($poldinfo) && $poldinfo["printer_template"] != $post["printer_template"]){
				$printertplobj->delPrintertplByPrinterId($id);
			}
			if($printer_template){
				$templateObj = D('template');
				$tempInfo = $templateObj->getTemplateById($printer_template);
			}else{
				$tempInfo["template_video"] = "1";
				$tempInfo["template_image"] = "5";
				$tempInfo["template_word"] = "1";
			}
			for($i = 1; $i <= $tempInfo["template_video"]; $i ++){
				$insertArray = array();
				$tplArray = array();
				$tplArray = $printertplobj->getPrintertplByCondition("printertpl_type='video' and printertpl_num=".$i." and printertpl_printer=".$id);
				if($post["video".$i] == "file"){
					if ($_FILES['video'.$i.'_file']['name']) {
						$upload = new \Think\Upload();
						$upload->maxSize = 3145728;//3M
						$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
						$upload->rootPath = './upload/';
						$uploadinfo = $upload->uploadOne($_FILES['video'.$i.'_file']);
						if(!$uploadinfo) {
							$this->error($upload->getError());
						}
						$insertArray['printertpl_content'] = $uploadinfo['savepath'].$uploadinfo['savename'];
					}
				}elseif($post["video".$i] == "text"){
					$insertArray['printertpl_content'] = $post["video".$i."_text"];
				}
				if($insertArray['printertpl_content']){
					$insertArray['printertpl_num'] = $i;
					$insertArray['printertpl_printer'] = $id;
					$insertArray['printertpl_type'] = 'video';
					if(count($tplArray)){
						if($tplArray[0]["printertpl_content"] && substr($tplArray[0]["printertpl_content"], 0,4) != "http"){
							unlink("./upload/".$tplArray[0]["printertpl_content"]);
						}
						$insertArray['printertpl_id'] = $tplArray[0]['printertpl_id'];
						$printertplid = $printertplobj->updatePrintertpl($insertArray);
					}else{
						$printertplid = $printertplobj->addPrintertpl($insertArray);
					}
				}
			}
			for($i = 1; $i <= $tempInfo["template_image"]; $i ++){
				$insertArray = array();
				$tplArray = array();
				$tplArray = $printertplobj->getPrintertplByCondition("printertpl_type='image' and printertpl_num=".$i." and printertpl_printer=".$id);
				if($post["image".$i] == "file"){
					if ($_FILES['image'.$i.'_file']['name']) {
						$upload = new \Think\Upload();
						$upload->maxSize = 3145728;//3M
						$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
						$upload->rootPath = './upload/';
						$uploadinfo = $upload->uploadOne($_FILES['image'.$i.'_file']);
						if(!$uploadinfo) {
							$this->error($upload->getError());
						}
						$insertArray['printertpl_content'] = $uploadinfo['savepath'].$uploadinfo['savename'];
					}
				}elseif($post["image".$i] == "text"){
					$insertArray['printertpl_content'] = $post["image".$i."_text"];
				}
				if($insertArray['printertpl_content']){
					$insertArray['printertpl_num'] = $i;
					$insertArray['printertpl_printer'] = $id;
					$insertArray['printertpl_type'] = 'image';
					if(count($tplArray)){
						if($tplArray[0]["printertpl_content"] && substr($tplArray[0]["printertpl_content"], 0,4) != "http"){
							unlink("./upload/".$tplArray[0]["printertpl_content"]);
						}
						$insertArray['printertpl_id'] = $tplArray[0]['printertpl_id'];
						$printertplid = $printertplobj->updatePrintertpl($insertArray);
					}else{
						$printertplid = $printertplobj->addPrintertpl($insertArray);
					}
				}
			}
			for($i = 1; $i <= $tempInfo["template_word"]; $i ++){
				$insertArray = array();
				$tplArray = array();
				$tplArray = $printertplobj->getPrintertplByCondition("printertpl_type='word' and printertpl_num=".$i." and printertpl_printer=".$id);
				$insertArray['printertpl_content'] = $post["ads".$i."_text"];
				if($insertArray['printertpl_content']){
					$insertArray['printertpl_num'] = $i;
					$insertArray['printertpl_printer'] = $id;
					$insertArray['printertpl_type'] = 'word';
					if(count($tplArray)){
						$insertArray['printertpl_id'] = $tplArray[0]['printertpl_id'];
						$printertplid = $printertplobj->updatePrintertpl($insertArray);
					}else{
						$printertplid = $printertplobj->addPrintertpl($insertArray);
					}
				}
			}
		}
        if ($id) {
            $this->success('保存成功', 'list');
        } else {
            $this->error('保存失败');
        }
    }
}