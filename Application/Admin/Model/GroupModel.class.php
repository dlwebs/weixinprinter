<?php
namespace Admin\Model;

class GroupModel extends BaseModel {

    public function getGroupInfo($group_id) {
        $data['group_id'] = $group_id;
        $data['group_status'] = 1;
        $groupInfo = $this->where($data)->find();
        return $groupInfo;
    }

    public function getGroupList($show = '') {
        $where = 'group_id != 1';
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

    public function truncateGroupAuth() {
        $where = 'group_auth != 1';
        return $this->where($where)->setField('group_auth', 0);
    }
}