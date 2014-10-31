<?php
namespace Admin\Controller;

class SystemController extends BaseController {

    public function settingAction(){
        $systemobj = D('system');
		$userId = I("get.userid");
        $systemArray = I("post.");
		$userobj = D('user');

		$user_id = $this->userInfo['user_id'];
		$group_id = $this->userInfo['group_id'];

		if($userId){
			$user_id = $userId;
			$this->assign('userId', $userId);
		}
        if(count($systemArray)){
			if ($group_id != 1) {
				$systemArray["system_user"] = $user_id;
				$userinfo = $userobj->getUserById($user_id);
				if($systemArray['user_pw']){
					$userinfo['user_pw'] = $systemArray['user_pw'];
					$usernumber = $userobj->updateUser($userinfo);
				}
				unset($systemArray['user_pw']);
			}else{
				if (!trim($systemArray['system_user'])) {
					$this->error("请选择用户");
				}
			}
            $systemnumber = $systemobj->updateSystem($systemArray);
			if($group_id == 1){
				$this->success('修改成功', 'list');
			}else{
				$this->success('修改成功', 'setting');
			}
			exit;
        }
		
		$userdata = $userobj->getUserGroupList("id != 1 and gid>0");
        $this->assign('userlist', $userdata['data']);
		
        $systeminfo = $systemobj->getSystemInfoByUser($user_id);
        $this->assign('systeminfo', $systeminfo);
        $this->display();
    }
	public function listAction(){
		$systemobj = D('system');
		$systemArray = I("post.");
		$userobj = D('user');
		$user_id = $this->userInfo['user_id'];
		$group_id = $this->userInfo['group_id'];
		if($group_id != 1){
			$this->error('您没有此操作权限');
		}
		$edu_id = I('post.edu_id');
		$condition = " id != 1 ";
        if($edu_id){
            $condition = $condition." and user_id like '%".$edu_id."%'";
            $this->assign('edu_id', $edu_id);
        }

		$userdata = $userobj->getUserList($condition);
        $this->assign('userlist', $userdata['data']);

		$this->display();
	}
}