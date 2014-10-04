<?php
namespace Admin\Controller;

class PrinterController extends BaseController {

    public function listAction(){
        $printerobj = D('printer');
        $searchArray = I('post.');
        if(count($searchArray)){
			$condition = " 1 ";
			if($searchArray["search_name"]){
				$condition = $condition." AND printer_name like '%".$searchArray["search_name"]."%'";
				$this->assign('search_name', $searchArray["search_name"]);
			}
			if($searchArray["search_weixin"]){
				$condition = $condition." AND printer_weixin like '%".$searchArray["search_weixin"]."%'";
				$this->assign('search_weixin', $searchArray['search_weixin']);
			}
			if($searchArray["search_type"]){
				$condition = $condition." AND printer_type = '".$searchArray["search_type"]."'";
				$this->assign('search_type', $searchArray['search_type']);
			}
        }
		if($condition){
			$printerdata = $printerobj->getPrinterList('', $condition);
		}else{
            $printerdata = $printerobj->getPrinterList();
        }
        $this->assign('printerlist', $printerdata['data']);
        $this->assign('page', $printerdata['page']);
        $this->display();
    }

    public function addAction(){
        $wxobj = D('weixin');
        $weixindata = $wxobj->getWeixinList('all');
        $this->assign('weixinlist', $weixindata['data']);
        $this->display();
    }

    public function modprinterAction(){
        $printer_id = I('get.printerid');
        $printerobj = D("printer");
        $printerinfo = $printerobj->getPrinterById($printer_id);
		$this->assign('printerinfo', $printerinfo);

		$weixin = D('weixin');
        $weixindata = $weixin->getWeixinList('all');
        $this->assign('weixinlist', $weixindata['data']);

        $this->display();
    }

    public function delprinterAction(){
        $printer_id = I('get.printerid');
        $printerobj = D("printer");
        $printerinfo = $printerobj->getPrinterById($printer_id);
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
			if (!$post['printer_name']) {
				$this->error("打印机名称不能为空");
			}
			$pninfo = $printerobj->getPrinterByName($post['printer_name']);
			if (count($pninfo) && $pninfo["printer_id"] != $post['printer_id']) {
				$this->error("打印机名称已存在");
			}
			if (!$post['printer_code']) {
				$this->error("打印机消费码不能为空");
			}
			$pcinfo = $printerobj->getPrinterByCode($post['printer_code']);
			if (count($pcinfo) && $pcinfo["printer_id"] != $post['printer_id']) {
				$this->error("打印机消费码已存在");
			}
			if(!$post['printer_weixin']){
				$this->error("公共帐号不能为空");
			}
			$pxinfo = $printerobj->getPrinterByWeixin($post['printer_weixin']);
			if (count($pxinfo) && $pxinfo["printer_id"] != $post['printer_id']) {
				$this->error("公众帐号已存在");
			}
            $printernumber = $printerobj->updatePrinter($post);
            $id = $post['id'];
        } else {
			if (!$post['printer_name']) {
				$this->error("打印机名称不能为空");
			}
			$pninfo = $printerobj->getPrinterByName($post['printer_name']);
			if ($pninfo) {
				$this->error("打印机名称已存在");
			}
			if (!$post['printer_code']) {
				$this->error("打印机消费码不能为空");
			}
			$pcinfo = $printerobj->getPrinterByCode($post['printer_code']);
			if ($pcinfo) {
				$this->error("打印机消费码已存在");
			}
			if(!$post['printer_weixin']){
				$this->error("公共帐号不能为空");
			}
			$pxinfo = $printerobj->getPrinterByWeixin($post['printer_weixin']);
			if ($pxinfo) {
				$this->error("公众帐号已存在");
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