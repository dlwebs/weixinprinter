<?php
namespace Admin\Model;

class AuthruleModel extends BaseModel {

    public function getAuthList($show = '') {
        $where = 'id != 1';
        if ($show == 'all') {
            $authlist = $this->where($where)->select();
            $pageinfo = array();
        } else {
            $count = $this->where($where)->count();
            $page = new \Think\Page($count, 10);
            $authlist = $this->where($where)->limit($page->firstRow.','.$page->listRows)->select();
            $pageinfo = $page->show();
        }
        return array('data' => $authlist, 'page' => $pageinfo);
    }

    public function deleteAuth() {
        $isDelete = $this->where('id != 1')->delete();
        if ($isDelete) {
            $group = D('group');
            return $group->truncateGroupAuth();
        }
        return false;
    }
}