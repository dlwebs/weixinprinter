<?php
namespace Admin\Controller;

class UserController extends BaseController {

    public function listAction(){
        $userobj = D('user');
        $userdata = $userobj->getUserList();
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
                $this->success('删除成功', 'user/list');
            } else {
                $this->error('删除失败', 'user/list');
            }
        }
        $this->error('无此用户', 'user/list');
    }

    public function saveAction() {
        $post = filterAllParam('post');
        $userobj = D('user');
        $usergroup = M('usergroup');
        if (isset($post['id']) && $post['id']) {
            $edunumber = $edu->where('id='.$post['id'])->save($post);
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