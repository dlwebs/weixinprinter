<?php
namespace Admin\Model;

class GroupModel extends BaseModel {

    public function getGroupInfo($group_id) {
        $data['group_id'] = $group_id;
        $data['group_status'] = 1;
        $groupInfo = $this->where($data)->find();
        return $groupInfo;
    }

    public function getGroupList($show = '', $where='group_id != 1') {
        if ($show == 'all') {
            $grouplist = $this->where($where)->select();
            $pageinfo = array();
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $grouplist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $grouplist, 'page' => $pageinfo);
    }

    public function getGroupById($groupid = '') {
        return $this->where('group_id = "'.$groupid.'"')->find();
    }

    public function getGroupByName($groupname = '') {
        return $this->where('group_name = "'.$groupname.'"')->find();
    }

    public function addGroup($data = array()) {
        $insert['group_name'] = $data['group_name'];
        $insert['group_status'] = $data['group_status'];
        $insert['group_auth'] = $data['group_auth'];
        return $this->add($insert);
    }

    public function updateGroup($data = array()) {
        $insert['group_name'] = $data['group_name'];
        $insert['group_status'] = $data['group_status'];
        $insert['group_auth'] = $data['group_auth'];
        return $this->where('group_id="'.$data['id'].'"')->save($insert);
    }

    public function deleteGroupById($groupid = '') {
        return $this->where('group_id = "'.$groupid.'"')->delete();
    }

    public function truncateGroupAuth() {
        $where = 'group_auth != 1';
        return $this->where($where)->setField('group_auth', 0);
    }
}