<?php
namespace Admin\Model;

class UserModel extends BaseModel {

    public function login($userid, $password) {
        $data['user_id'] = $userid;
        $data['user_pw'] = md5($password);
        $data['user_status'] = 1;
        $userInfo = $this->where($data)->join(' wxp_usergroup g on g.uid = wxp_user.user_id')->find();
        if ($userInfo['gid']) {
            $groupobj = D('group');
            $groupInfo = $groupobj->getGroupInfo($userInfo['gid']);
            if ($groupInfo['group_status'] == 1) {
                unset($userInfo['user_pw']);
                unset($userInfo['uid']);
                unset($userInfo['gid']);
                return array_merge($userInfo, $groupInfo);
            }
        }
        return false;
    }

    public function getUserById($userid = '') {
        return $this->where('user_id = "'.$userid.'"')->find();
    }

    public function deleteUserById($userid = '') {
        return $this->where('user_id = "'.$userid.'"')->delete();
    }

    public function getUserList() {
        $count = $this->where('id != 1')->count();
        $page = new \Think\Page($count, 10);
        $userlist = $this->where('id != 1')->field('user_pw', true)->order('user_regdate')->limit($page->firstRow.','.$page->listRows)->select();
        $pageinfo = $page->show();
        return array('data' => $userlist, 'page' => $pageinfo);
    }

    public function addUser($data = array()) {
        $insert['user_id'] = $data['user_id'];
        $insert['user_name'] = $data['user_name'];
        $insert['user_status'] = $data['user_status'];
        $insert['user_pw'] = md5($data['user_pw']);
        $insert['user_regdate'] = date('Y-m-d H:i:s');
        $insert['user_printer'] = 0;
        return $this->add($insert);
    }
}