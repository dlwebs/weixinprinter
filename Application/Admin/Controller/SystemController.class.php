<?php
namespace Admin\Controller;

class SystemController extends BaseController {

    public function settingAction(){
        $systemobj = D('system');
        $systemArray = I("post.");
		$user_id = $this->userInfo['user_id'];
		$group_id = $this->userInfo['group_id'];
        if(count($systemArray)){
			if ($group_id != 1) {
				$systemArray["system_user"] = $user_id;
			}else{
				if (!trim($systemArray['system_user'])) {
					$this->error("请选择用户");
				}
			}
            $systemnumber = $systemobj->updateSystem($systemArray);
        }
		$userobj = D('user');
		$userdata = $userobj->getUserList($condition);
        $this->assign('userlist', $userdata['data']);

        $systeminfo = $systemobj->getSystemInfoByUser($user_id);
        $this->assign('systeminfo', $systeminfo);
        $this->display();
    }
}