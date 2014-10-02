<?php
namespace Admin\Controller;

class SystemController extends BaseController {

    public function listAction(){
        $systemobj = D('system');
        $systemdata = $auth->getSystemList();
        $this->assign('systemlist', $systemdata['data']);
        $this->display();
    }
	public function settingAction(){
		$systemobj = D('system');
		$systemArray = I("post.");
		if(count($systemArray)){
			$systemnumber = $systemobj->updateSystem($systemArray);
		}
		$systeminfo = $systemobj->getSystemInfo();
		$this->assign('systeminfo', $systeminfo);
        $this->display();
	}
}