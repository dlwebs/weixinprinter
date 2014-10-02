<?php
namespace Admin\Controller;

class GroupController extends BaseController {

    public function listAction(){
        $groupobj = D('group');
        $group_name = I('post.group_name');
        if($group_name && trim($group_name)){
            $groupdata = $groupobj->getGroupList('', "group_id != 1 and group_name like '%".$group_name."%'");
        }else{
            $groupdata = $groupobj->getGroupList();
        }
        $this->assign('group_name', $group_name);
        $this->assign('grouplist', $groupdata['data']);
        $this->assign('page', $groupdata['page']);
        $this->display();
    }

    public function addAction(){
        $auth = D('authrule');
        $authdata = $auth->getAuthList('all');
        $this->assign('authlist', $authdata['data']);
        $this->display();
    }

    public function modgroupAction(){
        $group_id = I('get.groupid');
        $groupobj = D("group");
        $groupinfo = $groupobj->getGroupById($group_id);
        $this->assign('groupinfo', $groupinfo);

        $auth = D('authrule');
        $authdata = $auth->getAuthList('all');
        $this->assign('authlist', $authdata['data']);
        $this->display();
    }

    public function delgroupAction(){
        $group_id = I('get.groupid');
        $groupobj = D("group");
        $groupinfo = $groupobj->getGroupById($group_id);
        if ($groupinfo) {
            $isok = $groupobj->deleteGroupById($group_id);
            if ($isok) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
        $this->error('无此组信息');
    }

    public function saveAction() {
        $post = filterAllParam('post');
        $groupobj = D('group');
        if (isset($post['id']) && $post['id']) {
            if(count($post['group_auth'])){
                $post['group_auth'] = implode(",", $post['group_auth']);
            }
            $groupnumber = $groupobj->updateGroup($post);
            $id = $post['id'];
        } else {
            if (!$post['group_name']) {
                $this->error("组名称不能为空");
            }
            $groupinfo = $groupobj->getGroupByName($post['group_name']);
            if ($groupinfo) {
                $this->error("组名称已存在");
            }
            if (count($post['group_auth'])) {
                $post['group_auth'] = implode(",", $post['group_auth']);
            }
            $id = $groupobj->addGroup($post);
        }
        if ($id) {
            $this->success('保存成功', 'list');
        } else {
            $this->error('保存失败');
        }
    }
}