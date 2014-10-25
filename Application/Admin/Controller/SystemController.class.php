<?php
namespace Admin\Controller;

class SystemController extends BaseController {

    public function settingAction(){
        $systemobj = D('system');
        $systemArray = I("post.");
		$user_id = $this->userInfo['user_id'];
        if(count($systemArray)){
			$systemArray["system_user"] = $user_id;
            $systemnumber = $systemobj->updateSystem($systemArray);
        }
        $systeminfo = $systemobj->getSystemInfoByUser($user_id);
        $this->assign('systeminfo', $systeminfo);
        $this->display();
    }
}