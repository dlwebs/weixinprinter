<?php
namespace Admin\Controller;

class UserController extends BaseController {

    public function listAction(){
        $userobj = D('user');
		$condition = " id != 1 ";
		$edu_id = I('post.edu_id');
		if($edu_id){
			$condition = $condition." and user_id='".$edu_id."'";
			$this->assign('edu_id', $edu_id);
		}
		$edu_name = I('post.edu_name');
		if($edu_name){
			$condition = $condition." and user_name like '%".$edu_name."%'";
			$this->assign('edu_name', $edu_name);
		}
		$group_id = I('post.group_id');
		if($group_id){
			$usergroup = M('usergroup');
			$usergroupArray = $usergroup->where('gid = "'.$group_id.'"')->getField('uid', true);
			if(count($usergroupArray)){
				$condition = $condition." and user_id in (".implode(",", $usergroupArray).")";
			}
			$this->assign('group_id', $group_id);
		}
		$userdata = $userobj->getUserList($condition);
        $this->assign('userlist', $userdata['data']);
        $this->assign('page', $userdata['page']);

        $groupobj = D('group');
        $grouplist = $groupobj->getGroupList('all');
        $this->assign('grouplist', $grouplist['data']);
        $this->display();
    }

    public function addAction(){
        $groupobj = D('group');
        $grouplist = $groupobj->getGroupList('all');
        $this->assign('grouplist', $grouplist['data']);
        $this->display();
    }

    public function moduserAction(){
        $user_id = I('get.userid');
        $userobj = D("user");
        $userinfo = $userobj->getUserById($user_id);
        $this->assign('userinfo', $userinfo);

        $usergroup = M('usergroup');
        $groupid = $usergroup->where('uid = "'.$user_id.'"')->find();
        $this->assign('group_id', $groupid['gid']);

        $groupobj = D('group');
        $grouplist = $groupobj->getGroupList('all');
        $this->assign('grouplist', $grouplist['data']);
        $this->display();
    }

    public function deluserAction(){
        $user_id = I('get.userid');
        $userobj = D("user");
        $userinfo = $userobj->getUserById($user_id);
        if ($userinfo) {
            $isok = $userobj->deleteUserById($user_id);
            if ($isok) {
                $usergroup = M('usergroup');
                $usergroup->where('uid="'.$user_id.'"')->delete();
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
        $this->error('无此用户');
    }

    public function saveAction() {
        $post = filterAllParam('post');
        $userobj = D('user');
        $usergroup = M('usergroup');
        if (isset($post['id']) && $post['id']) {
            $usernumber = $userobj->updateUser($post);
            $deletenumber = $usergroup->where('uid="'.$post['user_id'].'" and gid = "'.$post['group_id'].'"')->delete();
            $id = $post['id'];
        } else {
            if (!$post['user_id']) {
                $this->error("用户名不能为空");
            }
            if (!$post['user_pw']) {
                $this->error("密码不能为空");
            }
            $userinfo = $userobj->getUserById($post['user_id']);
            if ($userinfo) {
                $this->error("用户ID已存在");
            }
            $id = $userobj->addUser($post);
        }
        if ($id && $post['group_id']) {
            $usergroup->add(array('uid'=>$post['user_id'], 'gid'=>$post['group_id']));
        }
        if ($id) {
            $this->success('保存成功', 'list');
        } else {
            $this->error('保存失败');
        }
    }
}