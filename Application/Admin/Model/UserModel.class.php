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

    public function getBlackList() {
        $where = 'user_follow = "unsubscribe" and user_pw = "" and resource_content != ""'; 
        $count = $this->where($where)->field('distinct(user_id) as user_id, user_weixin,user_regdate')->join(' wxp_resource on wxp_resource.resource_user = wxp_user.user_id')->count();
        $page = new \Think\Page($count, 10);
        $userlist = $this->where($where)->field('distinct(user_id) as user_id, user_weixin,user_regdate')->join(' wxp_resource on wxp_resource.resource_user = wxp_user.user_id')->limit($page->firstRow.','.$page->listRows)->select();
        $pageinfo = $page->show();
        return array('data' => $userlist, 'page' => $fanslist);
    }

    public function getUserById($userid = '') {
        return $this->where('user_id = "'.$userid.'"')->find();
    }
    
    public function getFans($user_token = '') {
        $count = $this->where('user_weixin = "'.$user_token.'"')->count();
        $page = new \Think\Page($count, 10);
        $fanslist = $this->where('user_weixin = "'.$user_token.'"')->order('user_regdate')->limit($page->firstRow.','.$page->listRows)->select();
        $pageinfo = $page->show();
        return array('data' => $userlist, 'page' => $fanslist);
    }

    public function deleteUserById($userid = '') {
        return $this->where('user_id = "'.$userid.'"')->delete();
    }

    public function getUserList($where='id != 1', $type = '') {
        if ($type == 'all') {
            $userlist = $this->where($where)->field('user_pw', true)->order('user_regdate')->select();
            return $userlist;
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $userlist = $this->where($where)->field('user_pw', true)->order('user_regdate')->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
            return array('data' => $userlist, 'page' => $pageinfo);
        }
    }
    
    public function getUserGroupList($where='id != 1', $type = ''){
        if ($type == 'all') {
            $userlist = $this->where($where)->join(' wxp_usergroup g on g.uid = wxp_user.user_id')->field('user_pw', true)->order('user_regdate')->select();
            return $userlist;
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $userlist = $this->where($where)->join(' wxp_usergroup g on g.uid = wxp_user.user_id')->field('user_pw', true)->order('user_regdate')->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
            return array('data' => $userlist, 'page' => $pageinfo);
        }
    }

    public function addUser($data = array()) {
        $insert['user_id'] = $data['user_id'];
        $insert['user_name'] = $data['user_name'];
        $insert['user_status'] = $data['user_status'];
        $insert['user_pw'] = md5($data['user_pw']);
        $insert['user_regdate'] = date('Y-m-d H:i:s');
        return $this->add($insert);
    }

    public function updateUser($data = array()) {
        unset($data['user_id']);
        if (!$data['user_pw']) {
            unset($data['user_pw']);
        } else {
            $insert['user_pw'] = md5($data['user_pw']);
        }
        $insert['user_name'] = $data['user_name'];
        $insert['user_status'] = $data['user_status'];
        return $this->where('id="'.$data['id'].'"')->save($insert);
    }
}